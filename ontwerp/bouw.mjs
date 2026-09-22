/** Herhaalbare ontwerpbuild; dezelfde kwaliteitsbouwers als de site, geïsoleerd van WordPress. */
import { mkdir, readFile, writeFile, copyFile } from 'node:fs/promises';
import { execFileSync } from 'node:child_process';
import { fileURLToPath } from 'node:url';
const root = fileURLToPath(new URL('../', import.meta.url));
const stage = new URL('./.build/theme/react2u-ontwerp/', import.meta.url);
await mkdir(new URL('assets/', stage), {recursive:true});
for (const name of ['ontwerp.css','ontwerp.js']) await copyFile(new URL(name, import.meta.url), new URL(name, stage));
execFileSync('node', ['../../scripts/kwaliteit-assets.mjs','ontwerp/.build'], {cwd:root,stdio:'inherit'});
execFileSync('node', ['../../scripts/kwaliteit-beelden.mjs','ontwerp/media'], {cwd:root,stdio:'inherit'});
const manifest = JSON.parse(await readFile(new URL('assets/quality-assets.json',stage)));
for (const item of Object.values(manifest)) await copyFile(new URL(item.path,stage), new URL(item.path,import.meta.url));
await writeFile(new URL('assets/quality-assets.json',import.meta.url),JSON.stringify(manifest,null,2)+'\n');
execFileSync('php',['ontwerp/bouw.php'],{cwd:root,stdio:'inherit'});
