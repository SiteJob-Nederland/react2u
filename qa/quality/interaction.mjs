/** Toetsenbord en zichtbaarheid op tussenmaten; nooit formulieren verzenden. */
import { configuration, dependency, save } from './config.mjs';
import { browserArgs } from '../qa-resolve.mjs';
const { chromium } = dependency('playwright');
const config = await configuration(), failures = [], results = [];
const browser = await chromium.launch({ args: browserArgs() });
try {
 for (const width of [390, 768, 900, 1100, 1280, 1440]) {
  const context = await browser.newContext({ viewport: { width, height: 900 }, reducedMotion: 'reduce', ...(process.env.QA_HTTP_PASSWORD ? { httpCredentials: { username: process.env.QA_HTTP_USER || 'sitejob', password: process.env.QA_HTTP_PASSWORD, origin: config.base.origin } } : {}) });
  await context.route('**/*', r => ['GET', 'HEAD', 'OPTIONS'].includes(r.request().method()) ? r.continue() : r.abort());
  const page = await context.newPage();
  try {
   await page.goto(config.base.href, { waitUntil: 'domcontentloaded' });
   await page.keyboard.press('Tab');
   const skip = page.locator('.skip-link, a[href="#main"]').first();
   if (!await skip.count()) throw Error('Skiplink ontbreekt.');
   await skip.focus();
   if (!await skip.isVisible()) throw Error('Skiplink is niet zichtbaar bij focus.');
   const focus = await skip.evaluate(e => { const s = getComputedStyle(e); return s.outlineStyle !== 'none' && parseFloat(s.outlineWidth) > 0 || s.boxShadow !== 'none'; });
   if (!focus) throw Error('Skiplink mist zichtbare focusmarkering.');
   await page.keyboard.press('Enter');
   if (!await page.evaluate(() => !!document.querySelector('main:focus, main :focus'))) throw Error('Skiplink verplaatst de focus niet naar main.');
   const toggle = page.locator('.menu-button, [data-menu-toggle], .nav-toggle').first();
   if (await toggle.count() && await toggle.isVisible()) {
    await toggle.focus(); await page.keyboard.press('Enter');
    if (await toggle.getAttribute('aria-expanded') !== 'true') throw Error('Menu opent niet via toetsenbord.');
    await page.keyboard.press('Tab');
    const inside = await page.evaluate(() => !!document.activeElement?.closest('nav, [role="navigation"], .mobile-menu, [data-nav-panel]'));
    if (!inside) throw Error('Focus bereikt het geopende menu niet.');
    await page.keyboard.press('Escape');
    if (await toggle.getAttribute('aria-expanded') !== 'false') throw Error('Escape sluit het menu niet.');
    if (!await toggle.evaluate(e => e === document.activeElement)) throw Error('Focus keert na Escape niet terug naar de menuknop.');
   } else if (!await page.locator('header nav a:visible').count()) throw Error('Geen bereikbare navigatie op deze breedte.');
   if (await page.evaluate(() => document.documentElement.scrollWidth > innerWidth + 1)) throw Error('Horizontale overflow.');
   results.push({ width, keyboard: true });
  } catch (error) { failures.push({ width, message: error.message }); }
  finally { await context.close(); }
 }
 const context = await browser.newContext({ javaScriptEnabled: false, viewport: { width: 390, height: 900 }, ...(process.env.QA_HTTP_PASSWORD ? { httpCredentials: { username: process.env.QA_HTTP_USER || 'sitejob', password: process.env.QA_HTTP_PASSWORD, origin: config.base.origin } } : {}) });
 const page = await context.newPage();
 await page.goto(config.base.href, { waitUntil: 'domcontentloaded' });
 if (!await page.locator('main h1').isVisible()) failures.push({ message: 'Hoofdinformatie onzichtbaar zonder JavaScript.' });
 const hidden = await page.locator('main [data-anim], main [data-anim-op]').evaluateAll(items => items.filter(e => getComputedStyle(e).opacity === '0' || getComputedStyle(e).visibility === 'hidden').length);
 if (hidden) failures.push({ message: `${hidden} inhoudselementen onzichtbaar zonder JavaScript.` });
 await context.close();
} catch (error) { failures.push({ message: error.message }); }
finally { await browser.close(); }
await save(config, 'interaction', { passed: !failures.length, results, failures, limitation: 'Dit is toetsenbord- en DOM-bewijs, geen volledige screenreaderbeoordeling.' });
for (const failure of failures) console.error(JSON.stringify(failure));
console.log(`Interactie: ${results.length}/6 schermbreedtes; ${failures.length} fouten.`);
process.exitCode = failures.length ? 1 : 0;
