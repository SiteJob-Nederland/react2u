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
				'slug'  => 'wvp',
				'title' => 'Verzuimbegeleiding WVP',
				'text'  => 'Werkgever én medewerker zijn samen verantwoordelijk voor het gehele proces van re-integratie.',
				'path'  => '/verzuimbegeleiding-wvp/',
				'image' => 'dienst-wvp.webp',
			),
			array(
				'slug'  => 'erd',
				'title' => 'Verzuimbegeleiding ERD/ZW',
				'text'  => 'Als eigenrisicodrager Ziektewet ben je verantwoordelijk voor de begeleiding van verzuimende ex-medewerkers.',
				'path'  => '/verzuimbegeleiding-erd-zw/',
				'image' => 'dienst-erd.webp',
			),
			array(
				'slug'  => 'preventie',
				'title' => 'Preventie & Vitaliteit',
				'text'  => 'React2u heeft alles in huis om de duurzame inzetbaarheid van je medewerkers te optimaliseren.',
				'path'  => '/preventie-en-vitaliteit/',
				'image' => 'dienst-preventie.webp',
			),
			array(
				'slug'  => 'coaching',
				'title' => 'Begeleiding & Coaching',
				'text'  => 'Door onze methodiek komen we tot de kern van het probleem en stimuleren we het zelfoplossend vermogen van je medewerker.',
				'path'  => '/begeleiding-en-coaching/',
				'image' => 'dienst-coaching.webp',
			),
			array(
				'slug'  => 'trainingen',
				'title' => 'Trainingen & Workshops',
				'text'  => 'Onze specialisten geven trainingen en cursussen op maat om je organisatie naar een hoger niveau te tillen.',
				'path'  => '/trainingen-en-workshops/',
				'image' => 'dienst-trainingen.webp',
			),
			array(
				'slug'  => 'risico',
				'title' => 'Risicomanagement',
				'text'  => 'Onze ervaren deskundigen adviseren en ondersteunen je bij het gezond ondernemen.',
				'path'  => '/risicomanagement/',
				'image' => 'dienst-risico.webp',
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
