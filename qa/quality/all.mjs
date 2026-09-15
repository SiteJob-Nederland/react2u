/** Behoud projecttests en voer de gezamenlijke lat altijd uit. */
import { spawn } from 'node:child_process';
import { configuration } from './config.mjs';
const config = await configuration();
const env = { ...(config.legacyEnv || {}), ...process.env, SITE_URL: config.base.origin, EXPECT_INDEXABLE: config.indexable ? '1' : '0', EXPECT_STAGING_NOINDEX: config.indexable ? '0' : '1' };
for (const command of ['alles:bestaand', 'kwaliteit']) {
 const status = await new Promise(resolve => {
  const p = spawn(process.platform === 'win32' ? 'npm.cmd' : 'npm', ['run', command], { stdio: 'inherit', env });
  p.on('error', () => resolve(1)); p.on('exit', code => resolve(code ?? 1));
 });
 if (status) process.exitCode = 1;
}
