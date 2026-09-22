# Ontwerpcontrole — 22 september 2026

## Resultaat

Lokaal ontwerp op http://127.0.0.1:8133 — **ontwerp-QA geslaagd**.
Dit is geen WordPress-releasegoedkeuring en er is niets gedeployd.

- 12 pagina’s × 320, 390, 768, 1024, 1280 en 1440 px: 72 geslaagde controles.
- Axe WCAG A/AA op 390 en 1440: geen automatische overtredingen of onbesliste resultaten.
- Eén H1/main, omschrijving, geen horizontale overflow, alle beelden en beide
  werkelijke fontbestanden geladen; Figtree-koppen en DM Sans-tekst gecontroleerd.
- 31 unieke interne links/ankers, menu met Enter/Tab/Escape, skiplink,
  werknemers- en werkgeversroute, FAQ en contactankers gecontroleerd.
- Slider: knopbediening, pijltjestoetsen, status en eindpunten gecontroleerd.
  Zonder JavaScript blijft de fotoreeks horizontaal scrollbaar.
- Geen externe netwerkverzoeken vanuit de pagina. Bellen/mailen zijn gewone
  links, niet uitgevoerd. Externe protocol-/documentlinks zijn bronverwijzingen.
- Bronbestanden worden niet door de lokale previewserver aangeboden.
- Screenshots beoordeeld: homepage desktop/mobiel, werkgevers desktop en
  preventie mobiel. Browsercontrole van de vernieuwde homepage en fontfamilies.
  Dit is geen handmatige screenreader- of volledige audit op echte apparaten.

Herhaalbare bewijsbestanden (lokaal, buiten Git):
`../qa/uitvoer/ontwerp/rapport.json` en screenshots in dezelfde map.

## Snelheid van de ontwerp-homepage

Centrale mobiele Lighthouse-meting, drie koude navigaties, gesimuleerde CPU en
verbinding; dezelfde grenzen als de werkmap, niet versoepeld.
Mediaan: **99/100**, LCP **2026 ms**, CLS **0.001**, TBT **0 ms**.
Alle grenzen gehaald (score ≥90, LCP ≤2500 ms, CLS ≤0.1, TBT ≤200 ms).
Alleen de homepage is op snelheid gemeten. Geen velddata/INP-bewijs.
Rapport: `../qa/uitvoer/ontwerp-performance/performance.json`.

## Motion review — scroll-animaties en review-animations

| Before | After | Why |
| --- | --- | --- |
| Statische kaarten zonder scrollentree | Eenmalige entree van 12px/320ms met bestaande curve, alleen buiten het eerste scherm (`ontwerp.css:55`, `ontwerp.js:75`) | Zachte overgang tussen inhoudssecties; 320ms is een zeldzame decoratieve entree, geen wachttijd op bediening |
| Risico dat onthulling inhoud afhankelijk maakt van JS | Basisinhoud altijd zichtbaar, observer start alleen een optioneel keyframe (`ontwerp.js:74`) | Scriptuitval kan de inhoud nooit verbergen |
| Scrollbeweging bij toetsenbord of verminderde beweging | Toetsenbord direct, voorkeur gelezen vóór start en live gestopt (`ontwerp.js:57`, `ontwerp.js:67`, `ontwerp.js:105`) | Lokale eis: alle beweging uit bij reduced motion, ook tijdens bezoek |
| Een slider die vanzelf doorschuift | Native scroll-snap, eigen knoppen, geen autoplay (`ontwerp.js:34`) | Bezoeker houdt controle over lees- en kijktijd |

**Toegankelijkheid:** echte animationstart waargenomen bij scrollen op 390/1440.
Daarna live reduced motion, terugscrollen, footer, hash-herladen en print getest.
Ook geblokkeerd script, ontbrekende IntersectionObserver en toetsenbordgebruik
getest: kaarten zichtbaar en geen decoratieve animaties. Geen wheel/touch-
overname. Toetsenbord/focus schakelt de optionele entree uit voor dat bezoek.

**Timing en samenhang:** alleen transform/opacity, geen bewegingsbibliotheek,
geen permanent will-change, geen hoverbeweging of vertraagd mobiel menu.
De native slider is onderbreekbaar en schakelt direct bij reduced motion.
De hoofdinhoud en hoofdacties zijn niet onderdeel van de entree.

**Approve — voor deze lokale ontwerpversie.** Geen vastgestelde blokkerende
motionbevindingen in de geteste scenario’s. Echte apparaat-/screenreadercontrole
blijft onderdeel van de latere WordPress-oplevering.

## Bestaande WordPress-basis: aparte, niet-groene releasecheck

Volgens de werkmap ook `npm --prefix qa run alles` uitgevoerd op de ingestelde
lokale WordPress-URL http://127.0.0.1:8103. Die test het oude thema, niet deze preview.

Thema-, link- en SEO-controle slagen. Gedeelde surfacecontrole: 34 schermen,
0 fouten; interactie: 6 breedtes, 0 fouten. Mobiele basismetingen halen de grens.
De gehele opdracht eindigt **niet groen**:

- Legacy must-haves vindt geen blog-/kennisartikel om te testen, auteur
  `/author/redactie/` geeft 404 en de service-detectie verwacht `/diensten/…`
  terwijl de bestaande diensten eigen root-URL’s hebben.
- De centrale runner probeert het ontbrekende `qa/qa-opties.mjs` te starten.

Geen dummyartikelen ingevoegd om een controle groen te maken en geen grenzen
of testdetectie verlaagd. Deze basisproblemen moeten bij de WordPress-integratie
worden opgelost; geen productiepublicatie of volledige oplevering geclaimd.

## Fotografie uitgebreid — 22 september 2026

Zes extra illustratieve foto’s gemaakt via Higgsfield (2K-bronnen behouden),
met persoonlijke gesprekken, samen leren, de werkvloer, een gezonde werkplek,
buiten wandelen en telefonisch contact. Herkomst en job-ID’s staan in
`media/HERKOMST.md`. Geen voorstelling als echte medewerkers of klantcases.

Het ontwerp gebruikt nu negen verschillende beelden. De homepage bevat vijf
foto’s, iedere dienstpagina drie verschillende foto’s en de werkgeverspagina
negen plaatsingen (zes verschillende beelden), inclusief fotografie op de
dienstenkaarten. Ook werknemers, over ons, contact en protocol hebben beelden
tussen de inhoud. Bestaande inhoud behouden; geen nieuwe teamclaims toegevoegd.

Alle beelden gebouwd met de gedeelde kwaliteitsscripts: responsive WebP in
480/768/1280/1600 pixels, afmetingen vastgelegd en lazy loading buiten de hero.

Validatie na deze wijziging op http://127.0.0.1:8133:
- `npm --prefix qa run ontwerp`: alle 72 pagina/breedtecombinaties geslaagd;
  geen fouten of onvolledige axe-resultaten. Beelddecodering, interne routes,
  slider, echte scrollentree en reduced motion opnieuw gecontroleerd.
- Visueel beoordeeld: werkgevers mobiel en coaching desktop/mobiel, inclusief
  de uitsneden en nieuwe foto/tekstblokken. Homepage in de browser vernieuwd.
- Drie mobiele Lighthouse-runs: mediaan 99/100, LCP 2026 ms, CLS 0.001 en
  TBT 0 ms. Alle bestaande grenzen gehaald. Alleen homepage gemeten.
- Geen wijzigingen aan motionlogica. Eerdere motionreview blijft van toepassing.

Dit betreft de lokale ontwerp-preview. De hierboven vastgelegde beperkingen
van de bestaande WordPress-basis blijven bestaan; geen productie-deployment.

## Visuele herziening na referenties — 22 september 2026

De eerste opzet voelde te braaf. De visuele taal van
[ArboNed](https://www.arboned.nl/en), [Arbo Unie](https://www.arbounie.nl/)
en [Acture](https://acture.nl/) is bekeken als referentie voor schaal,
contrast, fotografie en compositie. React2u behoudt het eigen logo, de
merkkleuren, Figtree voor koppen en DM Sans voor lopende tekst. De inhoud en
foto's zijn niet van de referenties overgenomen.

| Before | After | Why |
| --- | --- | --- |
| Rustige, grotendeels lichte eerste indruk | Grote indigo hero, forse witte en oranje kop, beeld met boogvorm en twee verzadigde doelgroepvlakken | De keuze voor werkgevers of werknemers is direct zichtbaar en de eerste indruk heeft meer karakter. |
| Vergelijkbare witte kaarten en gelijkmatig sectieritme | Afwisseling tussen editorial beeld/tekst, lila fotoslider, donkere waardenstrook, asymmetrische diensten en volle contactband | Meer spanning en een menselijker verhaal zonder de inhoud te verkorten. |
| Bescheiden fotografie en kleine letterhiërarchie | Grotere portretten en werkvloerscènes met gecontroleerde uitsneden, nadrukkelijke koppen | Het menselijke aspect krijgt ruimte; gezichten blijven in beeld. |
| Twee routekaarten pas na de hero-foto op mobiel | Doelgroepkeuze vóór de foto op mobiel | De belangrijkste navigatie blijft vroeg bereikbaar op een smal scherm. |

Handmatig bekeken in de lokale browser: homepage op 320 en 1440 px,
werkgeversdiensten desktop en coaching-hero desktop; ook de volledige
QA-screenshots van de homepage op 390 en 1440 px. Op 320 px geen horizontale
overflow. De nieuwe compositie overlapt geen tekst of bediening.

`npm --prefix qa run ontwerp` is opnieuw groen: 12 pagina's op 6 breedtes,
**72/72**, zonder failures of onbesliste axe-resultaten. De bestaande
interactiechecks voor routes, mobiel menu, slider, scrollentree en live
reduced motion slagen. Geen wijzigingen aan de motionlogica: de scrollentree
blijft kort en optioneel, de slider heeft geen autoplay, en de inhoud blijft
zichtbaar zonder JavaScript. **Motion review: approve voor de lokale preview.**

Mobiele Lighthouse-meting van de herziene homepage: drie koude runs,
mediaan **99/100**, LCP **2102 ms**, CLS **0**, TBT **0 ms**. De bestaande
grenzen zijn gehaald. Rapporten staan lokaal in
`../qa/uitvoer/ontwerp/rapport.json` en
`../qa/uitvoer/ontwerp-performance/performance.json`. Dit blijft een
ontwerp-preview; de eerder beschreven WordPress-releasepunten zijn nog open.

## Nieuwe correctie: van kleurvlakken naar editorial ontwerp

Kas gaf aan dat de vorige versie ondanks de fellere hero nauwelijks als een
echte verbetering voelde en te veel op een kleurplaat leek. Deze correctie
vervangt de indigo hero en de roze/turkooizen doelgroeptegels door een warme,
lichte basis, één donkere tekstkleur en kleine merkaccenten. Het hoofdbeeld
en de doelgroepkeuze dragen nu de eerste indruk. De tijdelijke
ontwerpstatusbalk is verwijderd, zodat de preview als echte site beoordeeld
kan worden.

| Before | After | Why |
| --- | --- | --- |
| Hero als bijna volledig indigo vlak met oranje regels | Warme achtergrond, donkere grote kop en groot menselijk beeld | De fotografie en boodschap krijgen voorrang boven kleur. |
| Roze en turkooizen doelgroepkaarten | Twee duidelijke genummerde navigatieregels met één pijl per route | Werkgever/werknemer blijft direct herkenbaar zonder decoratieve tegels. |
| Lila slider, donkere waardenstrook en magenta contactband | Fotogedreven slider op wit, waarden op papier en één indigo contactband | Meer ritme met minder concurrerende kleuren. |
| Rode gedraaide fotobijschriften en gekleurde dienstenranden | Rustige bijschriften, natuurlijke foto's en dunne scheidingslijnen | Subpagina's volgen hetzelfde rustige beeldsysteem. |

Visueel beoordeeld in de lokale browser: homepage op desktop en mobiel,
werkgevers-hero op desktop, en de fotoslider na het laden van de beelden.
De bestaande optionele scrollentree en de bediening van de slider zijn
ongewijzigd; deze correctie voegt geen extra beweging toe. Reduced motion
blijft de animaties direct uitschakelen. **Motion review: approve voor de
lokale preview**, onder de bestaande beperking van nog ontbrekende echte
apparaat- en screenreadercontrole.

Na een contrastcorrectie voor de actieve contactnavigatie slaagt
`npm --prefix qa run ontwerp`: **72/72** pagina/breedtecombinaties,
`failures: []`, `incomplete: []`. Interactie, beelddecodering, interne links,
scrollentree en reduced motion zijn opnieuw gecontroleerd. Drie mobiele
Lighthouse-runs op de homepage geven mediaan **99/100**, LCP **2102 ms**,
CLS **0**, TBT **0 ms**; alle bestaande grenzen gehaald. Rapporten:
`../qa/uitvoer/ontwerp/rapport.json` en
`../qa/uitvoer/ontwerp-performance/performance.json`. Dit is lokale
ontwerpvalidatie, geen WordPress-releasegoedkeuring of livegang.

## Structurele herziening na tweede afwijzing

De rustige versie veranderde vooral het kleurgebruik, terwijl de homepage
een gewone tekst/foto-splitsing bleef. De nieuwe versie verandert daarom de
informatiearchitectuur en de compositie van het eerste scherm. De homepage
opent met een beeld over de volle breedte. Het tekstpaneel overlapt de foto
en bevat direct de gelijkwaardige routes naar werkgevers en werknemers.
Daarna volgen een redactionele kennismaking met twee foto's, zes bestaande
diensten als beeldrijke navigatierijen en een grotere, beeldvullende
fotoslider. Alle binnenpagina's openen nu met een brede foto waar de titel
overheen valt. De teksten bij diensten zijn uit de bestaande React2u-bron
gehaald; er zijn geen cijfers of klantcases verzonnen.

| Before | After | Why |
| --- | --- | --- |
| Tekst links, foto rechts en routes eronder | Brede foto met overlappend verhaal en routes in hetzelfde paneel | Een werkelijk andere eerste indruk en een vroege doelgroepkeuze. |
| Kennismaking als tweede tekst/foto-splitsing | Grote redactionele kop met een asymmetrische combinatie van twee mensenfoto's | Het menselijke verhaal krijgt een eigen ritme. |
| Geen dienstenoverzicht op de homepage | Alle zes diensten als gefotografeerde, klikbare rijen | Meer inhoud en direct inzicht in het aanbod. |
| Kleine kaarten naast elkaar in de slider | Eén groot beeld per slide met compact tekstpaneel | De beelden zijn nu de hoofdpersoon van de interactie. |
| Subpagina's met dezelfde links/rechts hero | Foto eerst, titelvlak overlapt de onderkant | De nieuwe beeldtaal loopt door op werkgevers, werknemers en diensten. |

Visueel bekeken in de lokale browser op 1440, 390 en 320 px: eerste scherm,
kennismaking, dienstenrijen, slider en werkgeversopening. De hero-afbeelding
is opnieuw uitgesneden zodat beide gezichten op desktop volledig in beeld
staan; op mobiel blijven ze zichtbaar. De bestaande sliderlogica en optionele
scrollentree zijn niet gewijzigd.

### Motion review — review-animations

| Before | After | Why |
| --- | --- | --- |
| Een horizontale slider met kleinere tekst/fotokaarten | Dezelfde native scroll-snap en knoppen met grotere foto en statisch tekstpaneel | De compositie verandert, maar er ontstaat geen nieuwe automatische beweging. |
| Bestaande optionele scrollentree | Ongewijzigd: 12px/320ms voor zeldzame sectie-entree buiten het eerste scherm | Inhoud en primaire routes verschijnen direct en blijven zichtbaar bij scriptuitval. |
| Reduced motion schakelt alle beweging uit | Ongewijzigd, inclusief wisselen tijdens het bezoek | De lokale projecteis gaat voor de mildere externe skillrichtlijn. |

**Verdict: approve voor de lokale preview**, mits de huidige scherm- en
interactietests groen blijven. Geen autoplay, layoutanimatie of scrollovername
toegevoegd; de native slider blijft bedienbaar en onderbreekbaar.

Na de correctie van een overlappend fotobijschrift is de lokale ontwerp-QA
groen: **72/72** pagina/breedtecombinaties, `failures: []` en
`incomplete: []`. De slider, beide routes, mobiele navigatie, echte
scrollentree, scriptuitval en live reduced motion zijn opnieuw getest.
Mobiele Lighthouse-meting van de homepage: mediaan **97/100**, LCP
**2478 ms**, CLS **0**, TBT **0 ms** over drie koude runs. Alle bestaande
grenzen worden gehaald, maar de LCP zit dicht op de grens van 2500 ms.
Dit is labdata voor de lokale preview, geen velddata of productiebewijs.
Rapporten: `../qa/uitvoer/ontwerp/rapport.json` en
`../qa/uitvoer/ontwerp-performance/performance.json`.
