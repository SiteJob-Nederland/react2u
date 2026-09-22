<?php
/**
 * Kruimelpad — minimaal drie niveaus, zichtbaar én in BreadcrumbList.
 *
 * Home -> artikel is te weinig: het overzicht hoort ertussen, en waar een
 * categorie bestaat ook die. Zichtbaar pad en gestructureerde data komen uit
 * dezelfde functie, zodat ze niet uit elkaar kunnen lopen.
 *
 * @package React2u
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @return array<int,array{name:string,url:string}>
 */
function react2u_breadcrumb_items(): array {
	$items = array(
		array( 'name' => __( 'Home', 'react2u' ), 'url' => home_url( '/' ) ),
	);

	if ( is_front_page() ) {
		return $items;
	}

	if ( is_singular() ) {
		$post_id   = (int) get_queried_object_id();
		$post_type = (string) get_post_type( $post_id );

		if ( 'page' === $post_type ) {
			foreach ( array_reverse( (array) get_post_ancestors( $post_id ) ) as $ancestor_id ) {
				$items[] = array(
					'name' => (string) get_the_title( $ancestor_id ),
					'url'  => (string) get_permalink( $ancestor_id ),
				);
			}
			/* Bestaande dienst-URL's blijven top-level; het zichtbare pad toont wel hun echte doelgroep. */
			if ( 0 === count( get_post_ancestors( $post_id ) ) ) {
				$slug      = (string) get_post_field( 'post_name', $post_id );
				$audience  = in_array( $slug, array( 'verzuimbegeleiding-wvp', 'verzuimbegeleiding-erd-zw', 'preventie-en-vitaliteit', 'begeleiding-en-coaching', 'trainingen-en-workshops', 'risicomanagement', 'diensten' ), true ) ? 'werkgevers' : ( 'verzuimprotocol' === $slug ? 'werknemers' : '' );
				$hub       = '' !== $audience ? get_page_by_path( $audience ) : null;
				if ( $hub instanceof WP_Post && 'publish' === $hub->post_status ) {
					$items[] = array( 'name' => (string) get_the_title( $hub ), 'url' => (string) get_permalink( $hub ) );
				}
			}
		} else {
			$items[] = react2u_archive_crumb( $post_type );

			$term = react2u_primary_term( $post_id, $post_type );
			if ( $term ) {
				$items[] = array( 'name' => $term->name, 'url' => (string) get_term_link( $term ) );
			}
		}

		$items[] = array( 'name' => (string) get_the_title( $post_id ), 'url' => (string) get_permalink( $post_id ) );

		return $items;
	}

	if ( is_post_type_archive() ) {
		$queried = get_query_var( 'post_type' );
		$queried = is_array( $queried ) ? reset( $queried ) : $queried;
		$items[] = react2u_archive_crumb( (string) $queried );

		return $items;
	}

	if ( is_tax() || is_category() || is_tag() ) {
		$term = get_queried_object();
		if ( $term instanceof WP_Term ) {
			$taxonomy = get_taxonomy( $term->taxonomy );
			$objects  = $taxonomy ? (array) $taxonomy->object_type : array();
			$items[]  = react2u_archive_crumb( $objects ? (string) reset( $objects ) : 'post' );

			foreach ( array_reverse( (array) get_ancestors( $term->term_id, $term->taxonomy ) ) as $ancestor_id ) {
				$ancestor = get_term( $ancestor_id, $term->taxonomy );
				if ( $ancestor instanceof WP_Term ) {
					$items[] = array( 'name' => $ancestor->name, 'url' => (string) get_term_link( $ancestor ) );
				}
			}

			$items[] = array( 'name' => $term->name, 'url' => (string) get_term_link( $term ) );
		}

		return $items;
	}

	if ( is_home() ) {
		$items[] = react2u_archive_crumb( 'post' );

		return $items;
	}

	if ( is_author() ) {
		$author  = get_queried_object();
		$items[] = react2u_archive_crumb( 'post' );

		/*
		 * Een tussenkruimel "Auteurs" alleen als die pagina echt bestaat. Een
		 * kruimelpad dat naar een 404 wijst is erger dan een kruimel minder —
		 * en het komt ook zo in de BreadcrumbList terecht.
		 */
		$auteurspagina = get_page_by_path( 'auteurs' );
		if ( $auteurspagina instanceof WP_Post && 'publish' === $auteurspagina->post_status ) {
			$items[] = array(
				'name' => (string) get_the_title( $auteurspagina ),
				'url'  => (string) get_permalink( $auteurspagina ),
			);
		}

		if ( $author instanceof WP_User ) {
			$items[] = array( 'name' => $author->display_name, 'url' => (string) get_author_posts_url( $author->ID ) );
		}

		return $items;
	}

	if ( is_search() ) {
		$items[] = array( 'name' => __( 'Zoeken', 'react2u' ), 'url' => (string) get_search_link() );
		$items[] = array(
			/* translators: %s: zoekterm */
			'name' => sprintf( __( 'Resultaten voor “%s”', 'react2u' ), get_search_query() ),
			'url'  => (string) get_search_link( get_search_query() ),
		);

		return $items;
	}

	if ( is_date() ) {
		$items[] = react2u_archive_crumb( 'post' );
		$items[] = array( 'name' => (string) get_the_archive_title(), 'url' => '' );

		return $items;
	}

	if ( is_404() ) {
		$items[] = array( 'name' => __( 'Pagina niet gevonden', 'react2u' ), 'url' => '' );
	}

	return $items;
}

/** Het overzicht waar een contenttype onder hangt. */
function react2u_archive_crumb( string $post_type ): array {
	if ( 'post' === $post_type ) {
		$posts_page = (int) get_option( 'page_for_posts' );

		return array(
			'name' => $posts_page ? (string) get_the_title( $posts_page ) : __( 'Blog', 'react2u' ),
			'url'  => $posts_page ? (string) get_permalink( $posts_page ) : home_url( '/blog/' ),
		);
	}

	$object  = get_post_type_object( $post_type );
	$archive = get_post_type_archive_link( $post_type );

	return array(
		'name' => $object ? (string) $object->labels->name : react2u_content_label( $post_type ),
		'url'  => $archive ?: home_url( '/' ),
	);
}

/**
 * De categorie die het beste bij dit artikel past. Zonder plugin voor een
 * "primaire categorie" is de eerste toegewezen term de meest voorspelbare keuze.
 */
function react2u_primary_term( int $post_id, string $post_type ): ?WP_Term {
	$taxonomy = match ( $post_type ) {
		'post'                  => 'category',
		'react2u_kennisbank' => 'react2u_kennisbank_cat',
		'product'               => 'product_cat',
		default                 => '',
	};

	if ( '' === $taxonomy || ! taxonomy_exists( $taxonomy ) ) {
		return null;
	}

	$terms = get_the_terms( $post_id, $taxonomy );
	if ( ! is_array( $terms ) || ! $terms ) {
		return null;
	}

	foreach ( $terms as $term ) {
		if ( 'category' === $taxonomy && 'uncategorized' === $term->slug ) {
			continue; // De standaardcategorie zegt niets; die hoort niet in het pad.
		}
		return $term;
	}

	return null;
}

/** Zichtbaar kruimelpad. De laatste kruimel is geen link: daar ben je al. */
function react2u_breadcrumbs( array $args = array() ): void {
	$items = react2u_breadcrumb_items();
	if ( count( $items ) < 2 ) {
		return;
	}

	$class = 'breadcrumbs' . ( isset( $args['class'] ) ? ' ' . $args['class'] : '' );
	$last  = count( $items ) - 1;

	printf( '<nav class="%s" aria-label="%s"><ol>', esc_attr( $class ), esc_attr__( 'Kruimelpad', 'react2u' ) );

	foreach ( $items as $index => $item ) {
		echo '<li>';
		if ( $index === $last || '' === $item['url'] ) {
			printf( '<span aria-current="page">%s</span>', esc_html( $item['name'] ) );
		} else {
			printf( '<a href="%s">%s</a>', esc_url( $item['url'] ), esc_html( $item['name'] ) );
		}
		echo '</li>';
	}

	echo '</ol></nav>';
}
