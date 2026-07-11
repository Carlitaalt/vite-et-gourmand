# Vite & Gourmand

Application web de restauration événementielle.

## Présentation du projet

**Vite & Gourmand** est une entreprise de restauration événementielle composée de deux personnes.
L'application web permet de :
- Consulter les menus proposés par l'entreprise
- Passer une commande en ligne
- Gérer les commandes et les menus via un espace employé et administrateur
- Améliorer la visibilité et la notoriété de l'entreprise

## Technologies

- PHP / PDO
- MySQL
- MongoDB
- HTML5 / CSS / Bootstrap / JavaScript
- Docker / Docker Compose
- Git / GitHub

## Installation en local (avec Docker)

### Prérequis

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) installé et lancé
- Git

### Étapes

1. **Cloner le dépôt**
```bash
   git clone https://github.com/Carlitaalt/vite-et-gourmand.git
   cd vite-et-gourmand
   git checkout dev
```

2. **Créer le fichier d'environnement**

   Copier le fichier d'exemple et le renommer :
```bash
   cp .env.example .env
```
   Puis ouvrir `.env` et renseigner des valeurs (elles peuvent être arbitraires en local) :
```env
  DB_HOST=mysql
   DB_NAME=vite_gourmand
   DB_USER=vg_user
   DB_PASSWORD=votre_mot_de_passe
   MYSQL_ROOT_PASSWORD=votre_mot_de_passe_root
   MONGO_URI=mongodb://mongo:27017
```

3. **Lancer les conteneurs**
```bash
   docker compose up -d --build
```
   Ceci démarre 4 services :
   - `app` — serveur PHP/Apache (le site)
   - `mysql` — base de données relationnelle (schéma + données de démo importés automatiquement)
   - `mongo` — base de données NoSQL
   - `phpmyadmin` — interface d'administration MySQL

4. **Accéder à l'application**
   - Site : [http://localhost:8080](http://localhost:8080)
   - phpMyAdmin : [http://localhost:8081](http://localhost:8081) (utilisateur/mot de passe définis dans `.env`)

5. **Arrêter les conteneurs**
```bash
   docker compose down
```
   Pour tout réinitialiser (supprime aussi les données de la base) :
```bash
   docker compose down -v
```

### Comptes de démonstration

| Rôle | Email | Mot de passe |
|---|---|---|
| Administrateur | admin@vite-gourmand.fr | Admin123! |
| Employé | employe@vite-gourmand.fr | Employe123! |
| Client | client@vite-gourmand.fr | Client123! |

## 📁 Structure des dossiers

```
vite-et-gourmand/
├── actions/         → traitements des formulaires (connexion, inscription...)
├── assets/
│   ├── images/      → logos et images
│   ├── css/         → fichiers CSS
│   └── js/          → fichiers JavaScript
├── pages/           → pages PHP
├── includes/        → connexion BDD, header, footer, navbar, auth
├── database/        → fichiers SQL (create.sql, seed.sql)
├── Dockerfile
├── docker-compose.yml
├── .env.example
├── .gitignore
└── README.md
```

## Fonctionnalités

- **Page d'accueil** — Présentation de l'entreprise et avis clients
- **Menus** — Consultation et filtrage des menus (prix, thème, régime, personnes)
- **Authentification** — Inscription, connexion, réinitialisation du mot de passe
- **Commandes** — Passage et suivi de commande en ligne
- **Espace utilisateur** — Gestion du profil, des commandes, des avis
- **Espace employé** — Gestion des menus, des commandes et des avis
- **Espace administrateur** — Gestion complète + statistiques (MongoDB)

## Sécurité

- Mots de passe hashés avec `password_hash()` (bcrypt)
- Protection contre les injections SQL via PDO et requêtes préparées
- Protection contre les attaques XSS par échappement des données (`htmlspecialchars`)
- Validation des formulaires côté client et serveur
- Gestion des sessions sécurisées (régénération d'ID à la connexion)
- Contrôle des rôles utilisateurs (utilisateur, employé, administrateur)
- Identifiants de connexion à la base de données gérés via variables d'environnement (non versionnés)

## Auteur

**Carla Ferrero** — Développeuse Full Stack en formation
Projet ECF — 2025/2026