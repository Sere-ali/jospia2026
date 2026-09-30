-- ============================================================
-- JOSPIA 2026 - Schéma de base de données
-- Journées Spirituelles Islamiques d'Anyama
-- ============================================================
CREATE DATABASE IF NOT EXISTS jospia2026 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE jospia2026;

-- ---------------------------------------------------------------
-- Comptes (utilisateur / admin / super admin)
-- ---------------------------------------------------------------
CREATE TABLE comptes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    identifiant VARCHAR(100) NOT NULL UNIQUE,      -- contact utilisé comme login
    mot_de_passe VARCHAR(255) NOT NULL,
    role ENUM('membre','seminariste','admin','superadmin') NOT NULL DEFAULT 'membre',
    membre_id INT NULL,
    seminariste_id INT NULL,
    nom_affiche VARCHAR(150) NULL,
    actif TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- Membres de commission
-- ---------------------------------------------------------------
CREATE TABLE membres_commission (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom_prenoms VARCHAR(150) NOT NULL,
    commission VARCHAR(50) NOT NULL,
    contact VARCHAR(30) NOT NULL,
    photo VARCHAR(255) NULL,
    matricule VARCHAR(30) NOT NULL UNIQUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- Séminaristes
-- ---------------------------------------------------------------
CREATE TABLE seminaristes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom_prenoms VARCHAR(150) NOT NULL,
    genre ENUM('Masculin','Féminin') NOT NULL,
    niveau_etude VARCHAR(100) NOT NULL,
    anyama ENUM('Anyama 1','Anyama 2') NOT NULL,
    section VARCHAR(50) NOT NULL,
    sous_comite_final VARCHAR(50) NOT NULL,   -- calculé (peut être "Pépinière")
    lieu_residence VARCHAR(150) NOT NULL,
    maladie VARCHAR(50) NOT NULL DEFAULT 'Aucune',
    maladie_autre VARCHAR(150) NULL,
    age INT NOT NULL,
    contact VARCHAR(30) NOT NULL,
    photo VARCHAR(255) NULL,
    parent_nom VARCHAR(150) NOT NULL,
    parent_lien VARCHAR(50) NOT NULL,
    parent_contact VARCHAR(30) NOT NULL,
    matricule VARCHAR(30) NOT NULL UNIQUE,
    dortoir VARCHAR(30) NULL,
    test_complete TINYINT(1) NOT NULL DEFAULT 0,
    note_test DECIMAL(4,2) NULL,
    niveau_affecte VARCHAR(30) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- Compteurs de dortoirs (pour équilibrage round-robin)
-- ---------------------------------------------------------------
CREATE TABLE dortoirs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(30) NOT NULL UNIQUE,
    genre ENUM('Masculin','Féminin') NOT NULL,
    capacite INT NOT NULL DEFAULT 40,
    occupation INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

INSERT INTO dortoirs (nom, genre, capacite) VALUES
('Dortoir Hommes 1','Masculin',40),
('Dortoir Hommes 2','Masculin',40),
('Dortoir Hommes 3','Masculin',40),
('Dortoir Hommes 4','Masculin',40),
('Dortoir Femmes 1','Féminin',40),
('Dortoir Femmes 2','Féminin',40),
('Dortoir Femmes 3','Féminin',40),
('Dortoir Femmes 4','Féminin',40);

-- ---------------------------------------------------------------
-- Questions du test d'entrée
-- ---------------------------------------------------------------
CREATE TABLE questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    categorie VARCHAR(80) NOT NULL,
    enonce TEXT NOT NULL,
    option_a VARCHAR(255) NOT NULL,
    option_b VARCHAR(255) NOT NULL,
    option_c VARCHAR(255) NOT NULL,
    option_d VARCHAR(255) NOT NULL,
    bonne_reponse ENUM('A','B','C','D') NOT NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- Réponses données par les séminaristes
-- ---------------------------------------------------------------
CREATE TABLE reponses_test (
    id INT AUTO_INCREMENT PRIMARY KEY,
    seminariste_id INT NOT NULL,
    question_id INT NOT NULL,
    reponse_donnee ENUM('A','B','C','D') NULL,
    correcte TINYINT(1) NOT NULL DEFAULT 0,
    FOREIGN KEY (seminariste_id) REFERENCES seminaristes(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- Matières / compositions du séminaire (gérées par la commission scientifique)
-- ---------------------------------------------------------------
CREATE TABLE matieres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    note_max DECIMAL(5,2) NOT NULL DEFAULT 20,
    ordre INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

INSERT INTO matieres (nom, note_max, ordre) VALUES
('Connaissance du Coran', 20, 1),
('Jurisprudence islamique (Fiqh)', 20, 2),
('Dogme (Aqida)', 20, 3),
('Culture générale islamique', 20, 4),
('Gestion associative (AEEMCI)', 20, 5);

-- ---------------------------------------------------------------
-- Notes des séminaristes (une note par matière, saisie par un admin)
-- ---------------------------------------------------------------
CREATE TABLE notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    seminariste_id INT NOT NULL,
    matiere_id INT NOT NULL,
    note DECIMAL(5,2) NULL,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_sem_matiere (seminariste_id, matiere_id),
    FOREIGN KEY (seminariste_id) REFERENCES seminaristes(id) ON DELETE CASCADE,
    FOREIGN KEY (matiere_id) REFERENCES matieres(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- Paramètres généraux (ex. publication des résultats/bulletins)
-- ---------------------------------------------------------------
CREATE TABLE parametres (
    cle VARCHAR(50) PRIMARY KEY,
    valeur VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

INSERT INTO parametres (cle, valeur) VALUES ('resultats_publies', '0');

-- ---------------------------------------------------------------
-- Critiques des séminaristes envers les commissions
-- ---------------------------------------------------------------
CREATE TABLE critiques (
    id INT AUTO_INCREMENT PRIMARY KEY,
    seminariste_id INT NOT NULL,
    commission VARCHAR(50) NOT NULL,
    contenu TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (seminariste_id) REFERENCES seminaristes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- Le compte Super Administrateur est créé automatiquement par
-- install.php (utilise password_hash() en PHP -> plus sûr que
-- de coller un hash en dur ici). Identifiant : superadmin
-- ---------------------------------------------------------------
