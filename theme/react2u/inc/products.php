<?php
/**
 * Producten en productcategorieën — OPTIONEEL.
 *
 * Standaard doet dit bestand niets. Veel klanten zijn dienstverleners zonder
 * webshop; die hebben geen producten nodig en krijgen er dus ook geen lege
 * menu's voor. Zet het aan wanneer een klant een eenvoudige productcatalogus
 * wil (geen afrekenen, geen voorraad — dat is WooCommerce):
 *
 *   add_filter( 'react2u_enable_products', '__return_true' );
 *
 * Draait er WooCommerce, dan houdt deze module zich volledig stil: Woo levert
 * dan zijn eigen 'product'-posttype, 'product_cat'-taxonomie en templates, en
 * twee registraties van hetzelfde zouden botsen. De template-hiërarchie van
 * Woo pikt trouwens vanzelf een taxonomy-product_cat.php uit dit thema op, dus
 * voor een Woo-site bouw je de categoriepagina daar — met dezelfde eisen uit
 * docs/must-haves.md voor ogen.
 *
 * @package React2u
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Aan of uit. Uit tenzij een klantproject de filter op true zet. */
function react2u_products_enabled(): bool {
	if ( class_exists( 'WooCommerce' ) ) {
		return false; // Woo is de baas; wij bemoeien ons er niet mee.
	}
	return (bool) apply_filters( 'react2u_enable_products', false );
}

function react2u_register_products(): void {
	if ( ! react2u_products_enabled() ) {
		return;
	}

	/*
	 * Taxonomie vóór het posttype — zelfde reden als bij de kennisbank: anders
	 * vangt een rewrite-regel van het posttype /producten/categorie/<term>/ weg
	 * en geeft elke categoriepagina een 404.
	 */
	register_taxonomy(
		'product_cat',
		array( 'product' ),
		array(
			'labels'            => array(
				'name'          => __( 'Productcategorieën', 'react2u' ),
				'singular_name' => __( 'Productcategorie', 'react2u' ),
				'menu_name'     => __( 'Categorieën', 'react2u' ),
			),
			'public'            => true,
			'hierarchical'      => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'producten/categorie', 'with_front' => false ),
		)
	);

	register_post_type(
		'product',
		array(
			'labels'        => array(
				'name'          => __( 'Producten', 'react2u' ),
				'singular_name' => __( 'Product', 'react2u' ),
				'menu_name'     => __( 'Producten', 'react2u' ),
				'add_new_item'  => __( 'Nieuw product', 'react2u' ),
				'edit_item'     => __( 'Product bewerken', 'react2u' ),
				'not_found'     => __( 'Nog geen producten', 'react2u' ),
			),
			'public'        => true,
			'has_archive'   => 'producten',
			'menu_icon'     => 'dashicons-products',
			'menu_position' => 7,
			'rewrite'       => array( 'slug' => 'producten', 'with_front' => false ),
			'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes' ),
			'show_in_rest'  => true,
			'taxonomies'    => array( 'product_cat' ),
		)
	);
}
add_action( 'init', 'react2u_register_products', 5 ); // Vóór het kennisbanktype, net als daar: taxonomie eerst.

/**
 * De lange SEO-tekst die ONDER de productenlijst hoort. De korte
 * categoriebeschrijving (het term-omschrijvingsveld) staat bovenaan; deze staat
 * eronder, met eigen koppen. Bewerkbaar bij de categorie zelf.
 */
function react2u_register_product_cat_meta(): void {
	if ( ! react2u_products_enabled() ) {
		return;
	}
	register_term_meta(
		'product_cat',
		'_react2u_onder_tekst',
		array(
			'type'              => 'string',
			'single'            => true,
			'show_in_rest'      => true,
			'sanitize_callback' => 'wp_kses_post',
			'auth_callback'     => static fn(): bool => current_user_can( 'manage_categories' ),
		)
	);
}
add_action( 'init', 'react2u_register_product_cat_meta' );

/**
 * Onze eigen templates inhangen. Alleen als de module aanstaat; zo staat er geen
 * taxonomy-product_cat.php in de themawortel die een Woo-site zou overrulen.
 */
function react2u_product_template( string $template ): string {
	if ( ! react2u_products_enabled() ) {
		return $template;
	}

	$eigen = '';
	if ( is_tax( 'product_cat' ) ) {
		$eigen = 'template-parts/products/taxonomy-product_cat.php';
	} elseif ( is_post_type_archive( 'product' ) ) {
		$eigen = 'template-parts/products/archive-product.php';
	} elseif ( is_singular( 'product' ) ) {
		$eigen = 'template-parts/products/single-product.php';
	}

	if ( '' !== $eigen && file_exists( REACT2U_DIR . '/' . $eigen ) ) {
		return REACT2U_DIR . '/' . $eigen;
	}

	return $template;
}
add_filter( 'template_include', 'react2u_product_template', 20 );

/**
 * Productkaart. Los van de artikelkaart: een product toont een prijs-aanduiding
 * en geen leestijd of auteur.
 */
function react2u_product_card( int $product_id ): void {
	$thumb = has_post_thumbnail( $product_id )
		? wp_get_attachment_image( get_post_thumbnail_id( $product_id ), 'react2u-card', false, array( 'class' => 'product-image', 'loading' => 'lazy', 'decoding' => 'async' ) )
		: '<span class="product-image is-fallback" aria-hidden="true">' . react2u_logo_mark() . '</span>';
	?>
	<article class="product-card">
		<a class="product-link" href="<?php echo esc_url( (string) get_permalink( $product_id ) ); ?>">
			<span class="sr-only"><?php echo esc_html( (string) get_the_title( $product_id ) ); ?></span>
		</a>
		<div class="product-media"><?php echo $thumb; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
		<div class="product-body">
			<h3 class="product-title"><?php echo esc_html( (string) get_the_title( $product_id ) ); ?></h3>
			<p class="product-text"><?php echo esc_html( react2u_summary( $product_id, 90 ) ); ?></p>
		</div>
	</article>
	<?php
}
