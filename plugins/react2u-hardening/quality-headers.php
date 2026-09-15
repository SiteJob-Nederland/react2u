<?php
/** Aanvulling op bestaande hardening. Serverheaders dekken ook statische bestanden. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
add_filter( 'wp_headers', static function ( array $headers ): array {
    $existing = array_change_key_case( $headers, CASE_LOWER );
    $defaults = array(
        'Content-Security-Policy' => "base-uri 'self'; object-src 'none'; frame-ancestors 'self'",
        'X-Content-Type-Options' => 'nosniff',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Permissions-Policy' => 'camera=(), microphone=(), geolocation=(), browsing-topics=()',
        'X-Frame-Options' => 'SAMEORIGIN',
    );
    if ( is_ssl() ) { $defaults['Strict-Transport-Security'] = 'max-age=86400'; }
    foreach ( $defaults as $name => $value ) {
        if ( ! isset( $existing[ strtolower( $name ) ] ) ) { $headers[ $name ] = $value; }
    }
    if ( ! get_option( 'blog_public' ) ) {
        $headers['X-Robots-Tag'] = 'noindex, nofollow';
        $headers['Cache-Control'] = 'no-store, private';
    }
    return $headers;
}, 100 );
