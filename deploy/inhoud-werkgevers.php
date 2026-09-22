<?php
/** Inhoud van de nieuwe werkgeversroute; ook los lokaal te controleren. */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$paragraph = static fn( string $text ): string => '<!-- wp:paragraph --><p>' . $text . '</p><!-- /wp:paragraph -->' . "\n\n";
$heading   = static fn( string $text ): string => '<!-- wp:heading --><h2>' . esc_html( $text ) . '</h2><!-- /wp:heading -->' . "\n\n";

return array(
	'post_title'   => 'Arbodienst voor werkgevers',
	'post_excerpt' => 'Verzuimbegeleiding, preventie en duurzame inzetbaarheid met persoonlijke aandacht voor jouw medewerkers.',
	'post_content' =>
		$paragraph( 'Van verzuimbegeleiding tot preventie: React2u helpt je overzicht te houden en geeft je medewerkers persoonlijke begeleiding. We kijken niet alleen naar het dossier, maar ook naar gezondheid, omstandigheden en de werkomgeving.' )
		. $heading( 'Verzuimbegeleiding voor jouw organisatie' )
		. $paragraph( 'Werkgever en medewerker zijn samen betrokken bij re-integratie. Onze casemanagers, taakgedelegeerden en bedrijfsartsen begeleiden het vervolg met persoonlijke gesprekken, heldere afspraken en aandacht voor een duurzame terugkeer naar eigen, passend of ander werk.' )
		. $paragraph( 'We ondersteunen je bij <a href="' . esc_url( home_url( '/verzuimbegeleiding-wvp/' ) ) . '">verzuimbegeleiding volgens de WVP</a>. Ben je eigenrisicodrager voor de Ziektewet? Bekijk dan <a href="' . esc_url( home_url( '/verzuimbegeleiding-erd-zw/' ) ) . '">begeleiding bij ERD/ZW</a>.' )
		. $heading( 'Ook aandacht vóór verzuim' )
		. $paragraph( 'Je hoeft niet te wachten tot iemand uitvalt. Met <a href="' . esc_url( home_url( '/preventie-en-vitaliteit/' ) ) . '">preventie en vitaliteit</a>, <a href="' . esc_url( home_url( '/begeleiding-en-coaching/' ) ) . '">begeleiding en coaching</a>, <a href="' . esc_url( home_url( '/trainingen-en-workshops/' ) ) . '">trainingen en workshops</a> en <a href="' . esc_url( home_url( '/risicomanagement/' ) ) . '">risicomanagement</a> werk je aan een gezonde werkplek.' )
		. $heading( 'Persoonlijk contact en duidelijk overzicht' )
		. $paragraph( 'Bij WVP-begeleiding heeft je organisatie een eigen casemanager. Rapportages, afspraken en documenten komen samen in een online verzuimregistratiesysteem. Welke andere deskundigen aansluiten, hangt af van de situatie.' )
		. $paragraph( 'Wil je bespreken wat er in jouw organisatie speelt? <a href="' . esc_url( home_url( '/contact/' ) ) . '">Neem contact op met React2u</a>.' ),
);
