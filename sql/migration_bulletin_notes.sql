-- ============================================================
-- JOSPIA 2026 — Migration : ajout du système de bulletin de notes
-- À utiliser UNIQUEMENT si vous avez DÉJÀ une base existante avec
-- des inscriptions réelles que vous ne voulez pas perdre.
-- (Si votre base ne contient que des données de test, il est plus
-- simple de réimporter directement sql/jospia2026_complet.sql.)
--
-- Importez ce fichier dans phpMyAdmin sur votre base existante
-- (onglet Importer), il n'affecte aucune table déjà présente.
-- ============================================================

CREATE TABLE IF NOT EXISTS matieres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    note_max DECIMAL(5,2) NOT NULL DEFAULT 20,
    ordre INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

INSERT INTO matieres (nom, note_max, ordre)
SELECT * FROM (SELECT 'Connaissance du Coran' AS nom, 20 AS note_max, 1 AS ordre) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM matieres LIMIT 1);

INSERT INTO matieres (nom, note_max, ordre)
SELECT * FROM (SELECT 'Jurisprudence islamique (Fiqh)', 20, 2) AS tmp
WHERE (SELECT COUNT(*) FROM matieres) = 1;

INSERT INTO matieres (nom, note_max, ordre)
SELECT * FROM (SELECT 'Dogme (Aqida)', 20, 3) AS tmp
WHERE (SELECT COUNT(*) FROM matieres) = 2;

INSERT INTO matieres (nom, note_max, ordre)
SELECT * FROM (SELECT 'Culture générale islamique', 20, 4) AS tmp
WHERE (SELECT COUNT(*) FROM matieres) = 3;

INSERT INTO matieres (nom, note_max, ordre)
SELECT * FROM (SELECT 'Gestion associative (AEEMCI)', 20, 5) AS tmp
WHERE (SELECT COUNT(*) FROM matieres) = 4;

CREATE TABLE IF NOT EXISTS notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    seminariste_id INT NOT NULL,
    matiere_id INT NOT NULL,
    note DECIMAL(5,2) NULL,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_sem_matiere (seminariste_id, matiere_id),
    FOREIGN KEY (seminariste_id) REFERENCES seminaristes(id) ON DELETE CASCADE,
    FOREIGN KEY (matiere_id) REFERENCES matieres(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS parametres (
    cle VARCHAR(50) PRIMARY KEY,
    valeur VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

INSERT IGNORE INTO parametres (cle, valeur) VALUES ('resultats_publies', '0');
