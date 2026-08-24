# Aanleverlijst — wat React2u nog moet leveren

Alles hieronder staat nu als `[PLACEHOLDER]` in de site. Zolang een veld leeg is,
staat het op de site gemarkeerd en meldt het WordPress-dashboard hoeveel er nog
open staan. Ze blijven bewust uit de gegevens die naar Google gaan: een verzonnen
cijfer publiceren doen we niet.

## 1. Contactgegevens — ✅ klaar

Overgenomen van react2u.nl: telefoon, e-mail, adres (Stratumsedijk 29, 5611 NB
Eindhoven), KvK 95076824, btw NL866991906B01. Alleen nog na te lopen of dit nog
actueel is.

## 2. Sterrenscore — via Weergave → Aanpassen

| Veld | Waarde |
|---|---|
| Score (bijv. 4,8) | |
| Aantal beoordelingen | |
| Bron (Google / Trustpilot / eigen) | |
| Link naar de beoordelingen | |

Alle drie de eerste velden zijn nodig, anders blijft de score uit het schema.
Op de huidige site stond een sectie "Een greep uit onze tevreden klanten" zonder
zichtbare citaten — geen bron om over te nemen.

## 3. Cijferrij — vier cijfers, via Weergave → Aanpassen

| # | Waarde | Label | Kun je dit onderbouwen? |
|---|---|---|---|
| 1 | | | |
| 2 | | | |
| 3 | | | |
| 4 | | | |

Denk aan: jaren actief, aantal begeleide trajecten, gemiddelde reactietijd,
tevredenheidsscore uit het MTO.

## 4. Reviews — in `theme/react2u/inc/proof.php`

Per review: quote (2–3 zinnen, concreet, met een resultaat), naam, functie,
bedrijf. **Met toestemming van de klant om het te publiceren.**

## 5. Klantcases — in `theme/react2u/inc/proof.php`

Per case: branche, één resultaatcijfer, korte titel, twee zinnen. Geanonimiseerd
mag, zolang het cijfer klopt.

## 6. Beeldmateriaal

| Wat | Formaat | Status |
|---|---|---|
| Logo (SVG of vector) | het huidige logo is een PNG binnengehaald van de site; een scherpe vectorversie is welkom | open |
| Logo voor donkere ondergrond | het woordmerk is bijna zwart; voor gebruik op de donkere footer/CTA-secties is een lichte variant nodig | open |
| Favicon (512×512 PNG) | binnengehaald van de huidige site, mag scherper | optioneel |
| Hero-, over-ons-, contact- en dienstfoto's | ≥ 1200px breed, eigen foto's van React2u zelf | vervangt de AI-gegenereerde foto's hieronder |

Alle 27 foto's op de huidige site bleken stockbeeld of generieke icoontjes —
geen daarvan is overgenomen. Een stockfoto van een team dat niet bestaat, ziet
een bezoeker meteen. In plaats daarvan staan er nu negen gegenereerde
sfeerfoto's (Higgsfield), met mensen maar zonder naam of functie eraan
gekoppeld — dus geen verzonnen medewerker. Dit is een tussenoplossing:
zodra React2u eigen foto's van het kantoor of team aanlevert, vervangen die
`assets/images/hero-mensen.webp`, `over-ons-team.webp`, `contact-balie.webp`
en de zes `dienst-*.webp`-bestanden. Let bij het bekijken op één klein
lettertype-achtig weefpatroontje op de kleding in het contactbeeld — een
bekend AI-artefact, geen echte tekst, maar wel iets om te vervangen zodra er
een echte foto is.

## 7. Privacy reglement

De pagina `/privacy-reglement/` op de huidige site toont per vergissing de
verkeerde inhoud (een blok over Begeleiding & Coaching in plaats van een
privacyverklaring). Deze site heeft dus (nog) geen bruikbare privacytekst —
lever de echte tekst aan, dan vervangt die de placeholder.

Klachtenprocedure en algemene voorwaarden zijn wél overgenomen (verbatim, van
de huidige site) en staan al op `/klachtenprocedure/` en
`/algemene-voorwaarden/`.

## 8. Klantenportaal

De huidige site heeft een "Inloggen"-link naar een klantenportaal
(verzuimregistratiesysteem). De URL daarvan is niet meegenomen in de intake —
geef door waar dat naartoe moet linken.

## 9. Teksten om te bevestigen

- De drie USP's onder de hero zijn overgenomen uit "Gezond, menselijk,
  duidelijk" (react2u.nl/over-react2u) — akkoord, of anders?
- "Zo werkt het" (vier stappen op de homepage) is door ons samengesteld uit
  terugkerende taal op de huidige site (geen los stappenplan aangetroffen) —
  controleren of dit klopt met de echte werkwijze.

## 10. In de gaten houden: site in beweging

react2u.nl heeft naast de huidige structuur ook een tweede, nog niet
gekoppelde sectie in de sitemap staan: `/werkgever/...`, `/werknemer/...` en
`/adviseurs/...`, met wat oude pagina's al op een `x`-geprefixte slug gezet
(`/xdiensten/`, `/xbegeleidingx-xcoachingx/`). Dat wijst op een lopende
herstructurering naar een driedeling werkgever/werknemer/adviseurs, die nog
niet in het hoofdmenu hangt. Deze rebuild volgt bewust de structuur die nu
écht live en gelinkt staat; vraag de klant of die nieuwe indeling de
uiteindelijke richting is voor een volgende fase.
