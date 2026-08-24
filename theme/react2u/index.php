<?php
/**
 * Terugval voor alles waar geen specifieker sjabloon voor is.
 *
 * @package React2u
 */
get_header();
?>
<main id="main" class="site-main">
	<?php
	get_template_part(
		'template-parts/content/archive-loop',
		null,
		array(
			'eyebrow' => __( 'Overzicht', 'react2u' ),
			'title'   => wp_strip_all_tags( (string) get_the_archive_title( '', false ) ) ?: get_bloginfo( 'name' ),
		)
	);
	?>
</main>
<?php
get_footer();
