<?php
/**
 * Zoekresultaten.
 *
 * @package React2u
 */
get_header();

global $wp_query;
$found = (int) $wp_query->found_posts;

ob_start();
get_search_form();
$form = (string) ob_get_clean();
?>
<main tabindex="-1" id="main" class="site-main">
	<?php
	get_template_part(
		'template-parts/content/archive-loop',
		null,
		array(
			'eyebrow' => __( 'Zoeken', 'react2u' ),
			/* translators: %s: zoekterm */
			'title'   => sprintf( __( 'Resultaten voor “%s”', 'react2u' ), get_search_query() ),
			/* translators: %d: aantal resultaten */
			'intro'   => sprintf( _n( '%d resultaat gevonden.', '%d resultaten gevonden.', $found, 'react2u' ), $found ),
			'before'  => $form,
		)
	);
	?>
</main>
<?php
get_footer();
