<?php
/**
 * Plugin Name: React2u Hardening
 * Description: Beperkt gebruikersenumeratie en zet beveiligingsheaders die WordPress zelf niet levert.
 * Version:     0.1.0
 * Author:      SiteJob
 * License:     GPL-2.0-or-later
 * Text Domain: react2u-hardening
 *
 * Bewust een plugin en geen themacode: deze regels moeten blijven staan als het
 * thema ooit wisselt, en ze horen niet mee te verhuizen in de thema-ZIP die naar
 * de klant gaat.
 *
 * @package React2u
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Posttypen waarvan de auteur publiek zichtbaar mag zijn.
 *
 * Auteurspagina's horen bij de SEO/GEO-opzet: een gepubliceerde auteur krijgt
 * een eigen pagina met Person- en ProfilePage-schema. Een beheerder die alleen
 * interne WordPress-objecten op zijn naam heeft staan, hoort daar niet bij.
 *
 * @return array<int, string>
 */
function react2u_hardening_public_author_post_types(): array {
	return (array) apply_filters(
		'react2u_public_author_post_types',
		array( 'post', 'react2u_kennisbank' )
	);
}

/** Heeft deze gebruiker publiek zichtbare inhoud? */
function react2u_hardening_user_has_public_content( int $user_id ): bool {
	if ( $user_id <= 0 ) {
		return false;
	}

	return count_user_posts( $user_id, react2u_hardening_public_author_post_types(), true ) > 0;
}

/**
 * Verberg de REST-gebruikersroutes voor bezoekers die niet zijn ingelogd.
 *
 * WordPress toont /wp-json/wp/v2/users ongeauthenticeerd aan iedereen die ook
 * maar één gepubliceerd bericht heeft in een posttype met show_in_rest. Daar
 * valt ook wp_global_styles onder, het object dat WordPress zelf aanmaakt zodra
 * een thema een theme.json heeft. Daardoor lekt de beheerdersnaam zonder dat er
 * ook maar één artikel op zijn naam staat, en is inhoud opruimen geen oplossing.
 *
 * Ingelogde gebruikers houden de routes, anders breekt de blokeditor.
 *
 * @param array<string, mixed> $endpoints
 * @return array<string, mixed>
 */
function react2u_hardening_hide_rest_user_routes( array $endpoints ): array {
	if ( is_user_logged_in() ) {
		return $endpoints;
	}

	foreach ( array_keys( $endpoints ) as $route ) {
		if ( str_starts_with( (string) $route, '/wp/v2/users' ) ) {
			unset( $endpoints[ $route ] );
		}
	}

	return $endpoints;
}
add_filter( 'rest_endpoints', 'react2u_hardening_hide_rest_user_routes' );

/**
 * Auteursarchief van een gebruiker zonder publieke inhoud geeft een 404.
 * Zo is /?author=1 geen manier meer om inlognamen af te lopen.
 */
function react2u_hardening_guard_author_archive(): void {
	if ( ! is_author() ) {
		return;
	}

	$author = get_queried_object();
	if ( $author instanceof WP_User && ! react2u_hardening_user_has_public_content( (int) $author->ID ) ) {
		global $wp_query;
		$wp_query->set_404();
		status_header( 404 );
		nocache_headers();
	}
}
add_action( 'template_redirect', 'react2u_hardening_guard_author_archive', 1 );

/** oEmbed-, RSD- en WLW-links weg: ze voegen niets toe en verraden versies. */
remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );

/**
 * Het WordPress-versienummer ook uit de RSS/Atom-feeds halen. `wp_generator` uit
 * de <head> weghalen is niet genoeg: de feeds hebben hun eigen generator-tag,
 * en die is precies wat een scanner als tweede plek uitleest (zie de
 * wordpress-penetration-testing-skill, "version detection"). Eén filter dekt
 * head én feeds.
 */
add_filter( 'the_generator', '__return_empty_string' );

/** XML-RPC uitzetten: bijna nooit in gebruik, wel een geliefd doelwit. */
add_filter( 'xmlrpc_enabled', '__return_false' );

/**
 * Beveiligingsheaders die WordPress zelf niet zet. Geen CSP hier — die hangt af
 * van wat de site inlaadt en hoort per project te worden bepaald.
 */
function react2u_hardening_security_headers( array $headers ): array {
	$headers['X-Content-Type-Options'] = 'nosniff';
	$headers['Referrer-Policy']        = 'strict-origin-when-cross-origin';
	$headers['X-Frame-Options']        = 'SAMEORIGIN';
	$headers['Permissions-Policy']     = 'camera=(), microphone=(), geolocation=(), browsing-topics=()';

	return $headers;
}
add_filter( 'wp_headers', 'react2u_hardening_security_headers' );

/**
 * Inlogfouten zeggen niet meer of de gebruikersnaam bestond. Het standaardbericht
 * ("Onbekende gebruikersnaam") is een gratis controle voor wie namen aan het
 * raden is.
 */
function react2u_hardening_generic_login_error(): string {
	return __( 'De combinatie van gebruikersnaam en wachtwoord klopt niet.', 'react2u-hardening' );
}
add_filter( 'login_errors', 'react2u_hardening_generic_login_error' );

require __DIR__ . '/quality-headers.php';
