<?php
/**
 * Losse pagina — het generieke vangnet.
 *
 * Voor content zonder eigen sjabloon: privacy, algemene voorwaarden,
 * tarieven, en alles wat verder niet in een specifiek paginatype past.
 * Kruimelpad, H1, inhoud, FAQ — één kolom, geen CTA's door de tekst.
 *
 * Dienstpagina's horen hier niet meer: die lopen altijd via
 * template-parts/page/dienst.php, met een eigen opbouw (zie
 * react2u_is_service_page() hieronder en page-dienst.php). Over-ons en
 * contact hebben elk hun eigen bestand (page-over-ons.php, page-contact.php)
 * dat WordPress automatisch kiest op basis van de paginaslug — dit bestand
 * wordt daar niet voor geladen.
 *
 * De inhoud loopt altijd door react2u_prepare_content, zodat een pagina die
 * als lang artikel is geschreven dezelfde inhoudsopgave, FAQ en H2-ankers krijgt.
 *
 * @package React2u
 */

// Servicepagina zonder expliciet toegewezen sjabloon (Nova kopieert onder
// dezelfde ouder, zonder een sjabloon te kiezen) — dan toch de dienstopbouw.
if ( react2u_is_service_page() ) {
	get_template_part( 'template-parts/page/dienst' );
	return;
}

get_header();
?>
<main id="main" class="site-main">
	<?php
	while ( have_posts() ) :
		the_post();
		$prepared = react2u_prepare_content( (string) apply_filters( 'the_content', get_the_content() ) );
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'resource' ); ?> data-variant="page">
			<header class="resource-header page-hero page-hero--generic has-react-route">
				<div class="shell resource-header-inner">
					<?php react2u_breadcrumbs(); ?>

					<h1 class="resource-title"><?php the_title(); ?></h1>
					<?php if ( has_excerpt() ) : ?>
						<p class="resource-intro"><?php echo esc_html( get_the_excerpt() ); ?></p>
					<?php endif; ?>
					<div class="react-route react-route--hero" aria-hidden="true"><span class="react-route-signals"><i></i><i></i><i></i><i></i><i></i><i></i></span><span class="react-route-line"></span><span class="react-route-destination"></span></div>
				</div>
			</header>

			<?php if ( has_post_thumbnail() ) : ?>
				<div class="shell resource-banner"><?php react2u_banner( array( 'post_id' => get_the_ID(), 'fallback' => false ) ); ?></div>
			<?php endif; ?>

			<div class="shell resource-body is-single-column">
				<div class="resource-main">
					<?php react2u_toc( $prepared['toc'] ); ?>
					<div class="entry-content"><?php echo $prepared['content']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
				</div>
			</div>

			<?php if ( '' !== $prepared['faq'] ) : ?>
				<div class="shell resource-faq">
					<?php react2u_faq( $prepared['faq'] ); ?>
				</div>
			<?php endif; ?>

			<section class="resource-final-cta" aria-label="<?php esc_attr_e( 'Volgende stap', 'react2u' ); ?>">
				<div class="shell"><?php react2u_cta( array( 'variant' => 'quote', 'tone' => 'dark', 'rating' => true, 'secondary' => false ) ); ?></div>
			</section>
		</article>
		<?php
	endwhile;
	?>
</main>
<?php
get_footer();
