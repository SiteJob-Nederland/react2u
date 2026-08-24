# React2u — thema

Maatwerk WordPress-thema. Geen paginabouwer, geen SEO-plugin nodig, zelf-gehoste
fonts, één vaste huisstijl via tokens.

## Installeren

1. **Weergave → Thema's → Nieuw thema → Thema uploaden**, kies de ZIP.
2. **Instellingen → Permalinks** één keer opslaan (anders geeft `/kennisbank/` een 404).
3. **Instellingen → Lezen**: homepage en berichtenpagina toewijzen.
4. **Weergave → Aanpassen → "React2u — cijfers en contact"**: telefoon, e-mail,
   adres, KvK, sterrenscore en cijfers invullen.
5. **Weergave → Menu's**: hoofdnavigatie, footer en juridisch toewijzen. Zolang
   dat niet gebeurd is, toont het thema een terugvalmenu.

## Opbouw

| Map / bestand | Waarvoor |
|---|---|
| `functions.php` | Laadt alleen. Alle logica staat in `inc/`. |
| `inc/setup.php` | Theme supports, menu's, beeldformaten, REST-afscherming. |
| `inc/assets.php` | Stylesheet, script, zelf-gehoste fonts, favicon, themakleur. |
| `inc/post-types.php` | Kennisbank-posttype en de auteursvelden. |
| `inc/proof.php` | **Alle cijfers, quotes, contactgegevens en CTA-bestemmingen.** |
| `inc/content.php` | Inhoudsopgave, FAQ, CTA-terugval, leestijd, samenvatting. |
| `inc/blocks.php` | Blokkenbibliotheek: één functie per herbruikbaar onderdeel. |
| `inc/breadcrumbs.php` | Kruimelpad, minimaal drie niveaus. |
| `inc/seo.php` | Meta-description, canonical, OG, JSON-LD-graaf. |
| `inc/sitemap.php` | `/sitemap.xml` → `/wp-sitemap.xml`, lastmod, robots.txt. |
| `inc/customizer.php` | De velden die de klant zonder code invult. |
| `inc/patterns.php` | Categorie voor de blokpatronen in `patterns/`. |
| `template-parts/blocks/` | Markup van de blokken. |
| `template-parts/content/` | Artikel-, kaart- en overzichtssjabloon. |

## Waar dingen vandaan komen

**Cijfers, reviews, cases, contact** — altijd uit `inc/proof.php`, nooit in een
sjabloon. Wat nog niet geleverd is, blijft `[PLACEHOLDER]`: dat wordt op de site
zichtbaar gemarkeerd en gemeld op het dashboard, en het blijft uit de
gestructureerde data. Verzin nooit een cijfer om het gat te vullen.

**Inhoudsopgave, FAQ en CTA's** — worden uit de HTML van het artikel afgeleid
(`inc/content.php`), niet uit maatwerkvelden. Een externe redactietool die
alleen titel en tekst aanlevert, krijgt zo alsnog een compleet artikel. De FAQ
wordt herkend als expliciete `<section class="faq-section">` én als een gewone
H2 "Veelgestelde vragen" met H3-vragen eronder.

**Eén H1 per pagina** — het sjabloon zet hem. Komt er in de inhoud nog een H1
mee, dan wordt die automatisch een H2.

## Aanpassen per klant

1. `style.css` blok 1: de merkkleuren. De rest van het bestand werkt via tokens.
2. `assets/fonts/`: de woff2-bestanden erbij, `fonts.css` vullen, `--f-display`
   en `--f-body` in `style.css` zetten. Zie `assets/fonts/LEESMIJ.md`.
3. `assets/images/`: `logo.png`, `mark.png`, `favicon.png`. Zonder logo toont het
   thema de sitenaam als woordmerk.
4. `inc/proof.php`: alles invullen wat geverifieerd is.
5. `inc/setup.php`: de terugvalmenu's naar de echte paden.
6. `front-page.php` en `style.css` blok 16: de vormgeving. Dit is het deel dat
   per opdracht opnieuw wordt ontworpen.

## Opnieuw inpakken

```bash
../../scripts/bouw-zip.sh
```

## Eisen

PHP 8.1 of hoger, WordPress 6.6 of hoger.
