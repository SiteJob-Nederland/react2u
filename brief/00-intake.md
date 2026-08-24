# Intake — eerst vragen, dan bouwen

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
