<?php
/**
 * Gedeelde detailpagina voor de diensten van React2u.
 *
 * De redactionele inhoud blijft de bron: we laten die door the_content en de
 * bestaande inhoudshelpers lopen. Alleen wanneer beide rolhoofdstukken echt in
 * de inhoud staan, worden ze uit die stroom gelicht en als één tweepaneelblok
 * teruggeplaatst. Zo blijft alle aangeleverde tekst behouden zonder duplicatie.
 *
 * @package React2u
 */

get_header();

$all_services = (array) react2u_get( 'services', array() );
$steps        = (array) react2u_get( 'steps', array() );
?>
<main id="main" class="site-main">
	<?php
	while ( have_posts() ) :
		the_post();

		$post_id       = (int) get_the_ID();
		$service       = react2u_service_for_post( $post_id );
		$service       = is_array( $service ) ? $service : array();
		$service_slug  = sanitize_html_class( (string) ( $service['slug'] ?? '' ) );
		$service_css   = '' !== $service_slug ? ' service-card--' . $service_slug : '';
		$service_title = (string) ( $service['title'] ?? get_the_title() );
		$service_text  = trim( (string) ( $service['text'] ?? '' ) );
		$hero_intro    = has_excerpt() ? trim( get_the_excerpt() ) : $service_text;
		$ctas          = react2u_content_ctas( $post_id );

		$prepared = react2u_prepare_content( (string) apply_filters( 'the_content', get_the_content() ) );

		/* Tel voor de CTA-posities de oorspronkelijke H2's, inclusief de rolhoofdstukken. */
		$content = react2u_weave_ctas( $prepared['content'], 'quote', 'callback' );

		/*
		 * De twee rolhoofdstukken worden alleen verplaatst als ze allebei aanwezig
		 * zijn. Een losse of anders bedoelde kop blijft daardoor altijd gewoon in
		 * de redactionele inhoud staan.
		 */
		$comparison_candidates = array();
		if ( preg_match_all( '#(<h2\b[^>]*>(.*?)</h2>)(.*?)(?=<h2\b|$)#is', $content, $sections, PREG_SET_ORDER ) ) {
			foreach ( $sections as $section ) {
				$heading = trim( html_entity_decode( wp_strip_all_tags( $section[2] ), ENT_QUOTES | ENT_HTML5, get_bloginfo( 'charset' ) ?: 'UTF-8' ) );
				$id      = '';
				$key     = '';
				if ( preg_match( '#\bid\s*=\s*(["\'])([^"\']+)\1#i', $section[1], $id_match ) ) {
					$id = $id_match[2];
				}

				if ( preg_match( '/wat\s+doet\s+de\s+werkgever/iu', $heading ) ) {
					$key = 'employer';
				} elseif ( preg_match( '/wat\s+neemt\s+react2u\s+uit\s+handen/iu', $heading ) ) {
					$key = 'react2u';
				}

				if ( '' !== $key && '' !== trim( wp_strip_all_tags( $section[3] ) ) ) {
					$comparison_candidates[ $key ] = array(
						'title'   => $heading,
						'id'      => $id,
						'content' => trim( $section[3] ),
						'chunk'   => $section[0],
					);
				}
			}
		}

		$comparison = array();
		if ( isset( $comparison_candidates['employer'], $comparison_candidates['react2u'] ) ) {
			$comparison = $comparison_candidates;
			$content    = str_replace(
				array( $comparison['employer']['chunk'], $comparison['react2u']['chunk'] ),
				'',
				$content
			);
		}

		/*
		 * Korte verwachtingensamenvatting, uitsluitend afgeleid uit de H3-koppen
		 * onder het bestaande hoofdstuk "Dit kun je van ons verwachten".
		 */
		$expectations = array();
		if ( preg_match_all( '#<h2\b[^>]*>(.*?)</h2>(.*?)(?=<h2\b|$)#is', $prepared['content'], $sections, PREG_SET_ORDER ) ) {
			foreach ( $sections as $section ) {
				$heading = trim( wp_strip_all_tags( $section[1] ) );
				if ( ! preg_match( '/kun\s+je\s+van\s+ons\s+verwachten/iu', $heading ) ) {
					continue;
				}

				if ( preg_match_all( '#<h3\b[^>]*>(.*?)</h3>#is', $section[2], $subheadings ) ) {
					foreach ( array_slice( $subheadings[1], 0, 4 ) as $subheading ) {
						$expectation = trim( wp_strip_all_tags( $subheading ) );
						if ( '' !== $expectation ) {
							$expectations[] = $expectation;
						}
					}
				}
				break;
			}
		}

		$body = $content;

		/* Verwijder uit de inhoudsopgave de H2's die als rolpanelen terugkomen. */
		$toc = array_values(
			array_filter(
				$prepared['toc'],
				static function ( array $item ) use ( $content ): bool {
					$id = (string) ( $item['id'] ?? '' );
					if ( '' === $id ) {
						return false;
					}

					$pattern = '#\bid\s*=\s*(["\'])' . preg_quote( $id, '#' ) . '\1#i';
					return 1 === preg_match( $pattern, $content );
				}
			)
		);

		/* Drie volgende diensten, in de vaste volgorde uit inc/proof.php. */
		$related       = array();
		$current_index = null;
		foreach ( $all_services as $index => $candidate ) {
			if ( (string) ( $candidate['slug'] ?? '' ) === $service_slug ) {
				$current_index = (int) $index;
				break;
			}
		}
		$service_count = count( $all_services );
		if ( $service_count > 1 ) {
			for ( $offset = 1; $offset < $service_count && count( $related ) < 3; $offset++ ) {
				$index     = null === $current_index ? $offset - 1 : ( $current_index + $offset ) % $service_count;
				$candidate = (array) ( $all_services[ $index ] ?? array() );
				if ( (string) ( $candidate['slug'] ?? '' ) !== $service_slug ) {
					$related[] = $candidate;
				}
			}
		}
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'resource dienst-detail' . $service_css ); ?> data-variant="dienst">
			<header class="dienst-hero resource-header tone-dark" aria-labelledby="dienst-title">
				<div class="shell dienst-hero-inner">
					<div class="dienst-hero-copy">
						<?php react2u_breadcrumbs(); ?>
						<p class="eyebrow"><span class="eyebrow-dot" aria-hidden="true"></span><?php esc_html_e( 'Dienst van React2u', 'react2u' ); ?></p>
						<h1 class="dienst-hero-title resource-title" id="dienst-title"><?php the_title(); ?></h1>
						<?php if ( '' !== $hero_intro ) : ?>
							<p class="dienst-hero-intro resource-intro"><?php echo esc_html( $hero_intro ); ?></p>
						<?php endif; ?>
						<div class="dienst-hero-actions">
							<a class="button button-primary" href="<?php echo esc_url( react2u_cta_url( 'quote' ) ); ?>"><?php echo esc_html( react2u_cta_label( 'quote' ) ); ?></a>
							<a class="button button-outline-light" href="<?php echo esc_url( react2u_cta_url( 'pricing' ) ); ?>"><?php echo esc_html( react2u_cta_label( 'pricing' ) ); ?></a>
						</div>
					</div>

					<figure class="dienst-hero-model">
						<img src="<?php echo esc_url( REACT2U_URI . '/assets/images/react-wiel.png' ); ?>" width="1024" height="1024" alt="" fetchpriority="high" decoding="async">
						<figcaption class="dienst-banner-label">
							<span class="dienst-banner-label-kicker"><?php esc_html_e( 'De REACT-aanpak', 'react2u' ); ?></span>
							<strong><?php esc_html_e( 'Results · Expertise · Attention · Coaching · Together', 'react2u' ); ?></strong>
						</figcaption>
					</figure>
				</div>
			</header>

			<?php if ( '' !== $service_text || $expectations ) : ?>
				<section class="dienst-kern" aria-labelledby="dienst-kern-title">
					<div class="shell dienst-kern-inner">
						<header class="dienst-kern-heading">
							<p class="eyebrow"><span class="eyebrow-dot" aria-hidden="true"></span><?php esc_html_e( 'In het kort', 'react2u' ); ?></p>
							<h2 id="dienst-kern-title"><?php esc_html_e( 'Dit kun je van React2u verwachten', 'react2u' ); ?></h2>
						</header>

						<?php if ( '' !== $service_text ) : ?>
							<p class="dienst-kern-belofte"><?php echo react2u_text( $service_text ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
						<?php endif; ?>

						<?php if ( $expectations ) : ?>
							<ul class="dienst-kern-punten" role="list">
								<?php foreach ( $expectations as $expectation ) : ?>
									<li><span class="service-dot" aria-hidden="true"></span><?php echo esc_html( $expectation ); ?></li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</div>
				</section>
			<?php endif; ?>

			<div class="shell resource-body dienst-inhoud-layout">
				<div class="resource-main dienst-inhoud">
					<?php react2u_toc( $toc ); ?>
					<div class="entry-content dienst-entry-content"><?php echo $body; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- al door the_content-filters. ?></div>
				</div>

				<aside class="resource-aside dienst-contact-aside" aria-label="<?php esc_attr_e( 'Direct contact', 'react2u' ); ?>">
					<div class="aside-inner">
						<?php
						react2u_cta(
							array(
								'variant' => 'quote',
								'layout'  => 'aside',
								'tone'    => 'light',
								'heading' => 'p',
								'rating'  => true,
								'person'  => true,
								'eyebrow' => __( 'Hulp nodig?', 'react2u' ),
								'text'    => __( 'Vertel wat je nodig hebt. We reageren binnen één werkdag.', 'react2u' ),
							)
						);
						?>
					</div>
				</aside>
			</div>

			<?php if ( $comparison ) : ?>
				<?php
				$employer_heading_id = '' !== $comparison['employer']['id'] ? $comparison['employer']['id'] : 'dienst-werkgever-title';
				$react2u_heading_id   = '' !== $comparison['react2u']['id'] ? $comparison['react2u']['id'] : 'dienst-react2u-title';
				?>
				<section class="dienst-rolverdeling" aria-labelledby="dienst-rolverdeling-title">
					<div class="shell">
						<header class="dienst-section-heading">
							<p class="eyebrow"><span class="eyebrow-dot" aria-hidden="true"></span><?php esc_html_e( 'Samen aan de slag', 'react2u' ); ?></p>
							<h2 id="dienst-rolverdeling-title"><?php esc_html_e( 'Wat jij doet en wat React2u uit handen neemt', 'react2u' ); ?></h2>
						</header>
						<div class="dienst-rolverdeling-grid">
							<section class="dienst-rolpaneel dienst-rolpaneel--werkgever" aria-labelledby="<?php echo esc_attr( $employer_heading_id ); ?>">
								<h3 id="<?php echo esc_attr( $employer_heading_id ); ?>"><?php echo esc_html( $comparison['employer']['title'] ); ?></h3>
								<div class="dienst-rolpaneel-content"><?php echo $comparison['employer']['content']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- onderdeel van gefilterde the_content. ?></div>
							</section>
							<section class="dienst-rolpaneel dienst-rolpaneel--react2u" aria-labelledby="<?php echo esc_attr( $react2u_heading_id ); ?>">
								<h3 id="<?php echo esc_attr( $react2u_heading_id ); ?>"><?php echo esc_html( $comparison['react2u']['title'] ); ?></h3>
								<div class="dienst-rolpaneel-content"><?php echo $comparison['react2u']['content']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- onderdeel van gefilterde the_content. ?></div>
							</section>
						</div>
					</div>
				</section>
			<?php endif; ?>

			<?php if ( $steps ) : ?>
				<section class="dienst-react-route tone-dark" aria-labelledby="dienst-route-title">
					<div class="shell dienst-react-route-inner">
						<header class="dienst-react-route-heading">
							<p class="eyebrow"><span class="eyebrow-dot" aria-hidden="true"></span><?php esc_html_e( 'Zo werkt het', 'react2u' ); ?></p>
							<h2 id="dienst-route-title"><?php esc_html_e( 'De REACT-route naar een duidelijk plan', 'react2u' ); ?></h2>
						</header>
						<ol class="dienst-react-stappen">
							<?php foreach ( $steps as $index => $step ) : ?>
								<li class="dienst-react-stap">
									<span class="dienst-react-stap-nummer" aria-hidden="true"><?php echo esc_html( str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
									<h3><?php echo react2u_text( $step['title'] ?? '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></h3>
									<p><?php echo react2u_text( $step['text'] ?? '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
								</li>
							<?php endforeach; ?>
						</ol>
					</div>
				</section>
			<?php endif; ?>

			<?php if ( '' !== $prepared['faq'] ) : ?>
				<div class="shell resource-faq dienst-faq">
					<?php react2u_faq( $prepared['faq'] ); ?>
				</div>
			<?php endif; ?>

			<?php if ( $related ) : ?>
				<section class="section section-related dienst-verwant" aria-labelledby="dienst-verwant-title">
					<div class="shell">
						<header class="dienst-section-heading">
							<p class="eyebrow"><span class="eyebrow-dot" aria-hidden="true"></span><?php esc_html_e( 'Bekijk ook', 'react2u' ); ?></p>
							<h2 id="dienst-verwant-title"><?php esc_html_e( 'Andere diensten die kunnen aansluiten', 'react2u' ); ?></h2>
						</header>
						<ul class="diensten-kaarten diensten-kaarten--verwant" role="list">
							<?php foreach ( $related as $candidate ) : ?>
								<?php $candidate_slug = sanitize_html_class( (string) ( $candidate['slug'] ?? '' ) ); ?>
								<li class="diensten-kaart service-card service-card--<?php echo esc_attr( $candidate_slug ); ?>">
									<a class="diensten-kaart-link" href="<?php echo esc_url( home_url( (string) ( $candidate['path'] ?? '' ) ) ); ?>">
										<span class="service-dot" aria-hidden="true"></span>
										<h3 class="diensten-kaart-title service-title"><?php echo react2u_text( $candidate['title'] ?? '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></h3>
										<p class="diensten-kaart-text service-text"><?php echo react2u_text( $candidate['text'] ?? '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
										<span class="diensten-kaart-action"><?php esc_html_e( 'Bekijk deze dienst', 'react2u' ); ?> <?php echo react2u_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				</section>
			<?php endif; ?>

			<section class="resource-final-cta dienst-final-cta" aria-label="<?php esc_attr_e( 'Volgende stap', 'react2u' ); ?>">
				<div class="shell"><?php react2u_cta( array( 'variant' => 'quote', 'layout' => 'section', 'tone' => 'dark', 'rating' => true ) ); ?></div>
			</section>
		</article>

		<?php if ( $ctas ) : ?>
			<div class="sticky-cta dienst-mobile-cta" data-sticky-cta hidden>
				<div class="shell sticky-cta-inner">
					<p class="sticky-cta-text">
						<strong><?php echo esc_html( $service_title ); ?></strong>
						<span><?php esc_html_e( 'Bespreek je vraag met React2u.', 'react2u' ); ?></span>
					</p>
					<a class="button button-primary button-small" href="<?php echo esc_url( (string) $ctas[0]['url'] ); ?>"><?php echo esc_html( (string) $ctas[0]['label'] ); ?></a>
					<button class="sticky-cta-close" type="button" data-sticky-close>
						<span class="sr-only"><?php esc_html_e( 'Sluiten', 'react2u' ); ?></span>
						<?php echo react2u_icon( 'close' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</button>
				</div>
			</div>
		<?php endif; ?>
		<?php
	endwhile;
	?>
</main>
<?php
get_footer();
