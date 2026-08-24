<?php
/**
 * Template Name: Contact
 * Template Post Type: page
 *
 * DIT IS EEN SJABLOON DAT JE PER KLANT OPNIEUW ONTWERPT — zie front-page.php
 * en page-over-ons.php voor dezelfde afspraak. WordPress kiest dit bestand
 * automatisch voor de pagina met slug "contact"; er hoeft niemand een
 * sjabloon te kiezen.
 *
 * Bewust een andere opbouw dan een dienstpagina, over-ons of de generieke
 * page.php: geen inhoudsopgave, geen ingeweven CTA's, geen FAQ-extractie. Dit
 * is de plek waar iemand snel het telefoonnummer, e-mailadres en adres wil
 * vinden — en daarna in twee klikken een afspraak kan maken.
 *
 * Regels die blijven staan, ongeacht het ontwerp:
 *   - precies één H1, boven in de resource-header;
 *   - elke sectie heeft een toegankelijke naam (aria-labelledby op de H2);
 *   - contactgegevens komen uit inc/proof.php, nooit hardcoded.
 *
 * @package React2u
 */

get_header();

$contact_person = (array) react2u_get( 'people.contact', array() );
?>
<main id="main" class="site-main">

	<header class="resource-header page-hero page-hero--contact has-react-route">
		<div class="shell resource-header-inner">
			<?php react2u_breadcrumbs(); ?>
			<h1 class="resource-title"><?php the_title(); ?></h1>
			<?php if ( has_excerpt() ) : ?>
				<p class="resource-intro"><?php echo esc_html( get_the_excerpt() ); ?></p>
			<?php endif; ?>
			<div class="react-route react-route--hero" aria-hidden="true"><span class="react-route-signals"><i></i><i></i><i></i><i></i><i></i><i></i></span><span class="react-route-line"></span><span class="react-route-destination"></span></div>
		</div>
	</header>

	<?php
	/*
	 * PER KLANT: bewust een andere compositie dan over-react2u — daar overlapt
	 * een tekstkaart de foto vanaf de zijkant, hier is de foto de achtergrond
	 * van de hele sectie en zweeft de contactkaart erboven. Twee pagina's met
	 * "tekst plus foto" horen er niet hetzelfde uit te zien.
	 */
	?>
	<section class="section section-contact section-contact--react" aria-labelledby="contact-details-title">
		<div class="shell contact-layout">
			<div class="contact-card">
				<h2 class="sr-only" id="contact-details-title"><?php esc_html_e( 'Contactgegevens', 'react2u' ); ?></h2>
				<?php if ( get_the_content() ) : ?>
					<div class="entry-content"><?php the_content(); ?></div>
				<?php endif; ?>

				<ul class="contact-list">
					<?php if ( react2u_has_phone() ) : ?>
						<li>
							<?php echo react2u_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<a href="tel:<?php echo esc_attr( react2u_phone_link() ); ?>"><?php echo react2u_text( react2u_get( 'contact.phone' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
						</li>
					<?php endif; ?>
					<li>
						<?php echo react2u_icon( 'mail' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<a href="mailto:<?php echo esc_attr( str_replace( REACT2U_PLACEHOLDER . ' ', '', (string) react2u_get( 'contact.email' ) ) ); ?>"><?php echo react2u_text( react2u_get( 'contact.email' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
					</li>
					<li>
						<?php echo react2u_icon( 'pin' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<span>
							<?php echo react2u_text( react2u_get( 'contact.street' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><br>
							<?php echo react2u_text( react2u_get( 'contact.postcode' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<?php echo react2u_text( react2u_get( 'contact.city' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</span>
					</li>
				</ul>

				<?php if ( $contact_person ) : ?>
					<div class="contact-person">
						<?php react2u_person( array( 'person' => $contact_person, 'layout' => 'card' ) ); ?>
					</div>
				<?php endif; ?>
			</div>
			<div class="contact-react-model">
				<img src="<?php echo esc_url( REACT2U_URI . '/assets/images/react-wiel.png' ); ?>" width="1024" height="1024" alt="<?php esc_attr_e( 'Het REACT-model van React2u: Results, Expertise, Attention, Coaching, Together', 'react2u' ); ?>" loading="lazy">
				<div class="react-route react-route--contact" aria-hidden="true"><span class="react-route-signals"><i></i><i></i><i></i><i></i><i></i><i></i></span><span class="react-route-line"></span><span class="react-route-destination"></span></div>
			</div>
		</div>
	</section>

	<?php /* ---- Direct een afspraak maken ------------------------------------ */ ?>
	<section class="section section-final-cta" aria-label="<?php esc_attr_e( 'Direct een afspraak maken', 'react2u' ); ?>">
		<div class="shell"><?php react2u_cta( array( 'variant' => 'quote', 'tone' => 'dark', 'rating' => true, 'secondary' => false ) ); ?></div>
	</section>

</main>
<?php
get_footer();
