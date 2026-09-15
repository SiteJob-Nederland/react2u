# React2u — agent-instructies

## Scope

- `theme/react2u/` — maatwerk WordPress-thema, uploadbaar als ZIP. **Geen paginabouwer.**
- `plugins/react2u-hardening/` — beveiligingsregels die het thema moeten overleven.
- `plugins/react2u-mail/` — verzending via SMTP en de uitnodigingsmail voor een nieuw account.
- `brief/` — huisstijl, ontwerprichting, bouwplan, SEO-context. Leidend bij de bouw.
- `qa/` — alleen-lezen controlescripts.
- `deploy/` — staging op srv1 plus demo-inhoud.

## Werkwijze

0. **Intake eerst.** Begin een nieuwe site niet met bouwen. Loop `brief/00-intake.md`
   door — stel de vragen (huidige site, richting, wie levert wat, deadline) en leg
   de bestaande site vast met `node ../../scripts/intake.mjs --url <URL> --site <slug>`.
   De binnengehaalde beelden zijn referentie; alleen wat de klant zelf bezit gaat
   naar het thema.
1. Lees daarna `brief/01-merk.md` t/m `brief/04-seo-geo.md`.
2. Bouw verder op wat er staat. Het thema werkt al; de vormgeving is wat ontbreekt.
3. Merkkleuren zitten in `style.css` blok 1. Componenten gebruiken alleen de
   betekenis-tokens (`--bg`, `--surface`, `--text`, `--accent`) — nooit een hex
   in een component.
4. Vormgeving per klant hoort in `style.css` blok 16 en in `front-page.php`.
5. Draai `qa/` voordat je zegt dat iets af is.

## Uniek per klant

De opbouw is gedeeld, het uiterlijk niet. Blog, kennisbank en auteurspagina
hebben overal dezelfde structuur (en dezelfde SEO/a11y-principes); homepage,
servicepagina's en productcategorieën ontwerp je per klant, afgeleid van de
bestaande site. Zie `../../docs/uniek-per-klant.md`. Maak nooit twee sites die
op elkaar lijken.

## Must-haves per paginatype

`../../docs/must-haves.md` is de checklist die de klant heeft afgesproken. Draai
`qa/qa-musthaves.mjs` en loop de lijst na vóór oplevering.

## Harde eisen

Zie `../../docs/harde-eisen.md`. In het kort: precies één H1 per sjabloon,
kruimelpad van drie niveaus, meta-description overal, werkende sitemap, één
vaste huisstijl volledig via tokens (geen hex in een component), geen
horizontale overflow op 390 en 1440, focus zichtbaar, `prefers-reduced-motion`
gerespecteerd, fonts zelf gehost.

## Beweging

De ontwerp-skills staan in `../../.claude/skills/` en gelden ook hier. Roep ze
aan in plaats van zelf een curve te verzinnen:

| Wanneer | Skill |
|---|---|
| Je gaat iets animeren | `animate` |
| Je zoekt wát er zou mogen bewegen | `find-animation-opportunities` |
| Vóór je zegt dat de vormgeving af is | `review-animations` |
| Homepagevarianten naast elkaar zien | `prototype` |
| Diepte, materiaal, typografie | `emil-design-eng`, `apple-design` |

`review-animations` hoort bij de oplevering, naast `qa/`. Die skill keurt streng
en standaard af — dat is de bedoeling.

Wat er altijd bovenop gaat:

1. `prefers-reduced-motion` zet beweging **uit**, niet trager. Dat is een harde
   eis en wint van elk skill-advies.
2. Eén `--ease` in `style.css` blok 1. Nieuwe curves komen daarbij, niet ernaast.
3. Geen bewegingsbibliotheek. CSS-transitions, `@keyframes` alleen als het niet
   anders kan.
4. De skills zijn Engelstalig; jouw commentaar en commits blijven Nederlands.

## Mail en toegang

Deze site verstuurt via SMTP; zonder dat komt een accountuitnodiging niet aan.
Instellen: `./scripts/mail-instellen.sh <slug> --beide` vanuit de werkmap.

**Mail nooit een wachtwoord.** Uitnodigen gaat met
`./scripts/nodig-uit.sh <slug> naam@voorbeeld.nl --staging`; de ontvanger kiest
zelf een wachtwoord via een eenmalige link. Zie
`../../docs/mail-en-uitnodigingen.md`.

## Bewijs en cijfers

Alles wat een cijfer, quote, naam of contactgegeven is, komt uit `inc/proof.php`.
Nooit in een sjabloon. Wat niet geverifieerd is, blijft `[PLACEHOLDER]` — dat
wordt zichtbaar gemarkeerd op de site en blijft uit de gestructureerde data.

**Verzin nooit een cijfer, review, naam of foto om een gat te vullen.** Een leeg
vlak is eerlijker dan geleend beeld, en een placeholder is eerlijker dan een
verzonnen percentage.

## Taal

Nederlands, je-vorm. Ook in code-commentaar. Merktermen zoals de klant ze schrijft.

## Niet in deze fase

- ...

## Gedeelde kwaliteitscontrole

`npm --prefix qa run alles` voert ook de centrale techscanregels en drie mobiele
Lighthouse-metingen per vertegenwoordigd paginatype uit. `npm --prefix qa run
oplevering` vereist daarnaast gedateerd handmatig bewijs. Lees
`../../docs/website-kwaliteit.md`. Behoud `quality.config.json` en projectspecifieke
checks bij updates; werk beheerde bestanden bij via `scripts/kwaliteit-sync.mjs`.

## Git en GitHub — vaste werkwijze

Deze site heeft een eigen privé-repository bij SiteJob-Nederland. Lees
`../../docs/git-per-site.md`; het centrale register is `../../sites/repositories.json`.
Werk vanuit deze map en controleer bij de start `git status --short --branch`.
Gebruik voor nieuwe werkzaamheden een `codex/<onderwerp>`-branch.
Maak na ieder afgerond onderdeel een gerichte Nederlandse commit en push de
werkbranch. Hiervoor is geen nieuwe toestemmingsvraag nodig. Controleer de
remote commit; meld ontbrekende toegang of resterend werk expliciet.
Commit alleen beoordeelde bestanden; neem andermans lopende wijzigingen niet
blind mee. Een WIP-commit bewaart werk, maar bewijst geen geslaagde website-QA.
Geen force-push, stilzwijgende merge naar main of automatische deployment.
Installeer de meegeleverde hooks met `git config core.hooksPath .githooks`;
`gitleaks` is vereist. Geheimen, dumps, exports en runtime horen niet in Git.
