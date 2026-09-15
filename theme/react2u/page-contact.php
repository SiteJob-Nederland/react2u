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
$contact_person_has_pending_value = static function ( mixed $value ) use ( &$contact_person_has_pending_value ): bool {
	if ( is_array( $value ) ) {
		foreach ( $value as $nested_value ) {
			if ( $contact_person_has_pending_value( $nested_value ) ) {
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

$contact_person_is_unconfirmed = static function ( array $person ): bool {
	foreach ( array( 'placeholder', 'is_placeholder' ) as $flag ) {
		if ( array_key_exists( $flag, $person ) && true === filter_var( $person[ $flag ], FILTER_VALIDATE_BOOLEAN ) ) {
			return true;
		}
	}

	foreach ( array( 'verified', 'confirmed', 'is_verified', 'is_confirmed' ) as $flag ) {
		if ( array_key_exists( $flag, $person ) && true !== filter_var( $person[ $flag ], FILTER_VALIDATE_BOOLEAN ) ) {
			return true;
		}
	}

	$status = strtolower( trim( (string) ( $person['status'] ?? '' ) ) );
	return in_array( $status, array( '0', 'concept', 'draft', 'onbevestigd', 'pending', 'placeholder', 'todo', 'unconfirmed', 'unverified' ), true );
};

$contact_person_is_valid = ! $contact_person_has_pending_value( $contact_person )
	&& ! $contact_person_is_unconfirmed( $contact_person )
	&& '' !== trim( wp_strip_all_tags( (string) ( $contact_person['name'] ?? '' ) ) )
	&& '' !== trim( wp_strip_all_tags( (string) ( $contact_person['role'] ?? '' ) ) );
?>
<main tabindex="-1" id="main" class="site-main">

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
					<?php if ( react2u_has_email() ) : ?>
						<li>
							<?php echo react2u_icon( 'mail' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<a href="mailto:<?php echo esc_attr( react2u_email_link() ); ?>"><?php echo esc_html( react2u_email_link() ); ?></a>
						</li>
					<?php endif; ?>
					<li>
						<?php echo react2u_icon( 'pin' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<span>
							<?php echo react2u_text( react2u_get( 'contact.street' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><br>
							<?php echo react2u_text( react2u_get( 'contact.postcode' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<?php echo react2u_text( react2u_get( 'contact.city' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</span>
					</li>
				</ul>

				<?php if ( $contact_person_is_valid ) : ?>
					<div class="contact-person">
						<?php react2u_person( array( 'person' => $contact_person, 'layout' => 'card' ) ); ?>
					</div>
				<?php endif; ?>
			</div>
			<div class="contact-react-model">
				<picture class="human-photo contact-photo-picture">
					<source media="(max-width: 960px)" srcset="<?php echo esc_url( REACT2U_URI . '/assets/images/contact-balie-higgsfield-v2-960.webp' ); ?>">
					<img class="contact-human-photo" <?php echo react2u_quality_image_attrs( REACT2U_URI . '/assets/images/contact-balie-higgsfield-v2.webp' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> width="1920" height="1080" alt="<?php esc_attr_e( 'Een professional heet een bezoeker welkom in een rustige ontvangstruimte.', 'react2u' ); ?>" loading="lazy" fetchpriority="low" decoding="async">
				</picture>
				<img class="contact-react-wheel" <?php echo react2u_quality_image_attrs( REACT2U_URI . '/assets/images/react-wiel.png' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> width="1024" height="1024" alt="" loading="lazy" fetchpriority="low" decoding="async" aria-hidden="true">
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
