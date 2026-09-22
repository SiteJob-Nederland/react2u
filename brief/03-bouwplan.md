# Bouwplan — React2u

## Technische basis

- WordPress, maatwerkthema, **geen paginabouwer**
- PHP 8.1+, WordPress 6.6+
- Zelf-gehoste fonts, geen externe verzoeken bij het laden
- Geen SEO-plugin: het thema doet meta, canonical, OG, schema en sitemap zelf
- Kennisbank als eigen posttype, met REST aan (zodat een redactietool erin kan)

## Volgorde in de sessie

Bijsturing 22 september 2026: eerst de resterende ontwerpintake afronden en een
ontwerp voor de homepage plus werkgevers- en werknemersroute beoordelen. De
onderstaande bouwstappen volgen daarna. Geen CRO: de opties staan in
`06-opties.json`; GA4/Search Console en privacy zijn nog niet afgerond.

1. `inc/proof.php` vullen met wat geverifieerd is; de rest blijft `[PLACEHOLDER]`
2. Tokens in `style.css` blok 1 op de huisstijl zetten
3. Fonts erin, `fonts.css` en `theme.json` bijwerken
4. Logo, beeldmerk en favicon in `assets/images/`
5. Terugvalmenu's in `inc/setup.php` naar de echte paden
6. Homepage ontwerpen (`front-page.php` + `style.css` blok 16)
   - Twee gelijkwaardige ingangen, met eigen werkgevers- en werknemersroute.
   - Navigatie, mobiele doelgroepkeuze en contactacties per doelgroep uitwerken.
   - Bestaande URL's en sitemap controleren vóór een nieuwe werkgeverspagina.
7. Artikel- en kennisbanksjabloon nalopen op de huisstijl
8. Overzichten en 404 nalopen
9. Beweging nalopen met de `review-animations`-skill, bevindingen oplossen
10. QA draaien (`qa/`), bevindingen oplossen, `QA-RAPPORT.md` invullen
11. ZIP bouwen en staging opzetten

## Acceptatie — klaar om op te sturen

- [ ] Homepage en beide doelgroeppagina's visueel beoordeeld op desktop en mobiel
- [ ] Werkgevers en werknemers vinden direct hun eigen route en kunnen wisselen
- [ ] Werknemers krijgen praktische hulp; werkgevers een zakelijke kennismaking
- [ ] Geen CRO-tracking, sessieopnames, automatische maandreview of AI-analyse actief
- [ ] `npm run alles` in `qa/` geeft geen fouten
- [ ] Precies één H1 per sjabloon
- [ ] Kruimelpad van drie niveaus, zichtbaar én in het schema
- [ ] Meta-description op élk paginatype
- [ ] `/sitemap.xml` komt uit bij een werkende sitemap
- [ ] Geen horizontale overflow op 390 en 1440
- [ ] `prefers-reduced-motion` zet alle beweging uit
- [ ] `review-animations` gedraaid en de bevindingen opgelost of beargumenteerd
- [ ] Toetsenbord: menu te openen en te sluiten, focus overal zichtbaar
- [ ] Geen `[PLACEHOLDER]` in de gestructureerde data
- [ ] `AANLEVERLIJST.md` bijgewerkt met wat de klant nog moet leveren

## Wat NIET in deze fase

- ...

## Aandachtspunten

- ...
