# Modèle conceptuel de données — Vite & Gourmand

Le modèle a été conçu à partir du cahier des charges, avant le développement. Il est implémenté dans MySQL par
`database/create.sql` (structure) et `database/seed.sql` (données de référence et de démonstration).

## 1. Entités et associations (notation Merise)

| Association | Entité A | Card. A | Entité B | Card. B | Données portées |
|---|---|---|---|---|---|
| **possède** | UTILISATEUR | 1,1 | ROLE | 0,n | |
| **est employé** | EMPLOYE | 1,1 | UTILISATEUR | 0,1 | |
| **demande** (réinitialisation) | JETON_REINITIALISATION | 1,1 | UTILISATEUR | 0,n | |
| **passe** | COMMANDE | 1,1 | UTILISATEUR | 0,n | |
| **contient** | COMMANDE | 1,n | MENU | 0,n | quantité, prix unitaire |
| **est dans l'état** (actuel) | COMMANDE | 1,1 | STATUT | 0,n | |
| **a traversé** (historique) | COMMANDE | 1,n | STATUT | 0,n | date de modification |
| **appartient à** | MENU | 1,1 | THEME | 0,n | |
| **respecte** | MENU | 1,1 | REGIME | 0,n | |
| **illustre** | IMAGE | 1,1 | MENU | 0,n | |
| **compose** | MENU | 0,n | PLAT | 0,n | |
| **contient** (allergène) | PLAT | 0,n | ALLERGENE | 0,n | |
| **évalue** | AVIS | 1,1 | COMMANDE | 0,1 | |
| **rédige** | AVIS | 1,1 | UTILISATEUR | 0,n | |
| **est modéré** | AVIS | 1,1 | STATUT_AVIS | 0,n | |

`HORAIRE` est une entité indépendante (un enregistrement par jour de la semaine).

Choix de conception :

- **Un plat peut appartenir à plusieurs menus** (association n,n `compose`), comme le demande le cahier des charges.
- **Le statut actuel est stocké dans la commande**, et chaque changement est aussi historisé dans `commande_statut` :
  on affiche rapidement le statut courant, et le client dispose du suivi complet (statut + date et heure).
- **Le prix unitaire est copié dans `commande_menu`** au moment de la commande : si le prix du menu change ensuite,
  les commandes passées gardent leur prix réel.
- **Les rôles, statuts, thèmes et régimes sont des tables de référence** : on peut en ajouter sans modifier le code
  (le cahier des charges permet d'enrichir les régimes).
- **Les jetons de réinitialisation ne sont stockés que sous forme d'empreinte SHA-256.**

## 2. Modèle physique (MySQL)

```mermaid
erDiagram
    ROLE ||--o{ UTILISATEUR : "possède"
    UTILISATEUR ||--o| EMPLOYES : "est employé"
    UTILISATEUR ||--o{ PASSWORD_RESET_TOKENS : "demande"
    UTILISATEUR ||--o{ COMMANDE : "passe"
    UTILISATEUR ||--o{ AVIS : "rédige"
    STATUT ||--o{ COMMANDE : "statut actuel"
    COMMANDE ||--|{ COMMANDE_STATUT : "historique"
    STATUT ||--o{ COMMANDE_STATUT : ""
    COMMANDE ||--|{ COMMANDE_MENU : "contient"
    MENU ||--o{ COMMANDE_MENU : ""
    THEME ||--o{ MENU : "classe"
    REGIME ||--o{ MENU : "classe"
    MENU ||--o{ MENU_IMAGE : "galerie"
    MENU ||--o{ MENU_PLAT : "compose"
    PLAT ||--o{ MENU_PLAT : ""
    PLAT ||--o{ PLAT_ALLERGENE : "contient"
    ALLERGENE ||--o{ PLAT_ALLERGENE : ""
    COMMANDE ||--o| AVIS : "évaluée par"
    STATUT_AVIS ||--o{ AVIS : "modération"

    ROLE {
        int role_id PK
        varchar libelle
    }
    UTILISATEUR {
        int utilisateur_id PK
        int role_id FK
        varchar nom
        varchar prenom
        varchar email UK
        varchar mot_de_passe "hash bcrypt"
        varchar telephone
        varchar adresse_postale
        varchar ville
        varchar pays
        tinyint actif
        datetime created_at
        datetime updated_at
    }
    EMPLOYES {
        int employe_id PK
        int utilisateur_id FK
        varchar poste
        decimal salaire_horaire
        date date_embauche
    }
    PASSWORD_RESET_TOKENS {
        int token_id PK
        int utilisateur_id FK
        varchar token "empreinte SHA-256"
        datetime expire_at
        tinyint used
    }
    THEME {
        int theme_id PK
        varchar libelle
    }
    REGIME {
        int regime_id PK
        varchar libelle
    }
    MENU {
        int menu_id PK
        int theme_id FK
        int regime_id FK
        varchar titre
        text description
        text conditions
        int nombre_personne_minimum
        decimal prix_par_personne
        int stock_disponible
        tinyint actif
    }
    MENU_IMAGE {
        int image_id PK
        int menu_id FK
        varchar url
        int ordre
    }
    PLAT {
        int plat_id PK
        varchar titre_plat
        text description
        varchar categorie
        tinyint actif
    }
    MENU_PLAT {
        int menu_id PK, FK
        int plat_id PK, FK
    }
    ALLERGENE {
        int allergene_id PK
        varchar libelle
    }
    PLAT_ALLERGENE {
        int plat_id PK, FK
        int allergene_id PK, FK
    }
    STATUT {
        int statut_id PK
        varchar libelle
    }
    COMMANDE {
        int commande_id PK
        int utilisateur_id FK
        int statut_id FK
        datetime date_commande
        date date_prestation
        time heure_livraison
        varchar adresse_livraison
        varchar ville_livraison
        decimal distance_km
        int nombre_personnes
        decimal prix_livraison
        decimal prix_total
        tinyint pret_materiel
        text motif_annulation
        enum mode_contact
    }
    COMMANDE_MENU {
        int commande_id PK, FK
        int menu_id PK, FK
        int quantite
        decimal prix_unitaire
    }
    COMMANDE_STATUT {
        int commande_statut_id PK
        int commande_id FK
        int statut_id FK
        datetime date_modification
    }
    STATUT_AVIS {
        int statut_avis_id PK
        varchar libelle
    }
    AVIS {
        int avis_id PK
        int commande_id FK
        int utilisateur_id FK
        int statut_avis_id FK
        tinyint note "1 à 5"
        text description
        datetime created_at
    }
    HORAIRE {
        int horaire_id PK
        varchar jour
        time heure_ouverture
        time heure_fermeture
    }
```

## 3. Intégrité des données

- **Clés étrangères** sur toutes les relations (moteur InnoDB).
- **`ON DELETE CASCADE`** pour les données qui n'ont pas de sens seules : images et liaisons d'un menu, allergènes d'un plat,
  historique d'une commande, jetons d'un utilisateur.
- **Pas de cascade** entre commande, utilisateur et menu : on ne peut pas supprimer un menu déjà commandé (il faut le
  désactiver), et un compte client supprimé est anonymisé pour conserver les commandes (obligations comptables).
- Contrainte `CHECK (note BETWEEN 1 AND 5)` sur les avis, `UNIQUE` sur l'e-mail des utilisateurs.

## 4. Données non relationnelles (MongoDB)

Base `vite_gourmand`, collection `commandes_stats`. Un document est ajouté à chaque commande :

```json
{
  "commande_id": 12,
  "menu_id": 3,
  "menu_titre": "Menu de Noël",
  "prix_total": 550.0,
  "date": { "$date": "2026-09-12T09:30:00Z" }
}
```

L'espace administrateur les agrège (`$match` sur la période, puis `$group` par menu) pour afficher le nombre de
commandes par menu dans un graphique. Ce modèle « journal d'événements » s'agrège simplement sans jointure, et
l'historique statistique reste intact même si un menu est modifié ou supprimé dans MySQL.
