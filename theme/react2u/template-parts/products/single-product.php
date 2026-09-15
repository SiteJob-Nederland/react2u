<?php
/**
 * Eén product. Bewust géén blogopmaak (geen auteur, geen leestijd): een product
 * is geen artikel. Wel dezelfde inhoudsverwerking, zodat koppen en FAQ kloppen.
 *
 * @package React2u
 */
get_header();
?>
<main tabindex="-1" id="main" class="site-main">
	<?php
	while ( have_posts() ) :
		the_post();
		$prepared = react2u_prepare_content( (string) apply_filters( 'the_content', get_the_content() ) );
		?>
		<article <?php post_class( 'resource' ); ?> data-variant="product">
			<header class="resource-header">
				<div class="shell resource-header-inner">
					<?php react2u_breadcrumbs(); ?>
					<h1 class="resource-title"><?php the_title(); ?></h1>
					<?php if ( has_excerpt() ) : ?>
						<p class="resource-intro"><?php echo esc_html( get_the_excerpt() ); ?></p>
					<?php endif; ?>
				</div>
			</header>

			<?php if ( has_post_thumbnail() ) : ?>
				<div class="shell resource-banner"><?php react2u_banner( array( 'fallback' => false ) ); ?></div>
			<?php endif; ?>

			<div class="shell resource-body is-single-column">
				<div class="resource-main">
					<div class="entry-content"><?php echo $prepared['content']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
				</div>
			</div>

			<section class="resource-final-cta" aria-label="<?php esc_attr_e( 'Volgende stap', 'react2u' ); ?>">
				<div class="shell"><?php react2u_cta( array( 'variant' => 'quote', 'tone' => 'dark' ) ); ?></div>
			</section>
		</article>
		<?php
	endwhile;
	?>
</main>
<?php
get_footer();
