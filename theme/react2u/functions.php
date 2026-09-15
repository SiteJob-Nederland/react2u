<?php
/**
 * React2u — thema-bootstrap.
 *
 * Dit bestand laadt alleen; alle logica staat in inc/. Zo blijft duidelijk waar
 * iets thuishoort wanneer het thema later uitbreidt.
 *
 * @package React2u
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'REACT2U_VERSION', '0.1.0' );
define( 'REACT2U_DIR', get_template_directory() );
define( 'REACT2U_URI', get_template_directory_uri() );

/**
 * Content-posttypen die als artikel worden behandeld (blog + kennisbank).
 * Eén constante, zodat sjablonen, schema en archieven niet uit elkaar lopen.
 */
const REACT2U_ARTICLE_TYPES = array( 'post', 'react2u_kennisbank' );

require REACT2U_DIR . '/inc/setup.php';        // theme supports, menu's, beeldformaten
require REACT2U_DIR . '/inc/assets.php';       // zelf-gehoste fonts, stylesheet, script
require REACT2U_DIR . '/inc/post-types.php';   // kennisbank-posttype, auteursvelden
require REACT2U_DIR . '/inc/proof.php';        // centrale plek voor cijfers, reviews, cases
require REACT2U_DIR . '/inc/content.php';      // inhoudsopgave, FAQ, CTA-terugval, leestijd
require REACT2U_DIR . '/inc/blocks.php';       // blokkenbibliotheek (render-functies)
require REACT2U_DIR . '/inc/breadcrumbs.php';  // kruimelpad, minimaal drie niveaus
require REACT2U_DIR . '/inc/seo.php';          // meta-description, canonical, OG, JSON-LD
require REACT2U_DIR . '/inc/sitemap.php';      // werkende /sitemap.xml
require REACT2U_DIR . '/inc/customizer.php';   // invulvelden voor de klant
require REACT2U_DIR . '/inc/patterns.php';     // blokpatronen voor de editor
require REACT2U_DIR . '/inc/products.php';     // OPTIONEEL productcatalogus — uit tenzij ingeschakeld

require REACT2U_DIR . '/inc/quality.php'; // Gedeelde technische ondergrens.
