# Intake — eerst vragen, dan bouwen

## Klantopdracht — 22 september 2026

Bron: Kas in de projectchat. Deze opdracht gaat vóór eerdere ontwerpvoorstellen.

- Huidige website: <https://react2u.nl/>.
- Geen CRO: geen PostHog, sessieopnames, automatische maandreview of AI-analyse.
  Vastgelegd in `06-opties.json`; dit is een intakekeuze, geen runtimewijziging.
- Duidelijke splitsing tussen werkgevers en werknemers: beide direct herkenbaar
  op de homepage, in het hoofdmenu en in hun eigen vervolgroutes.
- UI is een hoofdprioriteit voor de klant. De doelgroepkeuze, typografie,
  witruimte, mobiele navigatie en interactiestaten verdienen een afzonderlijke
  ontwerpbeoordeling vóór de bouw.
- Er bestaat al een project met thema en een intake uit augustus. Hierop
  voortbouwen; geen tweede project of overschrijving van bestaande klantcontent.

Op 22 september zijn de publieke homepage, werknemerspagina en over-ons-pagina
opnieuw bekeken. De homepage is primair werkgeversgericht. Werknemers hebben
een menupunt, maar geen gelijkwaardige ingang in de hero. De huidige
inlogknop verwijst naar de homepage; de echte portaalbestemming is nog nodig.
De bestaande bronopname onder `assets/huidige-site/` blijft historisch materiaal.

### Voorstel, nog geen goedgekeurd ontwerp

Logo en herkenbare merkkleuren behouden, met een vernieuwde UI. Eén gezamenlijke
homepage met twee gelijkwaardige routes: **Voor werkgevers** en **Voor werknemers**.
Zie `02-ontwerprichting.md` voor de inhoud en acceptatiepunten per route.

### Nog te bepalen

- Ontwerpvrijheid en eventuele referentiesites: gevraagd aan Kas in deze chat.
- Eigen foto's, gebruiksrechten en definitieve teksten: bevestigen vóór gebruik.
  De eerdere merkbrief en aanleverlijst beschrijven verschillende beeldkeuzes;
  die gelden niet automatisch als actuele klantgoedkeuring.
- Deadline, aanleververantwoordelijke en gewenste hostingomgeving.
- Werkelijke portaal-URL en eventuele afzonderlijke ingangen per doelgroep.
- GA4 en Search Console zijn afzonderlijke keuzes en blijven onbeslist/uit.
- Privacy- en cookie-inrichting beoordelen op de uiteindelijk gebruikte diensten.
- Dashboardbedrijfstoewijzing ontbreekt; er is geen registratie uitgevoerd.

## Vragenlijst voor de resterende intake

Vóór er ook maar één regel vormgeving komt, hoort dit gesprek plaats te vinden.
Zonder dit bouw je op aannames, en dan komt de site er generiek uit.

De agent stelt deze vragen aan het begin van een nieuwe site — niet allemaal
tegelijk als een formulier, maar als een kort gesprek. Veel is al af te leiden
uit de huidige site (zie hieronder); vraag alleen door waar het antwoord het
ontwerp echt verandert.

## 1. De huidige site

- **Wat is de URL van de huidige website?**
- **Wat werkt daaraan goed** en moet blijven?
- **Wat moet juist weg** of anders?
- **Zijn de foto's op de huidige site van de klant zelf?** (mogen die hergebruikt
  worden, of is het deels stock / beeld van derden dat eruit moet?)

> Zodra de URL bekend is: `node scripts/intake.mjs --url <URL> --site <slug>`.
> Dat haalt in één run binnen: het **logo** en de **favicon** (meteen in het
> thema), de **foto's**, **screenshots**, en de **teksten + koppenstructuur**
> per pagina (`inhoud.md`). Loop het manifest en `inhoud.md` daarna samen door.
> Foto's van derden en stockbeeld eruit; het logo is van de klant en blijft.
>
> Heb je een Firecrawl-API-key, dan kun je die ervoor zetten voor een nettere
> volledige tekstexport — zie `docs/uniek-per-klant.md`. De intake werkt ook zonder.

## 2. De richting

- **Welke kant moet het op** qua uitstraling? (strak, warm, industrieel, premium…)
- **Referentiesites** die de klant mooi vindt — en waaróm?
- Is er een **merkdocument / huisstijl** (kleuren, fonts, logo in vector)?

## 3. De inhoud

- **Wie levert wat aan** — logo, foto's, teksten, cijfers, reviews — en wanneer?
- **Auteurs**: wie schrijft de blogs/kennisbank, met foto, functie en socials?
- **Diensten** en eventueel **producten**: welke, en hoe heten ze?

## 4. Het kader

- **Deadline** of oplevermoment?
- **Hosting**: waar komt de site te draaien? (bepaalt het deploy-pad)
- Domein, e-mail, bestaande koppelingen?

## Daarna

Zet de antwoorden in `01-merk.md` t/m `04-seo-geo.md`. Wat nog niet geleverd is,
blijft `[PLACEHOLDER]` — niet invullen met een aanname. Pas als de intake rond
is, begin je aan de tokens en de vormgeving.
