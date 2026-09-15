<?php
/**
 * Template Name: Over ons
 * Template Post Type: page
 *
 * DIT IS EEN SJABLOON DAT JE PER KLANT OPNIEUW ONTWERPT — zie front-page.php
 * voor dezelfde afspraak. WordPress kiest dit bestand automatisch voor de
 * pagina met slug "over-ons"; er hoeft niemand een sjabloon te kiezen.
 *
 * Bewust een andere opbouw dan een dienstpagina of de generieke page.php:
 * geen inhoudsopgave, geen ingeweven CTA's, geen zijkolom. Dit is het verhaal
 * van de klant — missie, kernwaarden, cijfers, de mensen erachter, bewijs dat
 * het werkt — met aan het eind precies één zachte uitnodiging.
 *
 * Regels die blijven staan, ongeacht het ontwerp:
 *   - precies één H1, boven in de resource-header;
 *   - elke sectie heeft een toegankelijke naam (aria-labelledby op de H2);
 *   - alle cijfers, quotes en namen komen uit inc/proof.php, nooit hardcoded.
 *
 * @package React2u
 */

get_header();

$usps = (array) react2u_get( 'usps', array() );

/*
 * Bewijs en personen komen pas op de publieke pagina als de aangeleverde data
 * compleet en bevestigd is. react2u_is_placeholder() vangt de centrale marker;
 * de extra controles sluiten ook losse redactie-markers en conceptstatussen uit.
 */
$proof_has_pending_value = static function ( mixed $value ) use ( &$proof_has_pending_value ): bool {
	if ( is_array( $value ) ) {
		foreach ( $value as $nested_value ) {
			if ( $proof_has_pending_value( $nested_value ) ) {
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

$stats = array();
foreach ( (array) react2u_get( 'stats', array() ) as $stat_index => $stat ) {
	if ( ! is_array( $stat ) ) {
		continue;
	}

	$stat['value'] = react2u_get( "stats.{$stat_index}.value", $stat['value'] ?? '' );
	$stat['label'] = react2u_get( "stats.{$stat_index}.label", $stat['label'] ?? '' );

	if ( isset( $stat['note'] ) && ! $proof_is_text( $stat['note'] ) ) {
		unset( $stat['note'] );
	}

	if (
		! $proof_has_pending_value( $stat )
		&& ! $proof_is_explicitly_unconfirmed( $stat )
		&& $proof_is_text( $stat['value'] ?? '' )
		&& 1 === preg_match( '/[1-9]/', wp_strip_all_tags( (string) ( $stat['value'] ?? '' ) ) )
		&& $proof_is_text( $stat['label'] ?? '' )
	) {
		$stats[] = $stat;
	}
}

$reviews = array_values(
	array_filter(
		(array) react2u_get( 'reviews', array() ),
		static function ( mixed $review ) use ( $proof_has_pending_value, $proof_is_explicitly_unconfirmed, $proof_is_text ): bool {
			if ( ! is_array( $review ) || $proof_has_pending_value( $review ) || $proof_is_explicitly_unconfirmed( $review ) ) {
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

$members = array_values(
	array_filter(
		(array) react2u_get( 'people.members', array() ),
		static function ( mixed $member ) use ( $proof_has_pending_value, $proof_is_explicitly_unconfirmed, $proof_is_text ): bool {
			return is_array( $member )
				&& ! $proof_has_pending_value( $member )
				&& ! $proof_is_explicitly_unconfirmed( $member )
				&& $proof_is_text( $member['name'] ?? '' )
				&& $proof_is_text( $member['role'] ?? '' );
		}
	)
);

$people_title = (string) react2u_get( 'people.title', '' );
$people_text  = (string) react2u_get( 'people.text', '' );
$people_title = $proof_is_text( $people_title ) ? $people_title : __( 'Wie je aan de lijn krijgt', 'react2u' );
$people_text  = $proof_is_text( $people_text ) ? $people_text : '';

/*
 * Alleen de openingsalinea's naast de foto — de rest van het verhaal (de
 * H2-secties) loopt eronder in een gewone leeskolom door. Alles in de smalle
 * overlap-kaart proppen liet de foto wegzakken naast een muur tekst; dat is
 * precies het "tekst-links-foto-rechts-en-noem-het-nieuw"-effect dat niet
 * de bedoeling is.
 */
$content = (string) apply_filters( 'the_content', get_the_content() );
$intro   = $content;
$rest    = '';
if ( preg_match( '/<h2[\s>]/i', $content, $m, PREG_OFFSET_CAPTURE ) ) {
	$split = $m[0][1];
	$intro = substr( $content, 0, $split );
	$rest  = substr( $content, $split );
}
?>
<main tabindex="-1" id="main" class="site-main">

	<header class="resource-header page-hero page-hero--about has-react-route">
		<div class="shell resource-header-inner">
			<?php react2u_breadcrumbs(); ?>
			<h1 class="resource-title"><?php the_title(); ?></h1>
			<?php if ( has_excerpt() ) : ?>
				<p class="resource-intro"><?php echo esc_html( get_the_excerpt() ); ?></p>
			<?php endif; ?>
			<div class="react-route react-route--hero" aria-hidden="true"><span class="react-route-signals"><i></i><i></i><i></i><i></i><i></i><i></i></span><span class="react-route-line"></span><span class="react-route-destination"></span></div>
		</div>
	</header>

	<?php /* ---- Verhaal: opening naast de foto, met de foto op volle hoogte -- */ ?>
	<section class="section section-story section-story--react" aria-labelledby="story-title">
		<h2 class="sr-only" id="story-title"><?php esc_html_e( 'Ons verhaal', 'react2u' ); ?></h2>
		<div class="shell split-inner is-story is-react-story">
			<div class="split-media react-model-media">
				<picture class="human-photo about-story-picture">
					<source media="(max-width: 960px)" srcset="<?php echo esc_url( REACT2U_URI . '/assets/images/over-ons-team-higgsfield-v2-960.webp' ); ?>">
					<img class="about-story-photo" <?php echo react2u_quality_image_attrs( REACT2U_URI . '/assets/images/over-ons-team-higgsfield-v2.webp' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> width="1920" height="1080" alt="<?php esc_attr_e( 'Een divers team overlegt informeel rond een tafel in een lichte werkomgeving.', 'react2u' ); ?>" loading="lazy" fetchpriority="low" decoding="async">
				</picture>
				<img class="about-react-wheel" <?php echo react2u_quality_image_attrs( REACT2U_URI . '/assets/images/react-wiel.png' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> width="1024" height="1024" alt="" loading="lazy" fetchpriority="low" decoding="async" aria-hidden="true">
				<div class="react-route react-route--about" aria-hidden="true"><span class="react-route-signals"><i></i><i></i><i></i><i></i><i></i><i></i></span><span class="react-route-line"></span><span class="react-route-destination"></span></div>
			</div>
			<div class="split-copy entry-content story-copy">
				<?php echo $intro; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
		</div>
	</section>

	<?php if ( '' !== $rest ) : ?>
		<section class="section section-story-rest" aria-label="<?php esc_attr_e( 'Verder over React2u', 'react2u' ); ?>">
			<div class="shell">
				<div class="entry-content article-column"><?php echo $rest; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
			</div>
		</section>
	<?php endif; ?>

	<?php /* ---- Kernwaarden ----------------------------------------------- */ ?>
	<?php if ( $usps ) : ?>
		<section class="section section-values" aria-labelledby="values-title">
			<div class="shell">
				<?php
				react2u_section_heading(
					array(
						'eyebrow' => __( 'Waar we voor staan', 'react2u' ),
						'title'   => __( 'Onze kernwaarden', 'react2u' ),
						'id'      => 'values-title',
					)
				);
				?>
				<ul class="usp-list">
					<?php foreach ( $usps as $usp ) : ?>
						<li>
							<?php echo react2u_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<?php echo react2u_text( $usp ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</section>
	<?php endif; ?>

	<?php /* ---- Cijfers --------------------------------------------------- */ ?>
	<?php if ( $stats ) : ?>
		<section class="section section-stats" aria-label="<?php esc_attr_e( 'In cijfers', 'react2u' ); ?>">
			<div class="shell"><?php react2u_stats( array( 'items' => $stats, 'notes' => true ) ); ?></div>
		</section>
	<?php endif; ?>

	<?php /* ---- De mensen ------------------------------------------------- */ ?>
	<?php if ( $members ) : ?>
		<section class="section section-team" aria-labelledby="team-title">
			<div class="shell">
				<?php
				react2u_section_heading(
					array(
						'eyebrow' => __( 'De mensen', 'react2u' ),
						'title'   => $people_title,
						'text'    => $people_text,
						'id'      => 'team-title',
					)
				);
				?>
				<div class="team-grid">
					<?php foreach ( $members as $member ) : ?>
						<?php react2u_person( array( 'person' => $member, 'layout' => 'card', 'phone' => false ) ); ?>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php /* ---- Reviews --------------------------------------------------- */ ?>
	<?php if ( $reviews ) : ?>
		<section class="section section-reviews" aria-labelledby="reviews-title">
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
					<?php foreach ( $reviews as $review ) : ?>
						<?php react2u_review( array( 'review' => $review ) ); ?>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php /* ---- Eén zachte uitnodiging, geen harde offerte-CTA ------------- */ ?>
	<section class="section section-final-cta" aria-label="<?php esc_attr_e( 'Kennismaken', 'react2u' ); ?>">
		<div class="shell"><?php react2u_cta( array( 'variant' => 'callback', 'tone' => 'soft', 'rating' => true, 'secondary' => false ) ); ?></div>
	</section>

</main>
<?php
get_footer();
