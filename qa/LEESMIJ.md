# QA

Drie scripts, alle drie alleen-lezen. Ze wijzigen niets op de site en versturen
nooit een formulier.

```bash
cd qa
npm install            # eenmalig; haalt Playwright binnen
npx playwright install chromium

npm run thema          # rendering, navigatie op elke breedte, overflow, consolefouten
npm run links          # kapotte links, koppenstructuur, meta-description, canonical
npm run seo            # sitemap, robots, JSON-LD, kruimelpad, FAQ-schema
npm run musthaves      # must-haves per paginatype (docs/must-haves.md)

npm run alles
```

Standaard kijken ze naar de lokale omgeving. Voor staging:

```bash
SITE_URL=https://staging.react2u.sitejob.nl/ npm run alles
```

Elk script eindigt met exitcode 1 zodra er een **FOUT** is. Waarschuwingen
laten de exitcode op 0: die beoordeel je zelf.

Schermafdrukken komen in `uitvoer/`. Die map staat in `.gitignore` — zet alleen
de afdrukken die je in het QA-rapport gebruikt bewust in de projectmap.
