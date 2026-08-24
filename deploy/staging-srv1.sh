#!/usr/bin/env bash
#
# React2u — deelbare staging op srv1
# -----------------------------------
# Zet de site neer met een eigen systeemgebruiker, een eigen PHP-FPM-pool, een
# eigen database, een Let's Encrypt-certificaat en een Nederlandse WordPress met
# dit thema en de hardening-plugin erop.
#
# Let op: deze site wordt NIET door Ploi aangemaakt en verschijnt dus ook niet in
# de Ploi-interface. Het certificaat wordt door certbot verlengd (systemd-timer).
#
# Draaien:  scp ../react2u.zip            kas@srv1.sitejob.nl:/tmp/
#           scp ../react2u-hardening.zip  kas@srv1.sitejob.nl:/tmp/
#           scp ../react2u-mail.zip        kas@srv1.sitejob.nl:/tmp/
#           scp seed.php                     kas@srv1.sitejob.nl:/tmp/react2u_seed.php
#           scp staging-srv1.sh              kas@srv1.sitejob.nl:/tmp/
#           ssh kas@srv1.sitejob.nl 'sudo bash /tmp/staging-srv1.sh'
#
# Een andere hostnaam meegeven kan als eerste argument:
#           ssh kas@srv1.sitejob.nl 'sudo bash /tmp/staging-srv1.sh preview.staging.react2u.sitejob.nl react2u_preview'
#
# Het tweede argument is de databasenaam. Draai je twee hostnamen op dezelfde
# server, geef dan ook een andere database mee — anders delen ze hun inhoud en
# overschrijft de tweede de eerste.
#
# Vooraf:   een A-record voor die hostnaam -> het IP van srv1, en niet achter de
#           Cloudflare-proxy tijdens de eerste certificaataanvraag.
#
# ZOEKMACHINES: deze opstelling zet altijd noindex/nofollow, in nginx én in
# WordPress, ongeacht de hostnaam. Dat is bewust — de site draagt nog
# werkteksten en placeholders. Zie AANLEVERLIJST.md voordat dat eraf gaat.
#
# Het script is idempotent: opnieuw draaien is veilig.

set -euo pipefail

USER_NAME="react2u-acc"
SITE="${1:-staging.react2u.sitejob.nl}"
DB_NAME="${2:-react2u_staging}"
ROOT="/home/${USER_NAME}/${SITE}"
PUBLIC="${ROOT}/public"
DB_USER="react2u_wp"
PHP_VERSION="8.4"
THEME_SLUG="react2u"
THEME_ZIP="/tmp/react2u.zip"
PLUGIN_ZIP="/tmp/react2u-hardening.zip"
MAIL_ZIP="/tmp/react2u-mail.zip"
SEED="/tmp/react2u_seed.php"
SITE_TITLE="React2u"
ADMIN_USER="kas"
ADMIN_EMAIL="info@sitejob.nl"

log() { printf '\n\033[1;35m==> %s\033[0m\n' "$*"; }

if [[ $EUID -ne 0 ]]; then
	echo "Dit script heeft root nodig. Start het met: sudo bash $0" >&2
	exit 1
fi

if [[ ! -f "${THEME_ZIP}" ]]; then
	echo "Themabestand ${THEME_ZIP} ontbreekt." >&2
	echo "Upload het eerst:  scp react2u.zip kas@srv1.sitejob.nl:/tmp/" >&2
	exit 1
fi

WP="/home/${USER_NAME}/bin/wp"
run_wp() { sudo -u "${USER_NAME}" "${WP}" --path="${PUBLIC}" "$@"; }

# ---------------------------------------------------------------------------
log "1/9  Systeemgebruiker ${USER_NAME}"
# Eén gebruiker per project, zonder sudo. Zo kan een lek in deze site niet bij
# de bestanden of de .env van een ander project.
if ! getent passwd "${USER_NAME}" >/dev/null; then
	adduser --disabled-password --gecos "" --shell /bin/bash "${USER_NAME}"
else
	echo "bestaat al"
fi

mkdir -p "${PUBLIC}"
chown -R "${USER_NAME}:${USER_NAME}" "/home/${USER_NAME}"
chmod 755 "/home/${USER_NAME}"

# ---------------------------------------------------------------------------
log "2/9  PHP-FPM-pool"
# Eigen pool met een eigen socket: PHP van deze site draait als ${USER_NAME} en
# nergens anders bij. nginx (gebruiker ploi) mag de socket aanspreken.
cat > "/etc/php/${PHP_VERSION}/fpm/pool.d/${USER_NAME}.conf" <<CONF
[www-${USER_NAME}]
user = ${USER_NAME}
group = ${USER_NAME}
listen = /run/php/php${PHP_VERSION}-fpm-${USER_NAME}.sock
listen.owner = ploi
listen.group = ploi
listen.mode = 0600
pm = dynamic
pm.max_children = 5
pm.start_servers = 2
pm.min_spare_servers = 1
pm.max_spare_servers = 3
request_terminate_timeout = 60
php_admin_value[upload_max_filesize] = 64M
php_admin_value[post_max_size] = 64M
CONF

# Eerst testen, dan pas herladen: een reload raakt de FPM-pools van álle sites
# op deze machine. Een fout in dit bestand mag geen andere klant omleggen.
"php-fpm${PHP_VERSION}" -t
systemctl reload "php${PHP_VERSION}-fpm"
sleep 1
test -S "/run/php/php${PHP_VERSION}-fpm-${USER_NAME}.sock" && echo "socket staat er"

# ---------------------------------------------------------------------------
log "3/9  Database"
# Eigen database én eigen databasegebruiker, met rechten op alleen deze database.
if [[ -f "/root/.dbpass-${DB_NAME}" ]]; then
	DB_PASS="$(cat "/root/.dbpass-${DB_NAME}")"
	echo "bestaand wachtwoord hergebruikt"
else
	DB_PASS="$(openssl rand -base64 30 | tr -d '/+=' | head -c 32)"
	printf '%s' "${DB_PASS}" > "/root/.dbpass-${DB_NAME}"
	chmod 600 "/root/.dbpass-${DB_NAME}"
	echo "nieuw wachtwoord opgeslagen in /root/.dbpass-${DB_NAME}"
fi

mysql <<SQL
CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
ALTER USER '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';
FLUSH PRIVILEGES;
SQL
echo "database ${DB_NAME} klaar"

# ---------------------------------------------------------------------------
log "4/9  WordPress en wp-cli"
install -d -o "${USER_NAME}" -g "${USER_NAME}" "/home/${USER_NAME}/bin"
if [[ ! -x "${WP}" ]]; then
	curl -fsSL -o "${WP}" https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
	chmod +x "${WP}"
	chown "${USER_NAME}:${USER_NAME}" "${WP}"
fi

if [[ ! -f "${PUBLIC}/wp-settings.php" ]]; then
	run_wp core download --locale=nl_NL
fi

if [[ ! -f "${PUBLIC}/wp-config.php" ]]; then
	sudo -u "${USER_NAME}" "${WP}" config create \
		--path="${PUBLIC}" \
		--dbname="${DB_NAME}" --dbuser="${DB_USER}" --dbpass="${DB_PASS}" \
		--dbcharset=utf8mb4 --dbcollate=utf8mb4_unicode_ci \
		--locale=nl_NL \
		--extra-php <<'PHPEXTRA'
/* Staging: geen debug-uitvoer naar bezoekers, geen bestandsbewerker in beheer. */
define( 'WP_ENVIRONMENT_TYPE', 'staging' );
define( 'WP_DEBUG', false );
define( 'WP_DEBUG_DISPLAY', false );
define( 'WP_DEBUG_LOG', false );
define( 'DISALLOW_FILE_EDIT', true );
PHPEXTRA
	chmod 640 "${PUBLIC}/wp-config.php"
	chown "${USER_NAME}:${USER_NAME}" "${PUBLIC}/wp-config.php"
fi

# ---------------------------------------------------------------------------
log "5/9  nginx zonder SSL (nodig voor de eerste certificaataanvraag)"
mkdir -p "/etc/nginx/ploi/${SITE}/before" "/etc/nginx/ploi/${SITE}/server" "/etc/nginx/ploi/${SITE}/after"

cat > "/etc/nginx/ploi/${SITE}/server/acme.conf" <<'CONF'
# Laat de controle van Let's Encrypt door, ook als er ooit basic auth op komt.
location /.well-known/acme-challenge/ {
    allow all;
    auth_basic off;
    default_type "text/plain";
}
CONF

if [[ ! -f "/etc/letsencrypt/live/${SITE}/fullchain.pem" ]]; then
	cat > "/etc/nginx/sites-available/${SITE}" <<CONF
server {
    listen 80;
    listen [::]:80;
    server_name ${SITE};
    root ${PUBLIC};
    index index.php index.html;
    include /etc/nginx/ploi/${SITE}/server/*;
    location / { try_files \$uri \$uri/ /index.php?\$query_string; }
}
CONF
	ln -sf "/etc/nginx/sites-available/${SITE}" "/etc/nginx/sites-enabled/${SITE}"
	nginx -t && systemctl reload nginx

	log "6/9  Let's Encrypt-certificaat"
	certbot certonly --webroot -w "${PUBLIC}" -d "${SITE}" \
		--non-interactive --agree-tos -m "${ADMIN_EMAIL}" --no-eff-email
else
	log "6/9  Certificaat bestaat al"
fi

# ---------------------------------------------------------------------------
log "6b/9  Verlenging van het certificaat"
# Ploi verlengt certificaten vanaf zijn eigen platform, maar deze site kent Ploi
# niet. Zonder eigen timer verloopt het certificaat na negentig dagen stil.
# `certbot renew` doet alleen iets bij certificaten die binnen dertig dagen
# aflopen, dus deze timer zit de rest niet in de weg.
cat > /etc/systemd/system/certbot-renew.service <<'CONF'
[Unit]
Description=Let's Encrypt-certificaten verlengen
After=network-online.target

[Service]
Type=oneshot
ExecStart=/usr/bin/certbot renew --quiet --deploy-hook "systemctl reload nginx"
CONF

cat > /etc/systemd/system/certbot-renew.timer <<'CONF'
[Unit]
Description=Twee keer per dag controleren of een certificaat verlengd moet worden

[Timer]
OnCalendar=*-*-* 03,15:27:00
RandomizedDelaySec=1h
Persistent=true

[Install]
WantedBy=timers.target
CONF

systemctl daemon-reload
systemctl enable --now certbot-renew.timer

# ---------------------------------------------------------------------------
log "7/9  nginx met SSL"
cat > "/etc/nginx/ploi/${SITE}/before/ssl-redirect.conf" <<CONF
server {
     listen 80;
     listen [::]:80;
     server_name .${SITE};
     return 301 https://\$host\$request_uri;
}
CONF

mkdir -p /etc/nginx/ssl
cat > "/etc/nginx/ssl/${SITE}" <<CONF
listen 443 ssl;
listen [::]:443 ssl;
http2 on;
ssl_certificate /etc/letsencrypt/live/${SITE}/fullchain.pem;
ssl_certificate_key /etc/letsencrypt/live/${SITE}/privkey.pem;
CONF

cat > "/etc/nginx/sites-available/${SITE}" <<CONF
include /etc/nginx/ploi/${SITE}/before/*;

server {
    # Staging mag nooit in Google terechtkomen.
    add_header X-Robots-Tag "noindex, nofollow" always;

    root ${PUBLIC};
    server_name ${SITE};

    include /etc/nginx/ssl/${SITE};

    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_prefer_server_ciphers off;

    index index.php index.html;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    charset utf-8;
    client_max_body_size 64M;

    include /etc/nginx/ploi/${SITE}/server/*;

    location / { try_files \$uri \$uri/ /index.php?\$query_string; }

    access_log off;
    error_log /var/log/nginx/${SITE}-error.log error;

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { try_files \$uri /index.php?\$query_string; access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \\.php\$ {
        try_files \$uri /index.php =404;
        fastcgi_split_path_info ^(.+\\.php)(/.+)\$;
        fastcgi_pass unix:/run/php/php${PHP_VERSION}-fpm-${USER_NAME}.sock;
        fastcgi_buffers 32 32k;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT \$realpath_root;
        include fastcgi_params;
    }

    # Geen toegang tot verborgen bestanden, behalve de Let's Encrypt-controle.
    location ~ /\\.(?!well-known).* { deny all; }

    location = /wp-config.php { deny all; }
    location = /xmlrpc.php { deny all; }

    # readme.html en license.txt verraden de WordPress-versie. Ze worden door de
    # webserver geserveerd voordat PHP eraan te pas komt, dus dit moet in nginx.
    # Op de productiehost hoort dezelfde regel te staan — zie docs/harde-eisen.md.
    location ~* ^/(readme\.html|license\.txt|wp-admin/install\.php)$ { deny all; }
}

include /etc/nginx/ploi/${SITE}/after/*;
CONF

ln -sf "/etc/nginx/sites-available/${SITE}" "/etc/nginx/sites-enabled/${SITE}"
nginx -t && systemctl reload nginx

# ---------------------------------------------------------------------------
log "8/9  WordPress installeren"
if ! run_wp core is-installed 2>/dev/null; then
	if [[ -f "/root/.wppass-${DB_NAME}" ]]; then
		ADMIN_PASS="$(cat "/root/.wppass-${DB_NAME}")"
	else
		ADMIN_PASS="$(openssl rand -base64 24 | tr -d '/+=' | head -c 24)"
		printf '%s' "${ADMIN_PASS}" > "/root/.wppass-${DB_NAME}"
		chmod 600 "/root/.wppass-${DB_NAME}"
	fi

	run_wp core install \
		--url="https://${SITE}" \
		--title="${SITE_TITLE}" \
		--admin_user="${ADMIN_USER}" \
		--admin_password="${ADMIN_PASS}" \
		--admin_email="${ADMIN_EMAIL}" \
		--skip-email
	echo "beheerderswachtwoord staat in /root/.wppass-${DB_NAME}"
fi

# Staginginstellingen: niet indexeren, geen open registratie, nette permalinks.
run_wp option update blog_public 0
run_wp option update users_can_register 0
run_wp option update timezone_string "Europe/Amsterdam"
# `wp rewrite structure` start intern een subproces; dat mag op deze server niet.
# De optie rechtstreeks zetten kan wel; het verversen gebeurt hieronder in PHP.
run_wp option update permalink_structure '/%postname%/'

# ---------------------------------------------------------------------------
log "9/9  Thema en plugin installeren, en vullen"
run_wp theme install "${THEME_ZIP}" --force --activate

# De hardening-plugin is geen extraatje: hij schermt de gebruikerslijst af
# (/wp-json/wp/v2/users, ?author=1) en zet de beveiligingsheaders die WordPress
# zelf niet levert. Zonder die plugin haalt de site docs/harde-eisen.md niet,
# ook al ziet hij er verder goed uit.
if [[ -f "${PLUGIN_ZIP}" ]]; then
	run_wp plugin install "${PLUGIN_ZIP}" --force --activate
else
	echo "LET OP: ${PLUGIN_ZIP} ontbreekt — de site draait zonder hardening."
fi

# De mailplugin zet de verzending op SMTP. Zonder hem valt WordPress terug op
# PHP mail(); een uitnodiging voor een account komt dan meestal niet aan, en
# WordPress meldt dat nergens. De inloggegevens zelf staan hier niet in — die
# zet je erna met ./scripts/mail-instellen.sh <slug> --staging.
if [[ -f "${MAIL_ZIP}" ]]; then
	run_wp plugin install "${MAIL_ZIP}" --force --activate
else
	echo "LET OP: ${MAIL_ZIP} ontbreekt — deze site verstuurt via PHP mail()."
fi

if [[ -f "${SEED}" ]]; then
	cp "${SEED}" "${PUBLIC}/seed.php"
	chown "${USER_NAME}:${USER_NAME}" "${PUBLIC}/seed.php"
	run_wp eval-file "${PUBLIC}/seed.php"
	rm -f "${PUBLIC}/seed.php"
fi

run_wp eval 'global $wp_rewrite; $wp_rewrite->init(); $wp_rewrite->flush_rules( true );'
chown -R "${USER_NAME}:${USER_NAME}" "${ROOT}"

# ---------------------------------------------------------------------------
log "Controle"
curl -sI "https://${SITE}" | grep -iE '^HTTP|x-robots-tag|x-content-type|referrer-policy' || true
echo
echo "gebruikersenumeratie (hoort 401 of 403 te zijn):"
curl -s -o /dev/null -w '  /wp-json/wp/v2/users -> %{http_code}\n' "https://${SITE}/wp-json/wp/v2/users"
echo "actieve plugins:"
run_wp plugin list --status=active --field=name | sed 's/^/  /'
echo
echo "Klaar: https://${SITE}"
echo "Beheer: https://${SITE}/wp-admin  (gebruiker ${ADMIN_USER})"
echo "Wachtwoord: sudo cat /root/.wppass-${DB_NAME}"
