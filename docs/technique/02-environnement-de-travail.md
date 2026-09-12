# Configuration de l'environnement de travail

## 1. Outils

| Outil | Usage |
|---|---|
| Visual Studio Code | Éditeur (extensions : PHP Intelephense, Docker, GitLens, Mermaid preview) |
| Docker Desktop | Exécution de l'application et des bases de données en conteneurs |
| Git + GitHub | Versionnement, dépôt public : https://github.com/Carlitaalt/vite-et-gourmand |
| Composer | Dépendances PHP (PHPMailer) et autoload PSR-4 des classes |
| phpMyAdmin (conteneur) | Consultation de la base MySQL en local |
| Mailpit (conteneur) | Réception des e-mails envoyés en local, sans risque d'envoi réel |
| Figma | Wireframes, mockups et charte graphique |
| Notion | Gestion de projet (Kanban) |

## 2. Conteneurs Docker (`docker-compose.yml`)

| Service | Image | Port local | Rôle |
|---|---|---|---|
| `app` | `php:8.2-apache` + extensions `pdo_mysql`, `mongodb` | 8080 (`APP_PORT`) | L'application |
| `mysql` | `mysql:8.0` | 3306 | Base relationnelle, initialisée par `create.sql` puis `seed.sql` |
| `mongo` | `mongo:7` | 27017 | Base NoSQL, initialisée par `mongo-init.js` |
| `phpmyadmin` | `phpmyadmin` | 8081 | Administration MySQL |
| `mailpit` | `axllent/mailpit` | 8025 | Boîte de réception de test |

Le code source est monté dans le conteneur (`./:/var/www/html`) : une modification est visible immédiatement, sans reconstruire l'image.

## 3. Installation en local

```bash
git clone https://github.com/Carlitaalt/vite-et-gourmand.git
cd vite-et-gourmand
cp .env.example .env          # puis renseigner les mots de passe (valeurs libres en local)
composer install              # ou : docker compose run --rm app composer install
docker compose up -d --build
```

- Site : http://localhost:8080 — phpMyAdmin : http://localhost:8081 — e-mails : http://localhost:8025
- Remise à zéro complète des données : `docker compose down -v` puis `docker compose up -d`

## 4. Variables d'environnement

Aucun secret n'est écrit dans le code : tout passe par des variables d'environnement, lues avec `getenv()`.
En local elles viennent du fichier `.env` (jamais versionné, voir `.gitignore`) ; en production, des variables du service Railway.

| Variable | Exemple local | Rôle |
|---|---|---|
| `APP_PORT`, `APP_URL` | `8080`, `http://localhost:8080` | Port local, URL utilisée dans les liens des e-mails |
| `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASSWORD` | `mysql`, `3306`, `vite_gourmand`… | Connexion MySQL |
| `MYSQL_ROOT_PASSWORD` | — | Mot de passe root du conteneur MySQL (local uniquement) |
| `MONGO_URI`, `MONGO_DB` | `mongodb://mongo:27017`, `vite_gourmand` | Connexion MongoDB |
| `MAIL_HOST`, `MAIL_PORT`, `MAIL_USER`, `MAIL_PASSWORD`, `MAIL_ENCRYPTION` | `mailpit`, `1025`, vide, vide, `none` | Serveur SMTP |
| `MAIL_FROM`, `MAIL_CONTACT` | `noreply@…`, `contact@…` | Expéditeur et destinataire des demandes de contact |

## 5. Organisation Git

- `main` : version stable, déployée automatiquement.
- `dev` : branche d'intégration.
- `feature/…` : une branche par fonctionnalité, créée depuis `dev`, fusionnée dans `dev` après test, puis `dev` fusionnée dans `main`.

Exemple : la refactorisation en couches et les appels `fetch` ont été développés sur `feature/architecture-poo`,
en commits thématiques (environnement, couches métier, contrôleurs et templates, API fetch).

## 6. Structure du projet

```
vite-et-gourmand/
├── pages/          points d'entrée des pages (3 lignes : appel du contrôleur)
├── actions/        points d'entrée des formulaires de connexion / inscription / déconnexion
├── api/            points d'entrée JSON appelés en fetch
├── src/
│   ├── Controller/ contrôleurs (et Controller/Api pour le JSON)
│   ├── Service/    logique métier
│   ├── Repository/ accès aux données MySQL et MongoDB
│   ├── Entity/     objets métier
│   ├── Enum/       rôles et statuts
│   ├── Core/       connexion BDD, vues, e-mails, messages flash
│   ├── Security/   session, rôles, jeton CSRF
│   └── bootstrap.php
├── templates/      vues HTML (layout, pages, onglets partagés admin/employé)
├── assets/         CSS, JavaScript, images
├── database/       create.sql, seed.sql, mongo-init.js
├── docs/           documentation technique et manuel
├── Dockerfile, docker-compose.yml, docker-entrypoint.sh
└── .env.example
```
