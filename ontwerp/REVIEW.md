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
