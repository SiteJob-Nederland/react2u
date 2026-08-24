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

## Wat per klant moet gebeuren

- [x] Zoekwoorden per paginatype vastgelegd (zie tabel hieronder)
- [ ] Meta-description-teksten in `inc/seo.php` op de dienstverlening zetten
- [x] Terugvalmenu's naar de echte URL's (`inc/setup.php`)
- [ ] Auteurs met échte functie, foto en bio — geen auteurs bekend bij deze
      klant; blog/kennisbank staan klaar maar zonder content
- [ ] Interne links: elke belangrijke pagina bereikbaar binnen drie klikken
- [x] Redirects: niet nodig — de herbouw hergebruikt exact dezelfde slugs als
      de huidige site (zie tabel)

## Zoekwoorden per paginatype

| Pagina | Primair zoekwoord | Verwante termen |
|---|---|---|
| Home | arbodienst | ziekteverzuim, re-integratie, verzuimbegeleiding |
| /verzuimbegeleiding-wvp/ | verzuimbegeleiding WVP | Wet verbetering poortwachter, WIA-aanvraag, plan van aanpak |
| /verzuimbegeleiding-erd-zw/ | verzuimbegeleiding ERD/ZW | eigenrisicodrager Ziektewet, flexbranche |
| /preventie-en-vitaliteit/ | preventie en vitaliteit | PMO, MTO, duurzame inzetbaarheid |
| /begeleiding-en-coaching/ | begeleiding en coaching | burn-outcoaching, loopbaancoaching |
| /trainingen-en-workshops/ | verzuimtraining | managementtraining, workshop op maat |
| /risicomanagement/ | risicomanagement RI&E | bedrijfsarts, arbeidshygiënist, hogere veiligheidskundige |
| /werknemers/ | verzuimprotocol werknemer | ziek melden, re-integratieplan |
| /over-react2u/ | over React2u | arbodienst Eindhoven |

## Redirect-tabel bij een herbouw

Niet van toepassing: zelfde domein, zelfde slugs, alleen het thema wordt
vervangen.

| Oude URL | Nieuwe URL |
|---|---|
| (ongewijzigd) | (ongewijzigd) |

## Wat we bewust NIET doen

- Geen aggregateRating publiceren die niet klopt. Een verzonnen score is een
  boete waard en is bovendien gewoon liegen tegen een bezoeker.
- Geen FAQ-schema zonder zichtbare FAQ op de pagina.
- Geen keyword-stuffing in de alt-teksten.
