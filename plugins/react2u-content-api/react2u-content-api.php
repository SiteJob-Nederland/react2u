<?php
/**
 * Plugin Name: react2u Content API
 * Description: Getypeerde SEO-velden voor WordPress REST en compatibiliteit met NOVA Bridge Suite.
 * Version: 0.1.0
 * Author: SiteJob
 * Requires at least: 6.6
 * Requires PHP: 8.1
 * License: GPL-2.0-or-later
 * Text Domain: react2u
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Opslag en beschrijvende REST-namen; werkt ook zonder de vendor-plugin. */
function react2u_content_api_fields(): array {
	return array(
		'_react2u_seo_title' => array( 'seo_title_text', 'SEO-titel. Leeg gebruikt de paginatitel.', 200 ),
		'_react2u_meta_description' => array( 'seo_meta_description_text', 'Meta-description. Leeg gebruikt de inhoudssamenvatting.', 320 ),
		'_react2u_canonical_url' => array( 'seo_canonical_url', 'Absolute HTTP(S) canonical. Leeg gebruikt de permalink.', 2000 ),
	);
}

function react2u_content_api_types(): array {
	return (array) apply_filters( 'react2u_content_api_post_types', array( 'post', 'page', 'react2u_kennisbank' ) );
}

function react2u_content_api_sanitize( $value, string $key ): string {
	if ( ! is_string( $value ) ) { return ''; }
	if ( '_react2u_canonical_url' === $key ) { return esc_url_raw( $value, array( 'http', 'https' ) ); }
	return mb_substr( sanitize_text_field( $value ), 0, react2u_content_api_fields()[ $key ][2] ?? 320 );
}

function react2u_content_api_register(): void {
	foreach ( react2u_content_api_types() as $type ) {
		if ( ! post_type_exists( $type ) ) { continue; }
		add_post_type_support( $type, 'custom-fields' );
		foreach ( react2u_content_api_fields() as $key => $field ) {
			$schema = array( 'type' => 'string', 'description' => $field[1], 'maxLength' => $field[2], 'context' => array( 'edit' ) );
			if ( 'seo_canonical_url' === $field[0] ) { $schema['pattern'] = '^(https?://\\S+)?$'; }
			register_post_meta( $type, $key, array(
				'type' => 'string', 'single' => true, 'default' => '',
				'show_in_rest' => array( 'name' => $field[0], 'schema' => $schema ),
				'sanitize_callback' => 'react2u_content_api_sanitize',
				'auth_callback' => static fn( $allowed, $meta_key, $post_id ): bool => current_user_can( 'edit_post', $post_id ),
			) );
		}
	}
}
add_action( 'init', 'react2u_content_api_register', 30 );

/** De officiële Suite-filters voegen de eigen renderer toe aan haar SEO-mapping. */
function react2u_content_api_suite_aliases( array $groups ): array {
	$groups['title'] = array_merge( array( '_react2u_seo_title', 'seo_title_text' ), $groups['title'] ?? array() );
	$groups['description'] = array_merge( array( '_react2u_meta_description', 'seo_meta_description_text' ), $groups['description'] ?? array() );
	return $groups;
}
add_filter( 'cf_tmrb_post_seo_alias_groups', 'react2u_content_api_suite_aliases' );

function react2u_content_api_suite_seo( array $updates, array $input, array $active, array $targets, $title, $description ): array {
	if ( null !== $title ) { $updates['_react2u_seo_title'] = react2u_content_api_sanitize( $title, '_react2u_seo_title' ); }
	if ( null !== $description ) { $updates['_react2u_meta_description'] = react2u_content_api_sanitize( $description, '_react2u_meta_description' ); }
	return $updates;
}
add_filter( 'cf_tmrb_post_seo_updates', 'react2u_content_api_suite_seo', 10, 6 );

function react2u_content_api_seo_pairs(): array {
	return array( '_react2u_seo_title' => '_yoast_wpseo_title', '_react2u_meta_description' => '_yoast_wpseo_metadesc', '_react2u_canonical_url' => '_yoast_wpseo_canonical' );
}

/** Ook een directe Yoast-meta-update bereikt het thema. Geen dubbele renderer. */
function react2u_content_api_sync( $meta_id, int $post_id, string $key, $value ): void {
	static $syncing = false;
	$pairs = react2u_content_api_seo_pairs();
	$map = $pairs + array_flip( $pairs );
	if ( $syncing || ! isset( $map[ $key ] ) || ! in_array( get_post_type( $post_id ), react2u_content_api_types(), true ) ) { return; }
	$syncing = true;
	try {
		$clean = react2u_content_api_sanitize( $value, isset( $pairs[ $key ] ) ? $key : $map[ $key ] );
		update_post_meta( $post_id, $key, wp_slash( $clean ) );
		update_post_meta( $post_id, $map[ $key ], wp_slash( $clean ) );
	} finally { $syncing = false; }
}
add_action( 'added_post_meta', 'react2u_content_api_sync', 10, 4 );
add_action( 'updated_post_meta', 'react2u_content_api_sync', 10, 4 );

function react2u_content_api_delete( $meta_ids, int $post_id, string $key, $value ): void {
	$pairs = react2u_content_api_seo_pairs();
	$map = $pairs + array_flip( $pairs );
	if ( isset( $map[ $key ] ) && in_array( get_post_type( $post_id ), react2u_content_api_types(), true ) ) { delete_post_meta( $post_id, $map[ $key ] ); }
}
add_action( 'deleted_post_meta', 'react2u_content_api_delete', 10, 4 );

/** Publieke leeswijzers. Geen interne AGENTS.md, geen geheimen en geen SEO-garantie. */
function react2u_content_api_discovery(): void {
	$path = (string) wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ), PHP_URL_PATH );
	$file = basename( $path );
	if ( ! in_array( $file, array( 'llms.txt', 'agents.md' ), true ) || $path !== wp_parse_url( home_url( '/' . $file ), PHP_URL_PATH ) ) { return; }
	if ( ! get_option( 'blog_public' ) || ! apply_filters( 'react2u_public_content_guides', true ) ) {
		status_header( 404 );
		nocache_headers();
		exit;
	}
	status_header( 200 );
	header( 'Content-Type: text/plain; charset=UTF-8' );
	header( 'X-Robots-Tag: noindex' );
	header( 'Cache-Control: public, max-age=300' );
	$name = sanitize_text_field( get_bloginfo( 'name' ) );
	echo '# ' . $name . "\n\n";
	if ( 'agents.md' === $file ) {
		echo "Publieke leeswijzer voor deze website; geen intern ontwikkelbestand. Gebruik canonicals als bronverwijzing en de sitemap voor actuele publicaties. Controleer auteur, datum en bronnen. Dit bestand verleent geen toegang tot redactie, accounts of persoonsgegevens.\n\n";
	}
	echo '## Website' . "\n\n- [Home](" . home_url( '/' ) . ")\n- [XML-sitemap](" . home_url( '/wp-sitemap.xml' ) . ")\n";
	foreach ( react2u_content_api_types() as $type ) {
		$archive = get_post_type_archive_link( $type );
		$object = get_post_type_object( $type );
		if ( $archive && $archive !== home_url( '/' ) && $object ) {
			echo '- [' . sanitize_text_field( $object->labels->name ) . '](' . $archive . ")\n";
		}
	}
	echo "\n## Recente publicaties\n\n";
	foreach ( get_posts( array( 'post_type' => array_values( array_diff( react2u_content_api_types(), array( 'page' ) ) ), 'post_status' => 'publish', 'has_password' => false, 'numberposts' => 20 ) ) as $post ) {
		$title = str_replace( array( '[', ']', "\n", "\r" ), '', wp_strip_all_tags( get_the_title( $post ) ) );
		echo '- [' . $title . '](' . get_permalink( $post ) . ")\n";
	}
	exit;
}
add_action( 'template_redirect', 'react2u_content_api_discovery', 1 );

/** Per project expliciete verhuisde URL’s opgeven; geen algemene 404-omleiding. */
function react2u_content_api_legacy_redirects(): void {
	if ( ! in_array( $_SERVER['REQUEST_METHOD'] ?? 'GET', array( 'GET', 'HEAD' ), true ) || is_preview() || is_customize_preview() ) { return; }
	$path = (string) wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ), PHP_URL_PATH );
	$base = (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH );
	if ( ! str_starts_with( $path, $base ) ) { return; }
	$relative = '/' . ltrim( substr( $path, strlen( $base ) ), '/' );
	$routes = (array) apply_filters( 'react2u_legacy_redirects', array() );
	$target = $routes[ $relative ] ?? null;
	if ( ! is_string( $target ) || ! str_starts_with( $target, '/' ) || str_starts_with( $target, '//' ) ) { return; }
	$url = home_url( $target );
	if ( $path === wp_parse_url( $url, PHP_URL_PATH ) ) { return; }
	wp_safe_redirect( $url, 301, 'react2u URL-migratie' );
	exit;
}
add_action( 'template_redirect', 'react2u_content_api_legacy_redirects', 0 );
