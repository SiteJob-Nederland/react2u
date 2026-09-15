<?php
/**
 * Meta-informatie en gestructureerde data.
 *
 * Bewust in het thema en niet in een plugin: de site heeft geen SEO-plugin
 * nodig om compleet te zijn. Draait er toch één (Yoast, Rank Math, AIOSEO,
 * SEOPress), dan houdt dit bestand zich stil om dubbele tags te voorkomen.
 *
 * Drie dingen waar het in de praktijk misgaat en die hier dus vastliggen:
 * een meta-description op élke paginasoort, een kruimelpad van drie niveaus in
 * het schema, en een volwaardig ImageObject met afmetingen in plaats van een
 * kale URL.
 *
 * @package React2u
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function react2u_seo_plugin_active(): bool {
	return defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'AIOSEO_VERSION' ) || defined( 'SEOPRESS_VERSION' );
}

/**
 * Beschrijving voor de huidige weergave. Nooit leeg: een overzichtspagina
 * zonder beschrijving laat Google zelf een zin uit de pagina plukken.
 *
 * PER KLANT: de vaste zinnen hieronder vervangen door tekst die klopt bij de
 * dienstverlening. Ze staan in de zoekresultaten, dus ze verkopen mee.
 */
function react2u_meta_description(): string {
	/* quality: meta_description override */
	if ( is_singular() ) {
		$override = trim( (string) get_post_meta( (int) get_queried_object_id(), '_react2u_meta_description', true ) );
		if ( '' !== $override ) { return $override; }
	}

	$site = get_bloginfo( 'name' );

	if ( is_front_page() ) {
		$description = trim( (string) get_bloginfo( 'description' ) );
		if ( '' !== $description ) {
			return $description;
		}

		$tagline = (string) react2u_get( 'contact.tagline' );

		return react2u_is_placeholder( $tagline )
			/* translators: %s: sitenaam */
			? sprintf( __( '%s — officiële website.', 'react2u' ), $site )
			: $tagline;
	}

	if ( is_singular() ) {
		$post_id = (int) get_queried_object_id();
		$custom  = trim( (string) get_post_meta( $post_id, '_react2u_meta_description', true ) );

		return $custom ?: react2u_summary( $post_id );
	}

	if ( is_home() ) {
		$posts_page = (int) get_option( 'page_for_posts' );
		if ( $posts_page ) {
			$custom = trim( (string) get_post_meta( $posts_page, '_react2u_meta_description', true ) );
			if ( '' !== $custom ) {
				return $custom;
			}
			$summary = react2u_summary( $posts_page );
			if ( '' !== $summary ) {
				return $summary;
			}
		}

		/* translators: %s: sitenaam */
		return sprintf( __( 'Artikelen en praktische kennis van het team van %s.', 'react2u' ), $site );
	}

	if ( is_post_type_archive( 'react2u_kennisbank' ) ) {
		/* translators: %s: sitenaam */
		return sprintf( __( 'De kennisbank van %s: stap voor stap uitgelegd hoe het werkt.', 'react2u' ), $site );
	}

	if ( is_post_type_archive() ) {
		$description = trim( wp_strip_all_tags( (string) get_the_post_type_description() ) );

		/* translators: 1: naam van het overzicht, 2: sitenaam */
		return $description ?: sprintf( __( 'Overzicht van %1$s bij %2$s.', 'react2u' ), strtolower( (string) post_type_archive_title( '', false ) ), $site );
	}

	if ( is_category() || is_tag() || is_tax() ) {
		$term        = get_queried_object();
		$description = $term instanceof WP_Term ? trim( wp_strip_all_tags( (string) $term->description ) ) : '';
		$name        = $term instanceof WP_Term ? $term->name : '';

		/* translators: 1: onderwerp, 2: sitenaam */
		return $description ?: sprintf( __( 'Alles over %1$s, verzameld door %2$s.', 'react2u' ), $name, $site );
	}

	if ( is_author() ) {
		$author = get_queried_object();
		if ( $author instanceof WP_User ) {
			$bio = trim( wp_strip_all_tags( (string) get_the_author_meta( 'description', $author->ID ) ) );
			if ( '' !== $bio ) {
				return mb_substr( $bio, 0, 155 );
			}

			$role = react2u_author_job_title( $author->ID );

			/* translators: 1: naam, 2: functie of "auteur", 3: sitenaam */
			return trim( sprintf( __( 'Artikelen van %1$s, %2$s bij %3$s.', 'react2u' ), $author->display_name, $role ?: __( 'auteur', 'react2u' ), $site ) );
		}
	}

	if ( is_search() ) {
		/* translators: 1: zoekterm, 2: sitenaam */
		return sprintf( __( 'Zoekresultaten voor “%1$s” bij %2$s.', 'react2u' ), get_search_query(), $site );
	}

	if ( is_404() ) {
		/* translators: %s: sitenaam */
		return sprintf( __( 'Deze pagina bestaat niet (meer). Zoek verder of neem contact op met %s.', 'react2u' ), $site );
	}

	/* translators: %s: sitenaam */
	return sprintf( __( '%s — officiële website.', 'react2u' ), $site );
}

/** Canonieke URL van de huidige weergave. */
function react2u_canonical_url(): string {
	/* quality: canonical_url override */
	if ( is_singular() ) {
		$override = trim( (string) get_post_meta( (int) get_queried_object_id(), '_react2u_canonical_url', true ) );
		if ( '' !== $override ) { return $override; }
	}

	if ( is_front_page() ) {
		return home_url( '/' );
	}

	if ( is_singular() ) {
		$post_id = (int) get_queried_object_id();
		$custom  = trim( (string) get_post_meta( $post_id, '_react2u_canonical_url', true ) );

		return $custom ?: (string) get_permalink( $post_id );
	}

	if ( is_home() ) {
		$posts_page = (int) get_option( 'page_for_posts' );

		return $posts_page ? (string) get_permalink( $posts_page ) : home_url( '/' );
	}

	if ( is_post_type_archive() ) {
		$queried = get_query_var( 'post_type' );
		$queried = is_array( $queried ) ? reset( $queried ) : $queried;
		$link    = get_post_type_archive_link( (string) $queried );

		return $link ?: home_url( '/' );
	}

	if ( is_category() || is_tag() || is_tax() ) {
		$term = get_queried_object();
		if ( $term instanceof WP_Term ) {
			$link = get_term_link( $term );

			return is_string( $link ) ? $link : home_url( '/' );
		}
	}

	if ( is_author() ) {
		$author = get_queried_object();

		return $author instanceof WP_User ? (string) get_author_posts_url( $author->ID ) : home_url( '/' );
	}

	if ( is_search() ) {
		return (string) get_search_link( get_search_query() );
	}

	return home_url( add_query_arg( array() ) );
}

/**
 * Afbeelding als ImageObject, mét afmetingen. Een kale URL laat Google zelf
 * uitzoeken hoe groot iets is; met breedte en hoogte erbij kan een afbeelding
 * in de zoekresultaten worden getoond.
 *
 * @return array<string,mixed>|null
 */
function react2u_image_object( ?int $attachment_id, string $size = 'full' ): ?array {
	if ( ! $attachment_id ) {
		return null;
	}

	$image = wp_get_attachment_image_src( $attachment_id, $size );
	if ( ! $image ) {
		return null;
	}

	$object = array(
		'@type'  => 'ImageObject',
		'@id'    => $image[0] . '#image',
		'url'    => $image[0],
		'width'  => (int) $image[1],
		'height' => (int) $image[2],
	);

	$alt = trim( (string) get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) );
	if ( '' !== $alt ) {
		$object['caption'] = $alt;
	}

	return $object;
}

/** Het logo als ImageObject, voor Organization. Null zolang er geen logo is. */
function react2u_logo_image_object(): ?array {
	$custom_logo_id = (int) get_theme_mod( 'custom_logo' );
	$object         = react2u_image_object( $custom_logo_id );

	if ( $object ) {
		return $object;
	}

	$path = REACT2U_DIR . '/assets/images/logo.png';
	if ( ! file_exists( $path ) ) {
		return null;
	}

	$size = getimagesize( $path );

	return array(
		'@type'  => 'ImageObject',
		'@id'    => home_url( '/#logo' ),
		'url'    => REACT2U_URI . '/assets/images/logo.png',
		'width'  => $size ? (int) $size[0] : 1000,
		'height' => $size ? (int) $size[1] : 300,
	);
}

function react2u_output_meta(): void {
	if ( react2u_seo_plugin_active() ) {
		return;
	}

	$description = react2u_meta_description();
	$canonical   = react2u_canonical_url();
	$type        = is_singular() && react2u_is_article() ? 'article' : 'website';

	$image = null;
	if ( is_singular() && has_post_thumbnail() ) {
		$image = react2u_image_object( (int) get_post_thumbnail_id(), 'full' );
	}

	echo "\n";
	if ( '' !== $description ) {
		printf( "<meta name=\"description\" content=\"%s\">\n", esc_attr( $description ) );
	}
	printf( "<link rel=\"canonical\" href=\"%s\">\n", esc_url( $canonical ) );

	if ( is_search() || is_404() || is_paged() && is_home() ) {
		echo "<meta name=\"robots\" content=\"noindex, follow\">\n";
	}

	printf( "<meta property=\"og:site_name\" content=\"%s\">\n", esc_attr( get_bloginfo( 'name' ) ) );
	printf( "<meta property=\"og:locale\" content=\"%s\">\n", esc_attr( str_replace( '-', '_', (string) get_bloginfo( 'language' ) ) ) );
	printf( "<meta property=\"og:type\" content=\"%s\">\n", esc_attr( $type ) );
	printf( "<meta property=\"og:title\" content=\"%s\">\n", esc_attr( wp_get_document_title() ) );
	printf( "<meta property=\"og:url\" content=\"%s\">\n", esc_url( $canonical ) );
	if ( '' !== $description ) {
		printf( "<meta property=\"og:description\" content=\"%s\">\n", esc_attr( $description ) );
	}
	if ( $image ) {
		printf( "<meta property=\"og:image\" content=\"%s\">\n", esc_url( (string) $image['url'] ) );
		printf( "<meta property=\"og:image:width\" content=\"%d\">\n", (int) $image['width'] );
		printf( "<meta property=\"og:image:height\" content=\"%d\">\n", (int) $image['height'] );
	}
	if ( 'article' === $type ) {
		printf( "<meta property=\"article:published_time\" content=\"%s\">\n", esc_attr( (string) get_the_date( DATE_ATOM ) ) );
		printf( "<meta property=\"article:modified_time\" content=\"%s\">\n", esc_attr( (string) get_the_modified_date( DATE_ATOM ) ) );
	}
	printf( "<meta name=\"twitter:card\" content=\"%s\">\n", $image ? 'summary_large_image' : 'summary' );
}
add_action( 'wp_head', 'react2u_output_meta', 2 );

/**
 * Waarde uit inc/proof.php die veilig in het schema mag: leeg en nog niet
 * ingevulde waarden laten we weg. Een [PLACEHOLDER] als telefoonnummer
 * doorgeven aan Google is erger dan geen telefoonnummer.
 */
function react2u_schema_value( string $path ): string {
	$value = (string) react2u_get( $path );

	return react2u_is_placeholder( $value ) ? '' : trim( $value );
}

/**
 * Eén JSON-LD-graaf per pagina. Losse blokken zouden elkaar niet kennen; met
 * één graaf verwijzen artikel, auteur en organisatie naar elkaar.
 */
function react2u_output_schema(): void {
	if ( react2u_seo_plugin_active() ) {
		return;
	}

	$home            = trailingslashit( home_url() );
	$organization_id = $home . '#organization';
	$website_id      = $home . '#website';

	$organization = array(
		'@type' => 'Organization',
		'@id'   => $organization_id,
		'name'  => get_bloginfo( 'name' ),
		'url'   => home_url( '/' ),
	);

	$logo = react2u_logo_image_object();
	if ( $logo ) {
		$organization['logo']  = $logo;
		$organization['image'] = array( '@id' => $logo['@id'] );
	}

	foreach ( array(
		'description' => 'contact.tagline',
		'email'       => 'contact.email',
		'telephone'   => 'contact.phone',
		'vatID'       => 'contact.vat',
	) as $key => $path ) {
		$value = react2u_schema_value( $path );
		if ( '' !== $value ) {
			$organization[ $key ] = $value;
		}
	}

	$address = array_filter(
		array(
			'streetAddress'   => react2u_schema_value( 'contact.street' ),
			'postalCode'      => react2u_schema_value( 'contact.postcode' ),
			'addressLocality' => react2u_schema_value( 'contact.city' ),
			'addressCountry'  => react2u_schema_value( 'contact.country_iso' ),
		)
	);
	if ( $address ) {
		$organization['address'] = array( '@type' => 'PostalAddress' ) + $address;
	}

	$identifier = react2u_schema_value( 'contact.kvk' );
	if ( '' !== $identifier ) {
		$organization['identifier'] = array(
			'@type' => 'PropertyValue',
			'name'  => 'KvK',
			'value' => $identifier,
		);
	}

	/* Badge en schema delen één fail-closed bron voor publieke ratingdata. */
	$rating = react2u_public_rating_data();
	if ( null !== $rating ) {
		$organization['aggregateRating'] = array(
			'@type'       => 'AggregateRating',
			'ratingValue' => $rating['score_normalized'],
			'bestRating'  => $rating['max_normalized'],
			'worstRating' => '1',
			'ratingCount' => (int) $rating['count_number'],
		);
	}

	$graph = array(
		$organization,
		array(
			'@type'           => 'WebSite',
			'@id'             => $website_id,
			'url'             => home_url( '/' ),
			'name'            => get_bloginfo( 'name' ),
			'description'     => react2u_meta_description(),
			'publisher'       => array( '@id' => $organization_id ),
			'inLanguage'      => get_bloginfo( 'language' ),
			'potentialAction' => array(
				'@type'       => 'SearchAction',
				'target'      => array(
					'@type'       => 'EntryPoint',
					'urlTemplate' => home_url( '/?s={search_term_string}' ),
				),
				'query-input' => 'required name=search_term_string',
			),
		),
	);

	$canonical = react2u_canonical_url();

	$graph[] = array(
		'@type'       => is_front_page() ? array( 'WebPage', 'CollectionPage' ) : 'WebPage',
		'@id'         => $canonical . '#webpage',
		'url'         => $canonical,
		'name'        => wp_get_document_title(),
		'description' => react2u_meta_description(),
		'isPartOf'    => array( '@id' => $website_id ),
		'inLanguage'  => get_bloginfo( 'language' ),
		'breadcrumb'  => array( '@id' => $canonical . '#breadcrumbs' ),
	);

	$graph[] = react2u_breadcrumb_schema( $canonical );

	if ( is_singular() ) {
		$post_id   = (int) get_queried_object_id();
		$post_type = (string) get_post_type( $post_id );

		if ( react2u_is_article( $post_type ) ) {
			$author_id = (int) get_post_field( 'post_author', $post_id );
			$article   = array(
				'@type'            => 'post' === $post_type ? 'BlogPosting' : 'Article',
				'@id'              => get_permalink( $post_id ) . '#article',
				'url'              => get_permalink( $post_id ),
				'headline'         => get_the_title( $post_id ),
				'description'      => react2u_meta_description(),
				'datePublished'    => get_the_date( DATE_ATOM, $post_id ),
				'dateModified'     => get_the_modified_date( DATE_ATOM, $post_id ),
				'inLanguage'       => get_bloginfo( 'language' ),
				'isPartOf'         => array( '@id' => $canonical . '#webpage' ),
				'mainEntityOfPage' => array( '@id' => $canonical . '#webpage' ),
				'publisher'        => array( '@id' => $organization_id ),
				'wordCount'        => (int) preg_match_all( '/[\p{L}\p{N}\'’-]+/u', wp_strip_all_tags( (string) get_post_field( 'post_content', $post_id ) ) ),
			);

			if ( $author_id ) {
				$article['author'] = array( '@id' => get_author_posts_url( $author_id ) . '#person' );
				$graph[]           = react2u_person_schema( $author_id, $organization_id );
			}

			$image = react2u_image_object( (int) get_post_thumbnail_id( $post_id ), 'full' );
			if ( $image ) {
				$article['image'] = $image;
			}

			$terms = get_the_terms( $post_id, 'post' === $post_type ? 'category' : 'react2u_kennisbank_cat' );
			if ( is_array( $terms ) && $terms ) {
				$article['articleSection'] = wp_list_pluck( $terms, 'name' );
			}

			$graph[] = $article;
		} elseif ( ! is_front_page() ) {
			/*
			 * De homepage heeft hierboven al een WebPage-knoop; een tweede zou
			 * dezelfde pagina twee keer beschrijven.
			 */
			$graph[] = array(
				'@type'      => 'WebPage',
				'@id'        => get_permalink( $post_id ) . '#content',
				'url'        => get_permalink( $post_id ),
				'name'       => get_the_title( $post_id ),
				'inLanguage' => get_bloginfo( 'language' ),
				'isPartOf'   => array( '@id' => $canonical . '#webpage' ),
			);
		}

		/*
		 * FAQPage komt uit de FAQ-sectie in de inhoud zelf. Een redactietool
		 * levert die sectie wel, maar nooit het schema erbij.
		 */
		$prepared = react2u_prepare_content( (string) apply_filters( 'the_content', (string) get_post_field( 'post_content', $post_id ) ) );
		$pairs    = react2u_faq_pairs( $prepared['faq'] );
		if ( $pairs ) {
			$graph[] = array(
				'@type'      => 'FAQPage',
				'@id'        => get_permalink( $post_id ) . '#faq',
				'inLanguage' => get_bloginfo( 'language' ),
				'isPartOf'   => array( '@id' => $canonical . '#webpage' ),
				'mainEntity' => array_map(
					static fn( array $pair ): array => array(
						'@type'          => 'Question',
						'name'           => $pair['question'],
						'acceptedAnswer' => array( '@type' => 'Answer', 'text' => $pair['answer'] ),
					),
					$pairs
				),
			);
		}
	} elseif ( is_author() ) {
		$author = get_queried_object();
		if ( $author instanceof WP_User ) {
			$graph[] = react2u_person_schema( (int) $author->ID, $organization_id );
			$graph[] = array(
				'@type'      => 'ProfilePage',
				'@id'        => get_author_posts_url( $author->ID ) . '#profile',
				'url'        => get_author_posts_url( $author->ID ),
				'name'       => wp_get_document_title(),
				'mainEntity' => array( '@id' => get_author_posts_url( $author->ID ) . '#person' ),
				'inLanguage' => get_bloginfo( 'language' ),
			);
		}
	} elseif ( is_home() || is_archive() ) {
		$graph[] = array(
			'@type'       => 'CollectionPage',
			'@id'         => $canonical . '#collection',
			'url'         => $canonical,
			'name'        => wp_get_document_title(),
			'description' => react2u_meta_description(),
			'isPartOf'    => array( '@id' => $website_id ),
			'inLanguage'  => get_bloginfo( 'language' ),
		);
	}

	$data = apply_filters( 'react2u_schema_graph', array( '@context' => 'https://schema.org', '@graph' => array_values( $graph ) ) );

	printf(
		"<script type=\"application/ld+json\">%s</script>\n",
		wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE )
	);
}
add_action( 'wp_head', 'react2u_output_schema', 30 );

/** @return array<string,mixed> */
function react2u_person_schema( int $author_id, string $organization_id ): array {
	$person = array(
		'@type'       => 'Person',
		'@id'         => get_author_posts_url( $author_id ) . '#person',
		'name'        => get_the_author_meta( 'display_name', $author_id ),
		'url'         => get_author_posts_url( $author_id ),
		'description' => (string) get_the_author_meta( 'description', $author_id ),
		'worksFor'    => array( '@id' => $organization_id ),
	);

	$job_title = react2u_author_job_title( $author_id );
	if ( '' !== $job_title ) {
		$person['jobTitle'] = $job_title;
	}

	$photo = react2u_author_photo_url( $author_id );
	if ( '' !== $photo ) {
		$person['image'] = array( '@type' => 'ImageObject', 'url' => $photo );
	}

	$socials = react2u_author_socials( $author_id );
	if ( $socials ) {
		$person['sameAs'] = array_values( array_unique( wp_list_pluck( $socials, 'url' ) ) );
	}

	return $person;
}

/** @return array<string,mixed> */
function react2u_breadcrumb_schema( string $canonical ): array {
	$elements = array();
	foreach ( react2u_breadcrumb_items() as $index => $item ) {
		$element = array(
			'@type'    => 'ListItem',
			'position' => $index + 1,
			'name'     => $item['name'],
		);
		if ( '' !== $item['url'] ) {
			$element['item'] = $item['url'];
		}
		$elements[] = $element;
	}

	return array(
		'@type'           => 'BreadcrumbList',
		'@id'             => $canonical . '#breadcrumbs',
		'itemListElement' => $elements,
	);
}

/**
 * Meta-description en canonical als bewerkbaar veld bij elk bericht en elke
 * pagina. Vult een redactietool ze niet, dan valt het thema terug op de
 * samenvatting — maar wie wil, kan bijsturen.
 */
function react2u_register_seo_meta(): void {
	foreach ( array_merge( REACT2U_ARTICLE_TYPES, array( 'page' ) ) as $post_type ) {
		register_post_meta(
			$post_type,
			'_react2u_meta_description',
			array(
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => 'sanitize_text_field',
				'auth_callback'     => static fn(): bool => current_user_can( 'edit_posts' ),
			)
		);
		register_post_meta(
			$post_type,
			'_react2u_canonical_url',
			array(
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => 'esc_url_raw',
				'auth_callback'     => static fn(): bool => current_user_can( 'edit_posts' ),
			)
		);
	}
}
add_action( 'init', 'react2u_register_seo_meta' );

/** Native SEO-titel; de bestaande documenttitel blijft de terugval. */
function react2u_seo_title_parts( array $parts ): array {
	if ( is_singular() ) {
		$title = trim( (string) get_post_meta( (int) get_queried_object_id(), '_react2u_seo_title', true ) );
		if ( '' !== $title ) { return array( 'title' => $title ); }
	}
	return $parts;
}
add_filter( 'document_title_parts', 'react2u_seo_title_parts', 30 );
