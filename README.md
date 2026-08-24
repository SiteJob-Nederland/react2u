# React2u

Maatwerk WordPress-site. Werkmap: thema, hardening-plugin, brief, QA en deploy.

## Structuur

| Map | Inhoud |
|---|---|
| `brief/` | Huisstijl, ontwerprichting, bouwplan, SEO-context |
| `theme/react2u/` | Het thema |
| `plugins/react2u-hardening/` | Beveiligingsregels die het thema overleven |
| `qa/` | Controlescripts (alleen lezen) |
| `deploy/` | Staging op srv1 en demo-inhoud |
| `assets/` | Bronbestanden van de klant (logo, foto's) |

## Lokaal draaien

```bash
cp .env.example .env      # wachtwoorden invullen
docker compose up -d
```

Site: <http://127.0.0.1:8103> · Beheer: <http://127.0.0.1:8103/wp-admin/>

Eerste keer, in deze volgorde:

```bash
# 1. WordPress installeren (wachtwoorden staan in .env)
docker compose run --rm cli core install \
  --url="http://127.0.0.1:8103" --title="React2u" \
  --admin_user=kas --admin_password="$(grep ADMIN_PASSWORD .env | cut -d= -f2)" \
  --admin_email=info@sitejob.nl --skip-email

# 2. Nederlands — anders staat er lang="en-US" in de HTML
docker compose run --rm cli language core install nl_NL --activate

# 3. Thema, hardening en permalinks
docker compose run --rm cli theme activate react2u
docker compose run --rm cli plugin activate react2u-hardening
docker compose run --rm cli option update permalink_structure '/%postname%/'

# 4. Demo-inhoud, zodat elk sjabloon te beoordelen is
docker compose run --rm cli eval-file /deploy/seed.php
docker compose run --rm cli eval 'global $wp_rewrite; $wp_rewrite->init(); $wp_rewrite->flush_rules(true);'
```

## QA

```bash
cd qa && npm install && npx playwright install chromium
npm run alles
```

## Thema inpakken

```bash
../../scripts/bouw-zip.sh
```

Levert `react2u.zip` op, klaar om te uploaden via
**Weergave → Thema's → Nieuw thema → Thema uploaden**.

## Staging

```bash
scp react2u.zip           kas@srv1.sitejob.nl:/tmp/
scp deploy/seed.php        kas@srv1.sitejob.nl:/tmp/react2u_seed.php
scp deploy/staging-srv1.sh kas@srv1.sitejob.nl:/tmp/
ssh kas@srv1.sitejob.nl 'sudo bash /tmp/staging-srv1.sh'
```

Staat daarna op <https://staging.react2u.sitejob.nl> — met `noindex, nofollow`,
zodat hij nooit in Google komt, maar zonder wachtwoord zodat je hem kunt
doorsturen.

## Status

- [ ] Brief ingevuld
- [ ] Huisstijl in de tokens
- [ ] Fonts erin
- [ ] `inc/proof.php` gevuld
- [ ] Homepage ontworpen
- [ ] QA groen
- [ ] Staging live
- [ ] Opgeleverd
