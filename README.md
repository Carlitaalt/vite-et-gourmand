# Vite & Gourmand

Application web de restauration événementielle — ECF « Développeur Web et Web Mobile » (Studi).

Julie et José, traiteurs à Bordeaux depuis 25 ans, présentent leurs menus et reçoivent les commandes en ligne ;
leur équipe gère les commandes, le catalogue, les horaires et les avis clients.

- **Documentation technique, conception et déploiement** : [docs/](docs/README.md)
- **Manuel d'utilisation** : [docs/manuel/manuel-utilisation.md](docs/manuel/manuel-utilisation.md)

## Fonctionnalités

| Profil | Fonctionnalités |
|---|---|
| Visiteur | Accueil (présentation, équipe, avis validés), menus filtrables sans rechargement, détail d'un menu, inscription, connexion, mot de passe oublié, contact |
| Client | Commande avec prix calculé en direct (minimum de personnes, remise de 10 %, livraison), suivi, modification et annulation avant acceptation, avis, profil |
| Employé | Traitement des commandes (statuts, annulation après contact client), filtres, modération des avis, menus, plats, allergènes, horaires |
| Administrateur | Tout l'espace employé + comptes employés (création, désactivation) + statistiques MongoDB et chiffre d'affaires filtrable |

E-mails automatiques : bienvenue, confirmation de commande, réinitialisation du mot de passe, compte employé, retour de matériel, commande terminée, annulation, contact.

## Technologies

- **Back-end** : PHP 8.2 orienté objet, architecture MVC en couches (contrôleurs, services, repositories, entités), PDO, Composer (autoload PSR-4, PHPMailer)
- **Bases de données** : MySQL 8 (données relationnelles) · MongoDB 7 (statistiques de commandes)
- **Front-end** : HTML 5, CSS, Bootstrap 5, JavaScript natif (`fetch`), Chart.js — accessibilité RGAA
- **Outils** : Docker / Docker Compose, Git / GitHub, Railway (production), Mailpit (e-mails en local)

## Installation en local (Docker)

### Prérequis

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) installé et lancé
- Git

### Étapes

1. **Cloner le dépôt**
   ```bash
   git clone https://github.com/Carlitaalt/vite-et-gourmand.git
   cd vite-et-gourmand
   ```

2. **Créer le fichier d'environnement** puis renseigner les mots de passe (valeurs libres en local)
   ```bash
   cp .env.example .env
   ```
   Si le port 8080 est déjà utilisé sur votre machine, changez `APP_PORT` (et `APP_URL`) dans `.env`.

3. **Lancer les conteneurs**
   ```bash
   docker compose up -d --build
   ```
   Au premier démarrage : les dépendances Composer sont installées automatiquement, MySQL exécute
   `database/create.sql` puis `database/seed.sql`, et MongoDB exécute `database/mongo-init.js`.

4. **Accéder à l'application**

   | Service | Adresse |
   |---|---|
   | Site | http://localhost:8080 |
   | phpMyAdmin | http://localhost:8081 |
   | E-mails envoyés (Mailpit) | http://localhost:8025 |

5. **Arrêter / réinitialiser**
   ```bash
   docker compose down        # arrête les conteneurs
   docker compose down -v     # arrête et supprime les données (réinstallation complète au prochain démarrage)
   ```

### Comptes de démonstration

| Rôle | E-mail | Mot de passe |
|---|---|---|
| Administrateur | admin@vite-gourmand.fr | Admin@2026! |
| Employé | employe@vite-gourmand.fr | Employe@2026! |
| Client | client@vite-gourmand.fr | Client@2026! |

Les données de démonstration contiennent des commandes (dont une en attente), un avis publié et un avis à modérer.

## Architecture

```
vite-et-gourmand/
├── pages/, actions/   points d'entrée HTML : chaque fichier appelle un contrôleur
├── api/               points d'entrée JSON appelés en fetch
├── src/
│   ├── Controller/    lecture de la requête, droits, jeton CSRF, choix de la réponse
│   ├── Service/       logique métier (prix, commandes, e-mails, statistiques)
│   ├── Repository/    accès aux données MySQL / MongoDB → objets entité
│   ├── Entity/, Enum/ objets métier et statuts
│   ├── Core/, Security/
│   └── bootstrap.php
├── templates/         vues HTML (aucune requête SQL)
├── assets/            CSS, JavaScript, images
├── database/          create.sql, seed.sql, mongo-init.js
└── docs/              documentation technique, manuel, PDF
```

### API (appels asynchrones)

| Point d'accès | Méthode | Utilisé par |
|---|---|---|
| `api/menus.php` | GET | Filtres de la page « Nos menus » |
| `api/prix-commande.php` | GET | Récapitulatif du prix pendant la commande |
| `api/avis.php` | POST (JSON + jeton CSRF) | Modération des avis (employé / administrateur) |
| `api/statistiques.php` | GET | Graphique et chiffre d'affaires de l'administrateur |

## Sécurité (résumé)

Requêtes préparées PDO, échappement des sorties, jeton CSRF sur tous les formulaires et appels fetch, mots de passe bcrypt
et politique de complexité, session sécurisée (HttpOnly, SameSite, régénération), contrôle des rôles et de la propriété
des données, jetons de réinitialisation hachés, contrôle des fichiers téléversés, secrets en variables d'environnement.
Détail et justification de chaque mesure : [docs/technique/07-securite.md](docs/technique/07-securite.md).

## Organisation Git

- `main` : version stable, déployée automatiquement sur Railway
- `dev` : intégration
- `feature/…` : une branche par fonctionnalité, créée depuis `dev` et fusionnée dans `dev` après test

## Auteur

**Carla Ferrero** — Développeuse web en formation — Projet ECF 2026
