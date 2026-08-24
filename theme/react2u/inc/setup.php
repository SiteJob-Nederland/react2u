<?php
/**
 * Thema-instellingen: ondersteuning, menu's, beeldformaten.
 *
 * @package React2u
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function react2u_theme_setup(): void {
	load_theme_textdomain( 'react2u', REACT2U_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 96,
			'width'       => 320,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	/*
	 * Breed en dun: de banner staat bij een artikel boven de inhoudsopgave en
	 * mag de eerste schermvulling niet opeten. 1360x420 is ongeveer 3,2:1.
	 */
	add_image_size( 'react2u-content-banner', 1360, 420, true );
	add_image_size( 'react2u-card', 760, 460, true );

	register_nav_menus(
		array(
			'primary' => __( 'Hoofdnavigatie', 'react2u' ),
			'footer'  => __( 'Footer — pagina\'s', 'react2u' ),
			'legal'   => __( 'Footer — juridisch', 'react2u' ),
		)
	);
}
add_action( 'after_setup_theme', 'react2u_theme_setup' );

function react2u_content_width(): void {
	$GLOBALS['content_width'] = $GLOBALS['content_width'] ?? 760;
}
add_action( 'after_setup_theme', 'react2u_content_width', 0 );

function react2u_document_title_separator(): string {
	return '—';
}
add_filter( 'document_title_separator', 'react2u_document_title_separator' );

/**
 * Menu-terugval zolang er in WordPress nog geen menu is toegewezen.
 * Zonder terugval staat een verse installatie zonder navigatie.
 *
 * @param array<string,string> $items Pad => label.
 */
function react2u_menu_fallback_render( array $items ): void {
	echo '<ul class="menu">';
	foreach ( $items as $path => $label ) {
		printf(
			'<li class="menu-item"><a href="%1$s">%2$s</a></li>',
			esc_url( home_url( $path ) ),
			esc_html( $label )
		);
	}
	echo '</ul>';
}

/* PER KLANT AANPASSEN: dit zijn de paden die de navigatie toont zolang er in
   WordPress nog geen menu is ingesteld. */
function react2u_primary_menu_fallback(): void {
	react2u_menu_fallback_render(
		array(
			'/diensten/'                 => __( 'Diensten', 'react2u' ),
			'/werknemers/'               => __( 'Werknemers', 'react2u' ),
			'/verzuimprotocol/'          => __( 'Verzuimprotocol', 'react2u' ),
			'/over-react2u/'             => __( 'Over React2u', 'react2u' ),
			'/contact/'                  => __( 'Contact', 'react2u' ),
		)
	);
}

function react2u_footer_menu_fallback(): void {
	react2u_menu_fallback_render(
		array(
			'/diensten/'      => __( 'Diensten', 'react2u' ),
			'/over-react2u/'  => __( 'Over React2u', 'react2u' ),
			'/contact/'       => __( 'Contact', 'react2u' ),
		)
	);
}

function react2u_legal_menu_fallback(): void {
	react2u_menu_fallback_render(
		array(
			'/algemene-voorwaarden/' => __( 'Algemene voorwaarden', 'react2u' ),
			'/klachtenprocedure/'    => __( 'Klachtenprocedure', 'react2u' ),
			'/privacy-reglement/'    => __( 'Privacy reglement', 'react2u' ),
		)
	);
}

/**
 * Menselijk label per contenttype. Gebruikt in kruimelpad, eyebrow en kaarten.
 */
function react2u_content_label( ?string $post_type = null ): string {
	return match ( $post_type ?: get_post_type() ) {
		'post'                  => __( 'Blog', 'react2u' ),
		'react2u_kennisbank' => __( 'Kennisbank', 'react2u' ),
		'page'                  => __( 'Pagina', 'react2u' ),
		default                 => __( 'React2u', 'react2u' ),
	};
}

function react2u_is_article( ?string $post_type = null ): bool {
	return in_array( $post_type ?: (string) get_post_type(), REACT2U_ARTICLE_TYPES, true );
}

/**
 * Het auteursarchief toont alles wat die persoon schreef, niet alleen berichten.
 * Anders lijkt een auteur die vooral kennisbankstukken schrijft leeg.
 */
function react2u_extend_author_archive( WP_Query $query ): void {
	if ( ! is_admin() && $query->is_main_query() && $query->is_author() ) {
		$query->set( 'post_type', REACT2U_ARTICLE_TYPES );
	}
}
add_action( 'pre_get_posts', 'react2u_extend_author_archive' );

/**
 * Zoekresultaten laten de kennisbank meelopen; die staat anders buiten beeld.
 */
function react2u_extend_search( WP_Query $query ): void {
	if ( ! is_admin() && $query->is_main_query() && $query->is_search() && ! $query->get( 'post_type' ) ) {
		$query->set( 'post_type', array_merge( REACT2U_ARTICLE_TYPES, array( 'page' ) ) );
	}
}
add_action( 'pre_get_posts', 'react2u_extend_search' );

/**
 * De REST-API geeft standaard de gebruikersnamen van beheerders vrij.
 * Auteursgegevens blijven bereikbaar voor wie is ingelogd, maar niet meer voor
 * een anonieme scanner.
 */
function react2u_restrict_user_endpoint( $result ) {
	if ( ! empty( $result ) || is_user_logged_in() ) {
		return $result;
	}

	$route = (string) ( $GLOBALS['wp']->query_vars['rest_route'] ?? '' );
	if ( str_starts_with( ltrim( $route, '/' ), 'wp/v2/users' ) ) {
		return new WP_Error(
			'react2u_rest_users_forbidden',
			__( 'Gebruikersgegevens zijn niet openbaar.', 'react2u' ),
			array( 'status' => 401 )
		);
	}

	return $result;
}
add_filter( 'rest_authentication_errors', 'react2u_restrict_user_endpoint' );

/** Auteurs-scan (?author=1) leidt naar de homepage in plaats van naar de inlognaam. */
function react2u_block_author_scan(): void {
	if ( ! is_admin() && isset( $_GET['author'] ) && ! is_user_logged_in() ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}
}
add_action( 'template_redirect', 'react2u_block_author_scan' );

/** Versienummers uit head halen; ze zeggen een scanner meer dan een bezoeker. */
remove_action( 'wp_head', 'wp_generator' );

/**
 * Kleuren voor de mail die deze site verstuurt.
 *
 * De mailplugin is themaonafhankelijk — hij moet blijven werken als het thema
 * wisselt — maar een mail hoort er wél uit te zien als de site waar hij vandaan
 * komt. Vandaar dit filter: het thema levert de kleuren aan, de plugin doet de
 * opmaak.
 *
 * Let op de knopkleur. --brand haalt als achtergrond onder witte tekst maar
 * 4,1:1, en de knoptekst is niet groot genoeg om onder de soepelere lat voor
 * grote tekst te vallen. Daarom de diepe variant; die haalt het ruim.
 *
 * @param array<string,string> $kleuren
 * @return array<string,string>
 */
function react2u_mail_huisstijl( array $kleuren ): array {
	return array_merge(
		$kleuren,
		array(
			'accent' => '#1A1815',
			'inkt'   => '#1A1815',
			'zacht'  => '#5A5348',
			'papier' => '#F6F4F0',
			'lijn'   => '#E2DED6',
			'op'     => '#FFFFFF',
		)
	);
}
add_filter( 'react2u_mail_kleuren', 'react2u_mail_huisstijl' );
