<?php
/**
 * Alle producten. Terugval als er geen specifieke categorie is aangevraagd.
 *
 * @package React2u
 */
get_header();
?>
<main tabindex="-1" id="main" class="site-main">
	<div class="archive-page">
		<header class="archive-header">
			<div class="shell archive-header-inner">
				<?php react2u_breadcrumbs(); ?>
				<p class="eyebrow"><span class="eyebrow-dot" aria-hidden="true"></span><?php esc_html_e( 'Producten', 'react2u' ); ?></p>
				<h1 class="archive-title"><?php esc_html_e( 'Alle producten', 'react2u' ); ?></h1>
			</div>
		</header>
		<div class="shell archive-body">
			<?php if ( have_posts() ) : ?>
				<div class="product-grid">
					<?php
					while ( have_posts() ) :
						the_post();
						react2u_product_card( (int) get_the_ID() );
					endwhile;
					?>
				</div>
			<?php else : ?>
				<div class="empty-state"><p><?php esc_html_e( 'Er staan nog geen producten online.', 'react2u' ); ?></p></div>
			<?php endif; ?>
		</div>
	</div>
</main>
<?php
get_footer();
