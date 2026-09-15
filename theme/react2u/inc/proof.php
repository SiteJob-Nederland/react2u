<?php
/**
 * Eén centrale plek voor bewijsmateriaal, contactgegevens en CTA-bestemmingen.
 *
 * ALLES wat de klant nog moet aanleveren staat hier — nergens anders in het
 * thema staan losse cijfers of quotes. Waarden die beginnen met [PLACEHOLDER]
 * worden op de site zichtbaar gemarkeerd en verschijnen in een lijstje op het
 * WordPress-dashboard, zodat niemand vergeet ze te vervangen.
 *
 * Invullen kan op twee manieren:
 *   1. Weergave -> Aanpassen -> "React2u — cijfers en contact" (geen code nodig);
 *   2. dit bestand aanpassen, voor de teksten die niet in de Customizer staan.
 *
 * Regel: verzin nooit een cijfer, een review of een naam. Wat niet geverifieerd
 * is, blijft [PLACEHOLDER] — dat is zichtbaar op de site en houdt het gesprek
 * met de klant eerlijk.
 *
 * @package React2u
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const REACT2U_PLACEHOLDER = '[PLACEHOLDER]';

/**
 * @return array<string,mixed>
 */
function react2u_config(): array {
	static $config = null;
	if ( null !== $config ) {
		return $config;
	}

	$config = array(

		/* ---- Contact — overgenomen van react2u.nl (voettekst, 2026-08-23) --- */
		'contact' => array(
			'phone'       => '085 - 620 58 00',
			'phone_link'  => '+31856205800',
			'email'       => 'info@react2u.nl',
			'street'      => 'Stratumsedijk 29',
			'postcode'    => '5611 NB',
			'city'        => 'Eindhoven',
			'country'     => 'Nederland',
			'country_iso' => 'NL',
			'kvk'         => '95076824',
			'vat'         => 'NL866991906B01',
			'group'       => '',
			'tagline'     => 'Jouw mensen, onze aandacht',
		),

		/* ---- Waar de CTA's naartoe wijzen ---------------------------------- *
		 * De sleutels blijven zoals ze zijn; de sjablonen roepen ze bij naam
		 * aan. Alleen label en pad passen zich aan de klant aan.
		 */
		'cta' => array(
			'quote'    => array( 'label' => 'Maak een afspraak',    'path' => '/contact/' ),
			'pricing'  => array( 'label' => 'Bekijk onze diensten', 'path' => '/diensten/' ),
			'contact'  => array( 'label' => 'Neem contact op',      'path' => '/contact/' ),
			'callback' => array( 'label' => 'Laat je terugbellen',  'path' => '/contact/?onderwerp=terugbelverzoek' ),
			'guide'    => array( 'label' => 'Naar de kennisbank',   'path' => '/kennisbank/' ),
			'portal'   => array( 'label' => 'Klantenportaal',       'path' => '/portaal/' ),
		),

		/* ---- Sterrenscore — KLANT LEVERT AAN ------------------------------- *
		 * Blijft uit het schema tot alle drie de velden echt zijn ingevuld.
		 * Een placeholder als aggregateRating publiceren zou een verzonnen
		 * cijfer aan Google doorgeven.
		 */
		'rating' => array(
			'score'  => REACT2U_PLACEHOLDER,
			'max'    => '5',
			'count'  => REACT2U_PLACEHOLDER,
			'source' => REACT2U_PLACEHOLDER,
			'url'    => '',
		),

		/* ---- Cijferrij — vier tegels --------------------------------------- */
		'stats' => array(
			array( 'value' => REACT2U_PLACEHOLDER . ' 000+', 'label' => 'label', 'note' => 'Toelichting van één zin.' ),
			array( 'value' => REACT2U_PLACEHOLDER . ' 00',   'label' => 'label', 'note' => 'Toelichting van één zin.' ),
			array( 'value' => REACT2U_PLACEHOLDER . ' 00 %', 'label' => 'label', 'note' => 'Toelichting van één zin.' ),
			array( 'value' => REACT2U_PLACEHOLDER . ' 0 uur','label' => 'label', 'note' => 'Toelichting van één zin.' ),
		),

		/* ---- Reviews — KLANT LEVERT AAN ------------------------------------ */
		'reviews' => array(
			array(
				'quote'   => REACT2U_PLACEHOLDER . ' Korte klantquote — twee tot drie zinnen, concreet, met een resultaat erin.',
				'name'    => REACT2U_PLACEHOLDER . ' Naam',
				'role'    => REACT2U_PLACEHOLDER . ' Functie',
				'company' => REACT2U_PLACEHOLDER . ' Bedrijf',
				'sector'  => '',
				'rating'  => 5,
			),
			array(
				'quote'   => REACT2U_PLACEHOLDER . ' Tweede quote, over een ander onderdeel van de dienstverlening.',
				'name'    => REACT2U_PLACEHOLDER . ' Naam',
				'role'    => REACT2U_PLACEHOLDER . ' Functie',
				'company' => REACT2U_PLACEHOLDER . ' Bedrijf',
				'sector'  => '',
				'rating'  => 5,
			),
			array(
				'quote'   => REACT2U_PLACEHOLDER . ' Derde quote — met een cijfer erin als dat kan.',
				'name'    => REACT2U_PLACEHOLDER . ' Naam',
				'role'    => REACT2U_PLACEHOLDER . ' Functie',
				'company' => REACT2U_PLACEHOLDER . ' Bedrijf',
				'sector'  => '',
				'rating'  => 5,
			),
		),

		/* ---- Klantcases — KLANT LEVERT AAN (geanonimiseerd mag) ------------- */
		'cases' => array(
			array(
				'sector'       => REACT2U_PLACEHOLDER . ' Branche',
				'metric'       => REACT2U_PLACEHOLDER . ' +00%',
				'metric_label' => 'resultaat',
				'title'        => REACT2U_PLACEHOLDER . ' Korte casetitel',
				'text'         => REACT2U_PLACEHOLDER . ' Twee zinnen: waar liep de klant tegenaan, en wat is er opgelost.',
				'url'          => '',
			),
			array(
				'sector'       => REACT2U_PLACEHOLDER . ' Branche',
				'metric'       => REACT2U_PLACEHOLDER . ' 0.000',
				'metric_label' => 'resultaat',
				'title'        => REACT2U_PLACEHOLDER . ' Korte casetitel',
				'text'         => REACT2U_PLACEHOLDER . ' Twee zinnen over de aanpak en het effect.',
				'url'          => '',
			),
			array(
				'sector'       => REACT2U_PLACEHOLDER . ' Branche',
				'metric'       => REACT2U_PLACEHOLDER . ' −00%',
				'metric_label' => 'resultaat',
				'title'        => REACT2U_PLACEHOLDER . ' Korte casetitel',
				'text'         => REACT2U_PLACEHOLDER . ' Twee zinnen over het resultaat op de langere termijn.',
				'url'          => '',
			),
		),

		/* ---- Woordmerken -------------------------------------------------- *
		 * Bewust als tekst. Logo's van derden mogen pas mee zodra de klant de
		 * bestanden mét toestemming aanlevert.
		 */
		'integrations' => array(),
		'carriers'     => array(),

		/* ---- Diensten — koppen en teksten van de klant (react2u.nl) --------- *
		 * 'slug' koppelt aan de --dienst-*-token in style.css blok 1, voor de
		 * kleurcodering per dienst die het onderscheidende detail van dit merk is.
		 */
		'services' => array(
			array(
				'slug'      => 'wvp',
				'title'     => 'Verzuimbegeleiding WVP',
				'text'      => 'Werkgever én medewerker zijn samen verantwoordelijk voor het gehele proces van re-integratie.',
				'path'      => '/verzuimbegeleiding-wvp/',
				'image'     => 'dienst-wvp-higgsfield-v2.webp',
				'image_alt' => 'Een begeleider en medewerker bespreken samen een helder re-integratieplan aan tafel.',
			),
			array(
				'slug'      => 'erd',
				'title'     => 'Verzuimbegeleiding ERD/ZW',
				'text'      => 'Als eigenrisicodrager Ziektewet ben je verantwoordelijk voor de begeleiding van verzuimende ex-medewerkers.',
				'path'      => '/verzuimbegeleiding-erd-zw/',
				'image'     => 'dienst-erd-higgsfield-v2.webp',
				'image_alt' => 'Twee professionals nemen in een rustige werkruimte een verzuimdossier en vervolgstappen door.',
			),
			array(
				'slug'      => 'preventie',
				'title'     => 'Preventie & Vitaliteit',
				'text'      => 'React2u heeft alles in huis om de duurzame inzetbaarheid van je medewerkers te optimaliseren.',
				'path'      => '/preventie-en-vitaliteit/',
				'image'     => 'dienst-preventie-higgsfield-v2.webp',
				'image_alt' => 'Collega’s maken tijdens een werkdag samen ruimte voor beweging en een gezond gesprek.',
			),
			array(
				'slug'      => 'coaching',
				'title'     => 'Begeleiding & Coaching',
				'text'      => 'Door onze methodiek komen we tot de kern van het probleem en stimuleren we het zelfoplossend vermogen van je medewerker.',
				'path'      => '/begeleiding-en-coaching/',
				'image'     => 'dienst-coaching-higgsfield-v2.webp',
				'image_alt' => 'Een coach luistert aandachtig tijdens een persoonlijk gesprek in een lichte, rustige ruimte.',
			),
			array(
				'slug'      => 'trainingen',
				'title'     => 'Trainingen & Workshops',
				'text'      => 'Onze specialisten geven trainingen en cursussen op maat om je organisatie naar een hoger niveau te tillen.',
				'path'      => '/trainingen-en-workshops/',
				'image'     => 'dienst-trainingen-higgsfield-v2.webp',
				'image_alt' => 'Een kleine groep professionals werkt actief samen tijdens een interactieve workshop.',
			),
			array(
				'slug'      => 'risico',
				'title'     => 'Risicomanagement',
				'text'      => 'Onze ervaren deskundigen adviseren en ondersteunen je bij het gezond ondernemen.',
				'path'      => '/risicomanagement/',
				'image'     => 'dienst-risico-higgsfield-v2.webp',
				'image_alt' => 'Een adviseur en manager brengen risico’s en mogelijke vervolgstappen samen in kaart.',
			),
		),

		/* ---- Zo werkt het — samengesteld uit terugkerende taal op react2u.nl -
		 * Geen los "stappenplan" op de huidige site; wel dezelfde vier momenten
		 * op elke dienstpagina (vrijblijvend contact -> gesprek -> plan van
		 * aanpak -> begeleiding). Hier samengevoegd tot één stappenrij.
		 */
		'steps' => array(
			array(
				'title' => 'Vrijblijvend contact',
				'text'  => 'Je neemt contact op, zodat we kennis met elkaar kunnen maken en jouw situatie kunnen begrijpen.',
			),
			array(
				'title' => 'Persoonlijk gesprek',
				'text'  => 'We lichten onze werkwijze toe en brengen samen in kaart wat er nodig is — geen protocol van de plank.',
			),
			array(
				'title' => 'Plan van aanpak',
				'text'  => 'Werkgever en medewerker krijgen een duidelijk plan, met heldere stappen, structuur en voortgang.',
			),
			array(
				'title' => 'Persoonlijke begeleiding',
				'text'  => 'Een vast aanspreekpunt begeleidt het traject tot een duurzame terugkeer naar werk.',
			),
		),

		/* ---- USP's onder de hero — de drie pijlers van react2u.nl/over-react2u/ */
		'usps' => array(
			'Gezond — advies voor een gezonde, prettige werkomgeving',
			'Menselijk — een luisterend oor, kritisch waar nodig',
			'Duidelijk — één platform voor rapportages, afspraken en documenten',
		),

		/* ---- De mensen ------------------------------------------------------ *
		 * Een naam onder het gezicht van een echt persoon verzinnen doen we niet.
		 * Foto's horen van de klant zelf te komen; stockbeeld van een team dat
		 * niet bestaat is precies wat een bezoeker doorheeft.
		 */
		'people' => array(
			'eyebrow' => 'De mensen',
			'title'   => REACT2U_PLACEHOLDER . ' Kop boven de mensensectie',
			'text'    => REACT2U_PLACEHOLDER . ' Twee zinnen over wie de klant aan de lijn krijgt.',

			'contact' => array(
				'name'  => REACT2U_PLACEHOLDER . ' Naam aanspreekpunt',
				'role'  => 'Jouw vaste aanspreekpunt',
				'photo' => '',
				'note'  => '',
			),

			/* Bestandsnamen in assets/images/. Leeg laten mag: het bannerblok
			   valt dan terug op het beeldmerk. */
			'photos'  => array(),
			'banners' => array(),
			'members' => array(),
		),

		/* ---- Prijsanker ----------------------------------------------------- */
		'pricing' => array(
			'from' => REACT2U_PLACEHOLDER . ' € 00,00',
			'unit' => REACT2U_PLACEHOLDER . ' per eenheid',
			'note' => '',
		),
	);

	$config = apply_filters( 'react2u_config', $config );

	return $config;
}

/**
 * Waarde ophalen met een puntpad, bijvoorbeeld react2u_get( 'contact.email' ).
 * De Customizer wint van de standaardwaarde hierboven.
 *
 * @return mixed
 */
function react2u_get( string $path, mixed $default = '' ): mixed {
	$override = react2u_customizer_override( $path );
	if ( null !== $override ) {
		return $override;
	}

	$value = react2u_config();
	foreach ( explode( '.', $path ) as $key ) {
		if ( ! is_array( $value ) || ! array_key_exists( $key, $value ) ) {
			return $default;
		}
		$value = $value[ $key ];
	}

	return $value;
}

/** Volledige URL bij een CTA-sleutel, bijvoorbeeld react2u_cta_url( 'quote' ). */
function react2u_cta_url( string $key ): string {
	$path = (string) react2u_get( "cta.{$key}.path", '/contact/' );
	return str_starts_with( $path, 'http' ) ? $path : home_url( $path );
}

function react2u_cta_label( string $key ): string {
	return (string) react2u_get( "cta.{$key}.label", __( 'Neem contact op', 'react2u' ) );
}

function react2u_is_placeholder( mixed $value ): bool {
	return is_string( $value ) && str_contains( $value, REACT2U_PLACEHOLDER );
}

/**
 * Tekst tonen. Een nog niet ingevulde waarde krijgt een zichtbare markering:
 * onopvallend genoeg om de pagina te kunnen beoordelen, opvallend genoeg om
 * niet per ongeluk live te gaan.
 */
function react2u_text( mixed $value ): string {
	$value = (string) $value;
	if ( ! react2u_is_placeholder( $value ) ) {
		return esc_html( $value );
	}

	return sprintf(
		'<span class="is-placeholder" title="%s">%s</span>',
		esc_attr__( 'Nog aan te leveren door de klant', 'react2u' ),
		esc_html( trim( str_replace( REACT2U_PLACEHOLDER, '', $value ) ) )
	);
}

/**
 * Alle openstaande placeholders, plat, voor de dashboardmelding en de QA.
 *
 * @return array<int,string>
 */
function react2u_open_placeholders(): array {
	$open = array();

	$walk = static function ( $value, string $path ) use ( &$walk, &$open ): void {
		if ( is_array( $value ) ) {
			foreach ( $value as $key => $item ) {
				$walk( $item, $path ? $path . '.' . $key : (string) $key );
			}
			return;
		}
		if ( react2u_is_placeholder( $value ) ) {
			$open[] = $path;
		}
	};

	foreach ( react2u_config() as $key => $value ) {
		$walk( $value, (string) $key );
	}

	return $open;
}

/**
 * Melding op het dashboard zolang er nog materiaal ontbreekt. Alleen voor wie
 * de site beheert, en alleen op het dashboard zelf — niet bij elke schermwissel.
 */
function react2u_placeholder_notice(): void {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'dashboard' !== $screen->id || ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}

	$open = react2u_open_placeholders();
	if ( ! $open ) {
		return;
	}

	printf(
		'<div class="notice notice-warning"><p><strong>%1$s</strong> %2$s</p><p><a class="button" href="%3$s">%4$s</a></p></div>',
		esc_html( sprintf( _n( '%d onderdeel wacht nog op materiaal.', '%d onderdelen wachten nog op materiaal.', count( $open ), 'react2u' ), count( $open ) ) ),
		esc_html__( 'Denk aan reviews, cijfers en klantcases. Zolang ze leeg zijn, staan ze gemarkeerd op de site.', 'react2u' ),
		esc_url( admin_url( 'customize.php?autofocus[section]=react2u_proof' ) ),
		esc_html__( 'Nu invullen', 'react2u' )
	);
}
add_action( 'admin_notices', 'react2u_placeholder_notice' );

/**
 * Telefoonnummer zoals het in een tel:-link hoort. Is het aparte veld leeg,
 * dan leiden we het af uit het weergavenummer — anders staat er een link die
 * niet belt zodra iemand vergeet beide velden in te vullen.
 */
function react2u_phone_link(): string {
	$link = trim( (string) react2u_get( 'contact.phone_link' ) );
	if ( '' !== $link && ! react2u_is_placeholder( $link ) ) {
		return $link;
	}

	/*
	 * Is het weergavenummer nog een placeholder, dan leveren we géén link.
	 *
	 * Zonder deze regel wordt "[PLACEHOLDER] 000 000 0000" netjes gestript tot
	 * 0000000000 en verschijnt er overal een belknop die naar een nummer wijst
	 * dat niet bestaat. De placeholder is dan wel zichtbaar in de tekst, maar de
	 * knop zelf ziet er volstrekt normaal uit — en dat is precies het soort fout
	 * dat pas opvalt als een bezoeker belt.
	 */
	$display = (string) react2u_get( 'contact.phone' );
	if ( react2u_is_placeholder( $display ) ) {
		return '';
	}

	return (string) preg_replace( '/[^0-9+]/', '', $display );
}

/** Is er een bruikbaar telefoonnummer om te tonen? */
function react2u_has_phone(): bool {
	return '' !== react2u_phone_link();
}

/** E-mailadres zoals het veilig in een mailto:-link en als tekst kan staan. */
function react2u_email_link(): string {
	$email = trim( (string) react2u_get( 'contact.email' ) );
	if ( '' === $email || react2u_is_placeholder( $email ) ) {
		return '';
	}

	$sanitized = sanitize_email( $email );
	return is_email( $sanitized ) ? $sanitized : '';
}

/** Is er een bevestigd, syntactisch bruikbaar e-mailadres om te tonen? */
function react2u_has_email(): bool {
	return '' !== react2u_email_link();
}

/**
 * Volledig gevalideerde sterrenscore voor publieke uitvoer.
 *
 * De badge en het schema gebruiken bewust dezelfde functie. Zo kan een deels
 * ingevulde score niet in de ene uitvoer wel en in de andere niet verschijnen.
 * Gegroepeerde aantallen gebruiken steeds hetzelfde scheidingsteken; een punt
 * of komma kan daardoor niet stilletjes van decimaal naar duizendtalseparator
 * veranderen in een verder malformed getal.
 *
 * @param array<string,mixed>|null $rating Eigen invoer voor tests, of null voor de actieve configuratie.
 * @return array{score:string,score_normalized:string,max:string,max_normalized:string,count:string,count_number:string,source:string,url:string}|null
 */
function react2u_public_rating_data( ?array $rating = null ): ?array {
	if ( null === $rating ) {
		$rating = (array) react2u_get( 'rating', array() );

		/* Customizer-overrides bestaan op leaf-paden, niet op de hele array. */
		foreach ( array( 'score', 'max', 'count', 'source', 'url' ) as $key ) {
			$rating[ $key ] = react2u_get( "rating.{$key}", $rating[ $key ] ?? '' );
		}
	}

	$trim_unicode = static function ( string $value ): string {
		$trimmed = preg_replace( '/^[\s\p{Z}\p{Cf}]+|[\s\p{Z}\p{Cf}]+$/u', '', $value );

		return is_string( $trimmed ) ? $trimmed : '';
	};

	$normalize_text = static function ( mixed $value ) use ( $trim_unicode ): ?string {
		if ( ! is_scalar( $value ) ) {
			return null;
		}

		$text = html_entity_decode( wp_strip_all_tags( (string) $value ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );

		return $trim_unicode( $text );
	};

	$validate_boolean = static function ( mixed $value ) use ( $trim_unicode ): ?bool {
		if ( ! is_bool( $value ) && ! is_int( $value ) && ! is_string( $value ) ) {
			return null;
		}

		if ( is_int( $value ) && ! in_array( $value, array( 0, 1 ), true ) ) {
			return null;
		}

		if ( is_string( $value ) ) {
			$value = $trim_unicode( $value );
			if ( '' === $value ) {
				return null;
			}
		}

		return filter_var( $value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE );
	};

	foreach ( array( 'placeholder', 'is_placeholder' ) as $flag ) {
		/* Een placeholder-vlag is alleen veilig als hij expliciet false is. */
		if ( array_key_exists( $flag, $rating ) && false !== $validate_boolean( $rating[ $flag ] ) ) {
			return null;
		}
	}

	foreach ( array( 'verified', 'confirmed', 'is_verified', 'is_confirmed' ) as $flag ) {
		if ( array_key_exists( $flag, $rating ) && true !== $validate_boolean( $rating[ $flag ] ) ) {
			return null;
		}
	}

	if ( array_key_exists( 'status', $rating ) ) {
		$status = $normalize_text( $rating['status'] );
		if ( null === $status || ! in_array( strtolower( $status ), array( '1', 'active', 'confirmed', 'live', 'published', 'verified' ), true ) ) {
			return null;
		}
	}

	$values              = array();
	$placeholder_set     = array( 'placeholder', 'tbd', 'todo', 'n.t.b.', 'ntb', 'nog aan te leveren', 'nog in te vullen', 'onbevestigd', 'unconfirmed', 'unverified' );
	$placeholder_pattern = '/\[(?:placeholder|onbevestigd|unconfirmed|unverified|todo|tbd|n\.?t\.?b\.?)\]/i';

	foreach ( array( 'score', 'max', 'count', 'source' ) as $key ) {
		if ( ! isset( $rating[ $key ] ) || ! is_string( $rating[ $key ] ) ) {
			return null;
		}

		$value      = $trim_unicode( $rating[ $key ] );
		$normalized = $normalize_text( $value );
		$text       = null === $normalized ? '' : strtolower( $normalized );
		if (
			'' === $value
			|| '' === $text
			|| react2u_is_placeholder( $value )
			|| 1 === preg_match( $placeholder_pattern, $text )
			|| in_array( $text, $placeholder_set, true )
		) {
			return null;
		}

		$values[ $key ] = $value;
	}

	$score           = str_replace( ',', '.', $values['score'] );
	$max             = str_replace( ',', '.', $values['max'] );
	$compare_decimal = static function ( string $left, string $right ): int {
		$left_parts  = array_pad( explode( '.', $left, 2 ), 2, '' );
		$right_parts = array_pad( explode( '.', $right, 2 ), 2, '' );
		$left_whole  = ltrim( $left_parts[0], '0' ) ?: '0';
		$right_whole = ltrim( $right_parts[0], '0' ) ?: '0';

		if ( strlen( $left_whole ) !== strlen( $right_whole ) ) {
			return strlen( $left_whole ) <=> strlen( $right_whole );
		}

		$whole_comparison = strcmp( $left_whole, $right_whole );
		if ( 0 !== $whole_comparison ) {
			return $whole_comparison <=> 0;
		}

		$decimal_length = max( strlen( $left_parts[1] ), strlen( $right_parts[1] ) );
		$left_decimal   = str_pad( $left_parts[1], $decimal_length, '0' );
		$right_decimal  = str_pad( $right_parts[1], $decimal_length, '0' );

		return strcmp( $left_decimal, $right_decimal ) <=> 0;
	};
	if (
		1 !== preg_match( '/^[1-9][0-9]{0,2}(?:\.[0-9]{1,2})?$/', $score )
		|| 1 !== preg_match( '/^[1-9][0-9]{0,2}(?:\.[0-9]{1,2})?$/', $max )
		|| $compare_decimal( $score, '1' ) < 0
		|| $compare_decimal( $max, '1' ) < 0
		|| $compare_decimal( $score, $max ) > 0
		/* Een sterrenbadge met meer dan 100 posities is geen bruikbare schaal. */
		|| $compare_decimal( $max, '100' ) > 0
	) {
		return null;
	}

	$count_plain   = 1 === preg_match( '/^[1-9][0-9]*\+?$/', $values['count'] );
	$count_grouped = 1 === preg_match( '/^[1-9][0-9]{0,2}([.,\x{00A0}\x{202F} ])[0-9]{3}(?:\1[0-9]{3})*\+?$/u', $values['count'] );
	if ( ! $count_plain && ! $count_grouped ) {
		return null;
	}

	$count_number = (string) preg_replace( '/[^0-9]/', '', $values['count'] );
	if ( '' === $count_number || $compare_decimal( $count_number, (string) PHP_INT_MAX ) > 0 ) {
		return null;
	}

	$url = '';
	if ( isset( $rating['url'] ) && is_string( $rating['url'] ) ) {
		$candidate      = $trim_unicode( $rating['url'] );
		$normalized_url = $normalize_text( $candidate );
		$url_text       = null === $normalized_url ? '' : strtolower( $normalized_url );
		if (
			'' !== $candidate
			&& '' !== $url_text
			&& ! react2u_is_placeholder( $candidate )
			&& 1 !== preg_match( $placeholder_pattern, $url_text )
			&& ! in_array( $url_text, $placeholder_set, true )
		) {
			$sanitized_url = esc_url_raw( $candidate, array( 'http', 'https' ) );
			$validated_url = wp_http_validate_url( $sanitized_url );
			$scheme        = false === $validated_url ? '' : strtolower( (string) parse_url( $validated_url, PHP_URL_SCHEME ) );

			if ( false !== $validated_url && in_array( $scheme, array( 'http', 'https' ), true ) ) {
				$url = $validated_url;
			}
		}
	}

	return array(
		'score'            => $values['score'],
		'score_normalized' => $score,
		'max'              => $values['max'],
		'max_normalized'   => $max,
		'count'            => $values['count'],
		'count_number'     => $count_number,
		'source'           => $values['source'],
		'url'              => $url,
	);
}
