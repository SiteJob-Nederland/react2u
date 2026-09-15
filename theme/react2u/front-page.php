<?php
/**
 * Homepage.
 *
 * De homepage maakt van het eigen REACT-model de visuele route: van losse
 * signalen naar een helder plan en duurzame terugkeer. De zes diensten en de
 * vier processtappen komen uit inc/proof.php, zodat inhoud en links overal
 * gelijk blijven.
 *
 * Bewijsmateriaal verschijnt alleen wanneer de klant het echt heeft ingevuld.
 * Lege, nul-, placeholder- en expliciet onbevestigde items worden vóór het
 * renderen verwijderd, zodat er nooit een lege bewijssectie overblijft.
 *
 * @package React2u
 */

get_header();

$services = (array) react2u_get( 'services', array() );
$steps    = (array) react2u_get( 'steps', array() );
$usps     = (array) react2u_get( 'usps', array() );
$service_icons = array(
	'wvp'        => 'route',
	'erd'        => 'map',
	'preventie'  => 'health',
	'coaching'   => 'chat',
	'trainingen' => 'edit',
	'risico'     => 'balance',
);

/*
 * Proof-data mag via react2u_config() of een filter worden aangeleverd. Omdat
 * de blokken zelf placeholders zichtbaar maken, schonen we de collecties hier
 * eerst op: op de publieke homepage is alleen daadwerkelijk bewijs welkom.
 */
$proof_has_placeholder = static function ( mixed $value ) use ( &$proof_has_placeholder ): bool {
	if ( is_array( $value ) ) {
		foreach ( $value as $nested_value ) {
			if ( $proof_has_placeholder( $nested_value ) ) {
				return true;
			}
		}
		return false;
	}

	if ( ! is_string( $value ) ) {
		return false;
	}

	$text = strtolower( trim( wp_strip_all_tags( $value ) ) );
	return react2u_is_placeholder( $value )
		|| 1 === preg_match( '/\[(?:placeholder|onbevestigd|unconfirmed|unverified)\]/i', $value )
		|| in_array( $text, array( 'placeholder', 'tbd', 'todo', 'n.t.b.', 'ntb', 'nog aan te leveren', 'nog in te vullen' ), true );
};

$proof_is_explicitly_unconfirmed = static function ( array $item ): bool {
	foreach ( array( 'placeholder', 'is_placeholder' ) as $flag ) {
		if ( array_key_exists( $flag, $item ) && true === filter_var( $item[ $flag ], FILTER_VALIDATE_BOOLEAN ) ) {
			return true;
		}
	}

	foreach ( array( 'verified', 'confirmed', 'is_verified', 'is_confirmed' ) as $flag ) {
		if ( array_key_exists( $flag, $item ) && true !== filter_var( $item[ $flag ], FILTER_VALIDATE_BOOLEAN ) ) {
			return true;
		}
	}

	$status = strtolower( trim( (string) ( $item['status'] ?? '' ) ) );
	return in_array( $status, array( '0', 'concept', 'draft', 'onbevestigd', 'pending', 'placeholder', 'todo', 'unconfirmed', 'unverified' ), true );
};

$proof_is_text = static function ( mixed $value ): bool {
	if ( ! is_scalar( $value ) || react2u_is_placeholder( $value ) ) {
		return false;
	}

	$text = trim( wp_strip_all_tags( (string) $value ) );
	if ( '' === $text ) {
		return false;
	}

	return ! in_array( strtolower( $text ), array( '0', 'label', 'placeholder', 'tbd', 'todo', 'n.t.b.', 'ntb', 'nog aan te leveren', 'nog in te vullen', 'toelichting van één zin.' ), true );
};

$proof_is_positive_measure = static function ( mixed $value ) use ( $proof_is_text ): bool {
	return $proof_is_text( $value ) && 1 === preg_match( '/[1-9]/', wp_strip_all_tags( (string) $value ) );
};

$integrations = array();
foreach ( (array) react2u_get( 'integrations', array() ) as $integration ) {
	if ( is_array( $integration ) ) {
		if ( $proof_is_explicitly_unconfirmed( $integration ) || $proof_has_placeholder( $integration ) ) {
			continue;
		}

		$integration = $integration['label'] ?? $integration['name'] ?? $integration['title'] ?? '';
	}

	if ( $proof_is_text( $integration ) ) {
		$integrations[] = trim( wp_strip_all_tags( (string) $integration ) );
	}
}
$integrations = array_values( array_unique( $integrations ) );

$stats = array();
foreach ( (array) react2u_get( 'stats', array() ) as $stat_index => $stat ) {
	if ( ! is_array( $stat ) ) {
		continue;
	}

	/* Customizer-overrides bestaan op leaf-paden, niet op de hele stats-array. */
	$stat['value'] = react2u_get( "stats.{$stat_index}.value", $stat['value'] ?? '' );
	$stat['label'] = react2u_get( "stats.{$stat_index}.label", $stat['label'] ?? '' );

	if ( isset( $stat['note'] ) && ! $proof_is_text( $stat['note'] ) ) {
		unset( $stat['note'] );
	}

	if (
		! $proof_has_placeholder( $stat )
		&& ! $proof_is_explicitly_unconfirmed( $stat )
		&& $proof_is_positive_measure( $stat['value'] ?? '' )
		&& $proof_is_text( $stat['label'] ?? '' )
	) {
		$stats[] = $stat;
	}
}

$reviews = array_values(
	array_filter(
		(array) react2u_get( 'reviews', array() ),
		static function ( mixed $review ) use ( $proof_has_placeholder, $proof_is_explicitly_unconfirmed, $proof_is_text ): bool {
			if ( ! is_array( $review ) || $proof_has_placeholder( $review ) || $proof_is_explicitly_unconfirmed( $review ) ) {
				return false;
			}

			$rating = filter_var( str_replace( ',', '.', (string) ( $review['rating'] ?? '' ) ), FILTER_VALIDATE_FLOAT );
			return $proof_is_text( $review['quote'] ?? '' )
				&& $proof_is_text( $review['name'] ?? '' )
				&& false !== $rating
				&& $rating > 0
				&& $rating <= 5;
		}
	)
);

$cases = array_values(
	array_filter(
		(array) react2u_get( 'cases', array() ),
		static function ( mixed $case ) use ( $proof_has_placeholder, $proof_is_explicitly_unconfirmed, $proof_is_positive_measure, $proof_is_text ): bool {
			return is_array( $case )
				&& ! $proof_has_placeholder( $case )
				&& ! $proof_is_explicitly_unconfirmed( $case )
				&& $proof_is_text( $case['sector'] ?? '' )
				&& $proof_is_positive_measure( $case['metric'] ?? '' )
				&& $proof_is_text( $case['metric_label'] ?? '' )
				&& $proof_is_text( $case['title'] ?? '' )
				&& $proof_is_text( $case['text'] ?? '' );
		}
	)
);
?>
<main tabindex="-1" id="main" class="site-main home-main">

	<?php /* ---- Hero: één belofte en één primaire vervolgstap --------------- */ ?>
	<section class="hero home-hero tone-dark has-react-route" aria-labelledby="hero-title">
		<div class="hero-grid home-hero-grid">
			<div class="shell hero-copy-shell home-hero-inner">
				<div class="hero-copy home-hero-copy">
					<p class="eyebrow"><span class="eyebrow-dot" aria-hidden="true"></span><?php esc_html_e( 'De persoonlijke arbodienstverlener', 'react2u' ); ?></p>

					<h1 class="hero-title home-hero-title" id="hero-title"><?php esc_html_e( 'Jouw mensen, onze aandacht', 'react2u' ); ?></h1>

					<p class="hero-lead home-hero-lead"><?php esc_html_e( 'Grip op ziekteverzuim. Menselijke begeleiding die werkt. React2u ondersteunt werkgevers bij verzuimbegeleiding en re-integratie — preventief en curatief, met persoonlijke aandacht en duidelijke structuur.', 'react2u' ); ?></p>

					<div class="hero-actions home-hero-actions">
						<a class="button button-primary" href="<?php echo esc_url( react2u_cta_url( 'quote' ) ); ?>"><?php echo esc_html( react2u_cta_label( 'quote' ) ); ?></a>
					</div>

					<?php if ( $usps ) : ?>
						<ul class="usp-list home-hero-usps" role="list">
							<?php foreach ( $usps as $usp ) : ?>
								<li>
									<?php echo react2u_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
									<?php echo react2u_text( $usp ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</div>

				<div class="hero-media home-hero-media">
					<picture class="human-photo home-hero-picture">
						<source media="(max-width: 960px)" srcset="<?php echo esc_url( REACT2U_URI . '/assets/images/hero-mensen-higgsfield-v2-960.webp' ); ?>">
						<img class="home-hero-human-image" <?php echo react2u_quality_image_attrs( REACT2U_URI . '/assets/images/hero-mensen-higgsfield-v2.webp' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> width="1920" height="1080" alt="<?php esc_attr_e( 'Een adviseur en manager lopen door een kantoor waar collega’s aan het werk zijn.', 'react2u' ); ?>" loading="eager" fetchpriority="high" decoding="async">
					</picture>
					<div class="react-route react-route--hero react-route--home-hero" aria-hidden="true">
						<span class="react-route-signals"><i></i><i></i><i></i><i></i><i></i><i></i></span>
						<span class="react-route-line"></span>
						<span class="react-route-destination"></span>
					</div>
				</div>
			</div>
		</div>
	</section>

	<?php /* ---- Echte koppelingen: nooit een lege woordmerkenrij ----------- */ ?>
	<?php if ( $integrations ) : ?>
		<section class="section section-logos home-integrations" aria-label="<?php esc_attr_e( 'Waar we mee werken', 'react2u' ); ?>">
			<div class="shell">
				<?php react2u_logo_row( array( 'items' => $integrations, 'label' => __( 'Werkt samen met', 'react2u' ) ) ); ?>
			</div>
		</section>
	<?php endif; ?>

	<?php /* ---- Diensten: zes herkenbare, volledig klikbare diensten -------- */ ?>
	<?php if ( $services ) : ?>
		<section class="section section-services home-services diensten-aanbod diensten-aanbod--visual" aria-labelledby="services-title">
			<div class="shell">
				<header class="diensten-section-heading home-section-heading">
					<p class="eyebrow"><span class="eyebrow-dot" aria-hidden="true"></span><?php esc_html_e( 'Onze diensten', 'react2u' ); ?></p>
					<h2 id="services-title"><?php esc_html_e( 'Waar kunnen we je bij helpen?', 'react2u' ); ?></h2>
					<p><?php esc_html_e( 'Van preventie tot re-integratie: bekijk hoe we jouw organisatie en medewerkers kunnen ondersteunen.', 'react2u' ); ?></p>
				</header>

				<ul class="diensten-kaarten home-diensten-grid" role="list">
					<?php foreach ( $services as $index => $service ) : ?>
						<?php
						$service_slug = sanitize_html_class( (string) ( $service['slug'] ?? '' ) );
						$service_path = (string) ( $service['path'] ?? '' );
						?>
							<li class="diensten-kaart service-card service-card--<?php echo esc_attr( $service_slug ); ?>">
								<a class="diensten-kaart-link" href="<?php echo esc_url( home_url( $service_path ) ); ?>">
									<span class="diensten-kaart-nummer" aria-hidden="true"><?php echo esc_html( str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
									<span class="diensten-kaart-icon" aria-hidden="true"><?php echo react2u_icon( $service_icons[ $service_slug ] ?? 'arrow', array( 'stroke' => '1.55' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
									<h3 class="diensten-kaart-title service-title"><?php echo react2u_text( $service['title'] ?? '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></h3>
								<p class="diensten-kaart-text service-text"><?php echo react2u_text( $service['text'] ?? '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
								<span class="diensten-kaart-action">
									<?php esc_html_e( 'Bekijk deze dienst', 'react2u' ); ?>
									<?php echo react2u_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</span>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</section>
	<?php endif; ?>

	<?php /* ---- Gevalideerde cijfers: alleen niet-nul bewijs ---------------- */ ?>
	<?php if ( $stats ) : ?>
		<section class="section section-stats home-stats" aria-label="<?php esc_attr_e( 'In cijfers', 'react2u' ); ?>">
			<div class="shell"><?php react2u_stats( array( 'items' => $stats, 'notes' => true ) ); ?></div>
		</section>
	<?php endif; ?>

	<?php /* ---- Proces: inhoud uit de echte, terugkerende klanttaal --------- */ ?>
	<?php if ( $steps ) : ?>
		<section class="section home-react-route dienst-react-route tone-dark" aria-labelledby="home-route-title">
			<div class="shell home-react-route-inner dienst-react-route-inner">
				<div class="home-react-route-lead">
					<header class="home-react-route-heading dienst-react-route-heading">
						<p class="eyebrow"><span class="eyebrow-dot" aria-hidden="true"></span><?php esc_html_e( 'Onze werkwijze', 'react2u' ); ?></p>
						<h2 id="home-route-title"><?php esc_html_e( 'Dit is React2u!', 'react2u' ); ?></h2>
						<p><?php esc_html_e( 'Bij React2u staat de medewerker centraal in het verzuim- en re-integratietraject. We combineren deskundigheid in wet- en regelgeving met persoonlijke aandacht, zodat werkgever en werknemer samen werken aan duurzaam herstel.', 'react2u' ); ?></p>
					</header>

					<figure class="home-react-model model-media">
						<img <?php echo react2u_quality_image_attrs( REACT2U_URI . '/assets/images/react-wiel.png' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> width="1024" height="1024" alt="<?php esc_attr_e( 'Het REACT-model van React2u: Results, Expertise, Attention, Coaching, Together', 'react2u' ); ?>" loading="lazy" fetchpriority="low" decoding="async">
					</figure>
				</div>

				<div class="react-route react-route--process" aria-hidden="true">
					<span class="react-route-signals"><i></i><i></i><i></i><i></i><i></i><i></i></span>
					<span class="react-route-line"></span>
					<span class="react-route-destination"></span>
				</div>

				<ol class="home-react-stappen dienst-react-stappen">
					<?php foreach ( $steps as $index => $step ) : ?>
						<li class="home-react-stap dienst-react-stap">
							<span class="dienst-react-stap-nummer" aria-hidden="true"><?php echo esc_html( str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
							<h3><?php echo react2u_text( $step['title'] ?? '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></h3>
							<p><?php echo react2u_text( $step['text'] ?? '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
						</li>
					<?php endforeach; ?>
				</ol>
			</div>
		</section>
	<?php endif; ?>

	<?php /* ---- Gevalideerde klantreviews ----------------------------------- */ ?>
	<?php if ( $reviews ) : ?>
		<section class="section section-reviews home-reviews" aria-labelledby="reviews-title">
			<div class="shell">
				<?php
				react2u_section_heading(
					array(
						'eyebrow' => __( 'Klanten', 'react2u' ),
						'title'   => __( 'Wat klanten ervan vinden', 'react2u' ),
						'id'      => 'reviews-title',
					)
				);
				?>
				<div class="review-grid">
					<?php foreach ( $reviews as $index => $review ) : ?>
						<?php react2u_review( array( 'review' => $review, 'size' => 0 === $index ? 'feature' : 'default' ) ); ?>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php /* ---- Gevalideerde klantcases ------------------------------------- */ ?>
	<?php if ( $cases ) : ?>
		<section class="section section-cases home-cases" aria-labelledby="cases-title">
			<div class="shell">
				<?php
				react2u_section_heading(
					array(
						'eyebrow' => __( 'Cases', 'react2u' ),
						'title'   => __( 'Wat het oplevert', 'react2u' ),
						'id'      => 'cases-title',
					)
				);
				?>
				<div class="case-grid">
					<?php foreach ( $cases as $case ) : ?>
						<?php react2u_case_card( array( 'case' => $case ) ); ?>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php
	/* ---- Laatste artikelen: alleen tonen wanneer er echte content is ------ */
	$latest = new WP_Query(
		array(
			'post_type'           => REACT2U_ARTICLE_TYPES,
			'posts_per_page'      => 3,
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		)
	);

	if ( $latest->have_posts() ) :
		?>
		<section class="section section-latest" aria-labelledby="latest-title">
			<div class="shell">
				<?php
				react2u_section_heading(
					array(
						'eyebrow' => __( 'Kennis', 'react2u' ),
						'title'   => __( 'Laatst geschreven', 'react2u' ),
						'id'      => 'latest-title',
					)
				);
				?>
				<div class="card-grid">
					<?php
					while ( $latest->have_posts() ) :
						$latest->the_post();
						get_template_part( 'template-parts/content/card', null, array( 'post_id' => get_the_ID() ) );
					endwhile;
					wp_reset_postdata();
					?>
				</div>
			</div>
		</section>
		<?php
	endif;
	?>

	<?php /* ---- Afsluitende CTA: geen onbevestigde rating ------------------ */ ?>
	<section class="section section-final-cta home-final-cta" aria-label="<?php esc_attr_e( 'Volgende stap', 'react2u' ); ?>">
		<div class="shell">
			<?php
			react2u_cta(
				array(
					'variant'   => 'quote',
					'tone'      => 'dark',
					'secondary' => '',
					'rating'    => false,
					'eyebrow'   => __( 'Volgende stap', 'react2u' ),
					'title'     => __( 'Bespreek wat jouw organisatie nodig heeft', 'react2u' ),
					'text'      => __( 'We luisteren naar je vraag en brengen samen de passende route in kaart.', 'react2u' ),
				)
			);
			?>
		</div>
	</section>

</main>
<?php
get_footer();
