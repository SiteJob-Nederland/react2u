<?php
/**
 * Kennisbankartikel.
 *
 * Zelfde opbouw als het blogartikel — de eisen aan een kennisbankstuk en een
 * blogartikel zijn dezelfde. Het verschil zit in het label en de accentnuance,
 * die het gedeelde sjabloon op basis van het posttype zet.
 *
 * @package React2u
 */
get_header();
?>
<main tabindex="-1" id="main" class="site-main">
	<?php
	while ( have_posts() ) :
		the_post();
		get_template_part( 'template-parts/content/single-resource' );
	endwhile;
	?>
</main>
<?php
get_footer();
