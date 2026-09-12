# Mesures et bonnes pratiques de sécurité

Chaque mesure est présentée avec la menace qu'elle traite, sa mise en œuvre dans le code et sa justification.
Les menaces sont rattachées au **Top 10 OWASP 2021**, référence utilisée pour la veille.

## 1. Tableau de synthèse

| # | Menace (OWASP) | Mesure | Où dans le code |
|---|---|---|---|
| 1 | Injection SQL (A03) | Requêtes préparées PDO, sans émulation | `src/Repository/*`, `Core/Database.php` |
| 2 | Cross-Site Scripting (A03) | Échappement à l'affichage (serveur et navigateur) | `templates/*`, `main.js: echapperHtml()` |
| 3 | Falsification de requête (CSRF) (A01) | Jeton secret par session sur chaque POST et chaque fetch | `Security/Csrf.php`, `AbstractController::verifierCsrf()` |
| 4 | Contrôle d'accès défaillant (A01) | Rôles hiérarchiques + vérification de propriété des données | `Security/Auth.php`, services |
| 5 | Stockage des mots de passe (A02) | Hachage bcrypt (`password_hash`) | `AuthService`, `EmployeService` |
| 6 | Authentification (A07) | Politique de mot de passe, messages neutres, session sécurisée | `AuthService`, `bootstrap.php` |
| 7 | Réinitialisation de mot de passe (A07) | Jeton aléatoire, à usage unique, 1 h, stocké haché | `TokenReinitialisationRepository` |
| 8 | Téléversement de fichiers (A04) | Type réel, liste blanche, taille, nom généré | `MenuService::ajouterImages()` |
| 9 | Secrets exposés (A05) | Variables d'environnement, `.env` exclu de Git et de l'image | `.gitignore`, `.dockerignore`, `Core/*` |
| 10 | Mauvaise configuration (A05) | Erreurs masquées en production, journalisées | `Dockerfile`, `bootstrap.php` |
| 11 | Redirection ouverte (A01) | Redirection après connexion limitée aux chemins internes | `AuthController::connexion()` |
| 12 | Intégrité des données (A04) | Transactions, clés étrangères, contraintes | `Database::transaction()`, `create.sql` |

## 2. Détail des mesures

### 2.1 Injections SQL
- **Toutes** les requêtes utilisent `prepare()` / `execute()` avec des marqueurs `?` ; aucune valeur saisie n'est concaténée dans du SQL.
- `PDO::ATTR_EMULATE_PREPARES => false` : la requête et les données sont envoyées séparément à MySQL (vraies requêtes préparées).
- Les filtres dynamiques (menus, chiffre d'affaires) ajoutent des **fragments SQL constants** et des paramètres, jamais du texte utilisateur.
- Les identifiants reçus sont convertis en entiers (`(int)`) et les listes (thèmes, statuts) sont validées côté serveur.

### 2.2 Cross-Site Scripting (XSS)
- Côté serveur : toute donnée affichée passe par `htmlspecialchars()` dans les templates, y compris dans les e-mails HTML.
- Côté navigateur : le JavaScript insère le texte reçu de l'API via `textContent` ou `echapperHtml()`.
- Les données JSON intégrées dans une page (statistiques) sont encodées avec `JSON_HEX_TAG | JSON_HEX_AMP` : un titre contenant `</script>` ne peut pas s'échapper de la balise.

### 2.3 Falsification de requête inter-sites (CSRF)
- Un jeton de 256 bits (`random_bytes(32)`) est généré par session.
- Il est ajouté en champ caché à **tous** les formulaires POST (`Csrf::champ()`) et envoyé en en-tête `X-CSRF-Token` par `appelApi()` pour les appels fetch qui modifient des données.
- Il est comparé avec `hash_equals()` (comparaison en temps constant). Un jeton absent ou faux entraîne un refus (message et redirection, ou HTTP 400 pour l'API).
- Le cookie de session est en `SameSite=Lax`, une seconde barrière contre les requêtes venant d'autres sites.

### 2.4 Contrôle d'accès
- Chaque page protégée appelle `Auth::exigerConnexion()` ou `Auth::exigerRole()` **avant tout traitement** ; l'API renvoie HTTP 403.
- Hiérarchie des rôles (`Role::aAuMoins()`) : l'administrateur hérite des droits de l'employé.
- **Vérification de propriété** : un client ne peut modifier, annuler ou noter que ses propres commandes (`CommandeService::commandeDuClient()`, `AvisService::deposer()`). Changer un identifiant dans le formulaire ne donne pas accès à la commande d'un autre client (protection IDOR).
- La désactivation de compte vérifie qu'il s'agit bien d'un employé : impossible de désactiver l'administrateur.
- Il n'existe aucun formulaire de création de compte administrateur (exigence du client).

### 2.5 Mots de passe
- Hachage avec `password_hash(PASSWORD_DEFAULT)` (bcrypt, sel aléatoire intégré, coût adaptatif) et vérification avec `password_verify()`.
- Politique imposée **côté serveur** (`AuthService::REGEX_MOT_DE_PASSE`) : 10 caractères minimum dont une majuscule, une minuscule, un chiffre et un caractère spécial. Le navigateur affiche les règles en direct, mais seul le serveur fait foi.
- Le mot de passe d'un nouvel employé n'est jamais envoyé par e-mail.

### 2.6 Authentification et session
- Même message « Email ou mot de passe incorrect » que le compte existe ou non (pas d'énumération des comptes).
- `session_regenerate_id(true)` à la connexion : protection contre la fixation de session.
- Cookie de session `HttpOnly` (inaccessible au JavaScript, donc à un éventuel XSS), `Secure` en HTTPS, `SameSite=Lax`, et `use_strict_mode` (refus des identifiants de session inventés).
- Déconnexion : session vidée, cookie supprimé, session détruite.

### 2.7 Réinitialisation du mot de passe
- Jeton de 64 caractères hexadécimaux (`random_bytes(32)`), valable 1 heure, à usage unique, un seul jeton actif par compte.
- **Seule l'empreinte SHA-256 est stockée** : une fuite de la base ne permet pas d'utiliser les liens envoyés.
- Réponse identique que l'adresse existe ou non.

### 2.8 Téléversement des photos de menu
- Le type est déterminé à partir du **contenu réel** du fichier (`finfo`), et non de son extension ni du type annoncé par le navigateur.
- Liste blanche : JPEG, PNG, WEBP ; 5 Mo maximum par fichier.
- Le **nom du fichier est généré par le serveur** (`menu_<id>_<aléatoire>.<extension>`) : impossible de déposer un `.php` ou d'écraser un fichier existant.
- Seuls les fichiers du dossier `assets/images/menus/` peuvent être supprimés par l'application.

### 2.9 Gestion des secrets
- Identifiants MySQL, MongoDB et SMTP lus depuis des variables d'environnement.
- `.env` exclu de Git (`.gitignore`) **et** de l'image Docker (`.dockerignore`) ; un `.env.example` sans secret documente les variables.
- Veille / incident : des identifiants avaient été versionnés dans un fichier suivi par Git avant d'être ignorés. Correction : rotation des mots de passe, retrait du suivi (`git rm --cached`), passage aux variables d'environnement. Même traitement pour des identifiants SMTP de test trouvés dans le code lors de la refactorisation.

### 2.10 Erreurs et journalisation
- En production : `display_errors = Off`, erreurs journalisées sur la sortie d'erreur (consultables dans les logs Railway).
- Un gestionnaire global d'exceptions (`bootstrap.php`) journalise l'erreur technique et n'affiche qu'un message générique.
- Les erreurs **métier** (`MetierException` : minimum de personnes non atteint, statut interdit…) ont un message pensé pour l'utilisateur ; les détails SQL ne sont jamais affichés.

### 2.11 Intégrité des données
- Transactions (`Database::transaction()`) pour les opérations en plusieurs étapes : commande + décrément du stock, annulation + remise en stock, création d'un employé (compte + fiche).
- Clés étrangères, contraintes `UNIQUE` (e-mail) et `CHECK` (note de 1 à 5) dans `create.sql`.
- Le prix est **toujours recalculé par le serveur** au moment de l'enregistrement : le prix affiché dans le navigateur n'est qu'un aperçu.

## 3. RGPD
- Données collectées limitées au besoin (identité, contact, adresse de livraison).
- Droit d'accès et de rectification : page « Mon profil ». Droit à l'effacement : suppression du compte, ou anonymisation s'il existe des commandes à conserver pour la comptabilité.
- Un seul cookie, strictement nécessaire (session) : pas de bandeau de consentement requis.
- Mentions légales détaillant l'usage des données.

## 4. Limites connues et améliorations prévues
- Limitation du nombre de tentatives de connexion (anti force brute), par exemple par adresse IP.
- En-têtes HTTP de sécurité (`Content-Security-Policy`, `X-Frame-Options`) à ajouter dans la configuration Apache.
- Analyse automatique des dépendances (`composer audit`) dans une intégration continue.
