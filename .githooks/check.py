#!/usr/bin/env python3
"""Controleer de staged inhoud; schrijf nooit gevonden geheimen naar de uitvoer."""
import pathlib
import re
import shutil
import subprocess
import sys


def forbidden(name):
    path = pathlib.PurePosixPath(name)
    base = path.name.lower()
    if base.startswith('.env') and not base.endswith(('.example', '.sample')):
        return True
    if base in {'wp-config.php', '.htpasswd', 'id_rsa', 'id_ed25519'}:
        return True
    if re.search(r'\.(sql(?:\.(gz|bz2|xz))?|zip|tar(?:\.gz)?|tgz|pem|key|p12|pfx)$', base):
        return True
    return bool(set(path.parts) & {'node_modules', 'backups', 'exports', 'uitvoer', '.appdeploy'})


def main():
    if not shutil.which('gitleaks'):
        sys.exit('Installeer gitleaks voordat je commit; de geheimencontrole is verplicht.')
    names = subprocess.check_output([
        'git', 'diff', '--cached', '--name-only', '--diff-filter=ACMR', '-z'
    ]).decode().split('\0')
    errors = []
    for name in filter(None, names):
        if forbidden(name):
            errors.append(f'Niet in Git toegestaan: {name}')
        size = int(subprocess.check_output(['git', 'cat-file', '-s', ':' + name]))
        if size > 50 * 1024 * 1024:
            errors.append(f'Bestand groter dan 50 MiB: {name}; gebruik beheerde assetopslag.')
    if errors:
        sys.exit('\n'.join(errors))
    result = subprocess.run(['gitleaks', 'git', '--pre-commit', '--staged',
                             '--redact', '--no-banner', '--log-level', 'error'])
    if result.returncode:
        sys.exit('Geheimencontrole mislukt. Onderzoek lokaal met gitleaks --redact; commit niet omzeilen.')


if __name__ == '__main__':
    main()
