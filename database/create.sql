SET NAMES utf8mb4;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS commande_statut;
DROP TABLE IF EXISTS commande_menu;
DROP TABLE IF EXISTS plat_allergene;
DROP TABLE IF EXISTS menu_plat;
DROP TABLE IF EXISTS menu_image;
DROP TABLE IF EXISTS avis;
DROP TABLE IF EXISTS commande;
DROP TABLE IF EXISTS menu;
DROP TABLE IF EXISTS plat;
DROP TABLE IF EXISTS allergene;
DROP TABLE IF EXISTS theme;
DROP TABLE IF EXISTS regime;
DROP TABLE IF EXISTS horaire;
DROP TABLE IF EXISTS statut;
DROP TABLE IF EXISTS statut_avis;
DROP TABLE IF EXISTS utilisateur;
DROP TABLE IF EXISTS role;

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- TABLE SANS DÉPENDANCES
-- =====================================================

CREATE TABLE role (
    role_id INT AUTO_INCREMENT PRIMARY KEY,
    libelle VARCHAR(50) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE statut (
    statut_id INT AUTO_INCREMENT PRIMARY KEY,
    libelle VARCHAR(50) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE statut_avis (
    statut_avis_id INT AUTO_INCREMENT PRIMARY KEY,
    libelle VARCHAR(50) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE theme (
    theme_id INT AUTO_INCREMENT PRIMARY KEY,
    libelle VARCHAR(50) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE regime (
    regime_id INT AUTO_INCREMENT PRIMARY KEY,
    libelle VARCHAR(50) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE allergene (
    allergene_id INT AUTO_INCREMENT PRIMARY KEY,
    libelle VARCHAR(50) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE horaire (
    horaire_id INT AUTO_INCREMENT PRIMARY KEY,
    jour VARCHAR(20) NOT NULL,
    heure_ouverture TIME NOT NULL,
    heure_fermeture TIME NOT NULL
) ENGINE=InnoDB;

-- =====================================================
-- TABLES AVEC FK SIMPLES
-- =====================================================

CREATE TABLE utilisateur (
    utilisateur_id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    nom VARCHAR(50) NOT NULL,
    prenom VARCHAR(50) NOT NULL,
    email varchar(255) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    telephone VARCHAR(20),
    adresse_postale VARCHAR(255),
    ville VARCHAR(50),
    pays VARCHAR(50) DEFAULT 'France',
    actif TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_utilisateur_role
    FOREIGN KEY(role_id) REFERENCES role(role_id)
) ENGINE=InnoDB;

CREATE TABLE employes (
    employe_id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NOT NULL,
    poste VARCHAR(100) NOT NULL,
    salaire_horaire DECIMAL(10, 2),
    date_embauche DATE,

    CONSTRAINT fk_employes_utilisateur FOREIGN KEY (utilisateur_id) REFERENCES utilisateur(utilisateur_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE password_reset_tokens (
    token_id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NOT NULL,
    token VARCHAR(255) NOT NULL,
    expire_at DATETIME NOT NULL,
    used TINYINT(1) NOT NULL DEFAULT 0,

    CONSTRAINT fk_reset_utilisateur FOREIGN KEY (utilisateur_id) REFERENCES utilisateur(utilisateur_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE plat (
    plat_id INT AUTO_INCREMENT PRIMARY KEY,
    titre_plat VARCHAR(100) NOT NULL,
    description TEXT,
    categorie VARCHAR(50) NOT NULL DEFAULT 'Plat',
    photo VARCHAR(255),
    actif TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE menu (
    menu_id INT AUTO_INCREMENT PRIMARY KEY,
    theme_id INT NOT NULL,
    regime_id INT NOT NULL,
    titre VARCHAR(100) NOT NULL,
    description TEXT,
    conditions TEXT,
    nombre_personne_minimum INT NOT NULL,
    prix_par_personne DECIMAL (10, 2) NOT NULL,
    actif TINYINT(1) NOT NULL DEFAULT 1,
    stock_disponible INT NOT NULL DEFAULT 10,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_menu_theme FOREIGN KEY (theme_id) REFERENCES theme(theme_id),
    CONSTRAINT fk_menu_regime FOREIGN KEY (regime_id) REFERENCES regime(regime_id)
) ENGINE=InnoDB;

-- =====================================================
-- TABLE DE JOINTURE 
-- =====================================================

CREATE TABLE menu_image (
    image_id INT AUTO_INCREMENT PRIMARY KEY,
    menu_id INT NOT NULL,
    url VARCHAR(255) NOT NULL,
    ordre INT DEFAULT 0,

    CONSTRAINT fk_menu_image_menu
    FOREIGN KEY (menu_id) REFERENCES menu(menu_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE plat_allergene (
    plat_id INT NOT NULL,
    allergene_id INT NOT NULL,
    PRIMARY KEY (plat_id, allergene_id),

    FOREIGN KEY (plat_id) REFERENCES plat(plat_id) ON DELETE CASCADE,
    FOREIGN KEY (allergene_id) REFERENCES allergene(allergene_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE menu_plat (
    menu_id INT NOT NULL,
    plat_id INT NOT NULL,
    PRIMARY KEY (menu_id, plat_id),

    FOREIGN KEY (menu_id) REFERENCES menu(menu_id) ON DELETE CASCADE,
    FOREIGN KEY (plat_id) REFERENCES plat(plat_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- COMMANDES
-- =====================================================

CREATE TABLE commande (
    commande_id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NOT NULL,
    statut_id INT NOT NULL,
    date_commande DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_prestation DATE NOT NULL,
    heure_livraison TIME NOT NULL,
    adresse_livraison VARCHAR(255) NOT NULL,
    ville_livraison VARCHAR(100) NOT NULL,
    distance_km DECIMAL(6, 1) NOT NULL DEFAULT 0,
    nombre_personnes INT NOT NULL,
    prix_total DECIMAL(10,2) NOT NULL,
    prix_livraison DECIMAL(10, 2) DEFAULT 0.00,
    pret_materiel TINYINT(1) DEFAULT 0,
    motif_annulation TEXT,
    mode_contact ENUM ('telephone', 'email'),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (utilisateur_id) REFERENCES utilisateur(utilisateur_id),
    FOREIGN KEY (statut_id) REFERENCES statut(statut_id)
) ENGINE=InnoDB;

CREATE TABLE commande_statut (
    commande_statut_id INT AUTO_INCREMENT PRIMARY KEY,
    commande_id INT NOT NULL,
    statut_id INT NOT NULL,
    date_modification DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (commande_id) REFERENCES commande(commande_id) ON DELETE CASCADE,
    FOREIGN KEY (statut_id) REFERENCES statut(statut_id)
)ENGINE=InnoDB;

CREATE TABLE commande_menu (
    commande_id INT NOT NULL,
    menu_id INT NOT NULL,
    quantite INT DEFAULT 1,
    prix_unitaire DECIMAL(10, 2) NOT NULL,
    PRIMARY KEY (commande_id, menu_id),

    FOREIGN KEY (commande_id) REFERENCES commande(commande_id) ON DELETE CASCADE,
    FOREIGN KEY (menu_id) REFERENCES menu(menu_id)
)ENGINE=InnoDB;

CREATE TABLE avis (
    avis_id INT AUTO_INCREMENT PRIMARY KEY,
    commande_id INT NOT NULL,
    utilisateur_id INT NOT NULL,
    statut_avis_id INT NOT NULL,
    note TINYINT NOT NULL CHECK (note BETWEEN 1 AND 5),
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (commande_id) REFERENCES commande(commande_id) ON DELETE CASCADE,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateur(utilisateur_id),
    FOREIGN KEY (statut_avis_id) REFERENCES statut_avis(statut_avis_id)
) ENGINE=InnoDB;