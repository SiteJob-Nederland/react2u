<?php
/** Gedeelde technische ondergrens. Bijwerken via scripts/kwaliteit-sync.mjs. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Verwijder de core-canonical pas nadat WordPress die heeft geregistreerd. */
add_action( 'wp_head', static function (): void {
    if ( function_exists( 'react2u_seo_plugin_active' ) && ! react2u_seo_plugin_active() ) {
        remove_action( 'wp_head', 'rel_canonical' );
    }
}, 0 );

/** Een thema/filter mag staging-noindex nooit omzetten in een publieke sitemap. */
add_filter( 'wp_sitemaps_enabled', static fn( $enabled ): bool => (bool) $enabled && (bool) get_option( 'blog_public' ), PHP_INT_MAX );
add_filter( 'wp_robots', static function ( array $robots ): array {
    if ( ! get_option( 'blog_public' ) || ( is_home() && ! is_front_page() && 0 === (int) wp_count_posts( 'post' )->publish ) ) {
        $robots['noindex'] = true;
    }
    return $robots;
}, 100 );

/** Behoud bestaande uitsluitingen en sluit wachtwoordpagina’s en afwijkende canonicals uit. */
add_filter( 'wp_sitemaps_posts_query_args', static function ( array $args, string $type ): array {
    $excluded = (array) apply_filters( 'react2u_sitemap_excluded_post_ids', array(), $type );
    if ( 'page' === $type && 0 === (int) wp_count_posts( 'post' )->publish ) {
        $excluded[] = (int) get_option( 'page_for_posts' );
    }
    static $canonical_exclusions = array();
    if ( ! isset( $canonical_exclusions[ $type ] ) ) {
        $canonical_exclusions[ $type ] = array();
        $candidates = get_posts( array( 'post_type' => $type, 'post_status' => 'publish', 'numberposts' => -1,
            'meta_key' => '_react2u_canonical_url', 'meta_compare' => 'EXISTS', 'fields' => 'ids', 'suppress_filters' => false ) );
        foreach ( $candidates as $id ) {
            $canonical = trim( (string) get_post_meta( $id, '_react2u_canonical_url', true ) );
            if ( '' !== $canonical && $canonical !== get_permalink( $id ) ) { $canonical_exclusions[ $type ][] = $id; }
        }
    }
    $args['post__not_in'] = array_values( array_unique( array_filter( array_map( 'intval', array_merge(
        (array) ( $args['post__not_in'] ?? array() ), $excluded, $canonical_exclusions[ $type ]
    ) ) ) ) );
    $args['has_password'] = false;
    return $args;
}, 100, 2 );

/** Werkelijke bestandswijzigingen breken de browsercache; queryparameters blijven behouden. */
function react2u_quality_asset_src( string $src ): string {
    $base = trailingslashit( get_template_directory_uri() );
    if ( ! str_starts_with( $src, $base ) ) { return $src; }
    $relative = rawurldecode( (string) wp_parse_url( substr( $src, strlen( $base ) ), PHP_URL_PATH ) );
    $file = realpath( get_template_directory() . '/' . $relative );
    $root = realpath( get_template_directory() );
    if ( ! $file || ! $root || ! str_starts_with( $file, $root . DIRECTORY_SEPARATOR ) || ! is_file( $file ) ) { return $src; }
    // De build geldt uitsluitend voor exact de huidige broninhoud. Na een edit
    // serveert het thema direct de nieuwe bron totdat de build opnieuw draait.
    static $asset_manifest = null;
    if ( null === $asset_manifest ) {
        $manifest_path = get_template_directory() . '/assets/quality-assets.json';
        $asset_manifest = is_file( $manifest_path ) ? json_decode( (string) file_get_contents( $manifest_path ), true ) : array();
    }
    $built = $asset_manifest[ $relative ] ?? null;
    if ( $built && preg_match( '#^[a-zA-Z0-9_./-]+\.quality\.[a-f0-9]{12}\.(css|js)$#', $built['path'] ?? '' ) ) {
        $built_file = realpath( get_template_directory() . '/' . $built['path'] );
        if ( $built_file && str_starts_with( $built_file, $root . DIRECTORY_SEPARATOR )
            && is_file( $built_file ) && hash_file( 'sha256', $file ) === $built['sourceHash'] ) {
            $query = array();
            wp_parse_str( (string) wp_parse_url( $src, PHP_URL_QUERY ), $query );
            unset( $query['ver'] );
            return add_query_arg( $query, $base . $built['path'] );
        }
    }
    if ( preg_match( '/[.-][a-f0-9]{8,}\./', basename( $file ) ) ) { return $src; }
    return add_query_arg( 'ver', (string) filemtime( $file ), $src );
}
add_filter( 'style_loader_src', 'react2u_quality_asset_src', 100 );
add_filter( 'script_loader_src', 'react2u_quality_asset_src', 100 );
add_action( 'wp_enqueue_scripts', static function (): void {
    foreach ( wp_scripts()->registered as $handle => $script ) {
        if ( is_string( $script->src ) && str_starts_with( $script->src, get_template_directory_uri() . '/' )
            && str_contains( $script->src, '/assets/js/site.js' ) ) {
            wp_script_add_data( $handle, 'strategy', 'defer' );
        }
    }
}, 100 );
add_action( 'after_setup_theme', static function (): void { add_image_size( 'react2u-author', 360, 360, true ); } );

/** Responsive thema-afbeeldingen uit het bouwmanifest; originelen blijven behouden. */
function react2u_quality_image_attrs( string $src, string $sizes = '(max-width: 767px) 100vw, 50vw' ): string {
    static $manifest = null;
    if ( null === $manifest ) {
        $path = get_template_directory() . '/assets/images/quality/manifest.json';
        $manifest = is_file( $path ) ? json_decode( (string) file_get_contents( $path ), true ) : array();
    }
    $base = trailingslashit( get_template_directory_uri() );
    // Oude beheerde inhoud kan nog HTTP gebruiken. Alleen dezelfde host/poort
    // volgt het huidige schema; externe bronnen blijven ongewijzigd.
    $source_url = wp_parse_url( $src );
    $theme_url = wp_parse_url( $base );
    if ( is_array( $source_url ) && is_array( $theme_url )
        && ! empty( $source_url['host'] ) && ! empty( $theme_url['host'] )
        && strtolower( $source_url['host'] ) === strtolower( $theme_url['host'] )
        && ( $source_url['port'] ?? null ) === ( $theme_url['port'] ?? null )
        && in_array( $source_url['scheme'] ?? '', array( 'http', 'https' ), true ) ) {
        $src = set_url_scheme( $src, $theme_url['scheme'] ?? 'https' );
    }
    $relative = str_starts_with( $src, $base ) ? (string) wp_parse_url( substr( $src, strlen( $base ) ), PHP_URL_PATH ) : '';
    $entry = $manifest[ $relative ] ?? null;
    if ( ! $entry || empty( $entry['variants'] ) ) { return 'src="' . esc_url( $src ) . '"'; }
    $set = array();
    $fallback = '';
    foreach ( $entry['variants'] as $variant ) {
        if ( ! is_file( get_template_directory() . '/' . $variant['path'] ) ) { continue; }
        $url = $base . $variant['path'];
        $set[] = esc_url( $url ) . ' ' . (int) $variant['width'] . 'w';
        if ( '' === $fallback || $variant['width'] <= 960 ) { $fallback = $url; }
    }
    if ( ! $set ) { return 'src="' . esc_url( $src ) . '"'; }
    return 'src="' . esc_url( $fallback ) . '" srcset="' . esc_attr( implode( ', ', $set ) ) . '" sizes="' . esc_attr( $sizes ) . '"';
}

/** Kleine varianten van bestaande crops; behoud verhouding en expliciete uitsnede. */
add_action( 'after_setup_theme', static function (): void {
    foreach ( array( 240, 480, 768 ) as $width ) { add_image_size( 'react2u-responsive-' . $width, $width, 0, false ); }
    foreach ( wp_get_registered_image_subsizes() as $name => $size ) {
        if ( empty( $size['crop'] ) || $size['width'] < 600 || empty( $size['height'] ) ) { continue; }
        foreach ( array( 240, 480, 768 ) as $width ) {
            if ( $width >= $size['width'] ) { continue; }
            $height = (int) round( $size['height'] * $width / $size['width'] );
            add_image_size( 'react2u-q-' . substr( md5( $name . $width ), 0, 10 ), $width, $height, $size['crop'] );
        }
    }
}, 100 );

/** Beelden onder de vouw concurreren niet met het eerste zichtbare beeld. */
add_filter( 'wp_get_attachment_image_attributes', static function ( array $attr ): array {
	if ( 'lazy' === ( $attr['loading'] ?? '' ) && 'high' !== ( $attr['fetchpriority'] ?? '' ) ) {
		$attr['fetchpriority'] = 'low';
	}
	return $attr;
}, 100 );

/** Kleine browsericonen uit de beeldbuild; een ingesteld WordPress-site-icoon blijft leidend. */
add_action( 'wp_head', static function (): void {
    if ( has_site_icon() ) { return; }
    $file = get_template_directory() . '/assets/images/quality/favicon.json';
    if ( ! is_file( $file ) ) { return; }
    $icons = json_decode( (string) file_get_contents( $file ), true );
    if ( ! is_array( $icons ) ) { return; }
    $valid = array_filter( $icons, static fn( $icon ): bool =>
        isset( $icon['path'], $icon['size'] )
        && preg_match( '#^assets/images/quality/icon-[a-f0-9]{16}-(32|180)\.png$#', $icon['path'] )
        && is_file( get_template_directory() . '/' . $icon['path'] ) );
    if ( ! $valid ) { return; }
    remove_action( 'wp_head', 'react2u_favicon', 2 );
    foreach ( $valid as $icon ) {
        printf( '<link rel="%1$s" type="image/png" href="%2$s" sizes="%3$dx%3$d">', 32 === (int) $icon['size'] ? 'icon' : 'apple-touch-icon', esc_url( get_template_directory_uri() . '/' . $icon['path'] ), (int) $icon['size'] );
    }
}, 1 );
