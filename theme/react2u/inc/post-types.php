<?php
/**
 * Kennisbank als eigen posttype, plus de auteursvelden.
 *
 * Bewust een gewoon, openbaar posttype met REST-ondersteuning: een externe
 * redactietool kan er dan naartoe schrijven zonder dat het thema iets over die
 * tool hoeft te weten. Geen maatwerkvelden — alles wat de sjablonen nodig
 * hebben, leiden ze uit de HTML af (zie inc/content.php).
 *
 * Heeft deze klant geen kennisbank nodig? Haal dan de require in functions.php
 * weg en verwijder 'react2u_kennisbank' uit REACT2U_ARTICLE_TYPES.
 *
 * @package React2u
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function react2u_register_post_types(): void {
	/*
	 * Taxonomie EERST registreren, posttype daarna. De volgorde is niet
	 * cosmetisch: WordPress bouwt de rewrite-regels op in de volgorde waarin de
	 * permastructs zijn toegevoegd, en de bijlage-regel van het posttype
	 * (kennisbank/[^/]+/([^/]+)) vangt anders /kennisbank/onderwerp/<term>/ weg
	 * voordat de taxonomie-regel aan de beurt is. Het gevolg is een 404 op elke
	 * categoriepagina — en dat merk je pas als iemand erop klikt.
	 */
	register_taxonomy(
		'react2u_kennisbank_cat',
		array( 'react2u_kennisbank' ),
		array(
			'labels'            => array(
				'name'          => __( 'Kennisbankcategorieën', 'react2u' ),
				'singular_name' => __( 'Kennisbankcategorie', 'react2u' ),
				'menu_name'     => __( 'Categorieën', 'react2u' ),
			),
			'public'            => true,
			'hierarchical'      => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'kennisbank/onderwerp', 'with_front' => false ),
		)
	);

	register_post_type(
		'react2u_kennisbank',
		array(
			'labels'        => array(
				'name'          => __( 'Kennisbank', 'react2u' ),
				'singular_name' => __( 'Kennisbankartikel', 'react2u' ),
				'add_new_item'  => __( 'Nieuw kennisbankartikel', 'react2u' ),
				'edit_item'     => __( 'Kennisbankartikel bewerken', 'react2u' ),
				'search_items'  => __( 'Kennisbank doorzoeken', 'react2u' ),
				'not_found'     => __( 'Nog geen kennisbankartikelen', 'react2u' ),
				'menu_name'     => __( 'Kennisbank', 'react2u' ),
			),
			'public'        => true,
			'has_archive'   => 'kennisbank',
			'menu_icon'     => 'dashicons-book-alt',
			'menu_position' => 6,
			'rewrite'       => array( 'slug' => 'kennisbank', 'with_front' => false ),
			'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'author', 'revisions', 'custom-fields', 'page-attributes' ),
			'show_in_rest'  => true,
			'rest_base'     => 'kennisbank',
			'taxonomies'    => array( 'react2u_kennisbank_cat' ),
		)
	);
}
add_action( 'init', 'react2u_register_post_types' );

/**
 * Permalinks één keer verversen na activeren; anders geeft /kennisbank/ een 404
 * tot iemand handmatig op Opslaan drukt bij de permalink-instellingen.
 */
function react2u_flush_rewrites_once(): void {
	if ( get_option( 'react2u_rewrites_version' ) === REACT2U_VERSION ) {
		return;
	}
	react2u_register_post_types();
	flush_rewrite_rules( false );
	update_option( 'react2u_rewrites_version', REACT2U_VERSION );
}
add_action( 'after_switch_theme', 'react2u_flush_rewrites_once' );
add_action( 'init', 'react2u_flush_rewrites_once', 99 );

/**
 * Auteurs van kennisbankstukken hebben dezelfde profielvelden nodig als
 * bloggers. WordPress kent van zichzelf geen functietitel of LinkedIn.
 */
function react2u_author_profile_fields( WP_User $user ): void {
	$fields = array(
		'react2u_job_title' => __( 'Functietitel', 'react2u' ),
		'react2u_linkedin'  => __( 'LinkedIn-profiel (volledige URL)', 'react2u' ),
		'react2u_photo_url' => __( 'Profielfoto (URL)', 'react2u' ),
	);
	?>
	<h2><?php esc_html_e( 'Auteursprofiel', 'react2u' ); ?></h2>
	<table class="form-table" role="presentation">
		<?php foreach ( $fields as $key => $label ) : ?>
			<tr>
				<th><label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label></th>
				<td>
					<input type="text" id="<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $key ); ?>"
						value="<?php echo esc_attr( (string) get_user_meta( $user->ID, $key, true ) ); ?>" class="regular-text">
				</td>
			</tr>
		<?php endforeach; ?>
		<tr>
			<th><label for="react2u_socials"><?php esc_html_e( 'Overige sociale profielen', 'react2u' ); ?></label></th>
			<td>
				<textarea id="react2u_socials" name="react2u_socials" rows="4" class="regular-text"
					placeholder="https://x.com/... &#10;https://instagram.com/..."><?php echo esc_textarea( (string) get_user_meta( $user->ID, 'react2u_socials', true ) ); ?></textarea>
				<p class="description"><?php esc_html_e( 'Eén volledige URL per regel. X, Instagram, Facebook en YouTube krijgen automatisch het juiste icoon.', 'react2u' ); ?></p>
			</td>
		</tr>
	</table>
	<?php
}
add_action( 'show_user_profile', 'react2u_author_profile_fields' );
add_action( 'edit_user_profile', 'react2u_author_profile_fields' );

function react2u_save_author_profile_fields( int $user_id ): void {
	if ( ! current_user_can( 'edit_user', $user_id ) ) {
		return;
	}
	check_admin_referer( 'update-user_' . $user_id );

	$map = array(
		'react2u_job_title' => 'sanitize_text_field',
		'react2u_linkedin'  => 'esc_url_raw',
		'react2u_photo_url' => 'esc_url_raw',
	);
	foreach ( $map as $key => $sanitize ) {
		if ( isset( $_POST[ $key ] ) ) {
			update_user_meta( $user_id, $key, $sanitize( wp_unslash( $_POST[ $key ] ) ) );
		}
	}

	// Socials is een tekstveld met één URL per regel; per regel schonen en de
	// lege regels eruit, zodat er geen halve URL's blijven staan.
	if ( isset( $_POST['react2u_socials'] ) ) {
		$regels = preg_split( '/[\r\n]+/', (string) wp_unslash( $_POST['react2u_socials'] ) ) ?: array();
		$schoon = array();
		foreach ( $regels as $regel ) {
			$url = esc_url_raw( trim( $regel ) );
			if ( '' !== $url ) {
				$schoon[] = $url;
			}
		}
		update_user_meta( $user_id, 'react2u_socials', implode( "\n", $schoon ) );
	}
}
add_action( 'personal_options_update', 'react2u_save_author_profile_fields' );
add_action( 'edit_user_profile_update', 'react2u_save_author_profile_fields' );
