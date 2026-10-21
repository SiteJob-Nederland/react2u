import test from 'node:test';
import assert from 'node:assert/strict';
import { reviewMarkupProblemen } from './review-markup.mjs';

const score = { '@type': 'AggregateRating', ratingValue: '4.6', bestRating: '5', ratingCount: 21 };
const organisatie = (extra = {}) => ({ '@type': 'Organization', '@id': 'https://voorbeeld.test/#organization', name: 'Voorbeeld', ...extra });
const graaf = (...knopen) => [{ '@context': 'https://schema.org', '@graph': knopen }];

test('organisatie zonder beoordelingen is in orde', () => assert.deepEqual(reviewMarkupProblemen(graaf(organisatie(), { '@type': 'WebSite', publisher: { '@id': 'https://voorbeeld.test/#organization' } })), []));
test('aggregateRating op de organisatie is een fout', () => assert.deepEqual(reviewMarkupProblemen(graaf(organisatie({ aggregateRating: score }))), ['aggregateRating op Organization']));
test('ook op een LocalBusiness-subtype of een dienst', () => assert.equal(reviewMarkupProblemen(graaf({ '@type': ['LegalService', 'Attorney'], aggregateRating: score }, { '@type': 'Service', review: [{ '@type': 'Review' }] })).length, 2));
test('losse Review die naar de organisatie verwijst is een fout', () => assert.deepEqual(reviewMarkupProblemen(graaf(organisatie(), { '@type': 'Review', itemReviewed: { '@id': 'https://voorbeeld.test/#organization' } })), ['Review over Organization']));
test('losse beoordeling zonder onderwerp is een fout', () => assert.deepEqual(reviewMarkupProblemen([{ '@context': 'https://schema.org', ...score }]), ['AggregateRating over een onbekend onderwerp']));
test('productbeoordelingen van de eigen site mogen', () => assert.deepEqual(reviewMarkupProblemen(graaf({ '@type': 'Product', name: 'Lamp', aggregateRating: score, review: [{ '@type': 'Review', reviewRating: { '@type': 'Rating', ratingValue: '5' } }] })), []));
test('losse Review over een product via @id mag', () => assert.deepEqual(reviewMarkupProblemen(graaf({ '@type': 'Product', '@id': '#lamp' }, { '@type': 'Review', itemReviewed: { '@id': '#lamp' } })), []));
test('genest in een andere knoop telt ook', () => assert.deepEqual(reviewMarkupProblemen(graaf({ '@type': 'WebPage', publisher: organisatie({ aggregateRating: score }) })), ['aggregateRating op Organization']));
