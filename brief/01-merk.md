# React2u — merkidentiteit

> Afgeleid van de bestaande site (react2u.nl) via de intake op 2026-08-23.
> Niets hier is verzonnen: kleuren zijn gepipetteerd uit de screenshots,
> teksten komen woordelijk van de huidige site.

## Logo

- Bestanden: `assets/huidige-site/merk/` (bron) en
  `theme/react2u/assets/images/logo.png` (in gebruik)
- Formaat: PNG, binnengehaald op de resolutie van de huidige site. Vraag de
  klant om een vector (AI/SVG) voor scherp gebruik op groot formaat —
  [PLACEHOLDER] tot die er is.
- Variant voor donkere ondergrond: het woordmerk zelf is bijna zwart
  ("React2u" in inkt, geen wit) — [PLACEHOLDER], vraag een lichte variant na
  voor gebruik in de donkere footer/hero. Tot die er is: logo op een lichte
  kaart plaatsen, nooit direct op de donkere ondergrond.
- Beeldmerk los beschikbaar (`mark.png`): nee — het beeldmerk is de stippenwolk
  rond het woordmerk, niet los aangeleverd. Wel is er een tweede origineel
  merkasset: het "REACT"-wiel (Results/Expertise/Attention/Coaching/Together),
  binnengehaald als `theme/react2u/assets/images/react-wiel.png`. Dit is geen
  stockbeeld maar een eigen werkwijzemodel van de klant — prominent te
  gebruiken.
- Wat er níet mee mag gebeuren: uitrekken, de stippenkleuren wijzigen, op een
  drukke achtergrond zetten.

## Kleuren

Gepipetteerd uit de homepage-screenshot (`assets/huidige-site/schermafdrukken/`).

| Rol | Hex | Waar |
|---|---|---|
| Primair | `#312E82` | knoppen, links, accenten — het diepindigo uit het woordmerk |
| Diep | `#22205A` | hover, donkere vlakken |
| Licht | `#8985D6` | accent op donkere ondergrond (AA-contrast getoetst) |
| Donkerste | `#0B0B19` | footer, donkere secties |

### Dienstenkleuren — het eigenzinnige detail

De huidige site kent élke dienst een eigen kleur toe uit de stippenwolk van het
logo. Dat maakt de zes diensten in één oogopslag herkenbaar en is het detail
dat deze site onderscheidt. Overgenomen, verdiept tot AA-tekstcontrast
(de originelen op de huidige site halen dat niet altijd op wit):

| Dienst | Origineel | Gebruikt (AA-veilig) |
|---|---|---|
| Verzuimbegeleiding WVP | `#CA152A` (rood) | `#CA152A` |
| Verzuimbegeleiding ERD/ZW | `#F19000` (oranje) | `#AB6600` |
| Preventie & Vitaliteit | `#39A5DD` (lichtblauw) | `#1E7EB0` |
| Begeleiding & Coaching | `#E51673` (roze) | `#D81570` |
| Trainingen & Workshops | `#00AA98` (teal) | `#008577` |
| Risicomanagement | — (gebruikte al het primair-indigo) | `#312E82` |

Donker-thema-varianten staan al in `style.css` blok 1 (`--dienst-*` binnen de
donker-media-query): rood en roze lichten op, de rest blijft vivid genoeg.

Neutralen: de kit-neutralen (een zweem indigo) zijn ongewijzigd overgenomen —
ze passen al bij deze merkkleur.

### Donker thema

`--brand-light` (`#8985D6`) is een opgelichte tint van het primair-indigo,
getoetst op 5,2–5,9:1 contrast tegen zowel `--night-deep` als `--surface` in
donker. Risicomanagement (dat het primair-indigo hergebruikt) valt in donker
terug op dezelfde `--brand-light`, anders verdwijnt de kaart tegen de
achtergrond.

## Typografie

| Rol | Familie | Gewichten | Licentie |
|---|---|---|---|
| Display (koppen) | Figtree (variabel) | 400–800 | SIL Open Font License — vrij voor webgebruik |
| Tekst | DM Sans (variabel) | 400–600 | SIL Open Font License — vrij voor webgebruik |
| Mono (optioneel) | systeemfont | — | — |

Beide zijn de families die de huidige site al gebruikt (zichtbaar in de
`@font-face`-links van react2u.nl: DM Sans + Figtree via Google Fonts). Hier nu
zelf gehost als variabele woff2 (`assets/fonts/`), alleen de latin-subset —
Nederlandse diakrieten (é, ë, ï, ö) vallen daarbinnen.

## Tone of voice

- Taal: Nederlands, je-vorm
- Wel: persoonlijk, mensgericht, concreet ("wat neemt React2u uit handen?"),
  regie en structuur benadrukken naast warmte
- Niet: kille verzekeringstaal, jargon zonder uitleg, overdreven marketingtaal
- Merkterm-spelling die vastligt:
  - "React2u" (nooit "React 2 U" of "react2u" onderaan een zin met kleine r)
  - "Wet verbetering poortwachter (WVP)"
  - "Eigenrisicodrager Ziektewet (ERD/ZW)" — de site gebruikt zowel
    "ERD/ZW" als "ERD/ZVW"; we houden "ERD/ZW" aan (consistent met menu en
    URL's), behalve in letterlijk overgenomen citaten
  - "arbodienst" / "arbodienstverlener" (aan elkaar)

## Wat we NIET overnemen van de huidige site

- De stockfoto's van "collega's in gesprek" bij hero en dienstenpagina's
  (bestandsnamen verraden zichzelf: `happy-confident-agent-customer-meeting…`,
  `serious-colleagues-discussing-documents-meeting…`). Geen van de 27
  binnengehaalde foto's is van React2u zelf — het zijn allemaal stockbeelden of
  generieke icoon-PNG's. Zie `docs/harde-eisen.md`: geen stockfoto's van "het
  team". In plaats daarvan: het REACT-wiel, de dienstenkleuren en typografie
  dragen de vormgeving.
- De cirkelvormige fotobijsnede in de hero (leeg vlak in plaats van geleend
  beeld, tot de klant eigen foto's aanlevert).
- Het testimonial-blok "Een greep uit onze tevreden klanten" bevatte geen
  zichtbare citaten in de opgehaalde HTML — geen reviews aangetroffen om over
  te nemen. Blijft `[PLACEHOLDER]` tot de klant reviews aanlevert.

## Broncontrole typografie — 22 september 2026

De actuele homepage-CSS zet `--g-primary-font: "DM Sans"` voor tekst en
`--g-secondary-font: "Figtree"` voor koppen. Ook h1–h6 zijn expliciet Figtree.
De eerdere rolverdeling in deze brief was omgekeerd en is hierboven gecorrigeerd.
Het vernieuwde ontwerp gebruikt Figtree voor koppen en DM Sans voor tekst,
zelf gehost. De bestaande bestandsnamen display/body verwijzen nog naar de
oude bestandsindeling; de CSS-koppeling bepaalt nu de juiste familie.
Bron: https://react2u.nl/ — inline `coachify-style-inline-css`.
