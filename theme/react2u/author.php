<?php
/**
 * Auteursarchief.
 *
 * Met foto, functie en bio: een auteurspagina die alleen titels toont, draagt
 * niets bij aan de geloofwaardigheid van het artikel dat ernaar linkt.
 *
 * @package React2u
 */
get_header();

$author    = get_queried_object();
$author_id = $author instanceof WP_User ? (int) $author->ID : 0;

ob_start();
if ( $author_id ) {
	react2u_author_card( $author_id, array( 'layout' => 'full' ) );
}
$card = (string) ob_get_clean();
?>
<main id="main" class="site-main">
	<?php
	get_template_part(
		'template-parts/content/archive-loop',
		null,
		array(
			'eyebrow' => __( 'Auteur', 'react2u' ),
			'title'   => $author_id ? (string) get_the_author_meta( 'display_name', $author_id ) : __( 'Auteur', 'react2u' ),
			'intro'   => '',
			'before'  => $card,
		)
	);
	?>
</main>
<?php
get_footer();
