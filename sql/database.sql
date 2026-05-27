-- ============================================================
-- GESTION DES MEMOIRES - UATM GASA FORMATION
-- Script de création de la base de données
-- ============================================================

CREATE DATABASE IF NOT EXISTS gestion_memoires
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE gestion_memoires;

-- ------------------------------------------------------------
-- TABLE : centre
-- ------------------------------------------------------------
CREATE TABLE centre (
    id_centre           INT AUTO_INCREMENT PRIMARY KEY,
    nom_centre          VARCHAR(100) NOT NULL,
    adresse             VARCHAR(255),
    telephone           VARCHAR(20),
    est_centre_principal BOOLEAN DEFAULT FALSE,
    created_at          DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- TABLE : filiere
-- ------------------------------------------------------------
CREATE TABLE filiere (
    id_filiere  INT AUTO_INCREMENT PRIMARY KEY,
    nom_filiere VARCHAR(100) NOT NULL,
    centre_id   INT NOT NULL,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (centre_id) REFERENCES centre(id_centre) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- TABLE : utilisateur (table mère)
-- ------------------------------------------------------------
CREATE TABLE utilisateur (
    id_utilisateur    INT AUTO_INCREMENT PRIMARY KEY,
    nom               VARCHAR(100) NOT NULL,
    email             VARCHAR(150) NOT NULL UNIQUE,
    mot_de_passe      VARCHAR(255) NOT NULL,  -- bcrypt
    centre_id         INT NOT NULL,
    role              ENUM('etudiant','professeur','directeur','technicien') NOT NULL,
    est_actif         BOOLEAN DEFAULT TRUE,
    doit_changer_mdp  BOOLEAN DEFAULT TRUE,
    date_creation     DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (centre_id) REFERENCES centre(id_centre)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- TABLE : etudiant (hérite de utilisateur)
-- ------------------------------------------------------------
CREATE TABLE etudiant (
    utilisateur_id       INT PRIMARY KEY,
    numero_etudiant      VARCHAR(50) NOT NULL UNIQUE,
    niveau_etude         ENUM('L1','L2','L3','M1','M2') NOT NULL,
    filiere_id           INT NOT NULL,
    est_diplome_permanent BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateur(id_utilisateur) ON DELETE CASCADE,
    FOREIGN KEY (filiere_id)     REFERENCES filiere(id_filiere)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- TABLE : professeur (hérite de utilisateur)
-- ------------------------------------------------------------
CREATE TABLE professeur (
    utilisateur_id INT PRIMARY KEY,
    specialite     VARCHAR(100),
    grade          VARCHAR(50),
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateur(id_utilisateur) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- TABLE : directeur_etudes (hérite de utilisateur)
-- ------------------------------------------------------------
CREATE TABLE directeur_etudes (
    utilisateur_id INT PRIMARY KEY,
    responsabilite VARCHAR(100),
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateur(id_utilisateur) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- TABLE : technicien (hérite de utilisateur)
-- ------------------------------------------------------------
CREATE TABLE technicien (
    utilisateur_id INT PRIMARY KEY,
    service        VARCHAR(100),
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateur(id_utilisateur) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- TABLE : memoire
-- ------------------------------------------------------------
CREATE TABLE memoire (
    id_memoire      INT AUTO_INCREMENT PRIMARY KEY,
    etudiant_id     INT NOT NULL,
    titre           VARCHAR(255) NOT NULL,
    theme           VARCHAR(255),
    fichier_pdf     VARCHAR(255) NOT NULL,   -- nom de fichier stocké côté serveur
    statut          ENUM('en_attente','en_verification','valide','rejete','publie','non_public')
                    NOT NULL DEFAULT 'en_attente',
    type_diplome    ENUM('licence','master') NOT NULL,
    annee_soutenance YEAR NOT NULL,
    date_depot      DATETIME DEFAULT CURRENT_TIMESTAMP,
    remarques       TEXT,
    professeur_id   INT,                     -- assigné lors de la vérification
    FOREIGN KEY (etudiant_id)   REFERENCES etudiant(utilisateur_id),
    FOREIGN KEY (professeur_id) REFERENCES professeur(utilisateur_id),
    -- Un étudiant ne peut déposer qu'un seul mémoire par type de diplôme
    UNIQUE KEY uq_etudiant_diplome (etudiant_id, type_diplome)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- TABLE : commentaire
-- ------------------------------------------------------------
CREATE TABLE commentaire (
    id_commentaire  INT AUTO_INCREMENT PRIMARY KEY,
    memoire_id      INT NOT NULL,
    utilisateur_id  INT NOT NULL,
    contenu         TEXT NOT NULL,
    date_creation   DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (memoire_id)     REFERENCES memoire(id_memoire) ON DELETE CASCADE,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateur(id_utilisateur) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- TABLE : like_memoire
-- ------------------------------------------------------------
CREATE TABLE like_memoire (
    id_like         INT AUTO_INCREMENT PRIMARY KEY,
    memoire_id      INT NOT NULL,
    utilisateur_id  INT NOT NULL,
    date_creation   DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (memoire_id)     REFERENCES memoire(id_memoire) ON DELETE CASCADE,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateur(id_utilisateur) ON DELETE CASCADE,
    -- Un utilisateur ne peut liker qu'une seule fois
    UNIQUE KEY uq_like (utilisateur_id, memoire_id)
) ENGINE=InnoDB;
