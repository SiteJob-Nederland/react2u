<?php
/**
 * Alles wat het thema uit de inhoud zelf afleidt.
 *
 * De regel erachter: een externe redactietool vult onze maatwerkvelden toch
 * niet. Alles wat de sjablonen nodig hebben — inhoudsopgave, FAQ, CTA's,
 * leestijd — komt daarom uit de HTML of uit standaard WordPress-velden. Zo
 * werkt een aangeleverd artikel zonder dat er iemand handmatig velden invult.
 *
 * @package React2u
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Inhoud klaarmaken voor het artikelsjabloon.
 *
 * Geeft de inhoud terug met ankers op elke H2, de lijst met die koppen voor de
 * inhoudsopgave, en de FAQ apart zodat het sjabloon die onderaan kan plaatsen.
 *
 * @return array{content:string, toc:array<int,array{id:string,text:string}>, faq:string}
 */
function react2u_prepare_content( string $html ): array {
	$extracted = react2u_extract_faq( $html );
	$html      = $extracted['content'];

	$toc  = array();
	$used = array();
	$html = (string) preg_replace_callback(
		'#<h2([^>]*)>(.*?)</h2>#is',
		static function ( array $match ) use ( &$toc, &$used ): string {
			$text = trim( wp_strip_all_tags( $match[2] ) );
			if ( '' === $text ) {
				return $match[0];
			}

			if ( preg_match( '#\sid=["\']([^"\']+)["\']#i', $match[1], $existing ) ) {
				$id = $existing[1];
			} else {
				$id   = sanitize_title( $text ) ?: 'sectie';
				$base = $id;
				$n    = 2;
				while ( isset( $used[ $id ] ) ) {
					$id = $base . '-' . $n;
					++$n;
				}
				$match[1] .= sprintf( ' id="%s"', esc_attr( $id ) );
			}

			$used[ $id ] = true;
			$toc[]       = array( 'id' => $id, 'text' => $text );

			return sprintf( '<h2%s>%s</h2>', $match[1], $match[2] );
		},
		$html
	);

	return array(
		'content' => $html,
		'toc'     => $toc,
		'faq'     => $extracted['faq'],
	);
}

/**
 * De FAQ uit de inhoud lichten, op twee manieren.
 *
 * 1. Een expliciete <section class="faq-section"> met <details>-items.
 * 2. Een gewone H2 in de trant van "Veelgestelde vragen", waarna elke H3 een
 *    vraag is. Dat is wat een redactietool in de praktijk aanlevert, en het is
 *    precies de vorm waar geen FAQPage-schema uit komt als je er niets mee doet.
 *
 * @return array{content:string, faq:string}
 */
function react2u_extract_faq( string $html ): array {
	$faq = '';

	$html = (string) preg_replace_callback(
		'#<section[^>]*class="[^"]*faq[^"]*"[^>]*>.*?</section>#is',
		static function ( array $match ) use ( &$faq ): string {
			$faq = $match[0];
			return '';
		},
		$html,
		1
	);

	if ( '' !== $faq ) {
		return array( 'content' => $html, 'faq' => $faq );
	}

	$pattern = apply_filters( 'react2u_faq_heading_pattern', '#(veelgestelde\s+vrag|^faq$|^faq\b|vraag\s+en\s+antwoord|vragen\s+en\s+antwoorden)#iu' );

	if ( ! preg_match_all( '#<h2[^>]*>(.*?)</h2>#is', $html, $headings, PREG_OFFSET_CAPTURE | PREG_SET_ORDER ) ) {
		return array( 'content' => $html, 'faq' => '' );
	}

	foreach ( $headings as $index => $heading ) {
		$title = trim( wp_strip_all_tags( $heading[1][0] ) );
		if ( ! preg_match( $pattern, $title ) ) {
			continue;
		}

		$start = (int) $heading[0][1];
		$end   = isset( $headings[ $index + 1 ] ) ? (int) $headings[ $index + 1 ][0][1] : strlen( $html );
		$chunk = substr( $html, $start, $end - $start );

		// Alleen overnemen als er echt vraag-en-antwoordparen in staan.
		$body  = (string) preg_replace( '#<h2[^>]*>.*?</h2>#is', '', $chunk );
		$items = react2u_faq_items_from_headings( $body );
		if ( ! $items ) {
			break;
		}

		return array(
			'content' => substr_replace( $html, '', $start, $end - $start ),
			'faq'     => $items,
		);
	}

	return array( 'content' => $html, 'faq' => '' );
}

/**
 * H3-koppen met hun antwoord omzetten naar een toegankelijke accordeon.
 * <details> werkt zonder JavaScript en is met het toetsenbord te bedienen.
 */
function react2u_faq_items_from_headings( string $html ): string {
	if ( ! preg_match_all( '#<h3[^>]*>(.*?)</h3>#is', $html, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER ) ) {
		return '';
	}

	$items = '';
	foreach ( $matches as $index => $match ) {
		$question = trim( wp_strip_all_tags( $match[1][0] ) );
		$start    = (int) $match[0][1] + strlen( $match[0][0] );
		$end      = isset( $matches[ $index + 1 ] ) ? (int) $matches[ $index + 1 ][0][1] : strlen( $html );
		$answer   = trim( substr( $html, $start, $end - $start ) );

		if ( '' === $question || '' === trim( wp_strip_all_tags( $answer ) ) ) {
			continue;
		}

		$items .= sprintf(
			'<details class="faq-item"><summary><span>%s</span></summary><div class="faq-answer">%s</div></details>',
			esc_html( $question ),
			wp_kses_post( $answer )
		);
	}

	return '' === $items ? '' : '<div class="faq-accordion" data-faq>' . $items . '</div>';
}

/**
 * Vraag-en-antwoordparen voor FAQPage-gestructureerde data.
 *
 * @return array<int,array{question:string,answer:string}>
 */
function react2u_faq_pairs( string $faq_html ): array {
	if ( '' === trim( $faq_html ) || ! preg_match_all( '#<details[^>]*>(.*?)</details>#is', $faq_html, $items ) ) {
		return array();
	}

	$pairs = array();
	foreach ( $items[1] as $item ) {
		if ( ! preg_match( '#<summary[^>]*>(.*?)</summary>#is', $item, $summary ) ) {
			continue;
		}
		$question = trim( wp_strip_all_tags( $summary[1] ) );
		$answer   = trim( wp_strip_all_tags( (string) preg_replace( '#<summary[^>]*>.*?</summary>#is', '', $item ) ) );
		if ( '' !== $question && '' !== $answer ) {
			$pairs[] = array( 'question' => $question, 'answer' => $answer );
		}
	}

	return $pairs;
}

/**
 * Markup van de inhoudsopgave. Onder de drie koppen voegt een index niets toe;
 * dan is de lezer sneller klaar met gewoon doorlezen.
 *
 * @param array<int,array{id:string,text:string}> $toc
 */
function react2u_toc_markup( array $toc ): string {
	if ( count( $toc ) < 3 ) {
		return '';
	}

	$items = '';
	foreach ( $toc as $item ) {
		$items .= sprintf(
			'<li><a href="#%1$s">%2$s</a></li>',
			esc_attr( $item['id'] ),
			esc_html( $item['text'] )
		);
	}

	return sprintf(
		'<details class="toc" data-toc open><summary><span class="toc-title">%1$s</span><span class="toc-count">%2$s</span></summary><ol class="toc-list">%3$s</ol></details>',
		esc_html__( 'Inhoudsopgave', 'react2u' ),
		esc_html( sprintf( _n( '%d hoofdstuk', '%d hoofdstukken', count( $toc ), 'react2u' ), count( $toc ) ) ),
		$items
	);
}

/** Markup na de eerste alinea plaatsen: eerst weten waar het over gaat. */
function react2u_insert_after_intro( string $content, string $html ): string {
	if ( '' === $html ) {
		return $content;
	}

	$position = stripos( $content, '</p>' );
	if ( false === $position ) {
		return $html . $content;
	}

	return substr_replace( $content, $html, $position + 4, 0 );
}

/**
 * Een blok vóór de zoveelste H2 zetten. Zo staat een inline CTA middenin het
 * artikel op een natuurlijke plek, in plaats van tussen twee alinea's door.
 */
function react2u_insert_before_heading( string $content, string $html, int $nth = 3 ): string {
	if ( '' === $html || ! preg_match_all( '#<h2[^>]*>#i', $content, $matches, PREG_OFFSET_CAPTURE ) ) {
		return $content;
	}

	$headings = $matches[0];
	if ( count( $headings ) < $nth ) {
		return $content;
	}

	return substr_replace( $content, $html, (int) $headings[ $nth - 1 ][1], 0 );
}

/**
 * Is dit een servicepagina?
 *
 * Drie signalen, van sterk naar zwak: het sjabloon "Dienst" is expliciet
 * toegewezen, de pagina hangt onder /diensten/, of iemand zette de meta
 * _react2u_is_service. Nova kopieert bestaande dienstpagina's en houdt ze
 * onder dezelfde ouder, dus de tweede regel vangt de gekopieerde pagina's op
 * zonder dat iemand een sjabloon hoeft te kiezen.
 */
function react2u_is_service_page( ?int $post_id = null ): bool {
	$post_id = $post_id ?: (int) get_the_ID();
	if ( 'page' !== get_post_type( $post_id ) ) {
		return false;
	}

	if ( 'page-dienst.php' === get_page_template_slug( $post_id ) ) {
		return true;
	}

	if ( get_post_meta( $post_id, '_react2u_is_service', true ) ) {
		return true;
	}

	$ouders = (array) get_post_ancestors( $post_id );
	$ouders[] = $post_id;
	foreach ( $ouders as $id ) {
		$slug = get_post_field( 'post_name', $id );
		if ( in_array( $slug, array( 'diensten', 'dienst', 'services', 'service' ), true ) ) {
			// De overzichtspagina /diensten/ zelf is geen dienst; de kinderen wel.
			return (int) $id !== $post_id;
		}
	}

	return (bool) apply_filters( 'react2u_is_service_page', false, $post_id );
}

/**
 * De service-entry (kleur, foto, slug) uit inc/proof.php die bij de huidige
 * dienstpagina hoort — gekoppeld op URL-pad, niet op titel, zodat een
 * hernoemde titel de koppeling niet breekt.
 *
 * @return array<string,mixed>|null
 */
function react2u_service_for_post( ?int $post_id = null ): ?array {
	$post_id = $post_id ?: (int) get_the_ID();
	if ( ! $post_id ) {
		return null;
	}

	$pad = wp_make_link_relative( (string) get_permalink( $post_id ) );
	foreach ( (array) react2u_get( 'services', array() ) as $service ) {
		if ( ! empty( $service['path'] ) && untrailingslashit( (string) $service['path'] ) === untrailingslashit( $pad ) ) {
			return (array) $service;
		}
	}

	return null;
}

function react2u_reading_time( ?int $post_id = null ): int {
	$post_id = $post_id ?: (int) get_the_ID();
	$text    = wp_strip_all_tags( (string) get_post_field( 'post_content', $post_id ) );
	$words   = preg_match_all( '/[\p{L}\p{N}\'’-]+/u', $text );

	return max( 1, (int) ceil( $words / 220 ) );
}

/**
 * CTA-terugval per posttype.
 *
 * Een redactietool vult ons CTA-veld niet, en blijft in de tekst zelf meestal
 * hangen op één link naar /contact/. Zonder terugval mist een aangeleverd
 * artikel dus precies het onderdeel dat de conversie draagt.
 *
 * @return array<int,array{label:string,url:string,style:string}>
 */
function react2u_content_ctas( ?int $post_id = null ): array {
	$post_id = $post_id ?: (int) get_the_ID();
	$stored  = get_post_meta( $post_id, '_react2u_ctas', true );
	if ( is_array( $stored ) && $stored ) {
		return $stored;
	}

	$hard = array(
		'label' => react2u_cta_label( 'quote' ),
		'url'   => react2u_cta_url( 'quote' ),
		'style' => 'primary',
	);

	$soft = match ( get_post_type( $post_id ) ) {
		'react2u_kennisbank' => array( 'label' => react2u_cta_label( 'contact' ), 'url' => react2u_cta_url( 'contact' ), 'style' => 'secondary' ),
		default                 => array( 'label' => react2u_cta_label( 'pricing' ), 'url' => react2u_cta_url( 'pricing' ), 'style' => 'secondary' ),
	};

	return apply_filters( 'react2u_content_ctas', array( $hard, $soft ), $post_id );
}

/**
 * Auteursfoto zonder afhankelijkheid van één specifieke avatar-plugin.
 * Eerst ons eigen veld, daarna de sleutels die avatar-plugins en externe
 * redactietools gebruiken. Een waarde mag een URL, een bijlage-ID of een array
 * per formaat zijn.
 */
function react2u_author_photo_url( int $author_id ): string {
	$keys = apply_filters(
		'react2u_author_photo_meta_keys',
		array( 'react2u_photo_url', '_react2u_author_photo_url', 'simple_local_avatar', 'wp_user_avatar', 'profile_picture', 'author_photo_url' ),
		$author_id
	);

	foreach ( $keys as $key ) {
		$value = get_user_meta( $author_id, $key, true );
		if ( is_array( $value ) ) {
			$value = $value['full'] ?? reset( $value );
		}
		if ( is_numeric( $value ) ) {
			$value = wp_get_attachment_image_url( (int) $value, 'full' );
		}
		$value = is_string( $value ) ? trim( $value ) : '';
		if ( '' !== $value && str_starts_with( $value, 'http' ) ) {
			return $value;
		}
	}

	return '';
}

/**
 * Auteursfoto als markup.
 *
 * Staat er geen foto, dan tekenen we zelf initialen in plaats van Gravatar aan
 * te roepen. Gravatar is een verzoek naar een derde partij bij élke
 * paginaweergave — hetzelfde bezwaar als bij fonts van een CDN, en de reden dat
 * we die zelf hosten. Wie Gravatar tóch wil, zet de filter hieronder op true.
 */
function react2u_author_photo( int $author_id, int $size = 96 ): string {
	$name   = (string) get_the_author_meta( 'display_name', $author_id );
	$custom = react2u_author_photo_url( $author_id );

	if ( '' !== $custom ) {
		$attachment_id = attachment_url_to_postid( $custom );
		if ( $attachment_id ) {
			return wp_get_attachment_image( $attachment_id, 'react2u-author', false, array( 'alt' => $name, 'class' => 'author-avatar-image', 'width' => $size, 'height' => $size, 'sizes' => $size . 'px', 'loading' => 'lazy', 'decoding' => 'async' ) );
		}
		return sprintf(
			'<img %1$s alt="%2$s" width="%3$d" height="%3$d" loading="lazy" fetchpriority="low" decoding="async" class="author-avatar-image">',
			react2u_quality_image_attrs( $custom, $size . 'px' ),
			esc_attr( $name ),
			$size
		);
	}

	if ( apply_filters( 'react2u_use_gravatar', false, $author_id ) ) {
		return get_avatar( $author_id, $size, '', $name, array( 'class' => 'author-avatar-image' ) );
	}

	return sprintf(
		'<span class="author-avatar-image is-initials" style="--avatar-size:%1$dpx" role="img" aria-label="%2$s">%3$s</span>',
		$size,
		esc_attr( $name ),
		esc_html( react2u_initials( $name ) )
	);
}

/** Maximaal twee initialen; meer wordt op een klein rondje toch onleesbaar. */
function react2u_initials( string $name ): string {
	$parts = preg_split( '/\s+/u', trim( $name ) ) ?: array();
	$parts = array_values( array_filter( $parts ) );

	if ( ! $parts ) {
		return mb_strtoupper( mb_substr( (string) get_bloginfo( 'name' ), 0, 1 ) );
	}

	$first = mb_substr( $parts[0], 0, 1 );
	$last  = count( $parts ) > 1 ? mb_substr( (string) end( $parts ), 0, 1 ) : '';

	return mb_strtoupper( $first . $last );
}

function react2u_author_job_title( int $author_id ): string {
	return (string) get_user_meta( $author_id, 'react2u_job_title', true );
}

function react2u_author_linkedin( int $author_id ): string {
	return (string) get_user_meta( $author_id, 'react2u_linkedin', true );
}

/**
 * Alle sociale profielen van een auteur, als lijst met label en icoon.
 *
 * Bronnen: het losse LinkedIn-veld (blijft, want dat is er het vaakst) plus een
 * vrij tekstveld met één URL per regel. Het label en het icoon leiden we uit
 * het domein af, zodat een redacteur alleen de URL hoeft te plakken. Een
 * onbekend netwerk krijgt netjes een globe-icoon in plaats van niets.
 *
 * Deze ene functie voedt zowel de auteurskaart als de `sameAs` in het schema,
 * zodat de zichtbare links en de gestructureerde data niet uit elkaar lopen.
 *
 * @return array<int,array{url:string,label:string,icon:string}>
 */
function react2u_author_socials( int $author_id ): array {
	$urls = array();

	$linkedin = react2u_author_linkedin( $author_id );
	if ( '' !== $linkedin ) {
		$urls[] = $linkedin;
	}

	$overig = (string) get_user_meta( $author_id, 'react2u_socials', true );
	foreach ( preg_split( '/[\r\n]+/', $overig ) ?: array() as $regel ) {
		$regel = trim( $regel );
		if ( '' !== $regel ) {
			$urls[] = $regel;
		}
	}

	// Bekende netwerken: nette naam en een eigen icoon. De rest valt terug op
	// het domein zelf en een globe-icoon.
	$bekend = array(
		'linkedin.com'  => array( 'LinkedIn', 'linkedin' ),
		'x.com'         => array( 'X', 'x' ),
		'twitter.com'   => array( 'X', 'x' ),
		'instagram.com' => array( 'Instagram', 'instagram' ),
		'facebook.com'  => array( 'Facebook', 'facebook' ),
		'youtube.com'   => array( 'YouTube', 'youtube' ),
		'youtu.be'      => array( 'YouTube', 'youtube' ),
	);

	$socials = array();
	$gezien  = array();

	foreach ( $urls as $url ) {
		if ( ! str_starts_with( $url, 'http' ) ) {
			continue; // Alleen volledige URL's; een los gebruikersnaam-fragment linkt nergens heen.
		}

		$host = strtolower( (string) wp_parse_url( $url, PHP_URL_HOST ) );
		$host = preg_replace( '/^www\./', '', $host );

		if ( isset( $gezien[ $url ] ) ) {
			continue;
		}
		$gezien[ $url ] = true;

		$match = $bekend[ $host ] ?? array( $host ?: __( 'Website', 'react2u' ), 'globe' );

		$socials[] = array(
			'url'   => $url,
			'label' => $match[0],
			'icon'  => $match[1],
		);
	}

	return apply_filters( 'react2u_author_socials', $socials, $author_id );
}

/**
 * Samenvatting van maximaal ~155 tekens: bruikbaar als meta-description en als
 * kaarttekst. Eerst de samenvatting, dan de inhoud.
 */
function react2u_summary( ?int $post_id = null, int $length = 155 ): string {
	$post_id = $post_id ?: (int) get_the_ID();
	$excerpt = trim( (string) get_post_field( 'post_excerpt', $post_id ) );

	if ( '' === $excerpt ) {
		$content = (string) get_post_field( 'post_content', $post_id );
		$content = (string) preg_replace( '#<(script|style)[^>]*>.*?</\1>#is', '', $content );
		$excerpt = wp_strip_all_tags( strip_shortcodes( $content ) );
	}

	$excerpt = trim( (string) preg_replace( '/\s+/u', ' ', $excerpt ) );

	if ( mb_strlen( $excerpt ) <= $length ) {
		return $excerpt;
	}

	$cut   = mb_substr( $excerpt, 0, $length );
	$space = mb_strrpos( $cut, ' ' );

	return rtrim( false === $space ? $cut : mb_substr( $cut, 0, $space ), " ,;:-" ) . '…';
}

/**
 * Eén H1 per pagina afdwingen.
 *
 * Het sjabloon zet de H1 zelf; komt er in de aangeleverde inhoud nog een H1 mee,
 * dan wordt die een H2. Zo blijft de koppenstructuur kloppen, ook als een
 * redactietool zich vergist.
 */
function react2u_demote_extra_h1( string $html ): string {
	return (string) preg_replace( '#<(/?)h1(\s[^>]*)?>#i', '<$1h2$2>', $html );
}
add_filter( 'the_content', 'react2u_demote_extra_h1', 20 );
