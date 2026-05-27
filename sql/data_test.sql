-- ============================================================
-- DONNÉES DE TEST - À utiliser uniquement en développement
-- ============================================================
USE gestion_memoires;

-- Centres
INSERT INTO centre (nom_centre, adresse, telephone, est_centre_principal) VALUES
('Cotonou',    'Rue X, Cotonou',    '97000001', TRUE),
('Porto-Novo', 'Rue Y, Porto-Novo', '97000002', FALSE);

-- Filières (centre Cotonou = id 1)
INSERT INTO filiere (nom_filiere, centre_id) VALUES
('Informatique', 1),
('Réseaux',      1),
('Gestion',      1),
('Informatique', 2),
('Gestion',      2);

-- Utilisateurs (mdp = "password123" hashé bcrypt)
-- hash généré par : password_hash('password123', PASSWORD_BCRYPT)
INSERT INTO utilisateur (nom, email, mot_de_passe, centre_id, role, doit_changer_mdp) VALUES
('Kofi Technicien', 'tech@uatm.bj',      '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 'technicien', FALSE),
('Amivi Directeur', 'directeur@uatm.bj', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 'directeur',  FALSE),
('Prof Akpo',       'prof@uatm.bj',      '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 'professeur', FALSE),
('Etud Mensah',     'etud@uatm.bj',      '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 'etudiant',   FALSE);

-- Technicien
INSERT INTO technicien (utilisateur_id, service) VALUES (1, 'Informatique');

-- Directeur
INSERT INTO directeur_etudes (utilisateur_id, responsabilite) VALUES (2, 'Responsable pédagogique');

-- Professeur
INSERT INTO professeur (utilisateur_id, specialite, grade) VALUES (3, 'Développement web', 'Maître de conférences');

-- Étudiant L3
INSERT INTO etudiant (utilisateur_id, numero_etudiant, niveau_etude, filiere_id) VALUES (4, 'ETU2024001', 'L3', 1);
