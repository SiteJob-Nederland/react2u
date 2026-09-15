<?php
/**
 * Echte productie-inhoud voor React2u.
 *
 * Dit is GEEN demo-content zoals deploy/seed.php (dat blijft staan voor de
 * sjabloon-QA op elke toekomstige site). Dit script zet de daadwerkelijke
 * tekst van de klant neer: overgenomen uit de huidige site (react2u.nl,
 * binnengehaald 2026-08-23), niet verzonnen. Zie assets/huidige-site/ voor
 * de brontekst per pagina.
 *
 * Draaien:  wp eval-file inhoud-react2u.php
 * Idempotent: opnieuw draaien werkt bestaande pagina's bij op slug.
 *
 * @package React2u
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit( "Dit script draait alleen via wp eval-file.\n" );
}

function react2u_inhoud_vind( string $slug, string $post_type = 'page' ): ?WP_Post {
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

function react2u_inhoud_pagina( string $slug, array $args ): int {
	$bestaand = react2u_inhoud_vind( $slug );
	$data     = wp_parse_args(
		$args,
		array(
			'post_name'   => $slug,
			'post_status' => 'publish',
			'post_type'   => 'page',
		)
	);

	if ( $bestaand ) {
		$data['ID'] = $bestaand->ID;
		wp_update_post( $data );
		WP_CLI::log( "bijgewerkt: {$slug}" );
		return (int) $bestaand->ID;
	}

	$id        = wp_insert_post( $data );
	$werkelijk = get_post_field( 'post_name', $id );
	if ( $werkelijk !== $slug ) {
		WP_CLI::warning( "slug bezet: gevraagd '{$slug}', geworden '{$werkelijk}'" );
	} else {
		WP_CLI::log( "aangemaakt: {$slug}" );
	}
	return (int) $id;
}

/** Kleine helper: array regels -> wp:list blok. */
function react2u_inhoud_lijst( array $items ): string {
	$li = implode( '', array_map( static fn( $item ) => '<li>' . esc_html( $item ) . '</li>', $items ) );
	return "<!-- wp:list --><ul>{$li}</ul><!-- /wp:list -->\n\n";
}
function react2u_inhoud_p( string $tekst ): string {
	return '<!-- wp:paragraph --><p>' . $tekst . '</p><!-- /wp:paragraph -->' . "\n\n";
}
function react2u_inhoud_h2( string $tekst ): string {
	return '<!-- wp:heading --><h2>' . esc_html( $tekst ) . '</h2><!-- /wp:heading -->' . "\n\n";
}
function react2u_inhoud_h3( string $tekst ): string {
	return '<!-- wp:heading {"level":3} --><h3>' . esc_html( $tekst ) . '</h3><!-- /wp:heading -->' . "\n\n";
}

/* ============================================================================
 * Diensten — overzicht
 * ========================================================================= */

$diensten_body =
	react2u_inhoud_p( 'Al onze diensten zijn erop gericht om je medewerkers snel, gelukkig en duurzaam terug te laten keren in het arbeidsproces en ze daar ook te houden. Daarvoor kijken we naar wat er nodig is. In goed onderling overleg met jou en je werknemer, en met behulp van vakspecialisten, helpen we werknemers bij hun herstel — medisch of niet-medisch. Dat doen we met het hart op de juiste plek en door écht te luisteren.' )
	. react2u_inhoud_h2( 'Wat wij aanbieden als arbodienst' )
	. react2u_inhoud_p( 'Onze dienstverlening is <strong>gezond</strong>, <strong>menselijk</strong> en <strong>duidelijk</strong>. Voor werkgevers én werknemers staan we klaar met hulp en advies voor een gezonde, prettige werkomgeving — met een luisterend oor waar dat nodig is, en één online platform waarin rapportages, afspraken en documenten eenvoudig terug te vinden zijn.' )
	. react2u_inhoud_h2( 'Onze dienstverlening' )
	. react2u_inhoud_p( 'Zes diensten, elk met een eigen aanpak:' )
	. react2u_inhoud_lijst(
		array(
			'Verzuimbegeleiding WVP — begeleiding bij het volledige WVP-traject, van ziekmelding tot eventuele WIA-aanvraag',
			'Verzuimbegeleiding ERD/ZW — begeleiding van verzuimende ex-medewerkers als eigenrisicodrager Ziektewet',
			'Preventie & Vitaliteit — PMO, MTO en een proactief beleid voor gezondheid en welzijn',
			'Begeleiding & Coaching — één-op-één coaching, burn-outcoaching, loopbaancoaching',
			'Trainingen & Workshops — verzuim-, management- en communicatietrainingen op maat',
			'Risicomanagement — RI&E met bedrijfsarts, arbeidshygiënist, hogere veiligheidskundige en arbeids- en organisatiedeskundige',
		)
	)
	. react2u_inhoud_h2( 'Over onze dienstverlening' )
	. react2u_inhoud_p( "React2u werkt persoonlijk, is betrokken en eerlijk. We maken verbinding, maar komen daarnaast 'to the point' bij jou als werkgever, en ook bij je werknemer. Samen met jou gaan we voor beter verzuimmanagement en dus een lager verzuimpercentage — niet alleen nu, maar ook in de toekomst." )
	. react2u_inhoud_p( 'Onze casemanagers werken vanuit passie en proberen de intrinsieke motivatie van je medewerker aan te wakkeren. Op die manier komen we vaak tot een snel en goed herstel. We zijn niet alleen inzetbaar wanneer er al verzuim is binnen je organisatie — ook preventief kunnen we veel voor je betekenen, bijvoorbeeld met preventief medisch onderzoek (PMO) of trainingen.' )
	. react2u_inhoud_h2( 'Veelgestelde vragen' )
	. react2u_inhoud_h3( 'Wat kan ik doen om mijn kennis van verzuim en re-integratie te verbeteren?' )
	. react2u_inhoud_p( 'Verzuim en re-integratie vereisen specialistische kennis en ervaring. React2u helpt bedrijven onder andere met de complexe wetgeving en bij het opstellen van een effectief en menselijk re-integratieplan. Daarnaast bieden we training en advies in het herkennen van vroege signalen van verzuim.' )
	. react2u_inhoud_h3( 'Wat maakt preventie en vitaliteit zo belangrijk?' )
	. react2u_inhoud_p( 'Preventie en vitaliteit dragen bij aan een gezonde, plezierige en productieve werkomgeving. Door het bevorderen van welzijn en het voorkomen van gezondheidsproblemen verminderen we verzuim en verhogen we de motivatie en betrokkenheid van je mensen.' )
	. react2u_inhoud_h3( 'Hoe wordt een re-integratieplan opgesteld en uitgevoerd?' )
	. react2u_inhoud_p( 'Dit omvat het bepalen van de mate van arbeidsgeschiktheid, het opstellen van een Plan van Aanpak, en het inrichten van werkplekaanpassingen of passende arbeid.' )
	. react2u_inhoud_h3( 'Waarvoor kan coaching van React2u worden ingezet?' )
	. react2u_inhoud_p( 'Onder andere voor het versterken van vitaliteit en werkgeluk, het ontwikkelen van veerkracht en stressmanagement, burn-outcoaching, rouwbegeleiding, het begeleiden van loopbaantransities en het ondersteunen bij re-integratie na ziekte of langdurige afwezigheid.' );

$diensten_id = react2u_inhoud_pagina(
	'diensten',
	array(
		'post_title'   => 'Diensten',
		'post_excerpt' => 'React2u is de moderne dienstverlener in verzuim- en re-integratiebegeleiding. Preventief en curatief werken we aan grip op je inzetbaarheid.',
		'post_content' => $diensten_body,
	)
);

/* ============================================================================
 * De zes dienstpagina's — sjabloon "Dienst" (page-dienst.php), top-level slug
 * zodat de URL's exact overeenkomen met de huidige site (geen redirects nodig).
 * ========================================================================= */

$diensten_paginas = array(
	'verzuimbegeleiding-wvp'    => array(
		'titel'   => 'Verzuimbegeleiding WVP',
		'excerpt' => 'Als arbodienst begeleiden wij werkgevers gedurende het volledige WVP-traject: van ziekmelding tot en met een eventuele WIA-aanvraag.',
		'body'    =>
			react2u_inhoud_h2( 'Verzuimbegeleiding Wet verbetering poortwachter' )
			. react2u_inhoud_p( 'Als arbodienst begeleiden wij werkgevers gedurende het volledige WVP-traject: van ziekmelding tot en met een eventuele WIA-aanvraag. Onze dienstverlening omvat het opstellen en bewaken van het plan van aanpak, tijdige evaluaties en bijstellingen, correcte dossieropbouw, begeleiding richting het UWV en het voorkomen van loonsancties. Wij combineren wettelijke kennis met een mensgerichte aanpak.' )
			. react2u_inhoud_h2( 'Dit kun je van ons verwachten' )
			. react2u_inhoud_h3( 'Betrokken eigen casemanager' )
			. react2u_inhoud_p( 'Elke organisatie krijgt bij React2u een vaste casemanager verzuimbegeleiding. Deze bewaakt het WVP-traject, onderhoudt contact met werknemer en werkgever, en zorgt dat alle verplichtingen tijdig worden nagekomen.' )
			. react2u_inhoud_h3( 'Taakdelegatie' )
			. react2u_inhoud_p( 'Binnen onze verzuimbegeleiding werken wij met taakdelegatie: ervaren taakgedelegeerden voeren bepaalde taken uit onder verantwoordelijkheid van de bedrijfsarts. Hierdoor wordt sneller geschakeld, wordt wachttijd verkort en blijft medische kwaliteit gewaarborgd. De bedrijfsarts blijft eindverantwoordelijk voor medische besluiten.' )
			. react2u_inhoud_h3( 'Gecertificeerd registratiesysteem' )
			. react2u_inhoud_p( 'Wij werken met een veilig en gecertificeerd verzuimregistratiesysteem dat voldoet aan alle wettelijke eisen en kwaliteitsnormen. Werkgevers hebben inzicht in het verzuimdossier, de voortgang van het WVP-traject, evaluatiemomenten en wettelijke deadlines. Dit voorkomt fouten in dossieropbouw en minimaliseert het risico op loonsancties.' )
			. react2u_inhoud_h3( 'Snel inzetbare providerboog' )
			. react2u_inhoud_p( 'Voor effectieve re-integratie maken wij gebruik van een breed netwerk van bedrijfsartsen, psychologen, fysiotherapeuten en arbeidsdeskundigen. Zo kunnen wij snel interventies inzetten, wachttijden verkorten en langdurig ziekteverzuim beperken.' )
			. react2u_inhoud_h2( 'Wat doet de werkgever?' )
			. react2u_inhoud_lijst( array( 'In gesprek blijven met de werknemer', 'Samen een plan van aanpak opstellen', 'Mogelijkheden voor passend werk onderzoeken', 'Afspraken naleven en vastleggen' ) )
			. react2u_inhoud_h2( 'Wat neemt React2u uit handen?' )
			. react2u_inhoud_lijst( array( 'Professionele begeleiding van het WVP-traject', 'Holistische analyse van de situatie', 'Advies over passend werk en interventies', 'Correcte dossieropbouw en wettelijke borging' ) ),
	),
	'verzuimbegeleiding-erd-zw' => array(
		'titel'   => 'Verzuimbegeleiding ERD/ZW',
		'excerpt' => 'Als eigenrisicodrager Ziektewet ben je verantwoordelijk voor de begeleiding van verzuimende ex-medewerkers. React2u neemt dit uit handen.',
		'body'    =>
			react2u_inhoud_h2( 'Verzuimbegeleiding eigenrisicodrager / Ziektewet' )
			. react2u_inhoud_p( 'Bij React2u hebben we alle expertise in huis om complexe situaties rondom je ziekteverzuim effectief te beheren en te coördineren — dat bespaart tijd en middelen. Daarnaast verzorgen wij de nauwkeurige opvolging van de wet- en regelgeving rond arbeidsongeschiktheid, waardoor juridische risico\'s voor de organisatie worden geminimaliseerd.' )
			. react2u_inhoud_h2( 'Dit kun je van ons verwachten' )
			. react2u_inhoud_h3( 'ERD-specialist voor organisaties en flexbranche' )
			. react2u_inhoud_p( 'Bij React2u hebben we uitgebreide kennis en ervaring op het gebied van verzuim en re-integratie — voor reguliere organisaties én voor de flexbranche, waar regelgeving en procedures net iets anders liggen. Door onze deskundige begeleiding helpen wij verzuim terug te dringen en de (flex)medewerker snel en duurzaam te laten terugkeren.' )
			. react2u_inhoud_h3( 'Gewaarborgd ERD-protocol' )
			. react2u_inhoud_p( 'Het gewaarborgde ERD-protocol van React2u zorgt voor een consistente en transparante registratie van verzuimgegevens en re-integratieactiviteiten. Dat verbetert de communicatie tussen werkgevers, werknemers, arbodiensten en verzekeraars en vermindert de kans op administratieve fouten en geschillen.' )
			. react2u_inhoud_h3( 'Ervaren bedrijfsartsen en taakgedelegeerden' )
			. react2u_inhoud_p( 'Onze ervaren bedrijfsartsen brengen medische kennis en ervaring mee die essentieel is voor het beoordelen van de gezondheid van werknemers en het bieden van de juiste re-integratieadviezen.' )
			. react2u_inhoud_h3( 'Overige dienstverlening' )
			. react2u_inhoud_p( 'Naast verzuimbegeleiding ERD/ZW bieden we meer diensten aan om ziekteverzuim terug te dringen en van je organisatie een gezonde, productieve werkomgeving te maken.' )
			. react2u_inhoud_h2( 'Wat doet de werkgever?' )
			. react2u_inhoud_lijst( array( 'Verzorgt een ERD-protocol voor de organisatie', 'Meldt de werknemer tijdig uit dienst', 'Blijft in contact met de (ex-)werknemer', 'Volgt de adviezen van de bedrijfsarts op' ) )
			. react2u_inhoud_h2( 'Wat neemt React2u uit handen?' )
			. react2u_inhoud_lijst( array( 'Helpen bij het opstellen van het verzuimprotocol', 'Bewaken van verplichtingen ten aanzien van de Ziektewet', 'Medische beoordelingen van de (ex-)werknemer', 'Tijdig informeren over de te nemen stappen richting terugkeer' ) ),
	),
	'preventie-en-vitaliteit'   => array(
		'titel'   => 'Preventie & Vitaliteit',
		'excerpt' => 'React2u heeft alles in huis om de duurzame inzetbaarheid van je medewerkers te optimaliseren — vóórdat er verzuim ontstaat.',
		'body'    =>
			react2u_inhoud_h2( 'Preventie & Vitaliteit' )
			. react2u_inhoud_p( 'Het ervaren verzuimteam van React2u helpt je bij het opzetten van een proactief beleid gericht op gezondheid en welzijn op de werkvloer: risico\'s holistisch aanpakken, preventieve maatregelen zoals ergonomische werkplekken, en regelmatige vitaliteitschecks en trainingen.' )
			. react2u_inhoud_h2( 'Dit kun je van ons verwachten' )
			. react2u_inhoud_h3( 'Preventief Medisch Onderzoek op maat' )
			. react2u_inhoud_p( 'Een PMO stelt ons in staat gerichte interventies door te voeren, afgestemd op de specifieke behoeften en risico\'s binnen je organisatie. Dat bevordert niet alleen het welzijn van medewerkers, maar helpt ook bij het vroegtijdig signaleren en aanpakken van gezondheidsproblemen.' )
			. react2u_inhoud_h3( 'Preventieve consulten met ons verzuimteam' )
			. react2u_inhoud_p( 'Medewerkers die zich gewaardeerd voelen zijn gemotiveerder om snel terug te keren. Een goede behandeling bevordert bovendien hun fysieke en mentale gezondheid, waardoor ze ook in de toekomst minder snel verzuimen.' )
			. react2u_inhoud_h3( 'Medewerkers Tevredenheids Onderzoek' )
			. react2u_inhoud_p( 'Met een MTO breng je in kaart hoe tevreden, betrokken en gemotiveerd je werknemers zijn, gemeten aan de hand van verschillende thema\'s — zodat je kunt prioriteren waar verbetering mogelijk is.' )
			. react2u_inhoud_h3( 'Holistische aanpak' )
			. react2u_inhoud_p( 'In een holistisch mensmodel wordt de mens als één geheel gezien: lichamelijke, psychologische, sociale, gedragsmatige en werk-privéfacetten zijn onlosmakelijk met elkaar verbonden.' )
			. react2u_inhoud_h3( 'Deskundige providerboog' )
			. react2u_inhoud_p( 'We maken gebruik van een breed netwerk gespecialiseerde zorgverleners — bedrijfsartsen, psychologen, fysiotherapeuten en arbeidsdeskundigen — om wachttijden te verkorten en re-integratie te versnellen.' )
			. react2u_inhoud_h3( 'Vroegtijdige signalering' )
			. react2u_inhoud_p( 'Door gesprekken en vragenlijsten over de mentale en fysieke situatie van je werknemers brengen wij risico\'s vroegtijdig in kaart, zodat uitval op de werkvloer voorkomen kan worden.' )
			. react2u_inhoud_h2( 'Wat doet de werkgever?' )
			. react2u_inhoud_lijst( array( 'Inventariseren', 'Signaleren', 'Bespreekbaar maken', 'Faciliteren en borgen' ) )
			. react2u_inhoud_h2( 'Wat neemt React2u uit handen?' )
			. react2u_inhoud_lijst( array( 'Tot de kern komen', 'Analyse maken', 'Adviseren en begeleiden', 'Borgen samen met werkgever' ) ),
	),
	'begeleiding-en-coaching'   => array(
		'titel'   => 'Begeleiding & Coaching',
		'excerpt' => 'Door onze methodiek komen we tot de kern van het probleem en stimuleren we het zelfoplossend vermogen van je medewerker.',
		'body'    =>
			react2u_inhoud_h2( 'Begeleiding & Coaching' )
			. react2u_inhoud_p( 'Onze coaches helpen mensen, teams en organisaties met een uitdaging om zichzelf vooruit te helpen. Coaching is de meest effectieve manier om je persoonlijk te ontwikkelen: het traject wordt nagenoeg volledig samengesteld op de persoonlijkheid en leerbehoefte van het individu of team. Persoonlijker kan het niet.' )
			. react2u_inhoud_h2( 'Dit kun je van ons verwachten' )
			. react2u_inhoud_h3( 'Eén-op-één coaching op maat' )
			. react2u_inhoud_p( 'In regelmatige persoonlijke sessies gaan we diep in op persoonlijke en professionele omstandigheden, zelfreflectie en het opbouwen van zelfvertrouwen — met op maat gemaakte oefeningen, feedback en praktische tips.' )
			. react2u_inhoud_h3( 'Burn-outcoaching op maat' )
			. react2u_inhoud_p( 'Een burn-out heeft ingrijpende effecten op iemands welzijn. Ons coachingtraject helpt mensen zichzelf terug te vinden, hun grenzen beter te bewaken, en weer met vertrouwen aan het werk te gaan.' )
			. react2u_inhoud_h3( 'Leven- en loopbaancoaching op maat' )
			. react2u_inhoud_p( 'We begeleiden werknemers in hun persoonlijke en professionele ontwikkeling, identificeren sterke punten, interesses en drijfveren, en werken zo aan meer voldoening en balans.' )
			. react2u_inhoud_h3( 'Ervaren en gediplomeerde coaches' )
			. react2u_inhoud_p( 'Begeleiding & coaching op maat vraagt om enthousiaste, ervaren en gediplomeerde coaches — die hebben we in dienst. Is een specialisme nodig dat we niet zelf in huis hebben, dan kunnen we rekenen op een snel inzetbare providerboog.' )
			. react2u_inhoud_h2( 'Wat doet de werkgever?' )
			. react2u_inhoud_lijst( array( 'Inventariseren', 'Signaleren', 'Bespreekbaar maken', 'Faciliteren en borgen' ) )
			. react2u_inhoud_h2( 'Wat neemt React2u uit handen?' )
			. react2u_inhoud_lijst( array( 'Tot de kern komen', 'Werknemer zelf in beweging zetten', 'Zo nodig verwijzen naar specialist', 'Borgen samen met werkgever' ) ),
	),
	'trainingen-en-workshops'   => array(
		'titel'   => 'Trainingen & Workshops',
		'excerpt' => 'Onze specialisten geven trainingen en cursussen op maat om je organisatie naar een hoger niveau te tillen.',
		'body'    =>
			react2u_inhoud_h2( 'Trainingen & Workshops' )
			. react2u_inhoud_p( 'Onze workshops en trainingen zijn gericht op medewerkers en professionals. Onze trainers prikkelen op een professionele manier beweging, energie, verbondenheid, plezier en creativiteit — verschil mag er zijn, en dat zorgt, wanneer je het van elkaar kent en waardeert, voor een prettige en constructieve samenwerking.' )
			. react2u_inhoud_h2( 'Dit kun je van ons verwachten' )
			. react2u_inhoud_h3( 'Verzuimtraining' )
			. react2u_inhoud_p( 'Gericht op het verminderen en voorkomen van verzuim: werknemers krijgen inzicht in de oorzaken en leren hoe ze daar effectief mee omgaan. Leidinggevenden krijgen inzicht in wet- en regelgeving en handvatten voor de omgang met een verzuimende werknemer.' )
			. react2u_inhoud_h3( 'Managementtraining' )
			. react2u_inhoud_p( 'React2u denkt strategisch mee en rolt verzuim- en preventiebeleid uit binnen organisaties. Samen kijken we wat er nodig is en binnen welke tijd we een nieuw of verbeterd, gedragen beleid kunnen realiseren.' )
			. react2u_inhoud_h3( 'Training communicatietechnieken' )
			. react2u_inhoud_p( 'Gericht op het verbeteren van communicatieve vaardigheden van leidinggevenden: actief luisteren, effectief vragen stellen en feedback geven — voor effectievere en constructievere gesprekken.' )
			. react2u_inhoud_h3( 'Vitaliteit- en preventietraining' )
			. react2u_inhoud_p( 'Gericht op het bevorderen van de fysieke en mentale gezondheid van werknemers, door middel van praktische oefeningen, bewustwording en gedragsverandering.' )
			. react2u_inhoud_h3( 'Workshop of training op maat' )
			. react2u_inhoud_p( 'Naast onze reguliere trainingen bieden we workshops en trainingen op maat, specifiek gericht op jouw organisatie of de vraag van je werknemers.' )
			. react2u_inhoud_h2( 'Trainingen en workshops op een rij' )
			. react2u_inhoud_lijst(
				array(
					'Verzuimtraining',
					'Leiderschaps- of managementtraining',
					'Training communicatietechnieken',
					'Training conflicthantering',
					'Vitaliteits- en preventietraining',
					'Weerbaarheidstraining',
					'Omgaan met klachten van burn-out',
					'Preventief werken',
					'Inzicht in gedrag en handelen',
					'Teambuilding',
				)
			),
	),
	'risicomanagement'          => array(
		'titel'   => 'Risicomanagement',
		'excerpt' => 'Onze ervaren deskundigen adviseren en ondersteunen je bij het gezond ondernemen — met een RI&E die verder gaat dan een vinkje.',
		'body'    =>
			react2u_inhoud_h2( 'Risicomanagement' )
			. react2u_inhoud_p( 'Onze deskundige medewerkers helpen je graag een goede inventarisatie te maken, waarna we een plan van aanpak opstellen om de risico\'s voor je bedrijf en je werknemers te verkleinen.' )
			. react2u_inhoud_h2( 'Wie zijn de kerndeskundigen?' )
			. react2u_inhoud_h3( 'Bedrijfsarts' )
			. react2u_inhoud_p( 'Een bedrijfsarts is geneeskundig specialist op het gebied van arbeid en gezondheid, preventief en curatief. De bedrijfsarts werkt onder eigen verantwoordelijkheid en BIG-registratie, en is eindverantwoordelijk voor het handelen van taakgedelegeerden die onder zijn supervisie werken.' )
			. react2u_inhoud_h3( 'Arbeidshygiënist' )
			. react2u_inhoud_p( 'De arbeidshygiënist bekommert zich om het welbevinden van mensen binnen een organisatie, met oog voor veiligheidsvoorschriften. Hij adviseert over arbeidshygiënische aspecten zoals beroepsziekten en meet schadelijke omgevingsfactoren als chemische stoffen, geluid, trillingen en droge lucht.' )
			. react2u_inhoud_h3( 'Hogere veiligheidskundige' )
			. react2u_inhoud_p( 'Levert een bijdrage aan een veilige werkomgeving en duurzame inzetbaarheid. De aangewezen specialist op het gebied van risicobeoordeling en risicobeheersing, en adviseert over het optimaliseren van de arbeidsomstandighedenzorg.' )
			. react2u_inhoud_h3( 'Arbeids- en organisatiedeskundige' )
			. react2u_inhoud_p( 'Houdt zich bezig met mensen in een organisatie: psychosociale arbeidsbelasting, kwaliteit van de arbeid en organisatiekunde. Adviseert over werkdruk, stress, gezondheidsmanagement, ongewenst gedrag en functioneringsproblematiek.' )
			. react2u_inhoud_h2( 'Wat zijn de voordelen van RI&E?' )
			. react2u_inhoud_lijst( array( 'Inzicht in arbeidspsychologie', 'Verhoogde veiligheid in je organisatie', "Risico's inzichtelijk, waar je wat aan kunt doen", 'Bruikbaar plan van aanpak' ) ),
	),
);

foreach ( $diensten_paginas as $slug => $dienst ) {
	react2u_inhoud_pagina(
		$slug,
		array(
			'post_title'   => $dienst['titel'],
			'post_excerpt' => $dienst['excerpt'],
			'post_content' => $dienst['body'],
			'page_template' => 'page-dienst.php',
		)
	);
}

/* ============================================================================
 * Over React2u
 * ========================================================================= */

react2u_inhoud_pagina(
	'over-react2u',
	array(
		'post_title'    => 'Over React2u',
		'post_excerpt'  => 'Een ervaren, dynamisch en energiek team dat werkgevers, adviseurs en vooral werknemers helpt bij herstel en een gezonde terugkeer naar de werkplek.',
		/*
		 * De echte slug is 'over-react2u', niet het kit-standaard 'over-ons' —
		 * page-over-ons.php wordt dus niet automatisch gekozen op basis van de
		 * slug en moet expliciet worden toegewezen.
		 */
		'page_template' => 'page-over-ons.php',
		'post_content'  =>
			react2u_inhoud_p( 'Welkom bij React2u. Met een ervaren, dynamisch en energiek team zijn wij dagelijks in de weer om werkgevers, adviseurs en vooral werknemers te helpen bij het herstel en een gezonde terugkeer naar de werkplek. We weten wat het betekent om langdurig — of hopelijk maar kort — aan de zijlijn te moeten staan.' )
			. react2u_inhoud_p( 'Verzuim vraagt om persoonlijke aandacht. Dat vinden wij belangrijk. Aandacht raakt. Bij React2u werken we altijd samen met de werkgever en werknemer aan een oplossing. Door alle betrokken partijen professioneel en persoonlijk te begeleiden, faciliteren en adviseren, brengen wij mensen weer duurzaam in beweging.' )
			. react2u_inhoud_p( 'Door de inzet van onze betrokken casemanagers, taakgedelegeerden en bedrijfsartsen, zijn wij in staat om op een professionele en vooral menselijke manier werknemers zo veilig en (voor)spoedig mogelijk terug te laten keren in eigen, passend of ander werk.' )
			. react2u_inhoud_h2( 'Gezond, menselijk, duidelijk' )
			. react2u_inhoud_h3( 'Gezond' )
			. react2u_inhoud_p( 'Voor zowel werkgevers als werknemers staan we klaar met hulp en advies voor een mooie carrière in een gezonde en prettige werkomgeving. Daar wordt iedereen beter van!' )
			. react2u_inhoud_h3( 'Menselijk' )
			. react2u_inhoud_p( 'Bij React2u willen wij op een menselijke manier te werk gaan om jouw werknemers weer terug te brengen in het arbeidsproces. We bieden een luisterend oor, zijn kritisch en gebruiken humor.' )
			. react2u_inhoud_h3( 'Duidelijk' )
			. react2u_inhoud_p( 'Bij React2u gebruiken we een online platform waar alle rapportages, afspraken en documenten eenvoudig terug te vinden zijn — super handig voor zowel werkgevers als werknemers.' )
			. react2u_inhoud_h2( 'Onze dienstverlening' )
			. react2u_inhoud_p( "React2u werkt persoonlijk, is betrokken en eerlijk. We maken verbinding, maar komen daarnaast 'to the point' bij jou als werkgever, en ook bij je werknemer. Samen met jou gaan we voor beter verzuimmanagement en dus een lager verzuimpercentage — niet alleen nu, maar ook in de toekomst." )
			. react2u_inhoud_p( 'Wist je dat zo\'n 80% van de verzuimende medewerkers vaak niet ziek is, maar om andere redenen verzuimt? Wij helpen om hier beter mee om te gaan.' )
			. react2u_inhoud_p( 'Naast het begeleiden van verzuim, preventieve dienstverlening en het geven van trainingen, is het ook mogelijk maatwerk te leveren. React2u staat open voor een gesprek om onze expertise in te zetten.' ),
	)
);

/* ============================================================================
 * Werknemers
 * ========================================================================= */

react2u_inhoud_pagina(
	'werknemers',
	array(
		'post_title'   => 'Werknemers',
		'post_excerpt' => 'Als je een tijdje niet kunt deelnemen aan het arbeidsproces, doet dat iets met je. React2u begeleidt je op weg naar herstel.',
		'post_content' =>
			react2u_inhoud_p( 'Werken is veel meer dan alleen je salaris verdienen. Het gaat om samenwerken, successen delen, plannen uitwerken en waardering krijgen. Wanneer je, bijvoorbeeld door ziekte of een arbeidsconflict, niet aan het arbeidsproces kunt deelnemen, valt er dus veel meer weg dan alleen je werk.' )
			. react2u_inhoud_p( 'Bij React2u weten we waar je als werknemer mee te maken krijgt. Samen met jou en andere betrokken partijen doen we er alles aan om je zo snel mogelijk weer gezond en onbezorgd op de werkvloer te krijgen.' )
			. react2u_inhoud_h2( 'Wat is React2u?' )
			. react2u_inhoud_p( 'React2u is de moderne verzuim- en re-integratiebegeleider. Samen met je werkgever zorgen we ervoor dat je zo snel mogelijk weer gezond en gelukkig aan het werk kunt — in goed overleg met jou en andere professionals die je bijstaan in een moeilijke periode.' )
			. react2u_inhoud_h2( 'Voor iedereen gezond, menselijk, duidelijk' )
			. react2u_inhoud_h3( 'Voor iedereen gezond' )
			. react2u_inhoud_p( 'Werk zorgt voor een inkomen en voor een nuttige, leerzame en waardevolle dagbesteding. Zowel voor werkgevers als werknemers staan we klaar met hulp en advies voor een mooie carrière in een gezonde en prettige werkomgeving.' )
			. react2u_inhoud_h3( 'Voor iedereen menselijk' )
			. react2u_inhoud_p( 'Aandacht raakt. Afwezig zijn op de werkvloer heeft meer gevolgen dan alleen gezondheid: je ontwikkelt minder snel, je verdient minder, je mist je collega\'s. Bij React2u willen wij op een menselijke manier werknemers terugbrengen in het arbeidsproces.' )
			. react2u_inhoud_h3( 'Voor iedereen duidelijk' )
			. react2u_inhoud_p( 'Als je een tijdje uit de running bent, wordt het soms lastiger om alles op orde te houden. Bij React2u hebben we een online platform waarin alle gesprekken, afspraken en documenten eenvoudig zijn terug te vinden.' )
			. react2u_inhoud_h2( 'Duidelijk & to the point' )
			. react2u_inhoud_p( 'Je bent ziek. Wat nu? Je krijgt de ruimte om thuis uit te zieken, maar je hebt de plicht om bereikbaar te zijn en blijven — niet alleen voor je werkgever, ook voor de arbodienstverlener. Samen kijken we naar jouw verzuimverloop en hoe we zorgen dat je zo snel mogelijk weer gezond aan het werk kunt gaan. Bekijk het volledige <a href="' . esc_url( home_url( '/verzuimprotocol/' ) ) . '">verzuimprotocol</a> voor de exacte stappen.' )
			. react2u_inhoud_h2( 'Veelgestelde vragen' )
			. react2u_inhoud_h3( 'Wat zijn de rechten en plichten van werkgever en werknemer bij verzuim?' )
			. react2u_inhoud_p( 'Dit omvat het recht op loondoorbetaling, de verplichting van de werknemer om mee te werken aan re-integratie, en de verantwoordelijkheid van de werkgever om een passend re-integratieplan te bieden. React2u ondersteunt daarbij.' )
			. react2u_inhoud_h3( 'Hoe wordt een re-integratieplan opgesteld en uitgevoerd?' )
			. react2u_inhoud_p( 'Dit omvat het bepalen van de arbeidsgeschiktheid, het opstellen van een plan van aanpak, en het inrichten van werkplekaanpassingen of passende arbeid.' )
			. react2u_inhoud_h3( 'Wat zijn de mogelijkheden voor aangepaste werkzaamheden of herplaatsing?' )
			. react2u_inhoud_p( 'Denk aan deeltijdwerken, aangepast werk, tijdelijk ander werk binnen het bedrijf, of externe re-integratiemogelijkheden. Bij React2u helpen we graag bij het opstellen van het juiste maatwerk re-integratieplan.' ),
	)
);

/* ============================================================================
 * Verzuimprotocol
 * ========================================================================= */

react2u_inhoud_pagina(
	'verzuimprotocol',
	array(
		'post_title'   => 'Verzuimprotocol',
		'post_excerpt' => 'Welke stappen volgen werkgever en werknemer vanaf het moment van ziekmelding? Een duidelijk protocol, zodat er niets fout kan gaan.',
		'post_content' =>
			react2u_inhoud_p( 'Werkgevers en werknemers willen vaak weten welke stappen er moeten worden gevolgd vanaf het moment dat een werknemer zich ziek meldt. Daarvoor hebben we bij React2u een duidelijk protocol. Worden alle stappen doorlopen, dan kan er eigenlijk niets fout gaan — en is er toch iets niet duidelijk, dan zijn wij altijd bereikbaar.' )
			. react2u_inhoud_h2( 'Veelgestelde vragen' )
			. react2u_inhoud_h3( 'Wat zijn de rechten en plichten van de werkgever en werknemer bij verzuim?' )
			. react2u_inhoud_p( 'Dit omvat het recht op loondoorbetaling, de verplichting van de werknemer om mee te werken aan re-integratie, en de verantwoordelijkheden van de werkgever om een passend re-integratieplan te bieden.' )
			. react2u_inhoud_h3( 'Hoe wordt een re-integratieplan opgesteld en uitgevoerd?' )
			. react2u_inhoud_p( 'Dit omvat het bepalen van de arbeidsgeschiktheid, het opstellen van een plan van aanpak, en het inrichten van werkplekaanpassingen of passende arbeid.' )
			. react2u_inhoud_h3( 'Wat zijn de mogelijkheden voor aangepaste werkzaamheden of herplaatsing?' )
			. react2u_inhoud_p( 'Denk aan deeltijdwerken, aangepast werk, tijdelijk ander werk binnen het bedrijf, of externe re-integratiemogelijkheden.' ),
	)
);

/* ============================================================================
 * Juridisch — algemene voorwaarden en klachtenprocedure (verbatim overgenomen)
 * ========================================================================= */

require_once __DIR__ . '/inc-juridisch.php';

foreach ( react2u_inhoud_juridisch() as $slug => $pagina ) {
	react2u_inhoud_pagina(
		$slug,
		array(
			'post_title'   => $pagina['titel'],
			'post_excerpt' => $pagina['excerpt'],
			'post_content' => $pagina['body'],
		)
	);
}

/*
 * Privacy reglement: de live pagina op react2u.nl toont bij vergissing de
 * verkeerde inhoud (een Begeleiding & Coaching-blok in plaats van een
 * privacyverklaring) — geen bruikbare bron. We claimen daarom niets wat niet is
 * aangeleverd, maar tonen op staging wel een nette, neutrale tussenmelding.
 */
react2u_inhoud_pagina(
	'privacy-reglement',
	array(
		'post_title'   => 'Privacy reglement',
		'post_excerpt' => 'Hoe React2u omgaat met persoonsgegevens.',
		'post_content' => react2u_inhoud_p( 'Het privacyreglement wordt momenteel bijgewerkt. Heb je in de tussentijd een vraag over de verwerking van persoonsgegevens? Neem dan contact op via <a href="mailto:info@react2u.nl">info@react2u.nl</a>.' ),
	)
);

/* ============================================================================
 * Contact
 * ========================================================================= */

react2u_inhoud_pagina(
	'contact',
	array(
		'post_title'   => 'Contact',
		'post_excerpt' => 'Ben je nieuwsgierig naar onze werkwijze? Neem vrijblijvend contact met ons op, zodat we kennis met elkaar kunnen maken.',
		/*
		 * page-contact.php wordt automatisch gekozen (de slug is exact
		 * 'contact') en rendert telefoon/e-mail/adres al zelf uit
		 * inc/proof.php. De introzin staat al in post_excerpt (resource-intro
		 * onder de H1) — post_content blijft leeg, anders staat dezelfde zin
		 * er twee keer.
		 */
		'post_content' => '',
	)
);

/* De footer verwijst naar de blog; houd die route ook zonder artikelen netjes bereikbaar. */
$blog_id = react2u_inhoud_pagina(
	'blog',
	array(
		'post_title'   => 'Blog',
		'post_excerpt' => 'Praktische inzichten over verzuim, preventie en duurzame inzetbaarheid.',
		'post_content' => '',
	)
);
update_option( 'page_for_posts', $blog_id );

/* ============================================================================
 * Generieke staging-demo opruimen
 * ========================================================================= */

/*
 * staging-srv1.sh draait eerst seed.php om het complete basisthema te kunnen
 * testen. Na de klantimport mogen die voorbeeldpagina's, -artikelen en de
 * demo-auteur niet meer publiek bereikbaar zijn. We verwijderen uitsluitend
 * de exact herkenbare seedrecords; echte redactionele inhoud blijft ongemoeid.
 */
$demo_records = array(
	array( 'slug' => 'demo-blogartikel',   'type' => 'post',                 'marker' => 'Demo-blogartikel' ),
	array( 'slug' => 'demo-kort-artikel',  'type' => 'post',                 'marker' => 'demo-artikel' ),
	array( 'slug' => 'demo-kennisartikel', 'type' => 'react2u_kennisbank',   'marker' => 'Demo-kennisartikel' ),
	array( 'slug' => 'voorbeelddienst',    'type' => 'page',                 'marker' => 'Demo-servicepagina' ),
	array( 'slug' => 'tarieven',           'type' => 'page',                 'marker' => 'Demo-pagina' ),
	array( 'slug' => 'over-ons',           'type' => 'page',                 'marker' => 'Demo-pagina' ),
	array( 'slug' => 'offerte',            'type' => 'page',                 'marker' => 'Demo-pagina' ),
	array( 'slug' => 'privacy-policy',      'type' => 'page',                 'marker' => 'Demo-pagina' ),
);

foreach ( $demo_records as $demo_record ) {
	$demo_post = react2u_inhoud_vind( $demo_record['slug'], $demo_record['type'] );
	if ( ! $demo_post ) {
		continue;
	}

	$demo_haystack = $demo_post->post_title . "\n" . $demo_post->post_excerpt . "\n" . $demo_post->post_content;
	if ( ! str_contains( $demo_haystack, $demo_record['marker'] ) ) {
		WP_CLI::warning( "niet opgeruimd: {$demo_record['slug']} wijkt af van de bekende seedinhoud" );
		continue;
	}

	wp_delete_post( (int) $demo_post->ID, true );
	WP_CLI::log( "opgeruimd: {$demo_record['slug']} (staging-demo)" );
}

$privacy_reglement = react2u_inhoud_vind( 'privacy-reglement' );
if ( $privacy_reglement ) {
	update_option( 'wp_page_for_privacy_policy', (int) $privacy_reglement->ID );
}

$demo_term = get_term_by( 'slug', 'demo-onderwerp', 'react2u_kennisbank_cat' );
if ( $demo_term && 0 === (int) $demo_term->count ) {
	wp_delete_term( (int) $demo_term->term_id, 'react2u_kennisbank_cat' );
	WP_CLI::log( 'opgeruimd: demo-onderwerp (staging-demo)' );
}

$demo_auteur = get_user_by( 'login', 'redactie' );
$demo_auteur_posts = $demo_auteur
	? get_posts(
		array(
			'author'      => (int) $demo_auteur->ID,
			'post_type'   => array( 'post', 'react2u_kennisbank' ),
			'post_status' => array( 'publish', 'draft', 'pending', 'private', 'future', 'trash' ),
			'numberposts' => 1,
			'fields'      => 'ids',
		)
	)
	: array();
if (
	$demo_auteur
	&& 'redactie@example.invalid' === strtolower( (string) $demo_auteur->user_email )
	&& array() === $demo_auteur_posts
) {
	require_once ABSPATH . 'wp-admin/includes/user.php';
	wp_delete_user( (int) $demo_auteur->ID );
	WP_CLI::log( 'opgeruimd: redactie (staging-demo)' );
}

/* ============================================================================
 * Menu's — de echte navigatie van react2u.nl
 * ========================================================================= */

$menus = array(
	'primary' => array(
		'naam'  => 'Hoofdnavigatie',
		'items' => array(
			array( 'slug' => 'diensten', 'kinderen' => array_keys( $diensten_paginas ) ),
			array( 'slug' => 'werknemers' ),
			array( 'slug' => 'verzuimprotocol' ),
			array( 'slug' => 'over-react2u' ),
			array( 'slug' => 'contact' ),
		),
	),
	'footer'  => array(
		'naam'  => 'Footer — pagina\'s',
		'items' => array(
			array( 'slug' => 'diensten' ),
			array( 'slug' => 'over-react2u' ),
			array( 'slug' => 'contact' ),
		),
	),
	'legal'   => array(
		'naam'  => 'Footer — juridisch',
		'items' => array(
			array( 'slug' => 'algemene-voorwaarden' ),
			array( 'slug' => 'klachtenprocedure' ),
			array( 'slug' => 'privacy-reglement' ),
		),
	),
);

$locaties = array();

foreach ( $menus as $locatie => $menu ) {
	$bestaand = wp_get_nav_menu_object( $menu['naam'] );
	if ( $bestaand ) {
		foreach ( wp_get_nav_menu_items( $bestaand->term_id ) ?: array() as $item ) {
			wp_delete_post( $item->ID, true );
		}
		$menu_id = (int) $bestaand->term_id;
	} else {
		$menu_id = (int) wp_create_nav_menu( $menu['naam'] );
	}

	foreach ( $menu['items'] as $item ) {
		$pagina = react2u_inhoud_vind( $item['slug'] );
		if ( ! $pagina ) {
			continue;
		}

		$parent_menu_item_id = wp_update_nav_menu_item(
			$menu_id,
			0,
			array(
				'menu-item-object-id' => $pagina->ID,
				'menu-item-object'    => 'page',
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
			)
		);

		foreach ( $item['kinderen'] ?? array() as $kind_slug ) {
			$kind = react2u_inhoud_vind( $kind_slug );
			if ( ! $kind ) {
				continue;
			}
			wp_update_nav_menu_item(
				$menu_id,
				0,
				array(
					'menu-item-object-id' => $kind->ID,
					'menu-item-object'    => 'page',
					'menu-item-type'      => 'post_type',
					'menu-item-status'    => 'publish',
					'menu-item-parent-id' => $parent_menu_item_id,
				)
			);
		}
	}

	$locaties[ $locatie ] = $menu_id;
}

// Bestaande locaties behouden die niet door deze klantimport worden beheerd.
$bestaand_locaties = (array) get_theme_mod( 'nav_menu_locations', array() );
set_theme_mod( 'nav_menu_locations', array_merge( $bestaand_locaties, $locaties ) );

WP_CLI::success( 'Echte productie-inhoud van React2u staat klaar.' );
