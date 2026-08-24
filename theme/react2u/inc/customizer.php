<?php
/**
 * Customizer: de velden die de klant zelf moet kunnen invullen zonder code.
 *
 * Elk veld hangt aan een pad uit inc/proof.php. Leeg laten betekent: gebruik de
 * standaardwaarde uit dat bestand. Zo blijft er één bron van waarheid.
 *
 * @package React2u
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @return array<string,array{path:string,label:string,section:string,type?:string,description?:string}>
 */
function react2u_customizer_fields(): array {
	return array(
		'react2u_phone'         => array( 'path' => 'contact.phone',      'label' => __( 'Telefoonnummer (weergave)', 'react2u' ), 'section' => 'react2u_contact' ),
		'react2u_phone_link'    => array( 'path' => 'contact.phone_link', 'label' => __( 'Telefoonnummer (voor tel:-link)', 'react2u' ), 'section' => 'react2u_contact' ),
		'react2u_email'         => array( 'path' => 'contact.email',      'label' => __( 'E-mailadres', 'react2u' ), 'section' => 'react2u_contact', 'type' => 'email' ),
		'react2u_street'        => array( 'path' => 'contact.street',     'label' => __( 'Straat en huisnummer', 'react2u' ), 'section' => 'react2u_contact' ),
		'react2u_postcode'      => array( 'path' => 'contact.postcode',   'label' => __( 'Postcode', 'react2u' ), 'section' => 'react2u_contact' ),
		'react2u_city'          => array( 'path' => 'contact.city',       'label' => __( 'Plaats', 'react2u' ), 'section' => 'react2u_contact' ),
		'react2u_kvk'           => array( 'path' => 'contact.kvk',        'label' => __( 'KvK-nummer', 'react2u' ), 'section' => 'react2u_contact' ),
		'react2u_vat'           => array( 'path' => 'contact.vat',        'label' => __( 'Btw-nummer', 'react2u' ), 'section' => 'react2u_contact' ),

		'react2u_cta_quote_url' => array( 'path' => 'cta.quote.path',     'label' => __( 'URL offerteformulier', 'react2u' ), 'section' => 'react2u_contact', 'type' => 'url', 'description' => __( 'Bijvoorbeeld /offerte/ of een volledige URL.', 'react2u' ) ),
		'react2u_cta_price_url' => array( 'path' => 'cta.pricing.path',   'label' => __( 'URL tarievenpagina', 'react2u' ), 'section' => 'react2u_contact', 'type' => 'url' ),

		'react2u_rating_score'  => array( 'path' => 'rating.score',       'label' => __( 'Sterrenscore (bijv. 4,8)', 'react2u' ), 'section' => 'react2u_proof' ),
		'react2u_rating_count'  => array( 'path' => 'rating.count',       'label' => __( 'Aantal beoordelingen', 'react2u' ), 'section' => 'react2u_proof' ),
		'react2u_rating_source' => array( 'path' => 'rating.source',      'label' => __( 'Bron van de score', 'react2u' ), 'section' => 'react2u_proof', 'description' => __( 'Bijvoorbeeld Google of Trustpilot. Verschijnt bij de score.', 'react2u' ) ),
		'react2u_rating_url'    => array( 'path' => 'rating.url',         'label' => __( 'Link naar de beoordelingen', 'react2u' ), 'section' => 'react2u_proof', 'type' => 'url' ),

		'react2u_stat_1_value'  => array( 'path' => 'stats.0.value',      'label' => __( 'Cijfer 1 — waarde', 'react2u' ), 'section' => 'react2u_proof' ),
		'react2u_stat_1_label'  => array( 'path' => 'stats.0.label',      'label' => __( 'Cijfer 1 — label', 'react2u' ), 'section' => 'react2u_proof' ),
		'react2u_stat_2_value'  => array( 'path' => 'stats.1.value',      'label' => __( 'Cijfer 2 — waarde', 'react2u' ), 'section' => 'react2u_proof' ),
		'react2u_stat_2_label'  => array( 'path' => 'stats.1.label',      'label' => __( 'Cijfer 2 — label', 'react2u' ), 'section' => 'react2u_proof' ),
		'react2u_stat_3_value'  => array( 'path' => 'stats.2.value',      'label' => __( 'Cijfer 3 — waarde', 'react2u' ), 'section' => 'react2u_proof' ),
		'react2u_stat_3_label'  => array( 'path' => 'stats.2.label',      'label' => __( 'Cijfer 3 — label', 'react2u' ), 'section' => 'react2u_proof' ),
		'react2u_stat_4_value'  => array( 'path' => 'stats.3.value',      'label' => __( 'Cijfer 4 — waarde', 'react2u' ), 'section' => 'react2u_proof' ),
		'react2u_stat_4_label'  => array( 'path' => 'stats.3.label',      'label' => __( 'Cijfer 4 — label', 'react2u' ), 'section' => 'react2u_proof' ),

		'react2u_price_from'    => array( 'path' => 'pricing.from',       'label' => __( 'Vanafprijs', 'react2u' ), 'section' => 'react2u_proof' ),
		'react2u_price_unit'    => array( 'path' => 'pricing.unit',       'label' => __( 'Eenheid bij de vanafprijs', 'react2u' ), 'section' => 'react2u_proof' ),
	);
}

/**
 * Ingevulde Customizer-waarde bij een configuratiepad, of null.
 * Leeg gelaten velden tellen niet als override.
 */
function react2u_customizer_override( string $path ): ?string {
	static $index = null;
	if ( null === $index ) {
		$index = array();
		foreach ( react2u_customizer_fields() as $id => $field ) {
			$index[ $field['path'] ] = $id;
		}
	}

	if ( ! isset( $index[ $path ] ) ) {
		return null;
	}

	$value = trim( (string) get_theme_mod( $index[ $path ], '' ) );

	return '' === $value ? null : $value;
}

function react2u_customize_register( WP_Customize_Manager $manager ): void {
	$manager->add_panel(
		'react2u_panel',
		array(
			'title'       => __( 'React2u — cijfers en contact', 'react2u' ),
			'priority'    => 20,
			'description' => __( 'Alles wat de klant zelf invult. Leeg laten betekent: de standaardwaarde uit het thema blijft staan.', 'react2u' ),
		)
	);

	$manager->add_section(
		'react2u_contact',
		array(
			'title'       => __( 'Contact en CTA-bestemmingen', 'react2u' ),
			'panel'       => 'react2u_panel',
			'priority'    => 10,
			'description' => __( 'Deze gegevens komen terug in de header, de footer en in de gestructureerde data voor Google.', 'react2u' ),
		)
	);

	$manager->add_section(
		'react2u_proof',
		array(
			'title'       => __( 'Sterrenscore en cijfers', 'react2u' ),
			'panel'       => 'react2u_panel',
			'priority'    => 20,
			'description' => __( 'Zolang een veld leegstaat, toont de site de markering "nog aan te leveren". Reviews en klantcases staan in het themabestand inc/proof.php.', 'react2u' ),
		)
	);

	foreach ( react2u_customizer_fields() as $id => $field ) {
		$type      = $field['type'] ?? 'text';
		$sanitizer = match ( $type ) {
			'email' => 'sanitize_email',
			'url'   => 'sanitize_text_field', // Mag ook een relatief pad zijn, dus geen esc_url_raw.
			default => 'sanitize_text_field',
		};

		$manager->add_setting(
			$id,
			array(
				'type'              => 'theme_mod',
				'default'           => '',
				'sanitize_callback' => $sanitizer,
				'transport'         => 'refresh',
				'capability'        => 'edit_theme_options',
			)
		);

		$manager->add_control(
			$id,
			array(
				'section'     => $field['section'],
				'label'       => $field['label'],
				'description' => $field['description'] ?? '',
				'type'        => 'url' === $type ? 'text' : $type,
				'input_attrs' => array( 'placeholder' => react2u_customizer_placeholder( $field['path'] ) ),
			)
		);
	}
}
add_action( 'customize_register', 'react2u_customize_register' );

/** De themawaarde als grijze hint in het invoerveld. */
function react2u_customizer_placeholder( string $path ): string {
	$value = react2u_config();
	foreach ( explode( '.', $path ) as $key ) {
		if ( ! is_array( $value ) || ! array_key_exists( $key, $value ) ) {
			return '';
		}
		$value = $value[ $key ];
	}

	return is_scalar( $value ) ? (string) $value : '';
}
