/** Alleen lokale ontwerp-QA. Geen formulieren, externe verzoeken of klantdata. */
import { dependency } from './quality/config.mjs';
const { chromium } = dependency('playwright');
const axe = dependency('axe-core');
import { mkdir, writeFile, readdir, readFile } from 'node:fs/promises';
import assert from 'node:assert/strict';

const origin = 'http://127.0.0.1:8133';
const files = (await readdir(new URL('../ontwerp/', import.meta.url))).filter(file => file.endsWith('.html')).sort();
const seoRoutes = JSON.parse(await readFile(new URL('../theme/react2u/inc/seo-routes.json', import.meta.url), 'utf8'));
const widths = [320, 390, 768, 1024, 1280, 1440];
const output = new URL('./uitvoer/ontwerp/', import.meta.url);
await mkdir(output, { recursive: true });
const browser = await chromium.launch();
const report = { date: new Date().toISOString(), origin, pages: [], failures: [], incomplete: [], interactions: [] };
const context = await browser.newContext();
const fail = text => report.failures.push(text);
await context.route('**/*', route => {
  const request = route.request();
  if (!request.url().startsWith(origin + '/') || !['GET', 'HEAD'].includes(request.method())) {
    fail(`Onverwacht netwerkverzoek: ${request.method()} ${request.url()}`);
    return route.abort();
  }
  return route.continue();
});
try {
  const page = await context.newPage();
  page.on('pageerror', error => fail(`JavaScript: ${error.message}`));
  page.on('console', message => { if (message.type() === 'error') fail(`Console: ${message.text()}`); });
  for (const file of files) {
    for (const width of widths) {
      await page.setViewportSize({ width, height: 900 });
      const response = await page.goto(`${origin}/${file}`);
      await page.evaluate(() => document.fonts.ready);
      assert.equal(response.status(), 200);
      assert.match(response.headers()['x-robots-tag'], /noindex/);
      const slug = file === 'index.html' ? 'home' : file.replace(/\.html$/, '');
      assert.equal(await page.title(), seoRoutes[slug]?.title, `${file}: SEO-titel wijkt af van de WordPress-bron`);
      assert.equal(await page.locator('meta[name=description]').getAttribute('content'), seoRoutes[slug]?.description, `${file}: beschrijving wijkt af van de WordPress-bron`);
      assert.match(await page.locator('meta[name=robots]').getAttribute('content'), /noindex/, `${file}: ontwerp mag niet indexeerbaar zijn`);
      for (const img of await page.locator('img').all()) {
        await img.scrollIntoViewIfNeeded();
        await img.evaluate(image => image.decode());
      }
      await page.evaluate(() => scrollTo(0,0));
      await page.waitForTimeout(450);
      const state = await page.evaluate(() => ({
        h1: document.querySelectorAll('h1').length,
        fonts: [...document.fonts].every(font => font.status === 'loaded'),
        headingFont: getComputedStyle(document.querySelector('h1')).fontFamily,
        bodyFont: getComputedStyle(document.body).fontFamily,
        main: document.querySelectorAll('main').length,
        overflow: document.documentElement.scrollWidth > innerWidth,
        images: [...document.images].filter(i => !i.complete || !i.naturalWidth).length,
        navVisible: !!document.querySelector('.nav, .audience-choices')?.getClientRects().length,
        toggleVisible: !!document.querySelector('.menu-toggle')?.getClientRects().length,
        description: !!document.querySelector('meta[name=description]')?.content,
        hashes: [...document.querySelectorAll('a[href^="#"]')].filter(a => !document.getElementById(a.hash.slice(1))).map(a => a.hash),
      }));
      const errors = [];
      if (!state.fonts || !state.headingFont.includes('Figtree') || !state.bodyFont.includes('DM Sans')) errors.push('Merkfonts niet correct geladen');
      if (state.h1 !== 1 || state.main !== 1) errors.push('Niet precies één H1/main');
      if (state.overflow) errors.push('Horizontale overflow');
      if (state.images) errors.push('Afbeelding niet geladen');
      if (!state.navVisible && !state.toggleVisible) errors.push('Navigatie onbereikbaar');
      if (!state.description) errors.push('Beschrijving ontbreekt');
      if (state.hashes.length) errors.push(`Kapotte ankers: ${state.hashes}`);
      errors.forEach(error => fail(`${file} @ ${width}: ${error}`));
      if ([390, 1440].includes(width)) {
        // Playwright voert deze alleen-lezen audit in de testcontext uit; de CSP blijft actief.
        await page.evaluate(axe.source);
        const result = await page.evaluate(() => axe.run(document, { runOnly: { type: 'tag', values: ['wcag2a','wcag2aa','wcag21a','wcag21aa'] } }));
        result.violations.forEach(v => fail(`${file} @ ${width}: axe ${v.id} (${v.nodes.map(n => n.target).join(', ')})`));
        result.incomplete.forEach(v => report.incomplete.push({ file, width, id: v.id, targets: v.nodes.map(n => n.target) }));
        await page.screenshot({ path: new URL(`${file.replace('.html','')}-${width}.png`, output).pathname, fullPage: true });
      }
      report.pages.push({ file, width, errors });
    }
  }
  await page.setViewportSize({ width:390, height:844 });
  await page.goto(`${origin}/index.html`);
  await page.keyboard.press('Tab');
  assert.equal(await page.evaluate(() => document.activeElement.className), 'skip');
  await page.keyboard.press('Enter');
  assert.equal(await page.evaluate(() => document.activeElement.id), 'main');
  report.interactions.push('Skiplink verplaatst focus naar main');

  assert.equal(await page.locator('.audience-choice').count(), 2);
  assert.equal(await page.locator('main section').count(), 1);
  assert.equal(await page.locator('.home-services-section, .moments-section, .manifesto-section').count(), 0);
  report.interactions.push('Homepage toont alleen de twee doelgroepkeuzes');

  await page.goto(`${origin}/werkgevers.html`);
  const toggle = page.locator('.menu-toggle');
  await toggle.focus();
  await page.keyboard.press('Enter');
  assert.equal(await toggle.getAttribute('aria-expanded'), 'true');
  await page.keyboard.press('Tab');
  assert.equal(await page.evaluate(() => document.activeElement.textContent), 'Werkgevers');
  await page.keyboard.press('Escape');
  assert.equal(await toggle.getAttribute('aria-expanded'), 'false');
  assert.equal(await page.evaluate(() => document.activeElement.className), 'menu-toggle');
  report.interactions.push('Mobiel menu werkt met Enter, Tab en Escape; focus keert terug');

  await page.goto(`${origin}/index.html`);
  await page.locator('.audience-choice-employee').click();
  assert.equal(new URL(page.url()).pathname, '/werknemers.html');
  await page.getByRole('link', { name:'Waar kunnen we je bij helpen?', exact:true }).click();
  assert.equal(new URL(page.url()).hash, '#hulp');
  await page.getByText('Wat doet React2u voor werknemers?', { exact:true }).click();
  assert.equal(await page.locator('details').first().getAttribute('open'), '');
  await page.getByRole('link', { name:'Stel je vraag', exact:true }).first().click();
  assert.equal(new URL(page.url()).hash, '#werknemer');
  report.interactions.push('Werknemersroute, hulpanker, FAQ en eigen contactanker werken');

  await page.goto(`${origin}/index.html`);
  await page.locator('.audience-choice-employer').click();
  await page.getByRole('link', { name:'Laten we kennismaken', exact:true }).click();
  assert.equal(new URL(page.url()).hash, '#werkgever');
  report.interactions.push('Werkgeversroute en eigen contactanker werken');

  for (const reducedMotion of ['no-preference','reduce']) {
    await page.emulateMedia({ reducedMotion });
    assert.equal(await page.evaluate(() => document.getAnimations().length), 0);
  }
  report.interactions.push('Reduced-motionwisseling stopt alle animaties');

  const noJs = await browser.newContext({ javaScriptEnabled:false, viewport:{width:390,height:844} });
  const noJsPage = await noJs.newPage();
  for (const file of files) {
    await noJsPage.goto(`${origin}/${file}`);
    assert(await noJsPage.getByRole('navigation',{name:file === 'index.html' ? 'Kies jouw route' : 'Hoofdnavigatie'}).isVisible());
    assert(await noJsPage.locator('h1').isVisible());
  }
  await noJs.close();
  report.interactions.push('Alle veertien pagina’s en navigatie bruikbaar zonder JavaScript');

  await page.goto(`${origin}/index.html`);
  const audienceUrls = await page.locator('.audience-choice').evaluateAll(links => links.map(link => new URL(link.href).pathname));
  assert.deepEqual(audienceUrls, ['/werkgevers.html', '/werknemers.html']);
  const audienceMeta = {
    'werkgevers.html': ['Arbodienst voor werkgevers', 'React2u ondersteunt werkgevers'],
    'werknemers.html': ['Verzuimbegeleiding voor werknemers', 'Ben je ziek of bezig met terugkeer naar werk?'],
    'blog.html': ['Blog over verzuim', 'Verken de onderwerpen'],
    'kennisbank.html': ['Kennisbank over verzuim', 'Vind uitleg van React2u'],
  };
  for (const [file, [title, description]] of Object.entries(audienceMeta)) {
    await page.goto(`${origin}/${file}`);
    assert((await page.title()).startsWith(title));
    assert((await page.locator('meta[name=description]').getAttribute('content')).startsWith(description));
  }
  report.interactions.push('Doelgroeppagina’s hebben eigen routes en beschrijvingen');

  await page.emulateMedia({ reducedMotion:'reduce' });
  await page.goto(`${origin}/werkgevers.html`);
  const track = page.locator('.moments-track');
  assert.equal(await page.locator('[data-slider]').count(), 1);
  await page.locator('.next').click();
  await page.waitForFunction(() => document.querySelector('.moments-track').scrollLeft > 100);
  await page.locator('.next').click();
  await page.waitForFunction(() => document.querySelector('.next').disabled);
  await track.focus();
  await page.keyboard.press('ArrowLeft');
  await page.waitForFunction(() => !document.querySelector('.next').disabled);
  await page.keyboard.press('ArrowLeft');
  await page.waitForFunction(() => document.querySelector('.previous').disabled);
  report.interactions.push('Werkgeversslider: klikken, toetsenbord en beide eindpunten');

  for (const width of [390,1440]) {
    await page.setViewportSize({width,height:900});
    await page.emulateMedia({reducedMotion:'no-preference'});
    await page.goto(`${origin}/werkgevers.html`);
    assert(await page.locator('[data-anim]').count() > 0);
    // Observeer echte animationstart-events terwijl kaarten het scherm in komen.
    await page.evaluate(() => {
      window.motionEvents = [];
      document.addEventListener('animationstart', e => window.motionEvents.push(e.target));
    });
    await page.locator('.service').first().scrollIntoViewIfNeeded();
    await page.waitForFunction(() => window.motionEvents.length > 0);
    await page.emulateMedia({reducedMotion:'reduce'});
    assert.equal(await page.evaluate(() => document.getAnimations().length),0);
    await page.locator('.footer').scrollIntoViewIfNeeded();
    assert(await page.locator('.footer').isVisible());
    await page.locator('.service').first().scrollIntoViewIfNeeded();
    assert.equal(await page.evaluate(() => document.getAnimations().length),0);

    await page.goto(`${origin}/werkgevers.html#diensten`);
    await page.reload();
    assert.equal(await page.locator('[data-anim]').count(),0);
    await page.emulateMedia({media:'print'});
    assert.equal(await page.evaluate(() => document.getAnimations().length),0);
    assert(await page.locator('#diensten').isVisible());
    await page.emulateMedia({media:'screen',reducedMotion:'no-preference'});
  }
  report.interactions.push('390/1440: werkelijke scrollentree, live reduced-motionstop, footer, terugscrollen, hash en print');

  for (const scenario of ['script-geblokkeerd','zonder-observer','toetsenbord']) {
    const fallback = await browser.newContext({viewport:{width:390,height:844}});
    const fp = await fallback.newPage();
    if (scenario === 'script-geblokkeerd') await fp.route(/ontwerp.*\.js$/, route => route.abort());
    if (scenario === 'zonder-observer') await fp.addInitScript(() => { delete window.IntersectionObserver; });
    await fp.goto(`${origin}/werkgevers.html`);
    if (scenario === 'toetsenbord') await fp.keyboard.press('Tab');
    await fp.locator('.service').first().scrollIntoViewIfNeeded();
    const visible = await fp.locator('.service').first().evaluate(el => ({opacity:getComputedStyle(el).opacity,display:getComputedStyle(el).display}));
    assert.equal(visible.opacity,'1');
    assert.notEqual(visible.display,'none');
    assert.equal(await fp.evaluate(() => document.getAnimations().length),0);
    await fp.locator('.footer').scrollIntoViewIfNeeded();
    assert(await fp.locator('.footer').isVisible());
    await fallback.close();
  }
  report.interactions.push('Zichtbare kaarten/footer bij geblokkeerd script, ontbrekende observer en toetsenbordbediening');

  const blocked = await context.request.get(`${origin}/bouw.php`);
  assert.equal(blocked.status(),404);
  const links = new Set();
  for (const file of files) {
    await page.goto(`${origin}/${file}`);
    for (const href of await page.locator('a').evaluateAll(links => links.map(a => a.href))) {
      if (href.startsWith(origin + '/')) links.add(href);
    }
  }
  for (const href of links) {
    const response = await context.request.get(href);
    if (response.status() !== 200) fail(`Interne link: ${response.status()} ${href}`);
    if (new URL(href).hash) {
      await page.goto(href);
      if (!await page.locator(new URL(href).hash).count()) fail(`Interne ankerbestemming ontbreekt: ${href}`);
    }
  }
  report.interactions.push(`${links.size} unieke interne links/ankers gecontroleerd; broncode wordt niet geserveerd`);
} catch (error) {
  fail(error.stack);
} finally {
  await browser.close();
  await writeFile(new URL('rapport.json', output), JSON.stringify(report,null,2)+'\n');
}
console.log(JSON.stringify(report,null,2));
process.exitCode = report.failures.length ? 1 : 0;
