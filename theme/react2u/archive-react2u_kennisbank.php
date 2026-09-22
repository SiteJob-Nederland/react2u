<?php
/**
 * Kennisbankoverzicht.
 *
 * @package React2u
 */
get_header();
?>
<main tabindex="-1" id="main" class="site-main">
	<?php
	get_template_part(
		'template-parts/content/archive-loop',
		null,
		array(
			'eyebrow' => __( 'Kennisbank', 'react2u' ),
			'title'   => __( 'Kennisbank over verzuim en re-integratie', 'react2u' ),
			'intro'   => __( 'Uitleg over verzuimbegeleiding, preventie en terugkeer naar werk. Met aandacht voor vragen van werkgevers en werknemers.', 'react2u' ),
		)
	);
	?>
</main>
<?php
get_footer();
