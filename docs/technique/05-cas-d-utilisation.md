# Diagramme de cas d'utilisation

Quatre acteurs, avec héritage des droits : un **utilisateur** connecté peut faire tout ce que fait un **visiteur** ;
l'**administrateur** peut faire tout ce que fait un **employé**.

```mermaid
flowchart LR
    Visiteur(["Visiteur"])
    Utilisateur(["Utilisateur (client)"])
    Employe(["Employé"])
    Admin(["Administrateur"])

    Utilisateur -. hérite de .-> Visiteur
    Admin -. hérite de .-> Employe

    subgraph Site["Application Vite & Gourmand"]
        direction TB
        V1(["Consulter l'accueil et les avis validés"])
        V2(["Consulter et filtrer les menus"])
        V3(["Voir le détail d'un menu"])
        V4(["Créer un compte"])
        V5(["Se connecter / réinitialiser son mot de passe"])
        V6(["Contacter l'entreprise"])

        U1(["Commander un menu"])
        U2(["Suivre ses commandes"])
        U3(["Modifier / annuler une commande non acceptée"])
        U4(["Donner un avis sur une commande terminée"])
        U5(["Gérer son profil / supprimer son compte"])

        E1(["Traiter les commandes (changer le statut)"])
        E2(["Annuler une commande après contact client"])
        E3(["Filtrer les commandes par statut ou client"])
        E4(["Valider / refuser les avis"])
        E5(["Gérer les menus, plats et allergènes"])
        E6(["Modifier les horaires"])

        A1(["Créer un compte employé"])
        A2(["Désactiver / réactiver un compte employé"])
        A3(["Consulter les commandes par menu (graphique MongoDB)"])
        A4(["Calculer le chiffre d'affaires par menu et période"])
    end

    Visiteur --- V1 & V2 & V3 & V4 & V5 & V6
    Utilisateur --- U1 & U2 & U3 & U4 & U5
    Employe --- V5
    Employe --- E1 & E2 & E3 & E4 & E5 & E6
    Admin --- A1 & A2 & A3 & A4

    U1 -. include .-> V5
    E2 -. include .-> E1
```

## Règles associées

| Cas d'utilisation | Règles de gestion |
|---|---|
| Créer un compte | Nom, prénom, GSM, e-mail, adresse postale obligatoires ; mot de passe ≥ 10 caractères avec majuscule, minuscule, chiffre et caractère spécial ; rôle « utilisateur » ; e-mail de bienvenue |
| Commander un menu | Connexion obligatoire (redirection vers la connexion puis retour à la commande) ; minimum de personnes du menu ; −10 % dès 5 personnes au-delà du minimum ; livraison gratuite à Bordeaux sinon 5 € + 0,59 €/km ; stock décrémenté ; e-mail de confirmation |
| Modifier / annuler | Possible tant que la commande est « en attente » ; tout est modifiable sauf le menu ; une annulation libère le stock |
| Donner un avis | Commande terminée, une seule fois ; note de 1 à 5 et commentaire ; visible sur l'accueil après validation |
| Traiter les commandes | Transitions imposées : en attente → acceptée → en préparation → en cours de livraison → livrée → (retour de matériel si prêt) → terminée ; e-mails automatiques au retour de matériel (600 € après 10 jours ouvrés) et à la fin |
| Annuler (employé) | Mode de contact (GSM ou e-mail) et motif obligatoires ; client prévenu par e-mail |
| Créer un compte employé | Réservé à l'administrateur ; l'e-mail envoyé ne contient pas le mot de passe ; impossible de créer un administrateur |
