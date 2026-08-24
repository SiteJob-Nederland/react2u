<?php
/**
 * Demo-inhoud voor staging.
 *
 * Draaien:  wp eval-file seed.php
 *
 * Zet neer wat nodig is om élk sjabloon te kunnen beoordelen: een homepage, een
 * blogpagina, een blogartikel, een kennisbankartikel met inhoudsopgave en FAQ,
 * een auteur met profiel, en de drie menu's. Idempotent: opnieuw draaien
 * overschrijft de bestaande demo-inhoud in plaats van te verdubbelen.
 *
 * ALLEEN VOOR STAGING. De teksten zijn onmiskenbaar demo-inhoud; zet dit nooit
 * op een productiesite.
 *
 * @package React2u
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit( "Dit script draait alleen via wp eval-file.\n" );
}

/**
 * Pagina of artikel neerzetten op slug. Bestaat hij al, dan wordt hij bijgewerkt.
 *
 * @param array<string,mixed> $args
 */
/**
 * Zoeken op slug, ongeacht waar de pagina in de boom hangt en ongeacht status.
 *
 * Twee valkuilen zitten hierin verwerkt:
 *
 * 1. get_page_by_path() wil het volledige pad, dus 'diensten/snelheid' en niet
 *    'snelheid'. Voor pagina's onder /diensten/ betekende dat: bij elke run niet
 *    gevonden, en dus opnieuw aangemaakt.
 * 2. 'post_status' => 'any' klinkt alsof het alles pakt, maar het slaat elke
 *    status over die met exclude_from_search is geregistreerd — en 'draft' is er
 *    daar één van. De concept-privacypagina die WordPress zelf neerzet bleef
 *    daardoor onvindbaar, hield de slug bezet, en de echte pagina belandde op
 *    'privacy-policy-2'.
 */
function react2u_seed_vind( string $slug, string $post_type = 'page' ): ?WP_Post {
	$gevonden = get_posts(
		array(
			'name'             => $slug,
			'post_type'        => $post_type,
			'post_status'      => array( 'publish', 'draft', 'pending', 'private', 'future' ),
			'numberposts'      => 1,
			'suppress_filters' => false,
		)
	);

	return $gevonden ? $gevonden[0] : null;
}

function react2u_seed_post( string $slug, array $args ): int {
	$post_type = (string) ( $args['post_type'] ?? 'page' );
	$bestaand  = react2u_seed_vind( $slug, $post_type );

	$data = wp_parse_args(
		$args,
		array(
			'post_name'   => $slug,
			'post_status' => 'publish',
			'post_type'   => $post_type,
		)
	);

	if ( $bestaand ) {
		$data['ID'] = $bestaand->ID;
		wp_update_post( $data );
		WP_CLI::log( "bijgewerkt: {$slug}" );

		return (int) $bestaand->ID;
	}

	$id = wp_insert_post( $data );

	/*
	 * Controleren of de slug ook echt geworden is wat we vroegen. Is hij al
	 * bezet, dan plakt wp_insert_post() er stilletjes "-2" achter en meldt
	 * niemand iets — en dat is dan de URL die in de voettekst en in de sitemap
	 * belandt. Zonder deze waarschuwing zie je dat pas als de site ergens staat.
	 */
	$werkelijk = get_post_field( 'post_name', $id );
	if ( $werkelijk !== $slug ) {
		WP_CLI::warning( "slug bezet: gevraagd '{$slug}', geworden '{$werkelijk}' — wie bezet die slug?" );
	} else {
		WP_CLI::log( "aangemaakt: {$slug}" );
	}

	return (int) $id;
}

/* ---- WordPress' eigen voorbeeldinhoud opruimen --------------------------- *
 * Dit moet vóór alles wat hierna komt, en het is geen schoonheidsfoutje.
 *
 * `wp core install` zet drie dingen neer: een gepubliceerd bericht ("Hello
 * world!"), een gepubliceerde voorbeeldpagina, en een concept-privacypagina.
 * Alle drie op naam van de beheerder. Dat levert twee echte problemen op:
 *
 * 1. Dat ene gepubliceerde bericht maakt het auteursarchief van de beheerder
 *    publiek. `/?author=1` leidt dan naar `/author/<inlognaam>/` met een 200, en
 *    daarmee is de inlognaam bekend. De hardening-plugin schermt auteurs zonder
 *    publieke inhoud af — maar mét dat bericht ís er publieke inhoud, dus de
 *    plugin doet precies wat hij moet en het lek blijft. Zie
 *    docs/harde-eisen.md: gebruikersnamen horen niet opvraagbaar te zijn.
 * 2. De concept-privacypagina bezet de slug 'privacy-policy' (nl_NL:
 *    'privacybeleid'). De pagina die dit script daarna aanmaakt komt dan op
 *    'privacy-policy-2' terecht.
 *
 * Allebei zie je pas als de site ergens staat, niet in een sjabloon.
 */

$standaard_slugs = array(
	'hello-world', 'hallo-wereld',       // het voorbeeldbericht
	'sample-page', 'voorbeeld-pagina',   // de voorbeeldpagina
	'privacy-policy', 'privacybeleid',   // de concept-privacypagina
);

foreach ( $standaard_slugs as $standaard_slug ) {
	foreach ( array( 'post', 'page' ) as $standaard_type ) {
		$gevonden = react2u_seed_vind( $standaard_slug, $standaard_type );
		if ( ! $gevonden ) {
			continue;
		}

		/*
		 * Alleen weghalen wat WordPress zelf heeft neergezet. Herkenbaar aan de
		 * lege excerpt en het ontbreken van onze eigen demotekst; een pagina die
		 * dit script eerder aanmaakte, laten we met rust.
		 */
		if ( str_contains( (string) $gevonden->post_content, 'Demo-pagina' ) ) {
			continue;
		}

		wp_delete_post( (int) $gevonden->ID, true ); // true = niet naar de prullenbak
		WP_CLI::log( "opgeruimd: {$standaard_slug} (WordPress-voorbeeldinhoud)" );
	}
}

/* ---- Auteur -------------------------------------------------------------- */

$auteur = get_user_by( 'slug', 'redactie' );
if ( ! $auteur ) {
	$auteur_id = wp_insert_user(
		array(
			'user_login'   => 'redactie',
			'user_pass'    => wp_generate_password( 24 ),
			'user_email'   => 'redactie@example.invalid',
			'display_name' => 'Redactie',
			'role'         => 'author',
			'description'  => 'Demo-auteur. Vervangen zodra de echte auteurs bekend zijn.',
		)
	);
} else {
	$auteur_id = (int) $auteur->ID;
}
update_user_meta( $auteur_id, 'react2u_job_title', 'Redactie' );
update_user_meta( $auteur_id, 'react2u_linkedin', 'https://www.linkedin.com/company/example/' );
update_user_meta( $auteur_id, 'react2u_socials', "https://x.com/example\nhttps://www.instagram.com/example/" );

/* ---- Pagina's ------------------------------------------------------------ */

$home_id = react2u_seed_post(
	'home',
	array(
		'post_title'   => 'Home',
		'post_content' => '',
	)
);

$blog_id = react2u_seed_post(
	'blog',
	array(
		'post_title'   => 'Blog',
		'post_content' => '',
		'post_excerpt' => 'Artikelen over ons vak, geschreven door de mensen die het werk doen.',
	)
);

foreach ( array(
	'diensten'             => 'Diensten',
	'tarieven'             => 'Tarieven',
	'over-ons'             => 'Over ons',
	'contact'              => 'Contact',
	'offerte'              => 'Offerte aanvragen',
	'privacy-policy'       => 'Privacy',
	'algemene-voorwaarden' => 'Algemene voorwaarden',
) as $slug => $titel ) {
	react2u_seed_post(
		$slug,
		array(
			'post_title'   => $titel,
			'post_content' => "<!-- wp:paragraph --><p>Demo-pagina. De echte tekst voor <strong>{$titel}</strong> volgt.</p><!-- /wp:paragraph -->",
			'post_excerpt' => "Demo-pagina voor {$titel}.",
		)
	);
}

update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $home_id );
update_option( 'page_for_posts', $blog_id );

/*
 * WordPress houdt bij welke pagina de privacyverklaring is; dat voedt de link op
 * het inlogscherm en de exportfunctie voor persoonsgegevens. Die optie wees naar
 * de concept-pagina die hierboven is opgeruimd.
 */
$privacy = react2u_seed_vind( 'privacy-policy' );
if ( $privacy ) {
	update_option( 'wp_page_for_privacy_policy', (int) $privacy->ID );
}

/* ---- Artikelen ----------------------------------------------------------- */

/*
 * Bewust een artikel met meer dan drie H2's, een FAQ als gewone koppen en genoeg
 * lengte: alleen zo zie je de inhoudsopgave, de inline CTA's, de leesvoortgang
 * en het FAQPage-schema daadwerkelijk werken.
 */
$lang_artikel = <<<'HTML'
<!-- wp:paragraph --><p>Dit is demo-inhoud op staging. De tekst zegt niets, de opbouw wel: hij laat zien hoe een aangeleverd artikel in dit thema terechtkomt.</p><!-- /wp:paragraph -->

<!-- wp:heading --><h2>Waar het over gaat</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Een alinea van een paar regels, zodat de regellengte en het ritme van de tekst te beoordelen zijn. De inhoudsopgave hierboven is uit deze koppen afgeleid, niet uit een maatwerkveld.</p><!-- /wp:paragraph -->

<!-- wp:heading --><h2>Hoe het werkt</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Nog een alinea. Onder deze kop hoort de eerste inline CTA te verschijnen — het thema plaatst die automatisch vóór het derde hoofdstuk.</p><!-- /wp:paragraph -->
<!-- wp:list --><ul><li>Een opsomming, om de lijstopmaak te zien.</li><li>Met een tweede regel.</li><li>En een derde.</li></ul><!-- /wp:list -->

<!-- wp:heading --><h2>Wat het oplevert</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Hier staat de derde kop, dus hier komt de inline CTA te staan.</p><!-- /wp:paragraph -->
<!-- wp:quote --><blockquote class="wp-block-quote"><p>Een citaat, om de blockquote-opmaak te controleren.</p></blockquote><!-- /wp:quote -->

<!-- wp:heading --><h2>Waar je op moet letten</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Vierde hoofdstuk.</p><!-- /wp:paragraph -->

<!-- wp:heading --><h2>Wat je zelf kunt doen</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Vijfde hoofdstuk.</p><!-- /wp:paragraph -->

<!-- wp:heading --><h2>Hoe je verder komt</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Zesde hoofdstuk — hier hoort de tweede inline CTA te staan.</p><!-- /wp:paragraph -->

<!-- wp:heading --><h2>Veelgestelde vragen</h2><!-- /wp:heading -->
<!-- wp:heading {"level":3} --><h3>Wordt deze FAQ automatisch herkend?</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Ja. Het thema herkent een H2 in de trant van "Veelgestelde vragen" en maakt van elke H3 eronder een vraag. Daar komt ook het FAQPage-schema uit.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Moet ik daar een maatwerkveld voor invullen?</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Nee. Alles wat de sjablonen nodig hebben, wordt uit de HTML afgeleid. Dat is precies de reden dat een extern aangeleverd artikel hier compleet uitkomt.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>En als er geen FAQ in staat?</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Dan blijft het blok weg, en komt er ook geen leeg FAQPage-schema op de pagina te staan.</p><!-- /wp:paragraph -->
HTML;

react2u_seed_post(
	'demo-blogartikel',
	array(
		'post_type'    => 'post',
		'post_title'   => 'Demo-blogartikel met inhoudsopgave, CTA’s en FAQ',
		'post_content' => $lang_artikel,
		'post_excerpt' => 'Demo-artikel op staging: laat zien hoe inhoudsopgave, inline CTA’s en de FAQ automatisch uit de tekst komen.',
		'post_author'  => $auteur_id,
	)
);

react2u_seed_post(
	'demo-kort-artikel',
	array(
		'post_type'    => 'post',
		'post_title'   => 'Kort demo-artikel zonder inhoudsopgave',
		'post_content' => '<!-- wp:paragraph --><p>Een kort artikel met minder dan drie koppen. De inhoudsopgave hoort hier weg te blijven: onder de drie hoofdstukken voegt een index niets toe.</p><!-- /wp:paragraph --><!-- wp:heading --><h2>Enige kop</h2><!-- /wp:heading --><!-- wp:paragraph --><p>Meer is het niet.</p><!-- /wp:paragraph -->',
		'post_excerpt' => 'Demo-artikel zonder inhoudsopgave, om de korte variant te kunnen beoordelen.',
		'post_author'  => $auteur_id,
	)
);

// Servicepagina onder /diensten/, met genoeg koppen zodat de ingeweven CTA's
// te zien zijn. Het kind-zijn van 'diensten' zet de servicemodus aan.
$diensten = react2u_seed_vind( 'diensten' );
react2u_seed_post(
	'voorbeelddienst',
	array(
		'post_title'   => 'Voorbeelddienst',
		'post_parent'  => $diensten ? $diensten->ID : 0,
		'post_excerpt' => 'Demo-servicepagina op staging: laat de ingeweven CTA\'s en de zijkolom zien.',
		'post_content' => $lang_artikel,
	)
);

update_option( 'show_on_front', 'page' );

if ( post_type_exists( 'react2u_kennisbank' ) ) {
	$kb_id = react2u_seed_post(
		'demo-kennisartikel',
		array(
			'post_type'    => 'react2u_kennisbank',
			'post_title'   => 'Demo-kennisartikel',
			'post_content' => $lang_artikel,
			'post_excerpt' => 'Demo-kennisartikel op staging, met dezelfde opbouw als een blogartikel.',
			'post_author'  => $auteur_id,
		)
	);

	$term = term_exists( 'demo-onderwerp', 'react2u_kennisbank_cat' );
	if ( ! $term ) {
		$term = wp_insert_term( 'Demo-onderwerp', 'react2u_kennisbank_cat', array( 'slug' => 'demo-onderwerp' ) );
	}
	if ( ! is_wp_error( $term ) ) {
		wp_set_object_terms( $kb_id, (int) $term['term_id'], 'react2u_kennisbank_cat' );
	}
}

/* ---- Menu's -------------------------------------------------------------- */

$menus = array(
	'primary' => array(
		'naam'  => 'Hoofdnavigatie',
		'items' => array( 'diensten', 'tarieven', 'kennisbank', 'blog', 'over-ons' ),
	),
	'footer'  => array(
		'naam'  => 'Footer — pagina\'s',
		'items' => array( 'diensten', 'tarieven', 'over-ons', 'contact' ),
	),
	'legal'   => array(
		'naam'  => 'Footer — juridisch',
		'items' => array( 'privacy-policy', 'algemene-voorwaarden' ),
	),
);

$locaties = array();

foreach ( $menus as $locatie => $menu ) {
	$bestaand = wp_get_nav_menu_object( $menu['naam'] );
	if ( $bestaand ) {
		// Leeghalen zodat opnieuw draaien geen dubbele items geeft.
		foreach ( wp_get_nav_menu_items( $bestaand->term_id ) ?: array() as $item ) {
			wp_delete_post( $item->ID, true );
		}
		$menu_id = (int) $bestaand->term_id;
	} else {
		$menu_id = (int) wp_create_nav_menu( $menu['naam'] );
	}

	foreach ( $menu['items'] as $slug ) {
		if ( 'kennisbank' === $slug ) {
			if ( ! post_type_exists( 'react2u_kennisbank' ) ) {
				continue;
			}
			wp_update_nav_menu_item(
				$menu_id,
				0,
				array(
					'menu-item-title'  => 'Kennisbank',
					'menu-item-url'    => (string) get_post_type_archive_link( 'react2u_kennisbank' ),
					'menu-item-status' => 'publish',
				)
			);
			continue;
		}

		$pagina = react2u_seed_vind( $slug );
		if ( ! $pagina ) {
			continue;
		}

		wp_update_nav_menu_item(
			$menu_id,
			0,
			array(
				'menu-item-object-id' => $pagina->ID,
				'menu-item-object'    => 'page',
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
			)
		);
	}

	$locaties[ $locatie ] = $menu_id;
}

set_theme_mod( 'nav_menu_locations', $locaties );

WP_CLI::success( 'Demo-inhoud staat klaar. Vergeet niet: dit is staging-inhoud.' );
