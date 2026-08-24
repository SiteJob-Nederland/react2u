<?php
/**
 * Kennisbankoverzicht.
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
			'eyebrow' => __( 'Kennisbank', 'react2u' ),
			/* PER KLANT: kop en intro van het kennisbankoverzicht. */
			'title'   => __( 'Alles uitgelegd, stap voor stap', 'react2u' ),
			'intro'   => __( 'Geschreven door de mensen die het elke dag doen.', 'react2u' ),
		)
	);
	?>
</main>
<?php
get_footer();
