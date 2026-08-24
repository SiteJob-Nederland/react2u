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

$usps    = (array) react2u_get( 'usps', array() );
$members = (array) react2u_get( 'people.members', array() );
$reviews = (array) react2u_get( 'reviews', array() );

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
<main id="main" class="site-main">

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
				<img src="<?php echo esc_url( REACT2U_URI . '/assets/images/react-wiel.png' ); ?>" width="1024" height="1024" alt="<?php esc_attr_e( 'Het REACT-model van React2u: Results, Expertise, Attention, Coaching, Together', 'react2u' ); ?>" loading="lazy">
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
	<section class="section section-stats" aria-label="<?php esc_attr_e( 'In cijfers', 'react2u' ); ?>">
		<div class="shell"><?php react2u_stats( array( 'notes' => true ) ); ?></div>
	</section>

	<?php /* ---- De mensen ------------------------------------------------- */ ?>
	<?php if ( $members ) : ?>
		<section class="section section-team" aria-labelledby="team-title">
			<div class="shell">
				<?php
				react2u_section_heading(
					array(
						'eyebrow' => __( 'De mensen', 'react2u' ),
						'title'   => (string) react2u_get( 'people.title', __( 'Wie je aan de lijn krijgt', 'react2u' ) ),
						'text'    => (string) react2u_get( 'people.text', '' ),
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
					<?php foreach ( array_keys( $reviews ) as $index ) : ?>
						<?php react2u_review( array( 'index' => (int) $index ) ); ?>
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
