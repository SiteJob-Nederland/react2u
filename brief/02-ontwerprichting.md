# Ontwerprichting — React2u

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

## Homepage

1. Hero — één belofte ("Jouw mensen, onze aandacht"), één knop. Geen
   stockfoto; de stippenwolk uit het logo als subtiel decoratief patroon
   rechts, geen cirkelfoto.
2. Bewijs (cijfers) — blijft `[PLACEHOLDER]` tot de klant cijfers aanlevert;
   QA-rapport en dashboardmelding maken dat zichtbaar.
3. Wat we doen — de zes dienstenkaarten, elk in zijn eigen kleur, met de
   echte eenregelige omschrijving van de huidige site.
4. Hoe het werkt — "Zo werkt het" afgeleid uit de terugkerende structuur op
   elke dienstpagina (kennismaking → analyse → plan van aanpak → begeleiding).
5. Wie we zijn — "Dit is React2u!" met het REACT-wiel, echte tekst van de
   huidige over-ons-pagina.
6. Reviews / cases — `[PLACEHOLDER]`, geen citaten aangetroffen op de huidige
   site om over te nemen.
7. Laatste artikelen — blog/kennisbank bestaan nog niet bij deze klant; het
   sjabloon blijft staan (vaste laag van de kit) maar de sectie verschijnt pas
   zodra er content is (front-page.php checkt al `$latest->have_posts()`).
8. Afsluitende CTA — "Kom met ons in contact", telefoonnummer + offerteknop.

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
