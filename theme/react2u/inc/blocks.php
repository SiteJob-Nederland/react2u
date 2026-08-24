<?php
/**
 * Blokkenbibliotheek.
 *
 * Eén functie per herbruikbaar onderdeel, met de markup in
 * template-parts/blocks/. De sjablonen roepen alleen deze functies aan, zodat
 * een CTA op de homepage en een CTA in een artikel gegarandeerd hetzelfde blok
 * zijn. Eén plek om te wijzigen, één plek om te testen.
 *
 * @package React2u
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @param array<string,mixed> $args
 */
function react2u_block( string $name, array $args = array() ): void {
	get_template_part( 'template-parts/blocks/' . $name, null, $args );
}

/**
 * CTA-blok in vier varianten. De variant bepaalt de tekst en de knoppen, de
 * plaatsing (inline, sectie, zijkolom) bepaalt de vorm.
 *
 * PER KLANT: alleen de teksten in $presets aanpassen. De sleutels blijven,
 * want de sjablonen roepen ze bij naam aan.
 *
 * @param array<string,mixed> $args variant, layout, tone, title, text, eyebrow.
 */
function react2u_cta( array $args = array() ): void {
	$variant = $args['variant'] ?? 'quote';

	$presets = array(
		'quote' => array(
			'eyebrow'   => __( 'Volgende stap', 'react2u' ),
			'title'     => __( 'Benieuwd wat dit voor jou kost?', 'react2u' ),
			'text'      => __( 'Vertel wat je nodig hebt. We rekenen het door en sturen een offerte op maat.', 'react2u' ),
			'primary'   => 'quote',
			'secondary' => 'pricing',
		),
		'pricing' => array(
			'eyebrow'   => __( 'Tarieven', 'react2u' ),
			'title'     => __( 'Wat kost het?', 'react2u' ),
			'text'      => sprintf(
				/* translators: 1: vanafprijs, 2: eenheid */
				__( 'Het begint bij %1$s %2$s. Wat jij betaalt hangt af van je situatie.', 'react2u' ),
				(string) react2u_get( 'pricing.from' ),
				(string) react2u_get( 'pricing.unit' )
			),
			'primary'   => 'pricing',
			'secondary' => 'quote',
		),
		'guide' => array(
			'eyebrow'   => __( 'Kennisbank', 'react2u' ),
			'title'     => __( 'Eerst rustig uitzoeken hoe het werkt?', 'react2u' ),
			'text'      => __( 'In de kennisbank staat stap voor stap uitgelegd wat erbij komt kijken.', 'react2u' ),
			'primary'   => 'guide',
			'secondary' => 'quote',
		),
		'callback' => array(
			'eyebrow'   => __( 'Liever even bellen?', 'react2u' ),
			'title'     => __( 'Laat je terugbellen', 'react2u' ),
			'text'      => __( 'Eén telefoontje is vaak sneller dan drie mailtjes. Je hoort binnen een werkdag van ons.', 'react2u' ),
			'primary'   => 'callback',
			'secondary' => 'contact',
		),
	);

	$preset = $presets[ $variant ] ?? $presets['quote'];

	/*
	 * De klant mag de CTA-tekst overschrijven vanuit inc/proof.php, met
	 * 'eyebrow', 'title' en 'text' naast de bestaande 'label' en 'path'.
	 *
	 * Reden: de teksten hierboven zijn geschreven voor een offertetraject
	 * ("we rekenen het door en sturen een offerte op maat"). Bij de ene klant
	 * klopt dat en bij de andere is het ronduit fout. Tekst is klantmateriaal en
	 * hoort dus bij het andere klantmateriaal, niet in dit bestand.
	 */
	foreach ( array( 'eyebrow', 'title', 'text' ) as $veld ) {
		$eigen = (string) react2u_get( "cta.{$variant}.{$veld}", '' );
		if ( '' !== $eigen ) {
			$preset[ $veld ] = $eigen;
		}
	}

	react2u_block(
		'cta',
		array(
			'variant'   => $variant,
			'layout'    => $args['layout'] ?? 'section',   // section | inline | aside | bar
			'tone'      => $args['tone'] ?? 'dark',        // dark | light | soft
			'eyebrow'   => $args['eyebrow'] ?? $preset['eyebrow'],
			'title'     => $args['title'] ?? $preset['title'],
			'text'      => $args['text'] ?? $preset['text'],
			'primary'   => $args['primary'] ?? $preset['primary'],
			'secondary' => array_key_exists( 'secondary', $args ) ? $args['secondary'] : $preset['secondary'],
			'rating'    => $args['rating'] ?? false,
			'person'    => $args['person'] ?? false,
			'heading'   => $args['heading'] ?? 'h2',
		)
	);
}

/**
 * Een inline-CTA als HTML-string, klaar om tussen de tekst te weven.
 * Zo roepen het artikel- én het servicesjabloon exact hetzelfde blok aan.
 *
 * @param array<string,mixed> $args
 */
function react2u_cta_inline_html( array $args = array() ): string {
	ob_start();
	react2u_cta( $args + array( 'layout' => 'inline', 'heading' => 'p' ) );
	return (string) ob_get_clean();
}

/**
 * Meerdere CTA's door lange inhoud weven: vóór het derde en vóór het zesde
 * hoofdstuk. Voor servicepagina's een must — de tekst die Nova van een
 * bestaande pagina kopieert, mist anders elke oproep behalve die ene link naar
 * de contactpagina. Zijn er te weinig koppen, dan komt er gewoon minder in;
 * geforceerd een CTA tussen twee zinnen proppen leest slechter dan er één.
 */
function react2u_weave_ctas( string $body, string $eerste = 'pricing', string $tweede = 'callback' ): string {
	$body = react2u_insert_before_heading(
		$body,
		react2u_cta_inline_html( array( 'variant' => $eerste, 'tone' => 'soft' ) ),
		3
	);
	$body = react2u_insert_before_heading(
		$body,
		react2u_cta_inline_html( array( 'variant' => $tweede, 'tone' => 'light' ) ),
		6
	);
	return $body;
}

/** Sterrenscore-badge. Herbruikbaar in hero, footer, CTA en sticky balk. */
function react2u_rating_badge( array $args = array() ): void {
	react2u_block(
		'rating',
		array(
			'size'    => $args['size'] ?? 'default', // small | default | large
			'compact' => $args['compact'] ?? false,
			'tone'    => $args['tone'] ?? 'auto',
		)
	);
}

/** Cijferrij. Vier tegels standaard; geef 'items' mee voor een eigen set. */
function react2u_stats( array $args = array() ): void {
	react2u_block(
		'stats',
		array(
			'items'   => $args['items'] ?? (array) react2u_get( 'stats', array() ),
			'tone'    => $args['tone'] ?? 'light', // light | dark
			'compact' => $args['compact'] ?? false,
			'notes'   => $args['notes'] ?? false,
		)
	);
}

/** Reviewblok: quote met sterren, naam, bedrijf. */
function react2u_review( array $args = array() ): void {
	$index  = (int) ( $args['index'] ?? 0 );
	$review = $args['review'] ?? ( (array) react2u_get( 'reviews', array() ) )[ $index ] ?? null;

	if ( ! $review ) {
		return;
	}

	react2u_block(
		'review',
		array(
			'review' => $review,
			'size'   => $args['size'] ?? 'default', // small | default | feature
		)
	);
}

/** Casekaart: branche, één resultaatcijfer, korte tekst, link. */
function react2u_case_card( array $args = array() ): void {
	$index = (int) ( $args['index'] ?? 0 );
	$case  = $args['case'] ?? ( (array) react2u_get( 'cases', array() ) )[ $index ] ?? null;

	if ( ! $case ) {
		return;
	}

	react2u_block( 'case-card', array( 'case' => $case ) );
}

/**
 * Bannerslot: breed en dun, met terugval. Zonder uitgelichte afbeelding valt
 * het blok terug op een eigen foto of op het beeldmerk, zodat een artikel
 * zonder beeld er niet half af uitziet.
 */
function react2u_banner( array $args = array() ): void {
	react2u_block(
		'banner',
		array(
			'post_id'  => (int) ( $args['post_id'] ?? get_the_ID() ),
			'label'    => $args['label'] ?? '',
			'fallback' => $args['fallback'] ?? true,
		)
	);
}

/** Inhoudsopgave uit de H2's. */
function react2u_toc( array $toc ): void {
	echo react2u_toc_markup( $toc ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- eigen markup, tekst al ge-escaped.
}

/** FAQ-accordeon; levert samen met inc/seo.php het FAQPage-schema. */
function react2u_faq( string $faq_html, array $args = array() ): void {
	if ( '' === trim( $faq_html ) ) {
		return;
	}

	react2u_block(
		'faq',
		array(
			'html'    => $faq_html,
			'title'   => $args['title'] ?? __( 'Veelgestelde vragen', 'react2u' ),
			'eyebrow' => $args['eyebrow'] ?? __( 'Vragen', 'react2u' ),
		)
	);
}

/** Auteurskaart: foto, functie, bio, LinkedIn, recente artikelen. */
function react2u_author_card( int $author_id, array $args = array() ): void {
	if ( ! $author_id ) {
		return;
	}

	react2u_block(
		'author-card',
		array(
			'author_id' => $author_id,
			'layout'    => $args['layout'] ?? 'full', // full | byline
			'recent'    => $args['recent'] ?? false,
		)
	);
}

/** Sectiekop met eyebrow — gebruikt door alle secties. */
function react2u_section_heading( array $args = array() ): void {
	react2u_block(
		'section-heading',
		array(
			'eyebrow' => $args['eyebrow'] ?? '',
			'title'   => $args['title'] ?? '',
			'text'    => $args['text'] ?? '',
			'level'   => $args['level'] ?? 'h2',
			'id'      => $args['id'] ?? '',
			'align'   => $args['align'] ?? 'left',
			'tone'    => $args['tone'] ?? 'light',
		)
	);
}

/** Woordmerkenrij (koppelingen, vervoerders, keurmerken). */
function react2u_logo_row( array $args = array() ): void {
	react2u_block(
		'logo-row',
		array(
			'items' => $args['items'] ?? (array) react2u_get( 'integrations', array() ),
			'label' => $args['label'] ?? '',
		)
	);
}

/**
 * Sterren als markup. Halve sterren worden naar beneden afgerond getekend maar
 * blijven in de tekstuele score staan; dat is eerlijker dan een halve ster die
 * op klein formaat toch niet te zien is.
 */
function react2u_stars_markup( float $score, int $max = 5 ): string {
	$full = (int) floor( $score );
	$out  = '';

	for ( $i = 1; $i <= $max; $i++ ) {
		if ( $i <= $full ) {
			$out .= sprintf( '<span class="star is-full" aria-hidden="true">%s</span>', react2u_icon( 'star' ) );
			continue;
		}

		if ( $i - 1 < $score ) {
			// Half: de gevulde ster ligt over de lege heen en wordt bijgesneden.
			$out .= sprintf(
				'<span class="star is-half" aria-hidden="true">%1$s<span class="star-fill">%1$s</span></span>',
				react2u_icon( 'star' )
			);
			continue;
		}

		$out .= sprintf( '<span class="star" aria-hidden="true">%s</span>', react2u_icon( 'star' ) );
	}

	return $out;
}

/** Score als getal, ook als hij met een komma is ingevuld. */
function react2u_rating_value(): float {
	$raw = str_replace( REACT2U_PLACEHOLDER, '', (string) react2u_get( 'rating.score', '0' ) );

	return (float) str_replace( ',', '.', trim( $raw ) );
}

/**
 * Inline SVG-iconen. Geen icoonfont en geen externe sprite: één bestand minder
 * om te laden en de kleur volgt gewoon de tekstkleur.
 *
 * Bewust een kleine set. Een icoon per dienst of per label maakt een pagina
 * druk en generiek; hier staat alleen wat een bezoeker echt helpt herkennen.
 */
function react2u_icon( string $name, array $args = array() ): string {
	$icons = array(
		'arrow'    => '<path d="M5 12h14M13 6l6 6-6 6"/>',
		'star'     => '<path d="m12 2.5 2.9 6.1 6.6.9-4.8 4.7 1.2 6.7-5.9-3.2-5.9 3.2 1.2-6.7L2.5 9.5l6.6-.9z"/>',
		'phone'    => '<path d="M6 3h4l2 5-2.5 1.5a12 12 0 0 0 5 5L16 12l5 2v4a2 2 0 0 1-2.2 2A17 17 0 0 1 4 5.2 2 2 0 0 1 6 3z"/>',
		'mail'     => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3.5 7 8.5 6 8.5-6"/>',
		'pin'      => '<path d="M12 21s7-5.5 7-11a7 7 0 1 0-14 0c0 5.5 7 11 7 11z"/><circle cx="12" cy="10" r="2.6"/>',
		'close'    => '<path d="m6 6 12 12M18 6 6 18"/>',
		'sun'      => '<circle cx="12" cy="12" r="4.2"/><path d="M12 2v2.5M12 19.5V22M2 12h2.5M19.5 12H22M4.9 4.9l1.8 1.8M17.3 17.3l1.8 1.8M19.1 4.9l-1.8 1.8M6.7 17.3l-1.8 1.8"/>',
		'moon'     => '<path d="M20 14.5A8.5 8.5 0 0 1 9.5 4a8.5 8.5 0 1 0 10.5 10.5z"/>',
		'quote'    => '<path d="M9.5 6C6.5 7.5 5 10 5 13v5h6v-6H8.2c.2-1.8 1-3.2 2.5-4.2zM19 6c-3 1.5-4.5 4-4.5 7v5h6v-6h-2.8c.2-1.8 1-3.2 2.5-4.2z"/>',
		'linkedin' => '<rect x="3" y="3" width="18" height="18" rx="3"/><path d="M8 10.5V17M8 7.6v.1M12 17v-3.6c0-1.3.9-2.2 2.1-2.2 1.2 0 1.9.8 1.9 2.2V17"/>',
		'x'        => '<path d="M4 4l16 16M20 4L4 20"/>',
		'instagram'=> '<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><path d="M17.5 6.5v.01"/>',
		'facebook' => '<path d="M14 8.5h2.5V5.5H14c-2 0-3 1.3-3 3.2V11H8.5v3H11v6h3v-6h2.3l.7-3H14V9c0-.4.2-.5.5-.5z"/>',
		'youtube'  => '<rect x="3" y="6" width="18" height="12" rx="3.5"/><path d="M10.5 9.5v5l4.2-2.5z"/>',
		'globe'    => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.5 2.5 2.5 15 0 18M12 3c-2.5 2.5-2.5 15 0 18"/>',
		'check'    => '<path d="m5 12.5 4.5 4.5L19 7.5"/>',
	);

	if ( ! isset( $icons[ $name ] ) ) {
		return '';
	}

	return sprintf(
		'<svg class="icon icon-%1$s" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="%2$s" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">%3$s</svg>',
		esc_attr( $name ),
		esc_attr( (string) ( $args['stroke'] ?? '1.7' ) ),
		$icons[ $name ]
	);
}

/**
 * Het logo van de klant, uit het aangeleverde bestand.
 *
 * Bewust het echte bestand en geen nagetekende versie: een merk teken je niet
 * na. Staat er nog geen logo in assets/images/, dan tonen we de sitenaam als
 * woordmerk — beter dan een gebroken afbeelding op een verse installatie.
 *
 * @param array<string,mixed> $args tone: auto|light, class, width.
 */
function react2u_logo( array $args = array() ): string {
	$tone  = (string) ( $args['tone'] ?? 'auto' );
	$class = 'site-logo' . ( 'light' === $tone ? ' is-light' : '' ) . ( isset( $args['class'] ) ? ' ' . $args['class'] : '' );
	$width = (int) ( $args['width'] ?? 200 );

	// SVG eerst: schaalbaar en scherp op elk scherm. Daarna raster.
	$bestand = '';
	foreach ( array( 'logo.svg', 'logo.png', 'logo.webp' ) as $kandidaat ) {
		if ( file_exists( REACT2U_DIR . '/assets/images/' . $kandidaat ) ) {
			$bestand = $kandidaat;
			break;
		}
	}

	if ( '' === $bestand ) {
		return sprintf( '<span class="%s is-wordmark">%s</span>', esc_attr( $class ), esc_html( (string) get_bloginfo( 'name' ) ) );
	}

	$ratio = react2u_logo_ratio( REACT2U_DIR . '/assets/images/' . $bestand );

	return sprintf(
		'<img class="%1$s" src="%2$s" alt="%3$s" width="%4$d" height="%5$d" decoding="async">',
		esc_attr( $class ),
		esc_url( REACT2U_URI . '/assets/images/' . $bestand ),
		esc_attr( (string) get_bloginfo( 'name' ) ),
		$width,
		(int) round( $width * $ratio )
	);
}

/** Hoogte/breedte-verhouding van een logobestand, ook voor SVG (via viewBox). */
function react2u_logo_ratio( string $pad ): float {
	if ( str_ends_with( strtolower( $pad ), '.svg' ) ) {
		$svg = (string) file_get_contents( $pad );
		if ( preg_match( '/viewBox="[\d.]+ [\d.]+ ([\d.]+) ([\d.]+)"/i', $svg, $m ) && (float) $m[1] > 0 ) {
			return (float) $m[2] / (float) $m[1];
		}
		if ( preg_match( '/width="([\d.]+)"/i', $svg, $w ) && preg_match( '/height="([\d.]+)"/i', $svg, $h ) && (float) $w[1] > 0 ) {
			return (float) $h[1] / (float) $w[1];
		}
		return 0.3;
	}

	$size = getimagesize( $pad );
	return $size && $size[0] ? $size[1] / $size[0] : 0.3;
}

/**
 * Alleen het beeldmerk, zonder woordmerk. Gebruikt als rustig vlak bij een
 * artikel zonder eigen afbeelding.
 */
function react2u_logo_mark( int $size = 72 ): string {
	if ( ! file_exists( REACT2U_DIR . '/assets/images/mark.png' ) ) {
		return '';
	}

	return sprintf(
		'<img class="logo-mark" src="%1$s" alt="" width="%2$d" height="%2$d" loading="lazy" decoding="async" aria-hidden="true">',
		esc_url( REACT2U_URI . '/assets/images/mark.png' ),
		$size
	);
}

/** URL van een foto uit de themamap. */
function react2u_image_url( string $file ): string {
	return REACT2U_URI . '/assets/images/' . ltrim( $file, '/' );
}

/**
 * Persoonskaart: het gezicht bij de dienst.
 *
 * Zonder portret tonen we een gemarkeerd leeg vlak in plaats van een willekeurig
 * gezicht. Een verzonnen persoon naast een echt telefoonnummer betekent dat
 * iemand straks vraagt naar iemand die niet bestaat.
 *
 * @param array<string,mixed> $args layout: card | inline
 */
function react2u_person( array $args = array() ): void {
	$person = $args['person'] ?? (array) react2u_get( 'people.contact', array() );

	if ( ! $person ) {
		return;
	}

	react2u_block(
		'person',
		array(
			'person' => $person,
			'layout' => $args['layout'] ?? 'card',
			'phone'  => $args['phone'] ?? true,
		)
	);
}
