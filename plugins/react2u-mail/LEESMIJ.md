# React2u Mail

Zorgt dat mail van deze site aankomt, en vervangt de uitnodigingsmail voor een
nieuw account door een Nederlandse versie in de huisstijl.

## Waarom

WordPress verstuurt standaard met PHP `mail()`. Zonder kloppende SPF, zonder
DKIM en met een afzender als `wordpress@<hostnaam>` komt die mail meestal in de
spammap of nergens. Erger: WordPress meldt dat niet. Je maakt een gebruiker aan,
ziet "gebruiker toegevoegd", en hoort pas dagen later dat er niets aankwam.

## Instellen

De instellingen komen uit constanten in `wp-config.php`, niet uit de database.
Zo staan ze niet in een database-export, zijn ze niet te wijzigen via een gekaapt
beheerdersaccount, en komen ze niet mee in de thema-ZIP.

Zet ze niet met de hand — draai vanuit de werkmap:

```bash
./scripts/mail-instellen.sh --account          # eenmalig: verzendaccount invullen
./scripts/mail-instellen.sh <site-slug> --beide
```

Dat leest `~/.sitejob/mail.env` en schrijft het blok in de lokale én de
staging-`wp-config.php`. Wat er komt te staan:

```php
define( 'SITEJOB_SMTP_HOST',   'smtp.gmail.com' );
define( 'SITEJOB_SMTP_PORT',   587 );
define( 'SITEJOB_SMTP_SECURE', 'tls' );
define( 'SITEJOB_SMTP_USER',   'noreply@sitejob.nl' );
define( 'SITEJOB_SMTP_PASS',   '<app-wachtwoord>' );
define( 'REACT2U_MAIL_FROM_NAME', 'React2u' );
```

Elke constante bestaat in twee smaken: `REACT2U_SMTP_HOST` wint van
`SITEJOB_SMTP_HOST`. Zo kun je het gedeelde blok overal plakken en per site
alleen overschrijven wat afwijkt.

| Constante | Betekenis |
|---|---|
| `SMTP_HOST` `SMTP_PORT` `SMTP_SECURE` | server, poort (587 voor STARTTLS), `tls` of `ssl` |
| `SMTP_USER` `SMTP_PASS` | het verzendaccount. Bij Google: een **app-wachtwoord**, niet het gewone |
| `MAIL_FROM` | afzenderadres. Leeg laten is meestal beter — zie hieronder |
| `MAIL_FROM_NAME` | afzendernaam. Standaard de naam van de site |
| `MAIL_REPLY_TO` | waar antwoorden heen gaan |
| `MAIL_LINK_DAGEN` | geldigheid van een uitnodigingslink. `0` = WordPress aanhouden (24 uur) |

### Google Workspace

Tweestapsverificatie moet aanstaan; maak dan een app-wachtwoord aan op
<https://myaccount.google.com/apppasswords>.

Gmail **herschrijft het afzenderadres** naar het account waarmee je inlogt,
tenzij dat adres daar als geverifieerde alias staat ("Verzenden als"). Laat
`MAIL_FROM` daarom leeg: dan gebruikt de plugin het SMTP-account zelf, klopt de
DKIM-handtekening en valt er niets te herschrijven. De naam van de site staat
er wél voor, dus de ontvanger ziet `React2u <noreply@sitejob.nl>`.

Wil je echt een eigen afzenderadres, voeg het dan eerst als alias toe in Gmail
en zet daarna `MAIL_FROM`.

## Iemand uitnodigen

```bash
./scripts/nodig-uit.sh <site-slug> naam@voorbeeld.nl --staging --naam="Jan de Vries"
```

of rechtstreeks op de server:

```bash
wp sitejob-mail uitnodigen naam@voorbeeld.nl --rol=administrator --opnieuw
```

**Er wordt nooit een wachtwoord gemaild.** Het account krijgt een willekeurig
wachtwoord van 32 tekens dat niemand te zien krijgt, en de ontvanger stelt via
een eenmalige link zelf een wachtwoord in. Een wachtwoord in een mailbox blijft
daar jaren staan; een gebruikte link is dood.

## Controleren

**Extra → Mail** in wp-admin toont de actieve instellingen en heeft een knop om
een testbericht te sturen. Staat er iets niet goed, dan verschijnt er een
waarschuwing bovenaan het beheer — ook als een verzending mislukt is.

## Buiten productie

Draait de site niet op productie (`WP_ENVIRONMENT_TYPE`), dan komt het onderwerp
met `[STAGING]` ervoor te staan. Een demo-site draagt echte teksten en echte
adressen; zonder die markering weet niemand of een mail van de demo of van de
echte site komt.
