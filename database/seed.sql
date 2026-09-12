SET NAMES utf8mb4;
-- =====================================================
-- SEED.SQL — Données d'intégration pour Vite & Gourmand
-- =====================================================
-- Ce fichier peuple toutes les tables de référence nécessaires
-- au bon fonctionnement de l'application, ainsi que des comptes
-- et des données de démonstration pour les tests du jury.

-- =====================================================
-- 1. RÔLES
-- IMPORTANT : l'ordre est déterminant. role_id 1 = utilisateur
-- (attribué par défaut à l'inscription), 2 = employe, 3 = administrateur.
-- =====================================================
INSERT INTO role (libelle) VALUES ('utilisateur'), ('employe'), ('administrateur');

-- =====================================================
-- 2. STATUTS DE COMMANDE
-- L'ordre correspond exactement au cycle de vie défini dans le cahier des charges.
-- =====================================================
INSERT INTO statut (libelle) VALUES
('En attente'),
('Acceptée'),
('En préparation'),
('En cours de livraison'),
('Livrée'),
('Retour matériel'),
('Terminée'),
('Annulée');

-- =====================================================
-- 3. STATUTS D'AVIS
-- =====================================================
INSERT INTO statut_avis (libelle) VALUES
('En attente'),
('Publié'),
('Refusé');

-- =====================================================
-- 4. THÈMES (Noel, Pâques, classique, évènement — cf. énoncé)
-- =====================================================
INSERT INTO theme (libelle) VALUES
('Noël'),
('Pâques'),
('Classique'),
('Évènement');

-- =====================================================
-- 5. RÉGIMES (vegetarien, vegan, classique — cf. énoncé)
-- =====================================================
INSERT INTO regime (libelle) VALUES
('Classique'),
('Végétarien'),
('Vegan');

-- =====================================================
-- 6. ALLERGÈNES (liste courante)
-- =====================================================
INSERT INTO allergene (libelle) VALUES
('Gluten'),
('Lactose'),
('Œuf'),
('Arachide'),
('Fruits à coque'),
('Soja'),
('Poisson'),
('Crustacés');

-- =====================================================
-- 7. HORAIRES D'OUVERTURE (lundi au dimanche)
-- =====================================================
INSERT INTO horaire (jour, heure_ouverture, heure_fermeture) VALUES
('Lundi', '09:00:00', '18:00:00'),
('Mardi', '09:00:00', '18:00:00'),
('Mercredi', '09:00:00', '18:00:00'),
('Jeudi', '09:00:00', '18:00:00'),
('Vendredi', '09:00:00', '18:00:00'),
('Samedi', '10:00:00', '16:00:00'),
('Dimanche', '00:00:00', '00:00:00');

-- =====================================================
-- 8. COMPTES DE DÉMONSTRATION
-- Mots de passe en clair (pour le manuel d'utilisation), conformes à la politique
-- de l'application (10 caractères min., majuscule, minuscule, chiffre, caractère spécial) :
--   Admin    : admin@vite-gourmand.fr   / Admin@2026!
--   Employé  : employe@vite-gourmand.fr / Employe@2026!
--   Client   : client@vite-gourmand.fr  / Client@2026!
-- Seuls les hachages bcrypt (password_hash) sont stockés.
-- =====================================================
INSERT INTO utilisateur (role_id, prenom, nom, email, mot_de_passe, telephone, adresse_postale, ville, pays, actif) VALUES
(3, 'Admin', 'Test', 'admin@vite-gourmand.fr', '$2y$12$zVM3wMMR77AHAg6kAID46Ocjp28m/2bUfObshwDAdYnjWXbuDAuca', '0600000001', '1 rue de l''Administration', 'Bordeaux', 'France', 1),
(2, 'Emma', 'Employée', 'employe@vite-gourmand.fr', '$2y$12$51VaZJCrsAumLV7yOHW/EuQPYwdyeORaW/8BYdM4N2/2p9JWgb9OW', '0600000002', '2 rue du Travail', 'Bordeaux', 'France', 1),
(1, 'Camille', 'Client', 'client@vite-gourmand.fr', '$2y$12$825U7zmsgTFkmKVhk/vgquRHs24/50X5jjgPD5AW2UOoGQ5UCF/um', '0600000003', '3 rue des Clients', 'Bordeaux', 'France', 1);

INSERT INTO employes (utilisateur_id, poste, salaire_horaire, date_embauche) VALUES
(2, 'Chef de cuisine', 14.50, '2024-01-15');

-- =====================================================
-- 9. PLATS DE DÉMONSTRATION
-- =====================================================
INSERT INTO plat (titre_plat, description, categorie, actif) VALUES
('Velouté de saison', 'Velouté de légumes frais du marché.', 'Entrée', 1),
('Tartare de saumon', 'Tartare de saumon frais, agrumes et aneth.', 'Entrée', 1),
('Feuilleté aux champignons', 'Feuilleté croustillant, champignons des bois.', 'Entrée', 1),
('Salade de chèvre chaud', 'Salade verte, toast de chèvre chaud et miel.', 'Entrée', 1),
('Saumon rôti', 'Saumon rôti, purée de patate douce et légumes croquants.', 'Plat', 1),
('Magret de canard', 'Magret de canard rôti, sauce au miel et romarin.', 'Plat', 1),
('Risotto aux champignons', 'Risotto crémeux aux champignons de saison.', 'Plat', 1),
('Filet de bar', 'Filet de bar poêlé, beurre blanc et légumes verts.', 'Plat', 1),
('Tarte au citron', 'Tarte au citron meringuée, cœur fondant.', 'Dessert', 1),
('Fondant au chocolat', 'Fondant au chocolat noir, cœur coulant.', 'Dessert', 1),
('Crème brûlée', 'Crème brûlée à la vanille de Madagascar.', 'Dessert', 1),
('Assiette de fromages', 'Sélection de fromages affinés et son pain aux noix.', 'Dessert', 1);

-- Allergènes associés à quelques plats (exemples)
INSERT INTO plat_allergene (plat_id, allergene_id) VALUES
-- Velouté de saison (crème)
(1, 2),
-- Tartare de saumon (poisson)
(2, 7),
-- Feuilleté aux champignons (pâte + beurre)
(3, 1), (3, 2),
-- Salade de chèvre chaud (fromage + toast)
(4, 1), (4, 2),
-- Saumon rôti (poisson)
(5, 7),
-- Magret de canard (aucun allergène courant)
-- Risotto aux champignons (parmesan/beurre)
(7, 2),
-- Filet de bar (poisson)
(8, 7),
-- Tarte au citron (pâte, œuf, crème)
(9, 1), (9, 2), (9, 3),
-- Fondant au chocolat (farine, œuf, beurre)
(10, 1), (10, 2), (10, 3),
-- Crème brûlée (œuf, crème)
(11, 2), (11, 3),
-- Assiette de fromages (lactose)
(12, 2);

-- =====================================================
-- 10. MENUS DE DÉMONSTRATION
-- theme_id : 1 Noël, 2 Pâques, 3 Classique, 4 Évènement
-- regime_id : 1 Classique, 2 Végétarien, 3 Vegan
-- =====================================================
INSERT INTO menu (theme_id, regime_id, titre, description, conditions, nombre_personne_minimum, prix_par_personne, actif, stock_disponible) VALUES
(3, 1, 'Menu Classique', 'Un menu convivial pour toutes vos réceptions.', 'Commande 5 jours avant la prestation.', 10, 35.00, 1, 15),
(4, 2, 'Menu Végétarien', 'Une sélection de plats végétariens raffinés.', 'Commande 7 jours avant la prestation.', 8, 32.00, 1, 12),
(1, 1, 'Menu de Noël', 'Notre menu festif pour les fêtes de fin d''année.', 'Commande 15 jours avant la prestation. Minimum 10 personnes.', 10, 55.00, 1, 8),
(2, 1, 'Menu de Pâques', 'Un menu printanier pour célébrer Pâques en famille.', 'Commande 10 jours avant la prestation.', 8, 42.00, 1, 10),
(4, 1, 'Menu Prestige', 'Notre menu haut de gamme pour événements exceptionnels.', 'Commande 15 jours avant la prestation. Prêt de matériel possible.', 15, 65.00, 1, 5);

-- Liaison menus / plats (une entrée, un plat, un dessert par menu)
INSERT INTO menu_plat (menu_id, plat_id) VALUES
(1, 1), (1, 5), (1, 9),
(2, 4), (2, 7), (2, 10),
(3, 2), (3, 6), (3, 11),
(4, 3), (4, 8), (4, 12),
(5, 2), (5, 6), (5, 11);

-- Images des menus (fichiers déjà présents dans assets/images/)
INSERT INTO menu_image (menu_id, url, ordre) VALUES
(1, 'assets/images/photo-accueil.jpg', 1),
(2, 'assets/images/menu-vegan.jpg', 1),
(3, 'assets/images/menu-noel.jpg', 1),
(4, 'assets/images/menu-paques.jpg', 1),
(5, 'assets/images/menu-prestige.jpg', 1);

-- =====================================================
-- 11. COMMANDES, SUIVI ET AVIS DE DÉMONSTRATION (compte client)
-- Permettent de tester immédiatement : avis publié sur l'accueil, avis à modérer,
-- commande en attente (à accepter par l'employé ou à modifier par le client), statistiques.
-- Les documents MongoDB correspondants sont dans database/mongo-init.js.
-- =====================================================
INSERT INTO commande (utilisateur_id, statut_id, date_commande, date_prestation, heure_livraison, adresse_livraison, ville_livraison, distance_km, nombre_personnes, prix_total, prix_livraison, pret_materiel) VALUES
(3, 7, '2026-06-01 10:00:00', '2026-06-13', '12:00:00', '3 rue des Clients', 'Bordeaux', 0, 12, 420.00, 0.00, 0),
(3, 7, '2026-06-20 14:30:00', '2026-07-04', '19:30:00', '3 rue des Clients', 'Bordeaux', 0, 10, 550.00, 0.00, 1),
(3, 1, '2026-09-01 09:15:00', '2026-12-19', '18:00:00', '8 avenue de la Gare', 'Mérignac', 10, 15, 985.90, 10.90, 0);

INSERT INTO commande_menu (commande_id, menu_id, quantite, prix_unitaire) VALUES
(1, 1, 12, 35.00),
(2, 3, 10, 55.00),
(3, 5, 15, 65.00);

-- Suivi : la commande 1 est terminée sans prêt de matériel, la commande 2 après retour du matériel
INSERT INTO commande_statut (commande_id, statut_id, date_modification) VALUES
(1, 1, '2026-06-01 10:00:00'), (1, 2, '2026-06-02 09:00:00'), (1, 3, '2026-06-13 08:00:00'),
(1, 4, '2026-06-13 11:15:00'), (1, 5, '2026-06-13 11:55:00'), (1, 7, '2026-06-13 15:00:00'),
(2, 1, '2026-06-20 14:30:00'), (2, 2, '2026-06-21 10:00:00'), (2, 3, '2026-07-04 14:00:00'),
(2, 4, '2026-07-04 18:45:00'), (2, 5, '2026-07-04 19:20:00'), (2, 6, '2026-07-04 23:00:00'),
(2, 7, '2026-07-10 10:00:00'),
(3, 1, '2026-09-01 09:15:00');

INSERT INTO avis (commande_id, utilisateur_id, statut_avis_id, note, description, created_at) VALUES
(1, 3, 2, 5, 'Un repas délicieux et une livraison à l''heure. Nos invités ont adoré le saumon rôti !', '2026-06-14 10:00:00'),
(2, 3, 1, 4, 'Très bon menu de Noël et service impeccable. Merci pour le prêt de la vaisselle.', '2026-07-11 09:30:00');