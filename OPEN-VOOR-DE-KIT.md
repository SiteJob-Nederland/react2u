# Open punten voor de kit

Wat tijdens klantprojecten naar boven kwam en voor élke site geldt, maar te
groot is om even tussendoor te doen. Wie eraan begint: haal het punt hier weg.

## ~~1. De animatiegroepen in `site.js` kennen alleen kit-klassen~~ — opgelost

Opgelost bij The High Value Club (augustus 2026) en teruggezet in de kit. Een
sjabloon noteert nu zelf `data-anim-op="rise"` op het element dat moet bewegen;
`site.js` maakt daar bij het laden een echte `data-anim` van en zet hem onder de
waarnemer. De vaste selectorlijst blijft bestaan voor kit-componenten, maar een
site met eigen componenten hoeft `site.js` niet meer aan te raken.

Twee dingen zaten daar aan vast en horen bij elkaar te blijven:

- De nul hangt aan `.js-anim`, een klasse die in de `<head>` wordt gezet. Zonder
  JavaScript komt die klasse er nooit en staat de pagina er gewoon; mét
  JavaScript staat de nul er vóór het eerste verfje, zodat inhoud boven de vouw
  niet eerst verschijnt om daarna weg te duiken.
- De vouwgrens geldt niet voor deze groep. Een sjabloon dat zelf om beweging
  vraagt, vraagt er meestal om juist bij het laden.

Er kwam een vierde onthullingssoort bij: `zet`, met `--dx`/`--dy` per element,
voor een groep die zich sluit.

## 2. `proof.php` en `schema.php` kennen één adres

Een klant met twee vestigingen (of drie) heeft per locatie een eigen adres,
telefoonnummer, e-mailadres en openingstijden. Nu past dat niet: `contact` is één
blok, en `inc/schema.php` maakt er één `LocalBusiness` van.

Nodig: een `locations`-array in `proof.php` (bij The High Value Club staat er al
één, als voorzet) en een `schema.php` die daar per locatie een `LocalBusiness`
van maakt, elk met eigen `geo`, `openingHours` en `areaServed`. Voor lokale SEO
is dat het verschil tussen één vindbare vestiging en twee.

*Gezien bij:* The High Value Club (augustus 2026).

## 3. Er is geen logovariant-mechanisme

Het thema kiest `logo.svg` → `logo.png` → `logo.webp`, en `.site-logo.is-light`
maakt daar met een filter een witte versie van. Wat ontbreekt is het omgekeerde:
een klant levert alleen een logo voor een dónkere ondergrond, en dan valt het
woordmerk weg zodra het lichte thema aanstaat.

Nu wordt dat per site opgelost door de sitekop donker te maken (blok 16). Dat is
een prima uitkomst, maar het hoort een keuze te zijn — bijvoorbeeld
`logo-dark.png` / `logo-light.png` met een `<picture>` op `prefers-color-scheme`.

*Gezien bij:* The High Value Club (augustus 2026).
