<?php
/** Bouw de geïsoleerde ontwerpversie; lees contact en diensten uit de echte bron. */
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
// De standalone builder registreert geen WordPress-adminhooks en start geen WordPress.
function add_action(string $hook, callable|string $callback): void {}
function apply_filters(string $hook, mixed $value): mixed { return $value; }
define('ABSPATH', __DIR__ . '/');
require dirname(__DIR__) . '/theme/react2u/inc/proof.php';
$config = react2u_config();
function e(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
function arrow(): string { return '<svg class="arrow" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 12h15M13 5l7 7-7 7"/></svg>'; }
function dots(): string { return '<div class="merk-cirkels" aria-hidden="true">' . str_repeat('<i></i>', 8) . '</div>'; }
function photo(string $name, string $alt, string $sizes, bool $priority = false): string {
    $root = __DIR__ . '/media/theme/react2u-ontwerp/';
    $manifest = json_decode(file_get_contents($root . 'assets/images/quality/manifest.json'), true, 512, JSON_THROW_ON_ERROR);
    $source = $name === 'aandacht' ? 'aandacht-definitief' : $name;
    $item = $manifest['assets/images/bron/' . $source . '.png'];
    $variants = $item['variants'];
    $fallback = $variants[min(2, count($variants) - 1)];
    $srcset = implode(', ', array_map(fn($v) => 'assets/quality/' . basename($v['path']) . ' ' . $v['width'] . 'w', $variants));
    return '<img src="assets/quality/' . basename($fallback['path']) . '" srcset="' . e($srcset) . '" sizes="' . e($sizes) . '" width="' . $item['sourceWidth'] . '" height="' . $item['sourceHeight'] . '" alt="' . e($alt) . '" ' . ($priority ? 'fetchpriority="high" loading="eager"' : 'loading="lazy"') . '>';
}
function button(string $url, string $label, bool $light = false): string {
    return '<a class="button' . ($light ? ' button-light' : '') . '" href="' . e($url) . '">' . e($label) . arrow() . '</a>';
}
function band(string $title, string $copy, string $url, string $label): string {
    return '<section class="contact-band" aria-labelledby="contact-heading"><div class="shell"><div><h2 id="contact-heading">' . $title . '</h2><p>' . $copy . '</p></div>' . button($url, $label) . '</div></section>';
}
function faq(string $question, string $answer): string {
    return '<details class="faq"><summary>' . $question . '</summary><p>' . $answer . '</p></details>';
}
function page(string $name, string $body): void {
    global $config;
    $routes = json_decode(file_get_contents(dirname(__DIR__) . '/theme/react2u/inc/seo-routes.json'), true, 512, JSON_THROW_ON_ERROR);
    if (!isset($routes[$name]['title'], $routes[$name]['description'])) {
        throw new RuntimeException('SEO-route ontbreekt: ' . $name);
    }
    $title = $routes[$name]['title'];
    $description = $routes[$name]['description'];
    $contact = $config['contact'];
    $assetManifest = is_file(__DIR__ . '/assets/quality-assets.json') ? json_decode(file_get_contents(__DIR__ . '/assets/quality-assets.json'), true) : [];
    $css = isset($assetManifest['ontwerp.css']) ? $assetManifest['ontwerp.css']['path'] : 'ontwerp.css';
    $js = isset($assetManifest['ontwerp.js']) ? $assetManifest['ontwerp.js']['path'] : 'ontwerp.js';
    ob_start(); ?>
<!doctype html>
<html lang="nl">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="robots" content="noindex,nofollow"><meta name="description" content="<?= e($description) ?>">
  <title><?= e($title) ?></title>
  <link rel="icon" href="assets/favicon.png"><link rel="stylesheet" href="<?= e($css) ?>">
  <link rel="preload" href="assets/display-var-latin.woff2" as="font" type="font/woff2" crossorigin>
  <script src="<?= e($js) ?>" defer></script>
</head>
<body class="<?= $name === 'home' ? 'page-chooser' : 'page-' . e($name) ?>">
<a class="skip" href="#main">Ga naar inhoud</a>
<?php if ($name === 'home'): ?>
<header class="chooser-header"><a class="logo" href="index.html" aria-label="React2u — naar home"><img src="assets/logo.png" width="164" height="101" alt="React2u"></a><p>Gezond. Menselijk. Duidelijk.</p></header>
<?php else: ?>
<header class="header"><div class="shell header-inner">
  <a class="logo" href="index.html" aria-label="React2u — naar home"><img src="assets/logo.png" width="164" height="101" alt="React2u"></a>
  <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="navigatie"><span data-menu-label>Menu</span><svg class="arrow" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18M3 12h18M3 18h18"/></svg></button>
  <nav class="nav" id="navigatie" aria-label="Hoofdnavigatie">
    <a href="werkgevers.html"<?= $name === 'werkgevers' ? ' aria-current="page"' : '' ?>>Werkgevers</a>
    <a href="werknemers.html"<?= $name === 'werknemers' ? ' aria-current="page"' : '' ?>>Werknemers</a>
    <a href="over-react2u.html">Over React2u</a>
    <a class="contact-link" href="contact.html"<?= $name === 'contact' ? ' aria-current="page"' : '' ?>>Contact <span aria-hidden="true">↗</span></a>
  </nav>
</div></header>
<?php endif; ?>
<main id="main" tabindex="-1"><?= $body ?></main>
<?php if ($name === 'home'): ?>
<footer class="chooser-footer"><span>React2u</span><nav aria-label="Documenten"><a href="https://react2u.nl/wp-content/uploads/2025/05/Privacy%20reglement%20r2u.pdf">Privacy</a><a href="https://react2u.nl/wp-content/uploads/2025/05/Klachtenprocedure%20r2u.pdf">Klachtenprocedure</a><a href="https://react2u.nl/wp-content/uploads/2025/05/Algemene%20voorwaarden%20r2u.pdf">Voorwaarden</a></nav></footer>
<?php else: ?>
<footer class="footer"><div class="shell">
  <div class="footer-grid">
    <div class="footer-brand"><a class="logo" href="index.html" aria-label="React2u — naar home"><img src="assets/logo.png" width="164" height="101" loading="lazy" alt="React2u"></a><p class="footer-tagline"><?= e($contact['tagline']) ?>.</p></div>
    <nav aria-label="Snel naar"><h2>Waar kunnen we je mee helpen?</h2><ul><li><a href="werkgevers.html">Voor werkgevers</a></li><li><a href="werknemers.html">Voor werknemers</a></li><li><a href="blog.html">Blog</a></li><li><a href="kennisbank.html">Kennisbank</a></li><li><a href="over-react2u.html">Over React2u</a></li><li><a href="contact.html">Neem contact op</a></li></ul></nav>
    <div><h2>Even contact</h2><ul><li><a href="tel:<?= e($contact['phone_link']) ?>"><?= e($contact['phone']) ?></a></li><li><a href="mailto:<?= e($contact['email']) ?>"><?= e($contact['email']) ?></a></li><li><?= e($contact['street']) ?><br><?= e($contact['postcode'] . ' ' . $contact['city']) ?></li></ul></div>
  </div>
  <div class="footer-bottom"><span>React2u · Gezond. Menselijk. Duidelijk.</span><nav class="legal" aria-label="Documenten"><a href="https://react2u.nl/wp-content/uploads/2025/05/Privacy%20reglement%20r2u.pdf">Privacy</a><a href="https://react2u.nl/wp-content/uploads/2025/05/Klachtenprocedure%20r2u.pdf">Klachtenprocedure</a><a href="https://react2u.nl/wp-content/uploads/2025/05/Algemene%20voorwaarden%20r2u.pdf">Voorwaarden</a></nav></div>
</div></footer>
<?php endif; ?>
</body></html>
<?php
    file_put_contents(__DIR__ . '/' . ($name === 'home' ? 'index' : $name) . '.html', ob_get_clean());
}

require __DIR__ . '/verdieping.php';

$home = '<section class="audience-gateway" id="keuze" aria-labelledby="chooser-title">
  <div class="gateway-intro shell"><p>Welkom bij React2u</p><h1 id="chooser-title">Waar kunnen we je mee helpen?</h1></div>
  <nav class="audience-choices shell" aria-label="Kies jouw route">
    <a class="audience-choice audience-choice-employer" href="werkgevers.html">
      ' . photo('werkplezier', '', '(max-width:760px) 100vw, 50vw', true) . '
      <span class="choice-copy"><span class="choice-kicker">Voor organisaties</span><h2>Ik ben<br>werkgever</h2><span class="choice-detail">Voor mijn mensen en organisatie</span><span class="choice-arrow">' . arrow() . '</span></span>
    </a>
    <a class="audience-choice audience-choice-employee" href="werknemers.html">
      ' . photo('persoonlijk-gesprek', '', '(max-width:760px) 100vw, 50vw', true) . '
      <span class="choice-copy"><span class="choice-kicker">Voor jou</span><h2>Ik ben<br>werknemer</h2><span class="choice-detail">Voor mijn herstel en werk</span><span class="choice-arrow">' . arrow() . '</span></span>
    </a>
  </nav>
</section>';
page('home', $home);

$services = '';
foreach ($config['services'] as $index => $service) {
    $serviceImage = $serviceContent[trim($service['path'], '/')]['image'];
    $url = e(trim($service['path'], '/') . '.html');
    if ($index === 0) {
        $services .= '<a class="service service-lead" data-service="' . e($service['slug']) . '" href="' . $url . '">' . photo($serviceImage, '', '100vw') . '<div class="service-lead-copy"><span class="service-number">01 / Verzuim en herstel</span><h3>' . e($service['title']) . '</h3><span class="service-lead-description">' . e($service['text']) . '</span><span class="service-lead-cta">Ontdek de begeleiding ' . arrow() . '</span></div></a><div class="service-index">';
        continue;
    }
    $services .= '<a class="service service-row" data-service="' . e($service['slug']) . '" href="' . $url . '"><span class="service-number">' . str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) . '</span><span class="service-row-photo">' . photo($serviceImage, '', '(max-width:760px) 84px, 120px') . '</span><div class="service-row-copy"><h3>' . e($service['title']) . '</h3><span>' . e($service['text']) . '</span></div><span class="service-row-arrow">' . arrow() . '</span></a>';
}
$services .= '</div>';
$employers = '<div class="shell"><nav class="breadcrumb" aria-label="Kruimelpad"><a href="index.html">Home</a><span aria-hidden="true">/</span><a href="index.html#keuze">Voor wie</a><span aria-hidden="true">/</span><span aria-current="page">Werkgevers</span></nav>
<section class="subhero" aria-labelledby="employer-title"><div><p class="eyebrow">Voor werkgevers</p><h1 id="employer-title">Jouw mensen.<br>Onze aandacht.</h1><p class="hero-intro">Van verzuimbegeleiding tot preventie. We helpen je overzicht te houden en geven je medewerkers de persoonlijke begeleiding die ze nodig hebben.</p><div class="button-row">' . button('contact.html#werkgever', 'Laten we kennismaken') . button('#diensten', 'Bekijk onze diensten', true) . '</div></div>
<figure class="subhero-photo">' . photo('op-de-werkvloer', 'twee collega’s bespreken samen het werk.', '(max-width:760px) 90vw, 50vw', true) . '<figcaption>Goed voor je mensen.<br>Goed voor je organisatie.</figcaption></figure></section></div>
<section class="section divider shell" id="diensten" aria-labelledby="services-title"><div class="section-heading"><div><p class="section-kicker">Onze dienstverlening</p><h2 id="services-title">Wat speelt er<br>in jouw organisatie?</h2></div><p>Ondersteuning bij verzuim én aandacht voor wat je kunt voorkomen. Bekijk welke dienstverlening past bij jouw vraag.</p></div><div class="services">' . $services . '</div></section>
<section class="section muted-section" aria-labelledby="approach-title"><div class="shell about"><div><p class="section-kicker">Onze aanpak</p><h2 id="approach-title">Duidelijke structuur.<br>Persoonlijk betrokken.</h2></div><div class="about-copy"><p>Elke organisatie en iedere medewerker is anders. We kijken samen wat er nodig is, brengen de betrokken partijen bij elkaar en begeleiden het vervolg.</p><a class="text-link" href="contact.html#werkgever">Bespreek jouw vraag' . arrow() . '</a></div></div></section>' . band('Goed voor je mensen.<br>Goed voor je organisatie.', 'We maken graag kennis en bespreken wat er speelt.', 'contact.html#werkgever', 'Plan een kennismaking');
$employerMoments = '<section class="moments-section" aria-labelledby="moments-title" data-slider>
  <div class="shell moments-heading"><div><p class="section-kicker">Mensen in beweging</p><h2 id="moments-title">Wat een gezonde<br>werkplek mogelijk maakt.</h2></div><div class="slider-controls" hidden><button type="button" class="slider-button previous" aria-label="Vorig beeld" aria-controls="momenten">' . arrow() . '</button><span class="slider-status" aria-live="polite">1 / 3</span><button type="button" class="slider-button next" aria-label="Volgend beeld" aria-controls="momenten">' . arrow() . '</button></div></div>
  <div class="moments-track" id="momenten" tabindex="0" role="region" aria-label="Preventie, begeleiding en werkplezier voor organisaties">
    <article class="moment" aria-label="1 van 3: Aandacht voor werkplezier"><figure>' . photo('werkplezier', '', '(max-width:760px) 85vw, 65vw') . '</figure><div class="moment-copy"><span class="moment-kicker">Preventie</span><h3>Ruimte voor werkplezier.</h3><p>Een gezonde werkomgeving begint met aandacht voor de mensen die er werken.</p><a class="text-link" href="preventie-en-vitaliteit.html">Bekijk preventie en vitaliteit' . arrow() . '</a></div></article>
    <article class="moment" aria-label="2 van 3: Samen leren"><figure>' . photo('samen-leren', '', '(max-width:760px) 85vw, 65vw') . '</figure><div class="moment-copy"><span class="moment-kicker">Ontwikkeling</span><h3>Samen verder leren.</h3><p>Met praktische trainingen geef je leidinggevenden en teams meer houvast.</p><a class="text-link" href="trainingen-en-workshops.html">Bekijk trainingen en workshops' . arrow() . '</a></div></article>
    <article class="moment" aria-label="3 van 3: Persoonlijke begeleiding"><figure>' . photo('persoonlijk-gesprek', '', '(max-width:760px) 85vw, 65vw') . '</figure><div class="moment-copy"><span class="moment-kicker">Begeleiding</span><h3>Elk verhaal telt.</h3><p>Als iemand uitvalt, helpen we je om het vervolg persoonlijk en duidelijk te maken.</p><a class="text-link" href="verzuimbegeleiding-wvp.html">Bekijk verzuimbegeleiding' . arrow() . '</a></div></article>
  </div>
</section>';
$employers = str_replace('<section class="contact-band"', $employerDetail . $employerMoments . '<section class="contact-band"', $employers);
page('werkgevers', $employers);

$employees = '<div class="shell"><nav class="breadcrumb" aria-label="Kruimelpad"><a href="index.html">Home</a><span aria-hidden="true">/</span><a href="index.html#keuze">Voor wie</a><span aria-hidden="true">/</span><span aria-current="page">Werknemers</span></nav>
<section class="subhero" aria-labelledby="employee-title"><div><p class="eyebrow">Voor werknemers</p><h1 id="employee-title">Even uit het werk.<br>Niet uit beeld.</h1><p class="hero-intro">Als werken even niet gaat, komt er veel op je af. We luisteren naar jouw verhaal en helpen je op weg. Met aandacht voor jou en duidelijkheid over de begeleiding.</p><div class="button-row">' . button('#hulp', 'Waar kunnen we je bij helpen?') . '</div></div>
<figure class="subhero-photo">' . photo('samen-buiten', 'twee mensen wandelen samen in een zonnig park.', '(max-width:760px) 90vw, 40vw', true) . '<figcaption>Je hoeft het niet<br>alleen uit te zoeken.</figcaption></figure></section></div>
<section class="section divider shell" id="hulp" aria-labelledby="help-title"><div class="section-heading"><div><p class="section-kicker">Snel naar de juiste informatie</p><h2 id="help-title">Hoe kunnen we<br>je helpen?</h2></div><p>Een helder vertrekpunt voor je vragen over verzuim, begeleiding en contact met React2u.</p></div>
<div class="help-links"><a class="help-link" href="#begeleiding">' . photo('persoonlijk-gesprek', '', '(max-width:760px) 90vw, 33vw') . '<h3>Hoe werkt de<br>begeleiding?</h3><p>Lees wat React2u voor je kan betekenen en hoe we samenwerken.</p><span class="text-link">Over je begeleiding' . arrow() . '</span></a><a class="help-link" href="verzuimprotocol.html">' . photo('even-bellen', '', '(max-width:760px) 90vw, 33vw') . '<h3>Ik zoek het<br>verzuimprotocol</h3><p>Bekijk de informatie en afspraken van React2u rondom verzuim.</p><span class="text-link">Bekijk het protocol' . arrow() . '</span></a><a class="help-link" href="contact.html#werknemer">' . photo('samen-buiten', '', '(max-width:760px) 90vw, 33vw') . '<h3>Ik heb<br>een vraag</h3><p>Neem contact op als je iets wilt bespreken of meer uitleg zoekt.</p><span class="text-link">Contact met React2u' . arrow() . '</span></a></div></section>
<section class="section muted-section" id="begeleiding" aria-labelledby="support-title"><div class="shell about"><div><p class="section-kicker">Jouw begeleiding</p><h2 id="support-title">We kijken naar jou.<br>En naar wat wél kan.</h2></div><div class="about-copy"><p>React2u begeleidt werknemers bij herstel en terugkeer naar werk. Samen met jou, je werkgever en de betrokken professionals kijken we welke ondersteuning past bij jouw situatie.</p><a class="text-link" href="contact.html#werknemer">Een vraag over jouw begeleiding?' . arrow() . '</a></div></div></section>
<section class="section shell" aria-labelledby="faq-title"><div class="faq-layout"><div><p class="section-kicker">Duidelijkheid helpt</p><h2 id="faq-title">Misschien vraag<br>je je dit af.</h2>' . editorialPhoto('even-bellen') . '<p>Staat jouw vraag er niet bij? We helpen je graag verder.</p><a class="text-link" href="contact.html#werknemer">Neem contact op' . arrow() . '</a></div><div>' .
faq('Wat doet React2u voor werknemers?', 'We begeleiden werknemers bij verzuim en re-integratie. We werken samen met jou, je werkgever en betrokken professionals aan herstel en terugkeer naar werk.') .
faq('Waar vind ik het verzuimprotocol?', 'Het verzuimprotocol staat op de website van React2u. <a href="verzuimprotocol.html">Bekijk het verzuimprotocol</a>. Heb je vragen over de informatie? Neem dan contact met ons op.') .
faq('Bij wie kan ik terecht met een vraag?', 'Je kunt React2u bellen of mailen via de <a href="contact.html#werknemer">contactpagina voor werknemers</a>. Daar vind je de contactgegevens.') .
'</div></div></section>' . band('Jouw verhaal telt.', 'Heb je een vraag of behoefte aan uitleg? Neem contact op.', 'contact.html#werknemer', 'Stel je vraag');
$employees = str_replace('<section class="section shell" aria-labelledby="faq-title">', $employeeDetail . '<section class="section shell" aria-labelledby="faq-title">', $employees);
page('werknemers', $employees);

function contactMethods(): string {
    global $config;
    $c = $config['contact'];
    return '<div class="contact-methods"><a href="tel:' . e($c['phone_link']) . '"><span>' . e($c['phone']) . '</span><small>Bellen ↗</small></a><a href="mailto:' . e($c['email']) . '"><span>' . e($c['email']) . '</span><small>E-mail opstellen ↗</small></a></div>';
}
$contactPage = '<div class="shell"><nav class="breadcrumb" aria-label="Kruimelpad"><a href="index.html">Home</a><span aria-hidden="true">/</span><a href="over-react2u.html">React2u</a><span aria-hidden="true">/</span><span aria-current="page">Contact</span></nav>
<section class="subhero" aria-labelledby="contact-title"><div><p class="eyebrow">Kom met ons in contact</p><h1 id="contact-title">Goed dat je<br>contact zoekt.</h1><p class="hero-intro">Een kennismaking voor je organisatie of een vraag over je begeleiding. We horen graag van je.</p></div><figure class="subhero-photo">' . photo('even-bellen', photoDescription('even-bellen'), '(max-width:760px) 90vw, 40vw', true) . '<figcaption>We horen graag<br>jouw verhaal.</figcaption></figure></section>
<div class="contact-options"><section class="contact-option" id="werkgever" aria-labelledby="contact-employer"><p class="section-kicker">Voor werkgevers</p>' . editorialPhoto('werkplezier') . '<h2 id="contact-employer">Laten we kennismaken.</h2><p>Bespreek je vraag over verzuim, preventie of ondersteuning voor je organisatie.</p>' . contactMethods() . '</section><section class="contact-option" id="werknemer" aria-labelledby="contact-employee"><p class="section-kicker">Voor werknemers</p>' . editorialPhoto('persoonlijk-gesprek') . '<h2 id="contact-employee">Waar kunnen we je bij helpen?</h2><p>Neem contact op met een vraag over React2u of je begeleiding.</p>' . contactMethods() . '</section></div></div>';
page('contact', $contactPage);

$assets = __DIR__ . '/assets';
if (!is_dir($assets)) mkdir($assets, 0755, true);
foreach (['logo.png', 'favicon.png'] as $name) copy(dirname(__DIR__) . '/theme/react2u/assets/images/' . $name, $assets . '/' . $name);
foreach (['display-var-latin.woff2', 'body-var-latin.woff2'] as $name) copy(dirname(__DIR__) . '/theme/react2u/assets/fonts/' . $name, $assets . '/' . $name);
if (!is_dir($assets . '/quality')) mkdir($assets . '/quality', 0755, true);
foreach (glob(__DIR__ . '/media/theme/react2u-ontwerp/assets/images/quality/*.webp') as $image) copy($image, $assets . '/quality/' . basename($image));
echo "Veertien ontwerppagina's gebouwd met bestaande merkassets en proof.php als bron.\n";
