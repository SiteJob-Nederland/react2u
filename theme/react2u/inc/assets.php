<?php
/**
 * Stylesheets, script en zelf-gehoste fonts.
 *
 * Fonts komen nooit van een CDN: dat is een verzoek naar een derde partij bij
 * elke paginaweergave, en het kost een extra verbinding voordat de eerste tekst
 * in beeld staat. Zie assets/fonts/LEESMIJ.md voor het binnenhalen.
 *
 * @package React2u
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Bestandstijd als versie: na een aanpassing hoeft niemand de cache te legen. */
function react2u_asset_version( string $relative_path ): string {
	$file = REACT2U_DIR . '/' . ltrim( $relative_path, '/' );
	return file_exists( $file ) ? (string) filemtime( $file ) : REACT2U_VERSION;
}

function react2u_enqueue_assets(): void {
	wp_enqueue_style(
		'react2u-fonts',
		REACT2U_URI . '/assets/fonts/fonts.css',
		array(),
		react2u_asset_version( 'assets/fonts/fonts.css' )
	);

	wp_enqueue_style(
		'react2u-style',
		get_stylesheet_uri(),
		array( 'react2u-fonts' ),
		react2u_asset_version( 'style.css' )
	);

	wp_enqueue_script(
		'react2u-site',
		REACT2U_URI . '/assets/js/site.js',
		array(),
		react2u_asset_version( 'assets/js/site.js' ),
		true
	);

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'react2u_enqueue_assets' );

/**
 * Eén regel in de <head>, vóór het eerste verfje.
 *
 * De onthulling bij het scrollen begint op `opacity: 0`, en die nul mag er
 * alleen staan als er ook iets is dat hem weer aanzet. Stond hij onvoorwaardelijk
 * in het stylesheet, dan zag een bezoeker zonder JavaScript een lege pagina.
 * Werd hij pas door site.js gezet, dan flitst inhoud boven de vouw eerst in
 * beeld en duikt daarna alsnog weg — dat script staat immers in de voet.
 *
 * De timer erachter is het vangnet voor het geval site.js wél verwacht werd
 * maar nooit aankwam. Dan valt de klasse na vier seconden weg en staat alles er
 * gewoon.
 */
function react2u_anim_klasse(): void {
	echo "<script>document.documentElement.classList.add('js-anim');setTimeout(function(){document.documentElement.classList.remove('js-anim');},4000);</script>\n";
}
add_action( 'wp_head', 'react2u_anim_klasse', 1 );

/**
 * De twee gezichtsbepalende fonts vooraf laden: de kop staat er anders even in
 * een systeemfont, wat op een trage verbinding zichtbaar verspringt. De rest
 * volgt vanzelf en is minder opvallend.
 *
 * PER KLANT: zet hier de bestandsnamen van de display- en tekstvariant die als
 * eerste in beeld komen. Bestaat een bestand niet, dan slaat de preload over —
 * een preload naar een 404 kost alleen maar tijd.
 */
function react2u_preload_fonts(): void {
	$fonts = apply_filters( 'react2u_preload_fonts', array( 'display-var-latin.woff2', 'body-var-latin.woff2' ) );

	foreach ( $fonts as $font ) {
		if ( ! file_exists( REACT2U_DIR . '/assets/fonts/' . $font ) ) {
			continue;
		}
		printf(
			"<link rel=\"preload\" href=\"%s\" as=\"font\" type=\"font/woff2\" crossorigin>\n",
			esc_url( REACT2U_URI . '/assets/fonts/' . $font )
		);
	}
}
add_action( 'wp_head', 'react2u_preload_fonts', 1 );

/** Favicon uit het thema, zodat een verse installatie er niet kaal bij staat. */
function react2u_favicon(): void {
	if ( has_site_icon() ) {
		return;
	}
	foreach ( array( 'favicon.svg', 'favicon.png', 'favicon.ico' ) as $kandidaat ) {
		if ( file_exists( REACT2U_DIR . '/assets/images/' . $kandidaat ) ) {
			$type = str_ends_with( $kandidaat, '.svg' ) ? ' type="image/svg+xml"' : '';
			printf(
				"<link rel=\"icon\"%s href=\"%s\" sizes=\"any\">\n",
				$type, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				esc_url( REACT2U_URI . '/assets/images/' . $kandidaat )
			);
			return;
		}
	}
}
add_action( 'wp_head', 'react2u_favicon', 2 );

/** Kleur van de browserbalk: gelijk aan --paper en de paper-preset. */
function react2u_theme_color(): void {
	$canvas = '#FBFBFD';
	printf( "<meta name=\"theme-color\" content=\"%s\">\n", esc_attr( $canvas ) );
}
add_action( 'wp_head', 'react2u_theme_color', 3 );

/**
 * De editor krijgt dezelfde tokens en typografie als de voorkant, zodat een
 * redacteur ziet wat de bezoeker straks ziet.
 */
function react2u_editor_styles(): void {
	add_editor_style( array( 'assets/fonts/fonts.css', 'assets/css/editor.css' ) );
}
add_action( 'after_setup_theme', 'react2u_editor_styles' );
