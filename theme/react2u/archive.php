<?php
/**
 * Overzicht voor categorieën, tags, taxonomieën en datumarchieven.
 *
 * @package React2u
 */
get_header();

$description = trim( wp_strip_all_tags( (string) get_the_archive_description() ) );
?>
<main id="main" class="site-main">
	<?php
	get_template_part(
		'template-parts/content/archive-loop',
		null,
		array(
			'eyebrow' => (string) get_the_archive_title( '', false ),
			'title'   => wp_strip_all_tags( (string) get_the_archive_title( '', false ) ),
			'intro'   => $description ?: __( 'Alle artikelen over dit onderwerp, op volgorde van publicatie.', 'react2u' ),
		)
	);
	?>
</main>
<?php
get_footer();
