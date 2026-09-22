# React2u — lokaal ontwerp

Twaalf gekoppelde ontwerppagina’s, met menselijke fotografie, behoud van logo,
merkkleuren en de brongetrouwe fontfamilies (Figtree/DM Sans). Geen CRO.

Vanuit de projectmap:

```sh
node ontwerp/bouw.mjs
python3 ontwerp/server.py
npm --prefix qa run ontwerp
```

Preview: http://127.0.0.1:8133/index.html

`bouw.php` en `verdieping.php` bouwen HTML. `inhoud.json` bevat de zes diensten.
`ontwerp.css` en `ontwerp.js` zijn de bronnen. De build gebruikt centrale CSS/JS-
en beeldkwaliteitsscripts in een geïsoleerde map en serveert de gebouwde assets.
De lokale server toont alleen toegestane pagina’s/assets en blokkeert broncode,
formulieren en externe script-/font-/beeldverzoeken via CSP.

De beelden zijn fictieve situaties; zie `media/HERKOMST.md`. Er worden geen
personen als echte medewerkers voorgesteld. Bronregistratie en inhoudskeuzes:
`../brief/07-inhoudscontrole.md`. De intake-opname zelf blijft lokaal buiten Git.

Dit is een ontwerpversie. WordPress-integratie, productie-URL-mapping,
contentgoedkeuring en publicatie zijn nog niet uitgevoerd. De ontwerp-QA heeft
een eigen rapport en kan geen volledige WordPress-releasecheck vervangen.
