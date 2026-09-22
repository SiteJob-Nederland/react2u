# Ontwerprichting — React2u

## Leidende opdracht — 22 september 2026

Kas vraagt een duidelijke splitsing tussen werkgevers en werknemers en legt
extra nadruk op UI. CRO is uitgesloten. De visuele uitwerking hieronder is een
voorstel; behoud van logo en kleuren is als richting voorgelegd aan Kas.
Historische beschrijvingen van de bestaande assets zijn geen actuele
goedkeuring van beeldrechten, teksten of ontwerp.

## Vertrekpunt: de bestaande site

- Huidige site: <https://react2u.nl>
- Secties die het werk doen (screenshots in `assets/huidige-site/schermafdrukken/`):
  - hero — belofte + knop, werkt, maar leunt op een cirkelfoto van twee
    (stock)modellen
  - "Daar zorgen wij voor" — een donker indigo tekstvlak, goed contrastmoment
  - "Dit is React2u!" — tekst naast het REACT-wiel; dit wiel is het sterkste
    grafische element van de hele site
  - zes dienstenkaarten met eigen kleur per dienst — het meest onderscheidende
    detail van het merk
  - "Er is altijd een passende oplossing" — lopende tekst, geen visuele kracht
  - FAQ (op meerdere pagina's, niet alleen home) — inhoudelijk sterk, visueel
    onopvallend
- Componenten die we overnemen (structuur, niet de HTML):
  - de zes gekleurde dienstenkaarten (kern van de identiteit)
  - het REACT-wiel als beeldmerk in de "wie we zijn"-sectie
  - de FAQ, nu met zichtbaar FAQPage-schema
  - "Wat doet de werkgever? / Wat neemt React2u uit handen?" — de
    tweekoloms-vergelijking op elke dienstpagina; heldere, unieke structuur
    die goed samenvalt met het thema se "Zo werkt het"-idee
- Componenten die we vervangen omdat ze zwak zijn:
  - de stockfoto-cirkel in de hero → leeg vlak met de stippenwolk/het wiel als
    decoratief motief, geen geleend beeld
  - de vlakke lichtgrijze secties tussen de kleurrijke blokken → gebruik van
    `--surface-alt` / `--surface-sink` met meer ademruimte
  - de dienstenkaarten met dunne gekleurde rand op wit → steviger kaarten met
    een gekleurd icoonvlak, AA-veilige tekstkleur (zie merkbrief)

## Waar het op moet lijken (en waarop niet)

- Referenties: geen expliciete referentiesites van de klant beschikbaar
  (nieuwe site, geen apart gesprek gevoerd) — [PLACEHOLDER] voor een echt
  klantgesprek. De richting hieronder is afgeleid van wat er al staat en van
  de harde eisen van de kit.
- Wat aan de huidige site goed is: de menselijke toon, de gekleurde
  dienstencodering, het REACT-wiel als eigen merkasset.
- Wat we bewust anders doen: minder drukte per sectie, geen stockfoto's, meer
  wit/ademruimte tussen de kleurblokken, scherpere typografische hiërarchie
  (de huidige site zet bijna alles vet en in hoofdletters).

## Algemene richting

React2u oogt als een arbodienst die zelf orde schept in een onderwerp
(verzuim) dat voor een werkgever al onoverzichtelijk genoeg is. De eerste
indruk is rust: veel wit, één duidelijke belofte, geen drukte. Kleur is
functioneel — elke dienst heeft er één, verder blijft de pagina overwegend
indigo-op-wit. Het REACT-wiel is het ene detail dat blijft hangen: een
werkwijzemodel dat niemand anders heeft. Typografie draagt gezag zonder kil te
worden — DM Sans in de koppen geeft net genoeg karakter, Figtree in de tekst
blijft leesbaar en menselijk.

## Homepage en doelgroepkeuze

1. Compacte header: logo, **Werkgevers**, **Werknemers**, Over React2u, Contact.
   Inloggen is een aparte hulplink zodra de juiste bestemming bekend is.
2. Korte gezamenlijke introductie met één H1 die beide doelgroepen aanspreekt.
   Daar direct bij twee gelijkwaardige ingangen: **Ik ben werkgever** en
   **Ik ben werknemer**, elk met een korte uitleg en een eigen bestemming.
   Op mobiel staan beide routes vóór de uitgebreide merk- en dienstinformatie.
3. Gezamenlijke werkwijze: gezond, menselijk, duidelijk; compact uitgelegd met
   bestaand merkbeeld, zonder onbevestigde resultaatclaims.
4. Korte introductie van React2u en relevante verwijzing naar de eigen route.
   De uitgebreide dienstencatalogus hoort bij werkgevers.
5. Contact met een herkenbare keuze tussen een zakelijke kennismaking en een
   vraag over begeleiding. Geen offerteknop als standaardactie voor werknemers.

Geen verplichte keuzepopup, intro-animatie of overlay. Beide routes blijven
gewone links en zijn rechtstreeks bereikbaar en deelbaar, ook zonder JavaScript.
De gekozen doelgroep blijft herkenbaar in navigatie en paginakop; wisselen
blijft mogelijk. Geen opgeslagen bezoekersprofiel nodig voor deze navigatie.

## Werkgeversroute — voorstel `/werkgevers/`

- Kernvraag: wat kan React2u voor mijn organisatie en medewerkers betekenen?
- Overzicht van de zes bestaande diensten, met herkenbare dienstkleuren.
- Werkwijze en taakverdeling, met verdere uitleg op de bestaande dienstpagina's.
- Hoofdactie: kennismaken via Contact.
- Klantcases, cijfers en reviews alleen tonen zodra onderbouwd en aangeleverd.

## Werknemersroute — bestaande `/werknemers/`

- Kernvraag: wat betekent de begeleiding voor mij en waar kan ik terecht?
- Eerst praktische ingangen: begeleiding, verzuimprotocol en contact.
- Rustige, begrijpelijke uitleg van wat de bezoeker kan verwachten; de precieze
  procesinhoud wordt met React2u gecontroleerd vóór publicatie.
- Veelgestelde vragen vanuit de werknemer, zonder zakelijke verkooppitch.
- Portaal alleen opnemen met de geverifieerde URL; geen inloglink naar home.

Behoud bestaande dienst- en informatie-URL's. `/werkgevers/` is een voorgestelde
nieuwe overzichtspagina; vóór implementatie bestaande sitemap en eventuele
`/werkgever/`-routes controleren om dubbele landingspagina's te voorkomen.

## Blog, kennisbank, auteurspagina — de vaste laag

Ongewijzigd volgens de kit. Geen sitespecifiek detail nodig; deze klant heeft
nog geen blog/kennisbank-content, dus de sjablonen staan klaar maar blijven
leeg tot de klant artikelen aanlevert.

## Servicepagina's — de bespoke laag

Zes dienstpagina's, elk met zijn eigen kleur uit de merkbrief, op de bestaande
URL's (geen redirects nodig — de herbouw hergebruikt de slugs):

- `/verzuimbegeleiding-wvp/` (rood)
- `/verzuimbegeleiding-erd-zw/` (oranje)
- `/preventie-en-vitaliteit/` (blauw)
- `/begeleiding-en-coaching/` (roze)
- `/trainingen-en-workshops/` (teal)
- `/risicomanagement/` (indigo)

Elke pagina krijgt: H1, korte SEO-tekst, "Dit kun je van ons verwachten" met
de echte subkoppen van de huidige site, en de "Wat doet de werkgever? / Wat
neemt React2u uit handen?"-vergelijking. Sjabloon **Dienst** (`page-dienst.php`)
weeft de CTA's er automatisch doorheen.

## Productcategorie

Niet van toepassing — React2u verkoopt diensten, geen producten.
`inc/products.php` blijft uit.

## Kwaliteitslat (UI)

- Beide doelgroepen zijn in de eerste schermsectie herkenbaar; de bezoeker hoeft
  geen dienstenjargon te begrijpen om de juiste ingang te kiezen.
- Eén samenhangende huisstijl. Gebruik labels, inhoud en actieve navigatie om
  routes te onderscheiden; kleur is nooit de enige aanwijzing.
- Beoordeel homepage én beide landingspagina's op desktop en mobiel vóór bouw.
- Controleer menu, focus, hover en actieve toestand; doelgroeplinks zijn echte
  links met een groot klikvlak en blijven bruikbaar met toetsenbord en zonder JS.
- Geen CRO-provider, opnames, maandreview of AI-analyse aan dit ontwerp koppelen.

- Ademruimte boven dichtheid — expliciet een correctie op de huidige site, die
  secties dicht op elkaar stapelt.
- Beweging: subtiel, volledig uit bij `prefers-reduced-motion`. `animate` voor
  de onthulling per sectie en de kaart-hover, `review-animations` voor
  oplevering.
- Geen icoontegels als decoratie — de dienstenkleuren zijn functioneel
  (categorisering), geen los ornament.
- Elk beeld is van de klant of het is er niet: alleen het logo en het
  REACT-wiel zijn echt van React2u; de rest van de vormgeving werkt met kleur,
  vorm en typografie in plaats van foto's.

## Wat af is aan het eind van de sessie

- [ ] Homepage
- [ ] Zes dienstpagina's
- [ ] Diensten-overzichtspagina
- [ ] Over React2u, Werknemers, Verzuimprotocol, Contact
- [ ] Blogartikel-sjabloon (vaste laag, geen sitespecifieke content nodig)
- [ ] Kennisbankartikel-sjabloon (idem)
- [ ] Overzichten (blog, kennisbank, categorie, auteur, zoeken, 404)
- [ ] 390 en 1440 gecontroleerd
