/**
 * Een hostnaam handmatig naar een IP wijzen, voor alle QA-scripts.
 *
 * Waarom dit bestaat: je zet een site op een verse hostnaam en wilt er meteen
 * de QA tegenaan gooien. De DNS is dan vaak nog niet overal doorgedrongen — en
 * erger, je eigen resolver heeft een negatieve uitkomst gecachet en houdt die
 * soms een uur vast. De site draait, jij kunt er alleen niet bij.
 *
 * Gebruik:
 *   QA_RESOLVE=staging.react2u.sitejob.nl=1.2.3.4 SITE_URL=https://... npm run alles
 *
 * Meerdere mogen, gescheiden door komma's. Zonder QA_RESOLVE gebeurt er niets.
 *
 * Let op: dit omzeilt alleen het opzoeken van het IP. Het certificaat wordt nog
 * gewoon tegen de hostnaam gecontroleerd, dus een fout certificaat valt hier
 * niet doorheen.
 */

import dns from 'node:dns';

/** @returns {Map<string,string>} hostnaam -> IP */
export function resolveKaart() {
	const rauw = (process.env.QA_RESOLVE || '').trim();
	const kaart = new Map();
	if (!rauw) return kaart;

	for (const paar of rauw.split(',')) {
		const [host, ip] = paar.split('=').map((v) => v && v.trim());
		if (host && ip) kaart.set(host.toLowerCase(), ip);
	}
	return kaart;
}

/** Chromium-argumenten, voor de scripts die Playwright gebruiken. */
export function browserArgs() {
	const kaart = resolveKaart();
	if (!kaart.size) return [];

	const regels = [...kaart].map(([host, ip]) => `MAP ${host} ${ip}`).join(',');
	return [`--host-resolver-rules=${regels}`];
}

/**
 * Voor de scripts die fetch() gebruiken: dns.lookup omleiden.
 *
 * fetch gaat via undici, undici via net.connect, en die valt terug op
 * dns.lookup. Eén patch dekt dus alles wat hier verbinding maakt.
 */
export function patchDnsLookup() {
	const kaart = resolveKaart();
	if (!kaart.size) return;

	const origineel = dns.lookup;
	dns.lookup = function (hostname, options, callback) {
		const ip = kaart.get(String(hostname).toLowerCase());
		if (!ip) return origineel.call(dns, hostname, options, callback);

		const cb = typeof options === 'function' ? options : callback;
		const alles = typeof options === 'object' && options !== null && options.all;
		if (alles) return process.nextTick(() => cb(null, [{ address: ip, family: 4 }]));
		return process.nextTick(() => cb(null, ip, 4));
	};
}
