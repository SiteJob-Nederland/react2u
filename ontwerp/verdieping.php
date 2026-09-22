<?php
/** Redactioneel herwerkt vanuit de volledige live-opname van 22 september 2026. */
function paragraphs(array $items): string { return implode('', array_map(fn($text) => '<p>' . e($text) . '</p>', $items)); }
function itemList(array $items): string { return '<ul>' . implode('', array_map(fn($text) => '<li>' . e($text) . '</li>', $items)) . '</ul>'; }
function contentCards(array $sections): string {
    return '<div class="content-cards">' . implode('', array_map(fn($item) => '<article><h3>' . e($item[0]) . '</h3><p>' . e($item[1]) . '</p></article>', $sections)) . '</div>';
}
function photoDescription(string $name): string {
    return match ($name) {
        'persoonlijk-gesprek' => 'Twee vrouwen praten met elkaar in een lichte, rustige kamer.',
        'samen-leren' => 'Vier mensen delen ervaringen rond een tafel met notitieboeken.',
        'op-de-werkvloer' => 'Twee collega’s in gesprek bij een inpaktafel.',
        'gezonde-werkplek' => 'Twee vrouwen bespreken een werkplek met beeldscherm en toetsenbord.',
        'ruimte-buiten', 'samen-buiten' => 'Twee mensen wandelen en praten samen in het groen.',
        'even-bellen' => 'Een vrouw voert thuis een telefoongesprek.',
        'werkplezier' => 'Collega’s zitten ontspannen samen aan tafel.',
        default => 'Twee mensen voeren een aandachtig gesprek aan tafel.',
    };
}
function editorialPhoto(string $name, string $caption = ''): string {
    return '<figure class="editorial-photo">' . photo($name, photoDescription($name), '(max-width:760px) 90vw, 45vw') . ($caption !== '' ? '<figcaption>' . e($caption) . '</figcaption>' : '') . '</figure>';
}
function splitSection(string $id, string $label, string $title, array $copy): string {
    $image = match ($id) {
        'samenwerken' => 'op-de-werkvloer', 'jouw-situatie' => 'persoonlijk-gesprek',
        'wie-wij-zijn' => 'samen-leren', 'ook-preventief' => 'gezonde-werkplek',
        default => null,
    };
    if ($image) {
        return '<section class="section shell photo-story" id="' . e($id) . '">' . editorialPhoto($image) . '<div><p class="section-kicker">' . e($label) . '</p><h2>' . $title . '</h2><div class="story-copy">' . paragraphs($copy) . '</div></div></section>';
    }
    return '<section class="section shell" id="' . e($id) . '"><div class="about"><div><p class="section-kicker">' . e($label) . '</p><h2>' . $title . '</h2></div><div class="about-copy">' . paragraphs($copy) . '</div></div></section>';
}
function infoHero(string $label, string $title, string $intro, string $image, string $caption, string $parent = 'werkgevers'): string {
    return '<div class="shell"><nav class="breadcrumb" aria-label="Kruimelpad"><a href="index.html">Home</a><span aria-hidden="true">/</span><a href="' . $parent . '.html">' . ucfirst($parent) . '</a><span aria-hidden="true">/</span><span aria-current="page">' . e($label) . '</span></nav><section class="subhero"><div><p class="eyebrow">' . e($label) . '</p><h1>' . e($title) . '</h1><p class="hero-intro">' . e($intro) . '</p></div><figure class="subhero-photo" data-photo="' . e($image) . '">' . photo($image, photoDescription($image), '(max-width:760px) 90vw, 40vw', true) . '<figcaption>' . e($caption) . '</figcaption></figure></section></div>';
}
$serviceContent = json_decode(file_get_contents(__DIR__ . '/inhoud.json'), true, 512, JSON_THROW_ON_ERROR);
foreach ($serviceContent as $slug => $item) {
    $body = infoHero($item['title'], $item['headline'], $item['intro'], $item['image'], $item['label']);
    $body .= '<section class="section shell service-intro"><p class="section-kicker">Samen aan de slag</p><h2>Wat kun je van ons verwachten?</h2><p class="reading-intro">' . e($item['context']) . '</p>' . '<div class="photo-story service-feature">' . editorialPhoto($item['detailImage']) . '<div><h3>' . e($item['sections'][0][0]) . '</h3><p>' . e($item['sections'][0][1]) . '</p></div></div>' . contentCards(array_slice($item['sections'], 1)) . '</section>';
    $body .= '<section class="section muted-section"><div class="shell"><p class="section-kicker">Helder voor iedereen</p><h2>Weten waar je aan toe bent.</h2><div class="responsibilities"><article><h3>' . e($item['leftTitle']) . '</h3>' . itemList($item['left']) . '</article><article><h3>' . e($item['rightTitle']) . '</h3>' . itemList($item['right']) . '</article></div></div></section>';
    $body .= '<section class="section shell related related-photo">' . editorialPhoto($item['closingImage']) . '<div><p class="section-kicker">Elke situatie is anders</p><h2>Wat speelt er bij jou?</h2><p>Bespreek je vraag met ons. Samen kijken we welke begeleiding of dienstverlening past bij jouw organisatie en je medewerkers.</p></div><div>' . button('contact.html#werkgever', 'Bespreek jouw vraag') . '<a class="text-link" href="werkgevers.html#diensten">Bekijk alle diensten' . arrow() . '</a></div></section>';
    page($slug, $item['title'], $item['intro'], $body);
}
$employerDetail = splitSection('samenwerken', 'Samenwerking die verder kijkt', 'Meer dan alleen<br>het verzuimdossier.', [
    'Een medewerker die uitvalt mist vaak meer dan het werk alleen. Ook voor jou als werkgever verandert er veel. React2u kijkt daarom naar gezondheid, persoonlijke omstandigheden én de werkomgeving. Werkdruk, communicatie en organisatiecultuur kunnen allemaal een rol spelen.',
    'Onze casemanagers, taakgedelegeerden en bedrijfsartsen werken met jou en je medewerker aan een passende aanpak. Met persoonlijke gesprekken, duidelijkheid over de stappen en aandacht voor een duurzame terugkeer naar eigen, passend of ander werk.'
]);
$employerDetail .= '<section class="section muted-section"><div class="shell"><p class="section-kicker">Zo werken we samen</p><h2>Betrokken bij je mensen.<br>Overzicht voor jou.</h2>' . contentCards([
    ['Een vast aanspreekpunt', 'Bij WVP-begeleiding heeft jouw organisatie een eigen casemanager. Die onderhoudt contact, volgt de voortgang en helpt bij de afspraken en dossieropbouw. Je weet bij wie je terechtkunt wanneer iets verandert.'],
    ['Informatie bij elkaar', 'React2u werkt met een online verzuimregistratiesysteem. Rapportages, afspraken en documenten ondersteunen de samenwerking en helpen overzicht te houden over de voortgang en evaluatiemomenten.'],
    ['De deskundigheid die past', 'Naast het verzuimteam kan een netwerk van andere specialisten worden betrokken, zoals psychologen, fysiotherapeuten en arbeidsdeskundigen. De situatie bepaalt welke aanvullende ondersteuning nodig is.'],
    ['Ook aandacht vóór verzuim', 'Je hoeft niet te wachten tot iemand uitvalt. Met preventief medisch onderzoek, medewerkerstevredenheidsonderzoek, coaching en trainingen werk je aan gezondheid, werkplezier en duurzame inzetbaarheid.']
]) . '</div></section>';
$employerDetail .= '<section class="section shell"><div class="faq-layout"><div><p class="section-kicker">Veelgestelde vragen</p><h2>Goed om<br>te weten.</h2>' . editorialPhoto('samen-leren') . '</div><div>' . faq('Waar begin ik als er verzuim speelt?', 'Neem contact op en vertel wat er in je organisatie speelt. React2u kan de situatie met je bespreken en uitleggen welke begeleiding daarbij past. Bekijk ook de uitleg over <a href="verzuimbegeleiding-wvp.html">WVP</a> en <a href="verzuimbegeleiding-erd-zw.html">ERD/ZW</a>.') . faq('Kunnen jullie ook helpen zonder ziekmelding?', 'Ja. Preventie en vitaliteit richten zich juist ook op gezond blijven werken. Denk aan een PMO, medewerkerstevredenheidsonderzoek, preventieve gesprekken en trainingen. <a href="preventie-en-vitaliteit.html">Lees over preventie en vitaliteit</a>.') . faq('Is maatwerk mogelijk voor onze organisatie?', 'React2u stemt begeleiding en coaching af op de vraag. Ook trainingen en workshops kunnen op maat worden samengesteld. In een gesprek bespreken we jullie wensen en behoeften.') . '</div></div></section>';

$employeeDetail = splitSection('jouw-situatie', 'Werk is meer dan inkomen', 'Ook jij als mens<br>blijft in beeld.', [
    'Collega’s spreken, samen iets bereiken en waardering krijgen: werk is een belangrijk deel van je leven. Als je tijdelijk niet kunt werken, kunnen juist die dingen wegvallen. Daarom kijken we niet alleen naar je afwezigheid, maar ook naar hoe het met je gaat.',
    'React2u is een verzuim- en re-integratiebegeleider. Samen met jou, je werkgever, de bedrijfsarts en andere betrokken professionals zoeken we naar een passende oplossing. We blijven betrokken en bespreken wat jij nodig hebt om weer deel te nemen aan het werk.'
]);
$employeeDetail .= '<section class="section shell"><p class="section-kicker">Wat je van ons kunt verwachten</p><h2>Begeleiding die aansluit<br>bij jouw situatie.</h2>' . contentCards([
    ['Ruimte voor je verhaal', 'Een persoonlijk gesprek helpt te begrijpen wat er speelt en waar je tegenaan loopt. We luisteren en kijken met jou welke ondersteuning past. Iedereen is anders; daarom maken we de begeleiding op maat.'],
    ['Samen met de juiste mensen', 'Onze begeleiders werken samen met bedrijfsartsen en andere professionals. Als dat nodig is, kan aanvullende deskundigheid worden ingezet. Samen onderzoeken we wat je helpt bij herstel en terugkeer naar werk.'],
    ['Kijken naar mogelijkheden', 'Terugkeren kan vragen om aanpassingen in taken, werktijden of de werkplek. We helpen samen met jou en je werkgever de mogelijkheden te onderzoeken, binnen je eigen werk of eventueel ergens anders.'],
    ['Overzicht houden', 'Afspraken, gesprekken en documenten worden in het online platform bijgehouden. Heb je een vraag over de begeleiding, een afspraak of hoe je bij de juiste informatie komt? Neem dan contact op met React2u.']
]) . '</section>';

$about = infoHero('Over React2u', 'Aandacht raakt.', 'Gezond werken gaat over mensen. Bij React2u verbinden we ervaring in verzuim en re-integratie met persoonlijke aandacht voor werkgever én werknemer.', 'persoonlijk-gesprek', 'Professioneel én persoonlijk', 'werkgevers');
$about .= splitSection('wie-wij-zijn', 'Maak kennis met ons', 'Warm in het contact.<br>Duidelijk in de aanpak.', [
    'React2u helpt werkgevers, adviseurs en werknemers bij herstel en een gezonde terugkeer naar de werkplek. We weten dat even aan de zijlijn staan impact heeft. Daarom is persoonlijke aandacht het vertrekpunt van onze begeleiding.',
    'Onze casemanagers, taakgedelegeerden en bedrijfsartsen werken samen met de betrokken partijen aan een oplossing. Daarbij kijken we naar eigen werk, passend werk of ander werk. Wat iemand nodig heeft en welke mogelijkheden er zijn, verschilt per situatie.',
    'We luisteren, stellen vragen en zijn eerlijk en duidelijk. Soms helpt een klein duwtje in de rug; soms vraagt een situatie om een andere aanpak of aanvullende expertise. We maken verbinding en bespreken wat nodig is om verder te komen.'
]);
$about .= '<section class="section muted-section"><div class="shell"><p class="section-kicker">Waar we voor staan</p><h2>Gezond. Menselijk. Duidelijk.</h2>' . contentCards([
    ['Gezond', 'We zetten ons in voor herstel én preventie. Met begeleiding, advies en ondersteuning helpen we werkgevers en werknemers werken aan een gezonde en prettige werkomgeving.'],
    ['Menselijk', 'Achter een ziekmelding zit een mens. We bieden een luisterend oor, durven kritisch te zijn en hebben ruimte voor humor. Persoonlijk contact helpt om te ontdekken wat er echt speelt.'],
    ['Duidelijk', 'We brengen structuur in het traject en bespreken afspraken en vervolgstappen. Het online platform ondersteunt het overzicht van rapportages, afspraken en documenten.'],
    ['Samen', 'Een oplossing ontstaat met de mensen die erbij betrokken zijn. We begeleiden, adviseren en verbinden werkgever, werknemer en professionals, met aandacht voor wat ieder kan bijdragen.']
]) . '</div></section>';
$about .= splitSection('ook-preventief', 'Verder kijken dan herstel', 'Goed voor vandaag.<br>Met oog voor morgen.', ['Naast verzuimbegeleiding kun je bij React2u terecht voor preventie en vitaliteit, coaching, trainingen en risicomanagement. Bijvoorbeeld wanneer je signalen eerder wilt herkennen, medewerkers wilt ondersteunen of wilt werken aan een gezondere organisatie.', 'Ook een specifieke vraag is welkom. We denken mee over een aanpak op maat en bespreken welke expertise daarvoor nodig is.']);
$about .= band('Maak kennis met React2u.', 'We horen graag wat er bij jou of jouw organisatie speelt.', 'contact.html', 'Laten we praten');
page('over-react2u', 'Over React2u', 'Maak kennis met de persoonlijke aanpak van React2u: aandacht voor mensen, herstel en gezond werken.', $about);

$protocol = infoHero('Verzuimprotocol', 'Ziek gemeld. Hoe nu verder?', 'Als je niet kunt werken, wil je weten wat je kunt verwachten. Het verzuimprotocol van React2u geeft richting aan het contact en de begeleiding na een ziekmelding.', 'even-bellen', 'Duidelijkheid geeft rust', 'werknemers');
$protocol .= '<section class="section shell"><p class="section-kicker">Het protocol in begrijpelijke stappen</p><h2>We houden contact.<br>En kijken samen vooruit.</h2><p class="reading-intro">De onderstaande uitleg volgt de onderwerpen van het bestaande visuele React2u-protocol. Bespreek vragen over jouw situatie en de afspraken binnen je organisatie met je werkgever of React2u.</p>' . contentCards([
    ['Rechten en plichten', 'Bij verzuim zijn er afspraken over bereikbaarheid, begeleiding en het samen zoeken naar mogelijkheden om te werken. React2u helpt je om te begrijpen welke stappen voor jouw situatie van belang zijn.'],
    ['Even je werk bellen', 'Laat je werkgever weten dat je niet kunt werken en volg de ziekmeldafspraken van je organisatie. Werk je via een uitzendbureau? Het bestaande React2u-protocol noemt ook contact met het uitzendbureau en de organisatie waar je werkt.'],
    ['Praktische afspraken maken', 'Bespreek hoe je bereikbaar bent en wat je weet over de verwachte duur van je afwezigheid. Leg vragen over werkhervatting en mogelijke werkzaamheden voor aan je begeleider.'],
    ['Contact met React2u', 'Na je ziekmelding neemt React2u volgens het protocol contact met je op. Samen met jou en je werkgever kijken we naar de begeleiding en de mogelijkheden om weer deel te nemen aan het werk.'],
    ['Begeleiding tijdens verzuim', 'Er is ruimte om te bespreken wat er speelt en welke hulp je nodig hebt. React2u ondersteunt het re-integratieproces en betrekt waar nodig de bedrijfsarts en andere professionals.'],
    ['Samen het vervolg bepalen', 'We kijken met jou en de bedrijfsarts naar wat je nodig hebt. Samen met je werkgever werk je aan afspraken en het plan van aanpak. React2u ondersteunt bij de stappen die daarbij horen.']
]) . '<a class="text-link" href="https://react2u.nl/wp-content/uploads/2024/06/Verzuimprotocol-1-e1717661637232-890x1024.png">Bekijk het oorspronkelijke visuele protocol' . arrow() . '</a></section>';
$protocol .= '<div class="shell protocol-photo">' . editorialPhoto('persoonlijk-gesprek', 'Ruimte voor je vragen. Aandacht voor jou.') . '</div>';
$protocol .= band('Niet duidelijk? Laat het ons weten.', 'Je hoeft vragen over de begeleiding niet voor jezelf te houden.', 'contact.html#werknemer', 'Stel je vraag');
page('verzuimprotocol', 'Verzuimprotocol', 'Uitleg bij het React2u-verzuimprotocol: ziekmelden, contact houden en samen werken aan het vervolg.', $protocol);
