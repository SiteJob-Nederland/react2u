<?php
/**
 * Plugin Name: React2u Mail
 * Description: Verstuurt de mail van deze site via SMTP in plaats van PHP mail(), en vervangt de uitnodigingsmail voor een nieuw account door een Nederlandse versie in de huisstijl.
 * Version:     0.1.0
 * Author:      SiteJob
 * License:     GPL-2.0-or-later
 * Text Domain: react2u-mail
 *
 * Waarom dit bestaat
 * ------------------
 * WordPress verstuurt standaard met PHP `mail()`. Op een gedeelde server komt
 * die mail of nergens aan of in de spammap: er is geen SPF die klopt, geen
 * DKIM-handtekening, en het afzenderadres is meestal `wordpress@<hostnaam>`.
 * Het gevolg merk je pas als iemand zegt dat hij zijn uitnodiging nooit heeft
 * gekregen — WordPress meldt namelijk niets.
 *
 * Bewust een plugin en geen themacode: mail moet blijven werken als het thema
 * wisselt, en hoort niet mee te verhuizen in de thema-ZIP die naar de klant gaat.
 *
 * Instellen
 * ---------
 * Alle instellingen komen uit constanten in `wp-config.php`, niet uit de
 * database. Drie redenen: ze staan niet in een database-export, ze zijn niet te
 * wijzigen via een gekaapt beheerdersaccount, en ze komen nooit per ongeluk in
 * git terecht. Zie LEESMIJ.md voor het blok dat je erin plakt.
 *
 * @package React2u
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const REACT2U_MAIL_FOUT = 'react2u_mail_laatste_fout';

/**
 * Instelling ophalen.
 *
 * Eerst de sitespecifieke constante (REACT2U_SMTP_HOST), dan de gedeelde
 * (SITEJOB_SMTP_HOST). Die volgorde is met opzet: het gedeelde blok kun je in
 * elke wp-config.php plakken zonder iets te hernoemen, en een site die een
 * eigen mailbox heeft overschrijft alleen wat afwijkt.
 */
function react2u_mail_instelling( string $naam, mixed $standaard = '' ): mixed {
	foreach ( array( 'REACT2U_' . $naam, 'SITEJOB_' . $naam ) as $constante ) {
		if ( defined( $constante ) ) {
			return constant( $constante );
		}
	}

	return $standaard;
}

/** Is er genoeg ingesteld om via SMTP te kunnen versturen? */
function react2u_mail_is_ingesteld(): bool {
	foreach ( array( 'SMTP_HOST', 'SMTP_USER', 'SMTP_PASS' ) as $veld ) {
		if ( '' === (string) react2u_mail_instelling( $veld ) ) {
			return false;
		}
	}

	return true;
}

/**
 * PHPMailer op SMTP zetten.
 *
 * Zonder deze haak valt WordPress terug op PHP mail(). Is er niets ingesteld,
 * dan doen we niets — dan is de terugval nog altijd beter dan een verbinding
 * die niet lukt en de pagina laat hangen.
 */
function react2u_mail_smtp( $phpmailer ): void {
	if ( ! react2u_mail_is_ingesteld() ) {
		return;
	}

	$phpmailer->isSMTP();
	$phpmailer->Host       = (string) react2u_mail_instelling( 'SMTP_HOST' );
	$phpmailer->Port       = (int) react2u_mail_instelling( 'SMTP_PORT', 587 );
	$phpmailer->SMTPAuth   = true;
	$phpmailer->Username   = (string) react2u_mail_instelling( 'SMTP_USER' );
	$phpmailer->Password   = (string) react2u_mail_instelling( 'SMTP_PASS' );
	$phpmailer->SMTPSecure = (string) react2u_mail_instelling( 'SMTP_SECURE', 'tls' );
	$phpmailer->CharSet    = 'UTF-8';
	$phpmailer->Timeout    = 15;

	/*
	 * Een tekstversie naast de HTML. Niet alleen voor lezers zonder HTML: een
	 * bericht dat alléén HTML bevat scoort meetbaar slechter bij spamfilters.
	 */
	if ( 'text/html' === $phpmailer->ContentType && '' === $phpmailer->AltBody ) {
		$phpmailer->AltBody = trim(
			html_entity_decode(
				wp_strip_all_tags( str_replace( array( '</p>', '<br>', '<br />' ), "\n", $phpmailer->Body ) ),
				ENT_QUOTES,
				'UTF-8'
			)
		);
	}
}
add_action( 'phpmailer_init', 'react2u_mail_smtp' );

/**
 * Afzender.
 *
 * Let op bij Google Workspace: Gmail herschrijft het afzenderadres naar het
 * account waarmee je inlogt, tenzij het adres daar als geverifieerde alias
 * ("Verzenden als") is toegevoegd. Standaard nemen we daarom het SMTP-account
 * als afzender — dan klopt de DKIM-handtekening en is er niets te herschrijven.
 * Wil je een eigen adres, voeg dat dan eerst als alias toe en zet
 * REACT2U_MAIL_FROM.
 */
function react2u_mail_from( string $van ): string {
	$ingesteld = (string) react2u_mail_instelling( 'MAIL_FROM' );
	if ( '' !== $ingesteld ) {
		return $ingesteld;
	}

	$gebruiker = (string) react2u_mail_instelling( 'SMTP_USER' );

	return is_email( $gebruiker ) ? $gebruiker : $van;
}
add_filter( 'wp_mail_from', 'react2u_mail_from' );

/** Afzendernaam: de naam van de site, niet "WordPress". */
function react2u_mail_from_name( string $naam ): string {
	$ingesteld = (string) react2u_mail_instelling( 'MAIL_FROM_NAME' );

	return '' !== $ingesteld ? $ingesteld : wp_specialchars_decode( (string) get_bloginfo( 'name' ), ENT_QUOTES );
}
add_filter( 'wp_mail_from_name', 'react2u_mail_from_name' );

/**
 * Onderwerp markeren buiten productie, en een antwoordadres meesturen.
 *
 * Het staging-voorvoegsel is geen sierlijkheid. Een demo-site draagt echte
 * teksten en echte adressen; zonder markering weet niemand of een mail van de
 * demo of van de echte site komt, en dan wordt er op de verkeerde gereageerd.
 *
 * @param array<string,mixed> $args
 * @return array<string,mixed>
 */
function react2u_mail_args( array $args ): array {
	if ( 'production' !== wp_get_environment_type() ) {
		$omgeving        = strtoupper( wp_get_environment_type() );
		$args['subject'] = sprintf( '[%s] %s', $omgeving, (string) ( $args['subject'] ?? '' ) );
	}

	$antwoord = (string) react2u_mail_instelling( 'MAIL_REPLY_TO' );
	if ( is_email( $antwoord ) ) {
		$headers = $args['headers'] ?? array();
		$headers = is_array( $headers ) ? $headers : array( $headers );

		$heeft_al = false;
		foreach ( $headers as $header ) {
			if ( is_string( $header ) && stripos( $header, 'reply-to:' ) === 0 ) {
				$heeft_al = true;
				break;
			}
		}

		if ( ! $heeft_al ) {
			$headers[]       = 'Reply-To: ' . $antwoord;
			$args['headers'] = $headers;
		}
	}

	return $args;
}
add_filter( 'wp_mail', 'react2u_mail_args' );

/**
 * Mislukte verzending onthouden.
 *
 * WordPress laat een mislukte wp_mail() stilletjes vallen: de gebruiker krijgt
 * "gebruiker aangemaakt" te zien en verder niets. Hier bewaren we de fout, zodat
 * hij op het dashboard en op de mailpagina zichtbaar wordt.
 */
function react2u_mail_fout_onthouden( WP_Error $fout ): void {
	update_option(
		REACT2U_MAIL_FOUT,
		array(
			'tijd'      => time(),
			'melding'   => $fout->get_error_message(),
			'ontvanger' => implode( ', ', (array) ( $fout->get_error_data()['to'] ?? array() ) ),
		),
		false
	);
}
add_action( 'wp_mail_failed', 'react2u_mail_fout_onthouden' );

/* ==========================================================================
   De uitnodiging
   ========================================================================== */

/**
 * Kleuren voor de mail.
 *
 * Het thema mag ze overschrijven — mail hoort eruit te zien als de site waar
 * hij vandaan komt, en die kan wisselen. Daarom een filter en geen constante.
 *
 * @return array<string,string>
 */
function react2u_mail_kleuren(): array {
	return (array) apply_filters(
		'react2u_mail_kleuren',
		array(
			'accent' => '#1A1815',
			'inkt'   => '#1A1815',
			'zacht'  => '#5A5348',
			'papier' => '#F6F4F0',
			'lijn'   => '#E2DED6',
			'op'     => '#FFFFFF',
		)
	);
}

/**
 * Geldigheidsduur van een herstel-/uitnodigingslink.
 *
 * WordPress houdt 24 uur aan. Dat is voor een wachtwoordherstel prima, maar
 * voor een uitnodiging vaak te kort: wie hem 's avonds krijgt en er de volgende
 * werkdag mee aan de slag gaat, is te laat. Zet
 * REACT2U_MAIL_LINK_DAGEN in wp-config.php om dat op te rekken.
 *
 * Weeg dat wel af: deze instelling geldt óók voor gewone wachtwoordherstel-
 * links. Langer geldig betekent langer bruikbaar voor wie een mailbox kaapt.
 */
function react2u_mail_link_geldigheid( int $seconden ): int {
	$dagen = (int) react2u_mail_instelling( 'MAIL_LINK_DAGEN', 0 );

	return $dagen > 0 ? $dagen * DAY_IN_SECONDS : $seconden;
}
add_filter( 'password_reset_expiration', 'react2u_mail_link_geldigheid' );

/**
 * De uitnodigingsmail voor een nieuw account.
 *
 * We vervangen hier alleen de mail náár de nieuwe gebruiker. De melding aan de
 * beheerder blijft zoals WordPress hem stuurt.
 *
 * Let op de sleutel: we vragen een verse aan in plaats van de link uit het
 * standaardbericht te vissen. Dat laatste is een reguliere expressie op een
 * tekst die per WordPress-versie kan wijzigen, en dan valt de knop stil zonder
 * dat iemand het merkt.
 *
 * @param array<string,mixed> $mail
 * @return array<string,mixed>
 */
function react2u_mail_uitnodiging( array $mail, WP_User $gebruiker, string $sitenaam ): array {
	$sleutel = get_password_reset_key( $gebruiker );

	// Lukt dat niet, dan laten we de standaardmail van WordPress staan. Een
	// mooie mail zonder werkende knop is slechter dan een lelijke die werkt.
	if ( is_wp_error( $sleutel ) ) {
		return $mail;
	}

	$url = network_site_url(
		sprintf( 'wp-login.php?action=rp&key=%s&login=%s', $sleutel, rawurlencode( $gebruiker->user_login ) ),
		'login'
	);

	$naam   = $gebruiker->first_name ? $gebruiker->first_name : $gebruiker->display_name;
	$dagen  = (int) react2u_mail_instelling( 'MAIL_LINK_DAGEN', 0 );
	$geldig = $dagen > 0
		? sprintf( _n( '%d dag', '%d dagen', $dagen, 'react2u-mail' ), $dagen )
		: __( '24 uur', 'react2u-mail' );

	$door        = wp_get_current_user();
	$uitgenodigd = ( $door instanceof WP_User && $door->exists() && $door->ID !== $gebruiker->ID )
		? sprintf(
			/* translators: %s: naam van degene die het account aanmaakte */
			__( '%s heeft een account voor je aangemaakt.', 'react2u-mail' ),
			$door->display_name
		)
		: __( 'Er is een account voor je aangemaakt.', 'react2u-mail' );

	$mail['subject'] = sprintf(
		/* translators: %s: naam van de site */
		__( 'Je account voor %s staat klaar', 'react2u-mail' ),
		$sitenaam
	);

	$mail['message'] = react2u_mail_uitnodiging_html(
		array(
			'sitenaam'    => $sitenaam,
			'naam'        => $naam,
			'inlognaam'   => $gebruiker->user_login,
			'uitgenodigd' => $uitgenodigd,
			'url'         => $url,
			'geldig'      => $geldig,
		)
	);

	$headers   = (array) ( $mail['headers'] ?? array() );
	$headers[] = 'Content-Type: text/html; charset=UTF-8';

	$mail['headers'] = $headers;

	return $mail;
}
add_filter( 'wp_new_user_notification_email', 'react2u_mail_uitnodiging', 10, 3 );

/**
 * De opmaak van de uitnodiging.
 *
 * Tabellen en stijl in het attribuut, want e-mailprogramma's zijn geen
 * browsers: Outlook kent geen flexbox en gooit een <style>-blok in de kop vaak
 * weg. Alles staat daarom inline, en de knop is ook zonder afbeeldingen leesbaar.
 *
 * @param array<string,string> $v
 */
function react2u_mail_uitnodiging_html( array $v ): string {
	$k = react2u_mail_kleuren();

	$hoi = sprintf(
		/* translators: %s: voornaam */
		__( 'Hoi %s,', 'react2u-mail' ),
		esc_html( $v['naam'] )
	);

	$intro = sprintf(
		/* translators: 1: wie het account aanmaakte, 2: naam van de site */
		__( '%1$s Je kunt vanaf nu inloggen op %2$s. Kies eerst een wachtwoord.', 'react2u-mail' ),
		esc_html( $v['uitgenodigd'] ),
		'<strong>' . esc_html( $v['sitenaam'] ) . '</strong>'
	);

	$vervalt = sprintf(
		/* translators: %s: geldigheidsduur, bijvoorbeeld "24 uur" */
		__( 'Deze link is %s geldig en werkt één keer.', 'react2u-mail' ),
		esc_html( $v['geldig'] )
	);

	$kwijt = sprintf(
		/* translators: %s: URL van de wachtwoord-vergeten-pagina */
		__( 'Verlopen? Vraag een nieuwe aan via <a href="%s" style="color:inherit;">wachtwoord vergeten</a>.', 'react2u-mail' ),
		esc_url( wp_lostpassword_url() )
	);

	return '<!doctype html><html lang="nl"><head><meta charset="utf-8">'
		. '<meta name="viewport" content="width=device-width,initial-scale=1">'
		. '<title>' . esc_html( $v['sitenaam'] ) . '</title></head>'
		. '<body style="margin:0;padding:0;background:' . esc_attr( $k['papier'] ) . ';">'
		. '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:' . esc_attr( $k['papier'] ) . ';">'
		. '<tr><td align="center" style="padding:32px 16px;">'

		. '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:520px;background:#FFFFFF;border:1px solid ' . esc_attr( $k['lijn'] ) . ';">'

		// Kop
		. '<tr><td style="padding:28px 32px 0;font:600 12px/1.4 Arial,Helvetica,sans-serif;letter-spacing:.14em;text-transform:uppercase;color:' . esc_attr( $k['accent'] ) . ';">'
		. esc_html( $v['sitenaam'] )
		. '</td></tr>'

		. '<tr><td style="padding:18px 32px 0;font:700 24px/1.25 Arial,Helvetica,sans-serif;color:' . esc_attr( $k['inkt'] ) . ';">'
		. esc_html__( 'Je account staat klaar', 'react2u-mail' )
		. '</td></tr>'

		. '<tr><td style="padding:20px 32px 0;font:400 15px/1.6 Arial,Helvetica,sans-serif;color:' . esc_attr( $k['inkt'] ) . ';">'
		. $hoi . '<br><br>' . $intro
		. '</td></tr>'

		// Knop
		. '<tr><td style="padding:26px 32px 0;">'
		. '<table role="presentation" cellpadding="0" cellspacing="0" border="0"><tr>'
		. '<td style="background:' . esc_attr( $k['accent'] ) . ';">'
		. '<a href="' . esc_url( $v['url'] ) . '" style="display:inline-block;padding:13px 26px;font:700 15px/1 Arial,Helvetica,sans-serif;color:' . esc_attr( $k['op'] ) . ';text-decoration:none;">'
		. esc_html__( 'Kies je wachtwoord', 'react2u-mail' )
		. '</a></td></tr></table>'
		. '</td></tr>'

		. '<tr><td style="padding:16px 32px 0;font:400 13px/1.6 Arial,Helvetica,sans-serif;color:' . esc_attr( $k['zacht'] ) . ';">'
		. $vervalt . ' ' . $kwijt
		. '</td></tr>'

		// Gegevens
		. '<tr><td style="padding:24px 32px 0;">'
		. '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-top:1px solid ' . esc_attr( $k['lijn'] ) . ';">'
		. '<tr><td style="padding-top:16px;font:400 13px/1.7 Arial,Helvetica,sans-serif;color:' . esc_attr( $k['zacht'] ) . ';">'
		. esc_html__( 'Inlognaam', 'react2u-mail' ) . ': <span style="color:' . esc_attr( $k['inkt'] ) . ';">' . esc_html( $v['inlognaam'] ) . '</span><br>'
		. esc_html__( 'Inloggen', 'react2u-mail' ) . ': <a href="' . esc_url( wp_login_url() ) . '" style="color:' . esc_attr( $k['accent'] ) . ';">' . esc_html( wp_login_url() ) . '</a>'
		. '</td></tr></table>'
		. '</td></tr>'

		// Losse link, voor als de knop niet klikbaar is
		. '<tr><td style="padding:20px 32px 30px;font:400 12px/1.6 Arial,Helvetica,sans-serif;color:' . esc_attr( $k['zacht'] ) . ';word-break:break-all;">'
		. esc_html__( 'Werkt de knop niet? Plak deze link in je browser:', 'react2u-mail' ) . '<br>'
		. esc_html( $v['url'] )
		. '</td></tr>'

		. '</table>'

		. '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:520px;">'
		. '<tr><td style="padding:16px 4px 0;font:400 12px/1.6 Arial,Helvetica,sans-serif;color:' . esc_attr( $k['zacht'] ) . ';">'
		. esc_html__( 'Je krijgt deze mail omdat er een account voor dit e-mailadres is aangemaakt. Was jij dat niet, dan kun je hem negeren — zonder de link hierboven gebeurt er niets.', 'react2u-mail' )
		. '</td></tr></table>'

		. '</td></tr></table></body></html>';
}

/* ==========================================================================
   Beheer: status en een testverzending
   ========================================================================== */

/**
 * Waarschuwing als er niets is ingesteld of als de laatste verzending mislukte.
 *
 * Dit is het hele punt van deze plugin. WordPress meldt een mislukte mail
 * nergens: je maakt een gebruiker aan, ziet "gebruiker toegevoegd", en pas
 * dagen later blijkt dat er niets is aangekomen.
 */
function react2u_mail_beheer_melding(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( ! react2u_mail_is_ingesteld() ) {
		printf(
			'<div class="notice notice-warning"><p><strong>%s</strong> %s</p></div>',
			esc_html__( 'Mail is niet ingesteld.', 'react2u-mail' ),
			sprintf(
				/* translators: %s: link naar de mailpagina */
				wp_kses_post( __( 'Deze site valt terug op PHP mail(); uitnodigingen komen dan vaak niet aan. %s', 'react2u-mail' ) ),
				'<a href="' . esc_url( admin_url( 'tools.php?page=react2u_mail' ) ) . '">' . esc_html__( 'Bekijk de mailinstellingen', 'react2u-mail' ) . '</a>'
			)
		);

		return;
	}

	$fout = (array) get_option( REACT2U_MAIL_FOUT, array() );
	if ( $fout && ( time() - (int) ( $fout['tijd'] ?? 0 ) ) < WEEK_IN_SECONDS ) {
		printf(
			'<div class="notice notice-error"><p><strong>%s</strong> %s</p></div>',
			esc_html__( 'De laatste mail is niet verstuurd.', 'react2u-mail' ),
			esc_html( (string) ( $fout['melding'] ?? '' ) )
		);
	}
}
add_action( 'admin_notices', 'react2u_mail_beheer_melding' );

/** Pagina onder Extra. */
function react2u_mail_beheer_menu(): void {
	add_management_page(
		__( 'Mail', 'react2u-mail' ),
		__( 'Mail', 'react2u-mail' ),
		'manage_options',
		'react2u_mail',
		'react2u_mail_beheer_pagina'
	);
}
add_action( 'admin_menu', 'react2u_mail_beheer_menu' );

function react2u_mail_beheer_pagina(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$bericht = '';

	if ( isset( $_POST['react2u_mail_test'] ) && check_admin_referer( 'react2u_mail_test' ) ) {
		delete_option( REACT2U_MAIL_FOUT );

		$naar = sanitize_email( wp_unslash( (string) ( $_POST['naar'] ?? '' ) ) );
		$naar = is_email( $naar ) ? $naar : (string) wp_get_current_user()->user_email;

		$ok = wp_mail(
			$naar,
			__( 'Testbericht', 'react2u-mail' ),
			sprintf(
				/* translators: 1: naam van de site, 2: datum en tijd */
				__( "Dit is een testbericht van %1\$s.\n\nVerstuurd op %2\$s. Komt hij aan, dan werkt de verzending.", 'react2u-mail' ),
				wp_specialchars_decode( (string) get_bloginfo( 'name' ), ENT_QUOTES ),
				wp_date( 'j F Y H:i' )
			)
		);

		$fout    = (array) get_option( REACT2U_MAIL_FOUT, array() );
		$bericht = $ok && ! $fout
			? sprintf( '<div class="notice notice-success"><p>%s</p></div>', esc_html( sprintf( __( 'Verstuurd naar %s.', 'react2u-mail' ), $naar ) ) )
			: sprintf( '<div class="notice notice-error"><p>%s</p></div>', esc_html( (string) ( $fout['melding'] ?? __( 'Verzenden mislukt.', 'react2u-mail' ) ) ) );
	}

	$rijen = array(
		__( 'Verzendmethode', 'react2u-mail' ) => react2u_mail_is_ingesteld()
			? __( 'SMTP', 'react2u-mail' )
			: __( 'PHP mail() — niet ingesteld', 'react2u-mail' ),
		__( 'Server', 'react2u-mail' )         => (string) react2u_mail_instelling( 'SMTP_HOST', '—' ),
		__( 'Poort', 'react2u-mail' )          => (string) react2u_mail_instelling( 'SMTP_PORT', 587 ),
		__( 'Account', 'react2u-mail' )        => (string) react2u_mail_instelling( 'SMTP_USER', '—' ),
		__( 'Afzender', 'react2u-mail' )       => react2u_mail_from_name( '' ) . ' <' . react2u_mail_from( '' ) . '>',
		__( 'Antwoordadres', 'react2u-mail' )  => (string) react2u_mail_instelling( 'MAIL_REPLY_TO', '—' ),
		__( 'Omgeving', 'react2u-mail' )       => wp_get_environment_type(),
	);

	echo '<div class="wrap"><h1>' . esc_html__( 'Mail', 'react2u-mail' ) . '</h1>';
	echo wp_kses_post( $bericht );

	echo '<p>' . esc_html__( 'De instellingen komen uit wp-config.php en zijn hier niet te wijzigen — dat is met opzet: ze horen niet in de database en niet in handen van een gekaapt beheerdersaccount.', 'react2u-mail' ) . '</p>';

	echo '<table class="widefat striped" style="max-width:640px"><tbody>';
	foreach ( $rijen as $label => $waarde ) {
		printf( '<tr><th scope="row" style="width:180px">%s</th><td>%s</td></tr>', esc_html( $label ), esc_html( $waarde ) );
	}
	echo '</tbody></table>';

	echo '<h2>' . esc_html__( 'Testbericht sturen', 'react2u-mail' ) . '</h2>';
	echo '<form method="post">';
	wp_nonce_field( 'react2u_mail_test' );
	printf(
		'<p><input type="email" name="naar" class="regular-text" value="%s"> <button type="submit" name="react2u_mail_test" value="1" class="button button-primary">%s</button></p>',
		esc_attr( (string) wp_get_current_user()->user_email ),
		esc_html__( 'Verstuur', 'react2u-mail' )
	);
	echo '</form></div>';
}

/* ==========================================================================
   WP-CLI: iemand uitnodigen
   ========================================================================== */

if ( defined( 'WP_CLI' ) && WP_CLI ) {

	/**
	 * Nodig iemand uit voor deze WordPress-omgeving.
	 *
	 * Er wordt nooit een wachtwoord gemaild. Het account krijgt een lang
	 * willekeurig wachtwoord dat niemand te zien krijgt — ook jij niet — en de
	 * ontvanger stelt via een eenmalige link zelf een wachtwoord in. Dat is het
	 * verschil tussen "inloggegevens mailen" en het veilig doen: een wachtwoord
	 * in een mailbox blijft daar jaren staan, een gebruikte link is dood.
	 *
	 * ## OPTIES
	 *
	 * <email>
	 * : Het e-mailadres van degene die je uitnodigt.
	 *
	 * [--rol=<rol>]
	 * : administrator, editor, author, contributor of subscriber.
	 * ---
	 * default: administrator
	 * ---
	 *
	 * [--naam=<naam>]
	 * : Volledige naam, bijvoorbeeld "Jan de Vries".
	 *
	 * [--gebruikersnaam=<naam>]
	 * : Inlognaam. Standaard het deel vóór de @ van het e-mailadres.
	 *
	 * [--opnieuw]
	 * : Bestaat het account al, stuur dan een nieuwe uitnodiging in plaats van
	 *   af te breken.
	 *
	 * ## VOORBEELDEN
	 *
	 *     wp sitejob-mail uitnodigen naam@inspace.nl --rol=administrator --naam="Jan de Vries"
	 *     wp sitejob-mail uitnodigen naam@inspace.nl --opnieuw
	 *
	 * @param array<int,string>    $args
	 * @param array<string,string> $opties
	 */
	function react2u_mail_cli_uitnodigen( array $args, array $opties ): void {
		$email = sanitize_email( $args[0] ?? '' );

		if ( ! is_email( $email ) ) {
			WP_CLI::error( 'Dat is geen geldig e-mailadres.' );
		}

		$rol = (string) ( $opties['rol'] ?? 'administrator' );
		if ( ! get_role( $rol ) ) {
			WP_CLI::error( sprintf( 'De rol "%s" bestaat niet op deze site.', $rol ) );
		}

		if ( ! react2u_mail_is_ingesteld() ) {
			WP_CLI::warning( 'SMTP is niet ingesteld; deze site valt terug op PHP mail(). De uitnodiging komt dan waarschijnlijk niet aan.' );
		}

		$gebruiker = get_user_by( 'email', $email );

		if ( $gebruiker instanceof WP_User ) {
			if ( ! isset( $opties['opnieuw'] ) ) {
				WP_CLI::error(
					sprintf(
						'Er bestaat al een account met %s (%s). Gebruik --opnieuw om een nieuwe uitnodiging te sturen.',
						$email,
						$gebruiker->user_login
					)
				);
			}

			WP_CLI::log( sprintf( 'Bestaand account %s — nieuwe uitnodiging.', $gebruiker->user_login ) );
		} else {
			$inlognaam = sanitize_user( (string) ( $opties['gebruikersnaam'] ?? strstr( $email, '@', true ) ), true );
			$basis     = $inlognaam;
			$n         = 1;
			while ( username_exists( $inlognaam ) ) {
				$inlognaam = $basis . ++$n;
			}

			$naam       = trim( (string) ( $opties['naam'] ?? '' ) );
			$voornaam   = '' !== $naam ? strstr( $naam . ' ', ' ', true ) : '';
			$achternaam = '' !== $naam ? trim( (string) strstr( $naam, ' ' ) ) : '';

			/*
			 * Een lang willekeurig wachtwoord dat nergens terechtkomt. Het wordt
			 * niet getoond, niet gelogd en niet gemaild: de ontvanger zet zelf
			 * een wachtwoord via de link. Zonder deze regel zou WordPress er
			 * zelf een maken en die alsnog nergens tonen — maar dan weet je niet
			 * zeker hoe sterk hij was.
			 */
			$id = wp_insert_user(
				array(
					'user_login'   => $inlognaam,
					'user_email'   => $email,
					'user_pass'    => wp_generate_password( 32, true, true ),
					'first_name'   => $voornaam,
					'last_name'    => $achternaam,
					'display_name' => '' !== $naam ? $naam : $inlognaam,
					'role'         => $rol,
				)
			);

			if ( is_wp_error( $id ) ) {
				WP_CLI::error( $id->get_error_message() );
			}

			$gebruiker = get_user_by( 'id', $id );
			WP_CLI::log( sprintf( 'Account %s aangemaakt met rol %s.', $inlognaam, $rol ) );
		}

		delete_option( REACT2U_MAIL_FOUT );

		// 'user': alleen naar de uitgenodigde. De beheerder krijgt geen kopie —
		// die weet dit al, hij typte het commando.
		wp_new_user_notification( $gebruiker->ID, null, 'user' );

		$fout = (array) get_option( REACT2U_MAIL_FOUT, array() );

		if ( $fout ) {
			WP_CLI::error( sprintf( 'Het account staat er, maar de mail is niet verstuurd: %s', $fout['melding'] ?? '' ) );
		}

		WP_CLI::success( sprintf( 'Uitnodiging verstuurd naar %s.', $email ) );
		WP_CLI::log( '  Er is geen wachtwoord meegestuurd; de ontvanger kiest er zelf een via de link.' );
	}

	WP_CLI::add_command( 'sitejob-mail uitnodigen', 'react2u_mail_cli_uitnodigen' );
}
