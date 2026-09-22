# SEO en GEO — React2u

GEO = Generative Engine Optimization: gevonden worden door AI-antwoorden, niet
alleen door de blauwe links. In de praktijk overlapt dat grotendeels met goede,
klassieke SEO — met één verschil: een AI leest je HTML, niet je maatwerkvelden.

## Wat het thema al doet

- Meta-description op élk paginatype, nooit leeg
- Canonical op élke weergave
- Eén JSON-LD-graaf per pagina: Organization, WebSite, WebPage, BreadcrumbList,
  Article/BlogPosting, Person, ProfilePage, FAQPage
- ImageObject mét breedte en hoogte, geen kale URL
- Kruimelpad van minimaal drie niveaus, zichtbaar en in het schema
- Inhoudsopgave en FAQ afgeleid uit de HTML, dus ook bij aangeleverde artikelen
- `/sitemap.xml` stuurt door naar de echte sitemap, met `lastmod`
- Eén H1 per pagina, afgedwongen in het sjabloon
- Auteurspagina's met foto, functie en bio — E-E-A-T-signalen die er echt staan

## Twee zoekroutes, één merkentree

De homepage `/` is alleen de merkentree: logo, een korte keuzevraag en twee
volledig klikbare routes. Zij richt zich op navigatie naar React2u, niet op het
algemene zoekwoord `arbodienst`. Een bezoeker uit een zoekresultaat moet direct
op de inhoudelijke pagina voor zijn of haar situatie kunnen landen.

| Route | Zoekintentie | Eigen inhoud en interne links |
|---|---|---|
| `/` | React2u vinden en doelgroep kiezen | Alleen duidelijke links naar `/werkgevers/` en `/werknemers/` |
| `/werkgevers/` | Arbodienst en verzuimbegeleiding voor een organisatie | Dienstverlening, aanpak, zes bestaande dienstpagina's en werkgeverscontact |
| `/werknemers/` | Hulp bij ziekte, begeleiding en terugkeer naar werk | Uitleg van het traject, veelgestelde vragen, `/verzuimprotocol/` en werknemerscontact |

Elke route krijgt een eigen title, meta-description, H1, intro en self-canonical.
De gedeelde contactpagina blijft bereikbaar vanuit beide routes. De
dienstpagina's behouden hun huidige slugs, zodat bestaande relevante URL's niet
onnodig verhuizen. `/werkgevers/` is een nieuwe centrale landingspagina en moet
bij de WordPress-uitwerking als echte pagina worden aangemaakt en intern worden
gelinkt; de huidige site had deze URL niet in de vastgelegde contentseed.

De lokale ontwerp-HTML blijft bewust `noindex,nofollow`. Indexeerbaarheid,
canonicals en sitemap van de WordPress-versie worden pas op de uiteindelijke
staging/production-weergave gecontroleerd. De merkentree kan in productie
indexeerbaar blijven voor merkgerelateerde zoekopdrachten, maar krijgt geen
generieke SEO-tekst om tegelijk op beide doelgroepen te mikken.

## Wat per klant moet gebeuren

- [x] Zoekwoorden per paginatype vastgelegd (zie tabel hieronder)
- [ ] Eigen SEO-titels en meta-descriptions voor de twee doelgroep-URL's in de WordPress-versie zetten en gerenderd verifiëren
- [ ] `/werkgevers/` als echte WordPress-pagina publiceren met eigen template en interne links
- [x] Terugvalmenu's naar de echte URL's (`inc/setup.php`)
- [ ] Auteurs met échte functie, foto en bio — geen auteurs bekend bij deze
      klant; blog/kennisbank staan klaar maar zonder content
- [ ] Interne links: elke belangrijke pagina bereikbaar binnen drie klikken
- [x] Geen redirects voor de bestaande dienst- en werknemerspagina's: hun slugs
      blijven gelijk. `/werkgevers/` wordt als nieuwe pagina toegevoegd.

## Zoekwoorden per paginatype

| Pagina | Primair zoekwoord | Verwante termen |
|---|---|---|
| Home `/` | React2u (merk) | werkgevers, werknemers |
| `/werkgevers/` | arbodienst voor werkgevers | verzuimbegeleiding werkgever, preventie, duurzame inzetbaarheid |
| /verzuimbegeleiding-wvp/ | verzuimbegeleiding WVP | Wet verbetering poortwachter, WIA-aanvraag, plan van aanpak |
| /verzuimbegeleiding-erd-zw/ | verzuimbegeleiding ERD/ZW | eigenrisicodrager Ziektewet, flexbranche |
| /preventie-en-vitaliteit/ | preventie en vitaliteit | PMO, MTO, duurzame inzetbaarheid |
| /begeleiding-en-coaching/ | begeleiding en coaching | burn-outcoaching, loopbaancoaching |
| /trainingen-en-workshops/ | verzuimtraining | managementtraining, workshop op maat |
| /risicomanagement/ | risicomanagement RI&E | bedrijfsarts, arbeidshygiënist, hogere veiligheidskundige |
| `/werknemers/` | verzuimbegeleiding voor werknemers | ziek melden, re-integratie, herstel, begeleiding |
| `/verzuimprotocol/` | verzuimprotocol werknemer | ziek melden, rechten en plichten, stappen bij ziekte |
| /over-react2u/ | over React2u | arbodienst Eindhoven |

## Redirect-tabel bij een herbouw

Voor bestaande pagina's niet van toepassing: zelfde domein en zelfde slugs.
`/werkgevers/` is een nieuwe URL en heeft daarom geen oude URL om om te leiden.

| Oude URL | Nieuwe URL |
|---|---|
| (ongewijzigd) | (ongewijzigd) |

## Wat we bewust NIET doen

- Geen aggregateRating publiceren die niet klopt. Een verzonnen score is een
  boete waard en is bovendien gewoon liegen tegen een bezoeker.
- Geen FAQ-schema zonder zichtbare FAQ op de pagina.
- Geen keyword-stuffing in de alt-teksten.
