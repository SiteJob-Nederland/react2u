/** Alle checks uitvoeren, ook als één faalt. Uitvoer bevat geen credentials. */
import { spawn } from 'node:child_process';
import { readFile } from 'node:fs/promises';
import path from 'node:path';
import { existsSync } from 'node:fs';
import { configuration, qaDir, save, sourceHash, manualFailures } from './config.mjs';
const config = await configuration();
const mode = process.argv[2] || 'all';
if (!['all', 'release', 'surface', 'performance', 'interaction'].includes(mode)) throw Error('Onbekende kwaliteitsmodus.');
const scripts = ['surface', 'performance', 'interaction'].includes(mode) ? [mode] : ['surface', 'interaction', 'performance'];
const results = [];
if (config.croRequired === true && ['all', 'release'].includes(mode)) scripts.push('cro');
if (existsSync(path.join(qaDir, '../brief/06-opties.json')) && ['all', 'release'].includes(mode)) scripts.push('opties');
for (const script of scripts) {
  const status = await new Promise(resolve => {
    const file = ['cro', 'opties'].includes(script) ? path.join(qaDir, 'qa-' + script + '.mjs') : path.join(qaDir, 'quality', script + '.mjs');
    const child = spawn(process.execPath, [file], { stdio: 'inherit', env: process.env });
    child.on('error', error => { console.error(error.message); resolve(1); });
    child.on('exit', code => resolve(code ?? 1));
  });
  results.push({ script, status });
}
if (mode === 'release') {
  // Geen stilzwijgend akkoord: bewijs is site-/versiegebonden en maximaal zeven dagen oud.
  try {
    const manual = JSON.parse(await readFile(path.join(qaDir, 'quality.manual.json'), 'utf8'));
    const missing = manualFailures(manual, config);
    if (missing.length) throw Error(missing.join('\n'));
    results.push({ script: 'manual', status: 0 });
  } catch (error) { console.error(error.message); results.push({ script: 'manual', status: 1 }); }
}
const sameSource = await sourceHash() === config.sourceHash;
if (!sameSource) console.error('Broncode wijzigde tijdens de controle; herhaal de acceptatie.');
results.push({ script: 'source-version', status: sameSource ? 0 : 1 });
await save(config, 'summary', { mode, passed: results.every(r => r.status === 0), results, manualRequiredForRelease: mode !== 'release' });
process.exitCode = results.some(r => r.status !== 0) ? 1 : 0;
