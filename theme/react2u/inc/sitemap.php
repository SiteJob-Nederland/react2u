<?php
/**
 * XML-sitemap.
 *
 * WordPress levert sinds 5.5 zelf een sitemap op /wp-sitemap.xml. Die is prima,
 * alleen zoekt iedereen — mens en crawler — eerst op /sitemap.xml. Hier stuurt
 * dat adres netjes door in plaats van dood te lopen op een 404.
 *
 * Draait er een SEO-plugin met een eigen sitemap, dan blijft dit bestand stil.
 *
 * @package React2u
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function react2u_sitemaps_enabled(): bool {
	return ! react2u_seo_plugin_active();
}

/** Core-sitemaps aan laten staan; zonder sitemap is er niets om naar te wijzen. */
function react2u_enable_core_sitemaps( bool $enabled ): bool {
	return react2u_sitemaps_enabled() ? true : $enabled;
}
add_filter( 'wp_sitemaps_enabled', 'react2u_enable_core_sitemaps' );

/**
 * Voorkom een onterechte 404-status op geldige sitemaps zonder blogberichten.
 *
 * WordPress bepaalt de HTTP-status vóór WP_Sitemaps de XML rendert. Op een
 * nieuwe site zonder berichten ziet de hoofdquery niets en zet core alvast een
 * 404, ook wanneer de sitemapindex daarna wél pagina-URL's bevat. Alleen
 * aantoonbaar geldige sitemaproutes slaan die vroege statusbepaling over;
 * onbekende providers en lege pagina's behouden daardoor hun echte 404.
 */
function react2u_sitemap_pre_handle_404( bool $preempt, WP_Query $query ): bool {
	if ( $preempt || ! react2u_sitemaps_enabled() ) {
		return $preempt;
	}

	$stylesheet = sanitize_text_field( (string) $query->get( 'sitemap-stylesheet' ) );
	if ( in_array( $stylesheet, array( 'sitemap', 'index' ), true ) ) {
		status_header( 200 );
		return true;
	}

	$sitemap = sanitize_text_field( (string) $query->get( 'sitemap' ) );
	if ( '' === $sitemap ) {
		return $preempt;
	}

	$server = wp_sitemaps_get_server();
	if ( 'index' === $sitemap ) {
		status_header( 200 );
		return true;
	}

	$provider = $server->registry->get_provider( $sitemap );
	if ( ! $provider ) {
		return $preempt;
	}

	$page     = max( 1, absint( $query->get( 'paged' ) ) );
	$subtype  = sanitize_text_field( (string) $query->get( 'sitemap-subtype' ) );
	$url_list = $provider->get_url_list( $page, $subtype );

	if ( ! empty( $url_list ) ) {
		status_header( 200 );
		return true;
	}

	return $preempt;
}
add_filter( 'pre_handle_404', 'react2u_sitemap_pre_handle_404', 10, 2 );

/**
 * /sitemap.xml en /sitemap_index.xml wijzen naar de echte sitemap.
 * Een 301 zodat een crawler het adres onthoudt.
 */
function react2u_sitemap_redirect(): void {
	if ( ! react2u_sitemaps_enabled() ) {
		return;
	}

	$path = strtolower( trim( (string) wp_parse_url( (string) ( $_SERVER['REQUEST_URI'] ?? '' ), PHP_URL_PATH ), '/' ) );

	if ( in_array( $path, array( 'sitemap.xml', 'sitemap_index.xml', 'sitemap-index.xml' ), true ) ) {
		wp_safe_redirect( home_url( '/wp-sitemap.xml' ), 301 );
		exit;
	}
}
add_action( 'template_redirect', 'react2u_sitemap_redirect', 0 );

/**
 * Pagina's zonder eigen inhoud horen niet in de sitemap. Een crawler die op een
 * lege pagina uitkomt, leert daar niets van.
 *
 * @param array<string,mixed> $args
 * @return array<string,mixed>
 */
function react2u_sitemap_query_args( array $args, string $post_type ): array {
	if ( 'page' === $post_type ) {
		$counts = wp_count_posts( 'post' );
		if ( 0 === (int) ( $counts->publish ?? 0 ) ) {
			$args['post__not_in'] = array_filter( array( (int) get_option( 'page_for_posts' ) ) );
		}
	}

	return $args;
}
add_filter( 'wp_sitemaps_posts_query_args', 'react2u_sitemap_query_args', 10, 2 );

/**
 * De laatste wijzigingsdatum meegeven. Core laat die weg; met lastmod erbij
 * hoeft een crawler ongewijzigde artikelen niet opnieuw op te halen.
 *
 * @param array<string,mixed> $entry
 * @return array<string,mixed>
 */
function react2u_sitemap_lastmod( array $entry, WP_Post $post ): array {
	$entry['lastmod'] = get_post_modified_time( DATE_W3C, true, $post );

	return $entry;
}
add_filter( 'wp_sitemaps_posts_entry', 'react2u_sitemap_lastmod', 10, 2 );

/** Bijlagen horen niet in de index. */
function react2u_sitemap_exclude_post_types( array $post_types ): array {
	unset( $post_types['attachment'] );

	return $post_types;
}
add_filter( 'wp_sitemaps_post_types', 'react2u_sitemap_exclude_post_types' );

/** Regel in robots.txt, ook wanneer WordPress in een submap staat. */
function react2u_robots_sitemap( string $output ): string {
	if ( ! react2u_sitemaps_enabled() || str_contains( $output, 'wp-sitemap.xml' ) ) {
		return $output;
	}

	return $output . "\nSitemap: " . esc_url( home_url( '/wp-sitemap.xml' ) ) . "\n";
}
add_filter( 'robots_txt', 'react2u_robots_sitemap', 20 );
