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
			'title'   => $posts_page ? get_the_title( $posts_page ) : __( 'Blog', 'react2u' ),
			/* PER KLANT: één zin die duidelijk maakt waarom deze blog bestaat. */
			'intro'   => __( 'Wat we in de praktijk tegenkomen, opgeschreven zodat je er zelf iets aan hebt.', 'react2u' ),
		)
	);
	?>
</main>
<?php
get_footer();
