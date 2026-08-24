<?php
/**
 * Sitehoofd.
 *
 * @package React2u
 */
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link" href="#main"><?php esc_html_e( 'Ga naar de inhoud', 'react2u' ); ?></a>

<div class="topbar site-signal-bar">
	<div class="shell topbar-inner">
		<p class="topbar-claim">
			<span class="topbar-signal" aria-hidden="true"></span>
			<?php echo react2u_text( react2u_get( 'contact.tagline' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</p>
		<p class="topbar-contact">
			<?php if ( react2u_has_phone() ) : ?>
				<a href="tel:<?php echo esc_attr( react2u_phone_link() ); ?>">
					<?php echo react2u_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<span><?php echo react2u_text( react2u_get( 'contact.phone' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				</a>
			<?php endif; ?>
			<a href="mailto:<?php echo esc_attr( str_replace( REACT2U_PLACEHOLDER . ' ', '', (string) react2u_get( 'contact.email' ) ) ); ?>">
				<?php echo react2u_icon( 'mail' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<span><?php echo react2u_text( react2u_get( 'contact.email' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			</a>
		</p>
	</div>
</div>

<header class="site-header" data-header>
	<div class="shell header-inner">
		<div class="header-brand-lockup">
			<?php if ( has_custom_logo() ) : ?>
				<div class="brand has-custom-logo"><?php the_custom_logo(); ?></div>
			<?php else : ?>
				<a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( sprintf( __( '%s — naar de homepage', 'react2u' ), (string) get_bloginfo( 'name' ) ) ); ?>">
					<?php echo react2u_logo( array( 'width' => 200 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</a>
			<?php endif; ?>
			<span class="brand-route-mark" aria-hidden="true"><span></span><i></i></span>
		</div>

		<button class="menu-button" type="button" aria-expanded="false" aria-controls="site-navigation">
			<span class="sr-only"><?php esc_html_e( 'Menu', 'react2u' ); ?></span>
			<span class="menu-bars" aria-hidden="true"><span></span><span></span><span></span></span>
		</button>

		<?php
		/*
		 * Navigatie en knoppen zitten in één paneel: op smallere schermen klapt
		 * dat als geheel uit, zodat de hoofd-CTA ook op mobiel bereikbaar
		 * blijft. Buiten het paneel zou die wegvallen.
		 */
		?>
		<div class="header-panel" id="site-navigation" data-nav-panel>
			<nav class="main-nav" aria-label="<?php esc_attr_e( 'Hoofdnavigatie', 'react2u' ); ?>">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'container'      => false,
						'fallback_cb'    => 'react2u_primary_menu_fallback',
						'depth'          => 2,
					)
				);
				?>
			</nav>

			<div class="header-actions">
				<a class="button button-primary button-small header-cta" href="<?php echo esc_url( react2u_cta_url( 'quote' ) ); ?>">
					<?php echo esc_html( react2u_cta_label( 'quote' ) ); ?>
				</a>
			</div>
		</div>
	</div>
</header>
