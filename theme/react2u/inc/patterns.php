<?php
/**
 * Blokpatronen: de blokkenbibliotheek zoals een redacteur hem in de editor ziet.
 *
 * De patronen in patterns/ worden door WordPress zelf ingelezen. Hier staat
 * alleen de categorie waaronder ze verschijnen, plus het opruimen van de
 * standaardpatronen die niets met deze site te maken hebben.
 *
 * @package React2u
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function react2u_register_pattern_category(): void {
	register_block_pattern_category(
		'react2u',
		array(
			'label'       => __( 'React2u-blokken', 'react2u' ),
			'description' => __( 'De vaste onderdelen van deze site: CTA\'s, reviews, cijfers en FAQ.', 'react2u' ),
		)
	);
}
add_action( 'init', 'react2u_register_pattern_category' );

/** Geen patronen van WordPress.org ophalen: die passen niet in deze huisstijl. */
function react2u_remove_core_patterns(): void {
	remove_theme_support( 'core-block-patterns' );
}
add_action( 'after_setup_theme', 'react2u_remove_core_patterns', 20 );
