# React2u — inhoudscontrole 22 september 2026

De eerste vier ontwerppagina’s waren inhoudelijk te beknopt. Vooral de diensten
waren onvoldoende opnieuw gecontroleerd. Op aanwijzing van de gebruiker is de
huidige site opnieuw vastgelegd en zijn de pagina’s inhoudelijk uitgebreid.

## Actuele bronnen

Volledige intake met `scripts/intake.mjs --paginas 40 --no-plaats`, aparte
uitvoermap `assets/huidige-site/2026-09-22/`: 40 pagina’s, 80 screenshots,
60 beelden, `inhoud.md` en `manifest.md`. Dit is lokale bronregistratie,
geen toestemming voor het hergebruiken van stockfoto’s. De volledige opname
blijft buiten Git. Onderstaande actuele hoofdpagina’s zijn inhoudelijk gelezen.

| Huidige bron | Verwerkt in ontwerp | Inhoud die behouden is |
| --- | --- | --- |
| https://react2u.nl/ | index.html | Menselijke belofte, merk, doelgroepkeuze |
| https://react2u.nl/diensten/ | werkgevers.html | Zes diensten, holistische aanpak, platform, preventie |
| https://react2u.nl/verzuimbegeleiding-wvp/ | verzuimbegeleiding-wvp.html | Eigen casemanager, taakdelegatie, dossier, netwerk, taakverdeling |
| https://react2u.nl/verzuimbegeleiding-erd-zw/ | verzuimbegeleiding-erd-zw.html | Organisaties/flex, protocol, bedrijfsarts, taken werkgever/React2u |
| https://react2u.nl/preventie-en-vitaliteit/ | preventie-en-vitaliteit.html | PMO, consulten, MTO, samenhang, netwerk, vroegsignalering |
| https://react2u.nl/begeleiding-en-coaching/ | begeleiding-en-coaching.html | Individueel, burn-out, leven/loopbaan, coaches, taakverdeling |
| https://react2u.nl/trainingen-en-workshops/ | trainingen-en-workshops.html | Verzuim, management, communicatie, vitaliteit, maatwerk, workshops |
| https://react2u.nl/risicomanagement/ | risicomanagement.html | RI&E, vier kerndeskundigheden en plan van aanpak |
| https://react2u.nl/werknemers/ | werknemers.html | Betekenis van werk, persoonlijke begeleiding, samenwerking, passend werk, overzicht |
| https://react2u.nl/over-react2u/ | over-react2u.html | Mensgerichte aanpak, casemanagers, taakgedelegeerden, bedrijfsartsen, preventie |
| https://react2u.nl/verzuimprotocol/ | verzuimprotocol.html | Zes onderwerpen uit het visuele protocol, gewone leesbare HTML en bronlink |
| https://react2u.nl/contact/ | contact.html + doelgroep-FAQ | Werkgever/werknemer-contact en bronvragen |

De tekst is redactioneel herschreven en geordend, niet willekeurig opgevuld.
Dienstpagina’s behouden hun eigen onderwerp en taakverdeling. Trainingen en
RI&E hebben hun eigen passende vergelijking in plaats van een generiek schema.
Het protocolbeeld is visueel gelezen. Geen nieuwe juridische termijnen,
medische adviezen of beloften toegevoegd. Voor publicatie blijft controle door
React2u nodig op bedrijfsclaims, protocol en de precieze toegang tot het platform.

## Wat niet als actuele inhoud is overgenomen

De sitemap bevat oude `/werkgever/`- en `/werknemer/`-routes, `xdiensten`,
`xbegeleidingx-xcoachingx`, Engelstalige coachingartikelen en teampagina’s met
“voornaam achternaam”. Ze zijn vastgelegd, maar zijn geen betrouwbaar team-
of contentbewijs voor dit ontwerp. Bij WordPress-integratie volgt een expliciete
redirect-/URL-mapping; het ontwerp doet geen uitspraak over hun bezoekersverkeer.

De claim “80% ... niet ziek” op Over React2u is niet onderbouwd en weggelaten.
Absolute beloften over herstel, wettelijke naleving en voorkomen van sancties
zijn niet overgenomen als garantie. Een bronpagina is geen onafhankelijke
verificatie van certificering. Er zijn geen reviews, cijfers of medewerkers verzonnen.
De privacy-/klachtenpagina’s bevatten ook ongerelateerde coachingtekst; de footer
verwijst daarom naar de bestaande PDF-documenten uit de actuele footer.
De onjuiste inloglink naar de homepage is niet gekopieerd.

## Typografie

De live inline CSS gebruikt **Figtree voor h1–h6** en **DM Sans als primair
tekstfont**. Dit is tegengesteld aan de eerdere brief. De brief en ontwerp-CSS
zijn gecorrigeerd; fonts worden lokaal geladen. De nieuwe UI varieert bewust
gewicht en lettergrootte, met behoud van de families, het logo en de merkkleuren.

## Omgeving

Twaalf statische, gekoppelde ontwerppagina’s op localhost:8133, geenindexering,
geen formulieren of tracking. De bron van het bestaande WordPress-thema is
niet vervangen en er is niets op productie gepubliceerd. Geen CRO gewenst.
