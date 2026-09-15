# Werken aan deze website

Lees `CLAUDE.md` en de eigen QA- en deploymentafspraken.

## Git en GitHub — vaste werkwijze

Deze site heeft een eigen privé-repository bij SiteJob-Nederland. Lees
`../../docs/git-per-site.md`; het centrale register is `../../sites/repositories.json`.
Werk vanuit deze map en controleer bij de start `git status --short --branch`.
Gebruik voor nieuwe werkzaamheden een `codex/<onderwerp>`-branch.
Maak na ieder afgerond onderdeel een gerichte Nederlandse commit en push de
werkbranch. Hiervoor is geen nieuwe toestemmingsvraag nodig. Controleer de
remote commit; meld ontbrekende toegang of resterend werk expliciet.
Commit alleen beoordeelde bestanden; neem andermans lopende wijzigingen niet
blind mee. Een WIP-commit bewaart werk, maar bewijst geen geslaagde website-QA.
Geen force-push, stilzwijgende merge naar main of automatische deployment.
Installeer de meegeleverde hooks met `git config core.hooksPath .githooks`;
`gitleaks` is vereist. Geheimen, dumps, exports en runtime horen niet in Git.
