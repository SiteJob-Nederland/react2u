# Fonts

Zelf hosten, nooit van een CDN. Twee redenen: een verzoek naar een derde partij
bij elke paginaweergave, en een extra verbinding voordat de eerste tekst staat.

## Ophalen

1. Kies de familie en de gewichten die de brief noemt (`brief/01-merk.md`).
2. Haal alleen `woff2` op — alles wat WordPress ondersteunt, ondersteunt woff2.
3. Zet ze hier neer als `<familie>-<gewicht>-<subset>.woff2`,
   bijvoorbeeld `body-400-latin.woff2` of `display-var-latin.woff2`.
4. Vul `fonts.css` (voorbeeldblok staat erin).
5. Zet de namen in `style.css` bij `--f-display` en `--f-body`.
6. Voeg dezelfde families toe in `theme.json` onder `settings.typography.fontFamilies`,
   met een `fontFace` per bestand — anders wijkt de editor af van de voorkant.
7. Controleer `inc/assets.php`: de preload-lijst noemt de twee bestanden die als
   eerste in beeld komen. Bestaat een bestand niet, dan slaat de preload over.

## Licentie

Controleer per familie of webgebruik is toegestaan. Betaalde fonts (Gilroy en
dergelijke) horen bij de klant vandaan te komen, mét licentiebewijs. Zet dat
bewijs in de projectmap, niet in het thema.
