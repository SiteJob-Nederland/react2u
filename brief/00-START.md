# Startprompt — React2u

Plak dit in een nieuwe sessie zodra de brief gevuld is.

**Stap 0 — eerst de intake.** Begin niet met bouwen. Loop eerst `00-intake.md`
door: stel de vragen, en leg de huidige site vast met
`node scripts/intake.mjs --url <URL> --site <slug>`. Pas als dat rond is, ga je
verder met de stappen hieronder.

---

Bouw de website van React2u in `sites/react2u/`.

Lees eerst, in deze volgorde:
1. `brief/01-merk.md` — kleuren, fonts, logo
2. `brief/02-ontwerprichting.md` — hoe het eruit moet zien en waarom
3. `brief/03-bouwplan.md` — wat, in welke volgorde, en wanneer het af is
4. `brief/04-seo-geo.md` — de SEO/GEO-eisen
5. `CLAUDE.md` — de werkafspraken voor dit project
6. `../../docs/harde-eisen.md` — de lat waar elke site overheen moet

Het thema in `theme/react2u/` werkt al: sjablonen, blokken, SEO en schema
staan er. Wat er nog niet is, is de vormgeving. Begin dus niet opnieuw — vul de
tokens in `style.css` blok 1, hang de fonts erin, vul `inc/proof.php` met wat
geverifieerd is, en ontwerp daarna de homepage en de vormgevingslaag.

Voor alles wat beweegt gebruik je de ontwerp-skills: `animate` om er een te
bouwen, `review-animations` voor je zegt dat het af is, `prototype` als je
eerst richtingen naast elkaar wil zien. Verzin zelf geen curve.

Verzin geen cijfers, reviews of namen. Wat niet geleverd is, blijft
`[PLACEHOLDER]`.

---

## Handig om paraat te hebben

- Huidige site van de klant: ...
- Wie levert wat aan, en wanneer: ...
- Deadline / oplevermoment: ...
