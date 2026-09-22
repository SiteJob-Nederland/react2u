<?php
/**
 * Blogoverzicht.
 *
 * @package React2u
 */
get_header();

$posts_page = (int) get_option( 'page_for_posts' );
?>
<main tabindex="-1" id="main" class="site-main">
	<?php
	get_template_part(
		'template-parts/content/archive-loop',
		null,
		array(
			'eyebrow' => __( 'Blog', 'react2u' ),
			'title'   => __( 'Blog over verzuim en gezond werken', 'react2u' ),
			'intro'   => __( 'Praktische inzichten over verzuim, preventie en gezond werken. Voor werkgevers en werknemers die verder willen kijken dan een ziekmelding.', 'react2u' ),
		)
	);
	?>
</main>
<?php
get_footer();
