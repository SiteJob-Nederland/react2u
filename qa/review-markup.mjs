/**
 * Review-markup over het eigen bedrijf herkennen in JSON-LD.
 *
 * Google staat geen beoordelingen toe die van een ander platform zijn
 * overgenomen, en een organisatie die beoordelingen over zichzelf publiceert
 * (ook via een widget) krijgt er geen sterren voor. Daarom hoort er geen
 * aggregateRating of Review over het bedrijf in de gestructureerde data. Zie
 * docs/review-markup-2026-10-12.md in de werkmap.
 *
 * Een beoordeling van iets wat de site zelf aanbiedt en waarover bezoekers op
 * deze site oordelen, mag wel: de typen hieronder, met hun gangbare subtypen.
 * Elk ander onderwerp, ook Organization, LocalBusiness, Service of WebPage,
 * telt als het eigen bedrijf.
 */
const BEOORDEELBAAR = new Set([
	'Product', 'ProductGroup', 'ProductModel', 'IndividualProduct', 'SomeProducts', 'Vehicle', 'Car', 'Motorcycle',
	'SoftwareApplication', 'MobileApplication', 'WebApplication', 'VideoGame', 'Game',
	'Book', 'Audiobook', 'Course', 'CourseInstance', 'HowTo', 'Recipe', 'Movie',
	'CreativeWorkSeason', 'CreativeWorkSeries', 'Episode', 'TVSeason', 'TVSeries', 'TVEpisode', 'PodcastSeries', 'PodcastEpisode',
	'MediaObject', 'AudioObject', 'VideoObject', 'MusicPlaylist', 'MusicAlbum', 'MusicRecording',
	'Event', 'BusinessEvent', 'EducationEvent', 'ExhibitionEvent', 'Festival', 'MusicEvent', 'SocialEvent', 'SportsEvent', 'TheaterEvent',
]);

const BEOORDELING = new Set(['Review', 'UserReview', 'CriticReview', 'AggregateRating']);
const VELDEN = ['aggregateRating', 'review', 'reviews'];

const typen = (knoop) => [knoop?.['@type']].flat().filter((type) => typeof type === 'string');

/** Geeft per gevonden beoordeling over het eigen bedrijf één regel tekst terug. */
export function reviewMarkupProblemen(grafen) {
	const knopen = [];
	const losse = [];
	const loop = (waarde, onderBeoordelingsveld = false) => {
		if (Array.isArray(waarde)) {
			for (const item of waarde) loop(item, onderBeoordelingsveld);
			return;
		}
		if (!waarde || typeof waarde !== 'object') return;
		knopen.push(waarde);
		// Een Review onder 'review' van een knoop beoordeelt die knoop; dat
		// oordeel volgt uit de ouder. Alleen een losse Review heeft een eigen onderwerp.
		if (!onderBeoordelingsveld && typen(waarde).some((type) => BEOORDELING.has(type))) losse.push(waarde);
		for (const [sleutel, kind] of Object.entries(waarde)) loop(kind, VELDEN.includes(sleutel));
	};
	for (const graaf of grafen) loop(graaf);

	const perId = new Map(knopen.filter((k) => k['@id'] && typen(k).length).map((k) => [k['@id'], k]));
	const onderwerp = (knoop) => (knoop?.['@id'] && !typen(knoop).length ? perId.get(knoop['@id']) ?? knoop : knoop);
	const magBeoordeeld = (knoop) => typen(onderwerp(knoop)).some((type) => BEOORDEELBAAR.has(type));
	const naam = (knoop) => typen(onderwerp(knoop)).join('/') || 'een onbekend onderwerp';

	const problemen = new Set();
	for (const knoop of knopen) {
		for (const veld of VELDEN) {
			if (knoop[veld] !== undefined && !magBeoordeeld(knoop)) problemen.add(`${veld} op ${naam(knoop)}`);
		}
	}
	for (const knoop of losse) {
		if (!magBeoordeeld(knoop.itemReviewed)) problemen.add(`${typen(knoop).join('/')} over ${naam(knoop.itemReviewed)}`);
	}
	return [...problemen];
}
