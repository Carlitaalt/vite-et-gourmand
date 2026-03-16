# Vite & Gourmand

Application web de restauration événementielle.

##Présentation du projet

**Vite & Gourmand** est une entreprise de restauration événementielle composéede deux personnes.
L'application web permet de :
- Consulter les menus proposés par l'entreprise
- Passer une commande en ligne
- Gérer les commandes et les menus via un espace via un espace employé et administrateur
- Améliorer la visibilité et la notoriété de l'entreprise

## Technologies
- PHP / PDO
- MySQL
-MongoDB
- HTML5 / CSS / Bootstrap / JavaScript
- Git / Github

## Installation

## 📁 Structure des dossiers
```
vite-et-gourmand/
├── assets/
│   ├── images/      → logos et images
│   ├── css/         → fichiers CSS
│   └── js/          → fichiers JavaScript
├── pages/           → pages PHP
├── config/          → connexion base de données
├── includes/        → header, footer, navbar
├── database/        → fichiers SQL
├── .gitignore
└── README.md

## Fonctionnalités

- **Page d'accueil** - Présentation de l'entreprise et avis clients
- **Menus** - Consultation et filtrages des menus (prix, thème, régime, personnes)
- **Authentification** - Inscription, connexion, réintialisation du mot de passe
- **Commandes** - Passe et suivi de commande en ligne
- **Espace utilisateur** - Gestion du profil, commandes, avis
- **Espace employé** - Gestion des menus, commandes et avis
- **Espace administrateur** - Gestion complète + statistiques

## Sécurité

- Mots de passe hashés avec un algorithme sécurisé
- Protection contre les injections SQL via PDO et requêtes préparées
- Protection contre les attaques XSS par échappement des données
- Validation des formulaires côté client et serveur
- Gestion des sessions sécurisées
- Contrôle des rôles utilisateurs (visiteur, client, employé, administrateur)

## Auteur

**Carla Ferrero** - Développeuse Full Stack en formation
Projet ECF - 2025/2026
