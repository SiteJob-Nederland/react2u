<?php
/**
 * Niet gevonden.
 *
 * @package React2u
 */
get_header();

$knowledge = post_type_exists( 'react2u_kennisbank' ) ? get_post_type_archive_link( 'react2u_kennisbank' ) : '';
?>
<main tabindex="-1" id="main" class="site-main">
	<div class="archive-page">
		<header class="archive-header page-hero page-hero--error has-react-route">
			<div class="shell archive-header-inner">
				<?php react2u_breadcrumbs(); ?>
				<p class="eyebrow"><span class="eyebrow-dot" aria-hidden="true"></span>404</p>
				<h1 class="archive-title"><?php esc_html_e( 'Deze pagina is verhuisd of bestaat niet meer', 'react2u' ); ?></h1>
				<p class="archive-intro"><?php esc_html_e( 'Zoek verder, of laat ons weten wat je zocht — dan wijzen we je de weg.', 'react2u' ); ?></p>
				<div class="hero-actions">
					<a class="button button-primary" href="<?php echo esc_url( $knowledge ?: home_url( '/' ) ); ?>">
						<?php echo esc_html( $knowledge ? __( 'Naar de kennisbank', 'react2u' ) : __( 'Naar de homepage', 'react2u' ) ); ?>
					</a>
					<a class="error-contact-link" href="<?php echo esc_url( react2u_cta_url( 'contact' ) ); ?>"><?php echo esc_html( react2u_cta_label( 'contact' ) ); ?></a>
				</div>
				<div class="react-route react-route--hero" aria-hidden="true"><span class="react-route-signals"><i></i><i></i><i></i><i></i><i></i><i></i></span><span class="react-route-line"></span><span class="react-route-destination"></span></div>
				<div class="error-search"><?php get_search_form(); ?></div>
			</div>
		</header>
	</div>
</main>
<?php
get_footer();
