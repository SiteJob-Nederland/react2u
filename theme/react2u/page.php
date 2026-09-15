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
<main tabindex="-1" id="main" class="site-main">
	<?php
	while ( have_posts() ) :
		the_post();
		$raw_content = (string) apply_filters( 'the_content', get_the_content() );
		$content_text = html_entity_decode(
			wp_strip_all_tags( $raw_content ),
			ENT_QUOTES | ENT_HTML5,
			get_bloginfo( 'charset' ) ?: 'UTF-8'
		);
		$content_is_pending = react2u_is_placeholder( $raw_content )
			|| 1 === preg_match( '/(?:\[(?:placeholder|tbd|todo)\]|\b(?:tbd|todo)\b)/iu', $content_text );

		$excerpt            = has_excerpt() ? trim( get_the_excerpt() ) : '';
		$excerpt_is_pending = react2u_is_placeholder( $excerpt )
			|| 1 === preg_match( '/(?:\[(?:placeholder|tbd|todo)\]|\b(?:tbd|todo)\b)/iu', $excerpt );

		if ( $content_is_pending ) {
			$temporary_content = sprintf(
				'<p>%1$s <a href="%2$s">%3$s</a>.</p>',
				esc_html__( 'De inhoud van deze pagina is nog niet beschikbaar.', 'react2u' ),
				esc_url( home_url( '/contact/' ) ),
				esc_html__( 'Neem contact met ons op als je een vraag hebt', 'react2u' )
			);
			$prepared = array(
				'content' => $temporary_content,
				'toc'     => array(),
				'faq'     => '',
			);
		} else {
			$prepared = react2u_prepare_content( $raw_content );
		}
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'resource' ); ?> data-variant="page">
			<header class="resource-header page-hero page-hero--generic has-react-route">
				<div class="shell resource-header-inner">
					<?php react2u_breadcrumbs(); ?>

					<h1 class="resource-title"><?php the_title(); ?></h1>
					<?php if ( '' !== $excerpt && ! $excerpt_is_pending ) : ?>
						<p class="resource-intro"><?php echo esc_html( $excerpt ); ?></p>
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

			<?php if ( ! $content_is_pending ) : ?>
				<section class="resource-final-cta" aria-label="<?php esc_attr_e( 'Volgende stap', 'react2u' ); ?>">
					<div class="shell"><?php react2u_cta( array( 'variant' => 'quote', 'tone' => 'dark', 'rating' => true, 'secondary' => false ) ); ?></div>
				</section>
			<?php endif; ?>
		</article>
		<?php
	endwhile;
	?>
</main>
<?php
get_footer();
