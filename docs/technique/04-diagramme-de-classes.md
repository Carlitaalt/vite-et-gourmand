# Diagramme de classes — architecture en couches

L'application est découpée en couches qui ont chacune une seule responsabilité :

| Couche | Dossier | Rôle |
|---|---|---|
| Point d'entrée | `pages/`, `actions/`, `api/` | Charge `src/bootstrap.php` puis appelle un contrôleur (3 lignes) |
| Contrôleur | `src/Controller` | Lit la requête, vérifie les droits et le jeton CSRF, appelle un service, choisit la réponse (page, redirection, JSON) |
| Service | `src/Service` | Logique métier : calcul du prix, règles de commande, e-mails, statistiques |
| Repository | `src/Repository` | Accès aux données (SQL préparé, MongoDB), transforme les lignes en objets entité |
| Entité | `src/Entity`, `src/Enum` | Objets métier et leurs règles (ex : une commande refuse une transition de statut interdite) |
| Vue | `templates/` | HTML uniquement, alimenté par les données préparées par le contrôleur |

Une dépendance ne va jamais « vers le haut » : un repository ne connaît pas les services, une entité ne connaît pas la base de données.

## 1. Modèle métier (entités)

```mermaid
classDiagram
    direction LR

    class Utilisateur {
        -int id
        -Role role
        -string prenom
        -string nom
        -string email
        -string motDePasseHash
        -bool actif
        +getNomComplet() string
        +verifierMotDePasse(string) bool
        +modifierProfil(...)
    }
    class Employe {
        -string poste
        -float salaireHoraire
        +modifierContrat(poste, salaire)
    }
    class Reference {
        <<abstract>>
        -int id
        -string libelle
        +getSlug() string
    }
    class Theme
    class Regime
    class Allergene
    class Menu {
        -string titre
        -int nombrePersonneMinimum
        -float prixParPersonne
        -int stockDisponible
        -bool actif
        +getPrixMinimum() float
        +estDisponible() bool
        +getPlatsParCategorie() array
        +jsonSerialize() array
    }
    class MenuImage {
        -string url
        -int ordre
    }
    class Plat {
        -string titre
        -string categorie
        +getAllergenesTexte() string
    }
    class Commande {
        -StatutCommande statut
        -int nombrePersonnes
        -float prixTotal
        -bool pretMateriel
        +getStatutsSuivants() StatutCommande[]
        +passerAuStatut(StatutCommande)
        +annuler(motif, modeContact)
        +estModifiableParClient() bool
        +modifierPrestation(..., DetailPrix)
        +peutRecevoirAvis() bool
    }
    class EtapeSuivi {
        -StatutCommande statut
        -DateTimeImmutable date
    }
    class Avis {
        -int note
        -string commentaire
        -StatutAvis statut
        +getAuteurAffiche() string
    }
    class DetailPrix {
        +float sousTotal
        +float remise
        +float fraisLivraison
        +getTotal() float
    }
    class StatutCommande {
        <<enumeration>>
        EnAttente
        Acceptee
        EnPreparation
        EnLivraison
        Livree
        RetourMateriel
        Terminee
        Annulee
        +libelle() string
        +suivantsPossibles() array
    }
    class Role {
        <<enumeration>>
        Utilisateur
        Employe
        Administrateur
        +aAuMoins(Role) bool
    }

    Reference <|-- Theme
    Reference <|-- Regime
    Reference <|-- Allergene
    Employe *-- Utilisateur : compte
    Utilisateur --> Role
    Menu --> Theme
    Menu --> Regime
    Menu *-- "0..*" MenuImage
    Menu o-- "0..*" Plat
    Plat o-- "0..*" Allergene
    Commande --> Menu
    Commande --> Utilisateur : client
    Commande *-- "1..*" EtapeSuivi : historique
    Commande o-- "0..1" Avis
    Commande --> StatutCommande
    Commande ..> DetailPrix : utilise
```

## 2. Couches : exemple de la commande

```mermaid
classDiagram
    direction TB

    class AbstractController {
        <<abstract>>
        #render(template, donnees)
        #rediriger(url) never
        #verifierCsrf(urlRetour)
        #executer(action, message, urlRetour) never
        #json(donnees, code) never
    }
    class CommandeController {
        +formulaire()
    }
    class PrixApiController {
        +calculer() never
    }
    class EspaceEmployeController {
        #traiterAction(action) never
        #donneesPage() array
    }
    class EspaceAdminController {
        #traiterAction(action) never
        #donneesPage() array
    }
    class CommandeService {
        +calculerPrix(Menu, nb, ville, km) DetailPrix
        +passerCommande(Utilisateur, donnees) Commande
        +modifierParClient(clientId, commandeId, donnees)
        +changerStatut(commandeId, statut) Commande
        +annulerParPersonnel(commandeId, contact, motif)
    }
    class NotificationService {
        +confirmationCommande(Commande)
        +retourMateriel(Commande)
        +commandeTerminee(Commande)
    }
    class AbstractRepository {
        <<abstract>>
        #PDO pdo
    }
    class CommandeRepository {
        +findById(id) Commande
        +findByUtilisateur(id) Commande[]
        +inserer(Commande)
        +enregistrerStatut(Commande)
    }
    class MenuRepository {
        +findById(id) Menu
        +rechercher(filtres) Menu[]
        +decrementerStock(id) bool
    }
    class StatistiqueRepository {
        +enregistrerCommande(...)
        +commandesParMenu(debut, fin) StatistiqueMenu[]
    }
    class Database {
        <<singleton>>
        +getConnexion() PDO
        +transaction(callable) mixed
    }
    class Mailer {
        +envoyer(destinataire, sujet, html) bool
    }

    AbstractController <|-- CommandeController
    AbstractController <|-- PrixApiController
    AbstractController <|-- EspaceEmployeController
    EspaceEmployeController <|-- EspaceAdminController
    CommandeController --> CommandeService
    PrixApiController --> CommandeService
    EspaceEmployeController --> CommandeService
    CommandeService --> CommandeRepository
    CommandeService --> MenuRepository
    CommandeService --> StatistiqueRepository
    CommandeService --> NotificationService
    NotificationService --> Mailer
    AbstractRepository <|-- CommandeRepository
    AbstractRepository <|-- MenuRepository
    AbstractRepository --> Database
    CommandeService ..> Database : transaction
```

Principes de programmation orientée objet appliqués :

- **Encapsulation** : propriétés privées, modifiées uniquement par des méthodes métier qui contrôlent les règles
  (`Commande::passerAuStatut()` lève une `MetierException` si la transition est interdite).
- **Héritage** : `AbstractController`, `AbstractRepository`, `Reference` (Theme, Regime, Allergene) ;
  l'espace administrateur hérite de l'espace employé et n'ajoute que ses actions propres.
- **Polymorphisme** : `EspaceAdminController::traiterAction()` redéfinit la méthode parente et délègue à `parent::traiterAction()`
  pour les actions communes.
- **Injection de dépendances** : chaque service reçoit ses repositories dans son constructeur (valeurs par défaut),
  ce qui permet de les remplacer pour les tests.
- **Enums PHP 8.1** : les statuts et rôles sont des types, pas des nombres « magiques » dispersés dans le code.
