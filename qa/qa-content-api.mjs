/** Lokale schrijftest met fictieve inhoud. Werkt tegen native WordPress + eigen content-API.
 * SITE_URL=http://127.0.0.1:... WP_USER=... WP_APP_PASSWORD=... NOVA_SUITE=1 node qa-content-api.mjs
 * Credentials via secret runtime, nooit in git. Opruimen gebeurt ook na een mislukte test.
 */
import assert from 'node:assert/strict';
import { randomUUID } from 'node:crypto';
const base = new URL(process.env.SITE_URL || 'http://127.0.0.1:8103');
assert(['localhost','127.0.0.1','[::1]'].includes(base.hostname), 'Schrijftest uitsluitend op loopback.');
assert(process.env.WP_USER && process.env.WP_APP_PASSWORD, 'WP_USER en WP_APP_PASSWORD vereist.');
const token = Buffer.from(`${process.env.WP_USER}:${process.env.WP_APP_PASSWORD}`).toString('base64');
const ids = [];
const run = randomUUID();
async function request(path, body, auth = true, status = 200, method = body ? 'POST' : 'GET') {
  const url = new URL(path, base);
  assert.equal(url.origin, base.origin, 'Geen credentials of writes buiten de lokale oorsprong.');
  const res = await fetch(url, { method, redirect: 'manual', signal: AbortSignal.timeout(30000), headers: { ...(auth ? {Authorization:`Basic ${token}`} : {}), ...(body ? {'Content-Type':'application/json'} : {}) }, body: body ? JSON.stringify(body) : undefined });
  const text = await res.text(); let data; try {data=JSON.parse(text);} catch {data=text;}
  assert([status].flat().includes(res.status), `${method} ${url.pathname}: HTTP ${res.status}, verwacht ${status}.`);
  return data;
}
const api = (path, ...args) => request(`/wp-json/wp/v2/${path}`, ...args);
const read = (collection,id) => api(`${collection}/${id}?context=edit`);
const decode = text => text.replaceAll('&amp;','&').replaceAll('&#039;',"'").replaceAll('&quot;','"');
function seo(html, title, description, canonical) {
  assert.equal(decode(html.match(/<title>(.*?)<\/title>/s)?.[1] || ''),title);
  assert.equal((html.match(/<meta name="description"/g)||[]).length,1);
  assert.equal(decode(html.match(/<meta name="description" content="([^"]*)"/)?.[1]||''),description);
  assert.equal((html.match(/<link rel="canonical"/g)||[]).length,1);
  assert.equal(decode(html.match(/<link rel="canonical" href="([^"]*)"/)?.[1]||''),canonical);
}
try {
  for (const collection of ['posts','pages','kennisbank']) {
    const schema = await api(collection, undefined, false, 200, 'OPTIONS');
    for (const field of ['seo_title_text','seo_meta_description_text','seo_canonical_url']) assert(schema.schema.properties.meta.properties[field].description, `${collection}: ${field} discoverable`);
    const initial = { seo_title_text:`Fictieve titel ${run}`,seo_meta_description_text:`Fictieve beschrijving ${run}`,seo_canonical_url:`${base.origin}/qa-canonical-${run}/` };
    const created = await api(collection,{ title:`Fictieve test ${run}`,excerpt:'Fictieve introductie.',content:'<p>Fictieve inhoud voor de API-controle.</p>',status:'publish',meta:initial },true,201);
    ids.push([collection,created.id]);
    const id=created.id;
    seo(await request(created.link,undefined,false),initial.seo_title_text,initial.seo_meta_description_text,initial.seo_canonical_url);
    await api(`${collection}/${id}`,{title:'Fictieve gewijzigde titel'});
    for (const [key,value] of Object.entries(initial)) assert.equal((await read(collection,id)).meta[key],value,'Weggelaten veld behouden');
    await api(`${collection}/${id}`,{meta:{seo_title_text:'Verboden'}},false,[401,403]);
    await api(`${collection}/${id}`,{meta:{seo_canonical_url:'javascript:alert(1)'}},true,400);
    assert.equal((await read(collection,id)).meta.seo_canonical_url,initial.seo_canonical_url);
    if (process.env.NOVA_SUITE==='1') {
      const title=`Suite titel ${run}`,description=`Suite omschrijving ${run}`;
      await api(`${collection}/${id}`,{meta_all:{meta_title:title,meta_description:description}});
      seo(await request(created.link,undefined,false),title,description,initial.seo_canonical_url);
      await api(`${collection}/${id}`,{meta_all_flat:{meta_title:'',meta_description:'',_yoast_wpseo_canonical:''}});
    } else {
      await api(`${collection}/${id}`,{meta:{seo_title_text:'',seo_meta_description_text:'',seo_canonical_url:''}});
    }
    const cleared=await read(collection,id);
    for (const key of Object.keys(initial)) assert.equal(cleared.meta[key],'','Expliciet leeg veld wist override');
    const html=await request(created.link,undefined,false);
    assert(html.includes('Fictieve gewijzigde titel'));
    assert.equal(decode(html.match(/<link rel="canonical" href="([^"]*)"/)?.[1]||''),created.link);
    console.log(`PASS ${collection}: schema, frontend, gedeeltelijke updates, afwijzing en wissen${process.env.NOVA_SUITE==='1'?' via NOVA Suite':''}.`);
  }
} finally {
  for (const [collection,id] of ids.reverse()) {
    try { await api(`${collection}/${id}?force=true`,undefined,true,200,'DELETE'); console.log(`Opgeruimd ${collection}/${id}`); }
    catch(error) { console.error(`Opruimen mislukt ${collection}/${id}: ${error.message}`); process.exitCode=1; }
  }
}
