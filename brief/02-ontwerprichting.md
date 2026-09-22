# Ontwerprichting — React2u

## Bijsturing: geen kleurplaat

Kas ziet in de versie met indigo hero en grote roze/turkooizen doelgroepkaarten
geen overtuigende ontwerpverbetering. De merkidentiteit blijft aanwezig, maar
de felle dienstkleuren vullen niet langer hele secties of doelgroepvlakken.
De referenties blijven leidend voor de schaal van typografie, de fotografie
en het ritme; hun kleurgebruik wordt niet letterlijk nagevolgd.

De nieuwe richting is editorial en beeldgedreven: een rustige warme basis,
donkere typografie, grote menselijke fotografie, zichtbare witruimte en een
doelgroepkeuze als twee sterke navigatieregels. Indigo draagt de tekst en één
contactsectie; oranje is een klein accent. Dienstkleuren blijven behouden in
het logo en kleine functionele accenten. Subpagina's gebruiken dezelfde
fotografische opbouw. De eerder beschreven grote verzadigde keuzevlakken zijn
hiermee vervallen.

## Richting na feedback: meer lef en drie referenties

Kas vindt de eerste lokale ontwerpversie te braaf en te standaard. Als
visuele referenties noemt hij [ArboNed](https://www.arboned.nl/en),
[Arbo Unie](https://www.arbounie.nl/) en [Acture](https://acture.nl/).
Op 22 september 2026 visueel gecontroleerd: ArboNed opent met zeer grote
typografie, een verzadigd kleurvlak, menselijke fotografie en overlappende
snelle routes; Arbo Unie geeft fotografie bijna de hele hero en heeft
duidelijke inhoudelijke ingangen; Acture gebruikt een asymmetrische verdeling
van donker tekstvlak en licht beeldvlak. Dit zijn referenties voor ritme en
lef, geen bron voor React2u-cijfers, claims, beelden of diensten.

De nieuwe React2u-richting gebruikt het eigen indigo als groot contrastvlak,
de bestaande dienstkleuren als stevige keuzevlakken, forsere Figtree-koppen,
een beeldgedreven hero en kaarten die deels over het hoofdbeeld vallen.
Werkgevers en werknemers blijven twee gelijkwaardige routes en komen op
mobiel vóór de hero-foto. Secties krijgen afwisselend volle kleur, beeld en
editoriale tekst; identieke witte kaartjes zijn teruggedrongen. De logo-,
font- en kleurbronnen blijven React2u-eigen. Geen verzonnen klantbewijs.

De eerdere aanwijzingen hieronder die een overwegend witte, rustige en
terughoudende presentatie voorschrijven, zijn door deze feedback ingehaald.

## Leidende opdracht — 22 september 2026

Kas vraagt een duidelijke splitsing tussen werkgevers en werknemers en legt
extra nadruk op UI. CRO is uitgesloten. Kas heeft de richting bevestigd:
**logo en merkkleuren behouden, de UI vernieuwen**. Dit geeft ruimte voor een
nieuwe compositie, typografische hiërarchie, navigatie en componenten binnen
de bestaande merkidentiteit. De concrete schermuitwerking is nog een voorstel.
Historische beschrijvingen van de bestaande assets zijn geen actuele
goedkeuring van beeldrechten, teksten of ontwerp.

## Bijsturing 22 september 2026 — leidend voor het ontwerp

De gebruiker vraagt expliciet om een menselijke, warme UI met fotografie,
sliders en subtiele scrollanimaties. Logo en merkkleuren blijven behouden.
De onderstaande oudere uitgangspunten “geen stockfoto’s” / “geen foto’s”
zijn hiermee vervangen: bestaand klantbeeld of hoogwaardige Higgsfield-beelden
is toegestaan. Herkomst wordt intern vastgelegd; geen labels “AI-sfeerbeeld”
in de interface en geen voorstelling van gegenereerde personen als echte medewerkers.
Koppen gebruiken Figtree, lopende tekst DM Sans, volgens de actuele broncontrole.

Te korte doelgroeppagina’s worden aangevuld met echte inhoud uit de huidige site.
De bronopname bevat 40 pagina’s en 80 screenshots (22 september). Zie
`07-inhoudscontrole.md` voor de selectie en vertaling naar 12 ontwerppagina’s.
De leesbare inhoud heeft voorrang op animatie; geen CRO/tracking.

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
worden — Figtree in de koppen geeft net genoeg karakter, DM Sans in de tekst
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

## Fotografie uitgebreid na feedback — 22 september 2026

De gebruiker vindt de hoeveelheid fotografie nog te klein. Daarom wordt beeld
onderdeel van de hele pagina: zes nieuwe hoogwaardige beelden, negen unieke
foto’s totaal. Dienstpagina’s krijgen drie beelden per pagina; op werkgevers
ook fotografie in de diensttegels. Persoonlijke gesprekken, werkplekken,
samen leren, wandelen en bellen zorgen voor meer variatie in context.
Tekstinhoud blijft behouden. Foto’s laden responsief en onder de vouw lazy.
