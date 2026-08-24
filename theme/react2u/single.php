<?php
/**
 * Blogartikel.
 *
 * @package React2u
 */
get_header();
?>
<main id="main" class="site-main">
	<?php
	while ( have_posts() ) :
		the_post();
		get_template_part( 'template-parts/content/single-resource' );
	endwhile;
	?>
</main>
<?php
get_footer();
