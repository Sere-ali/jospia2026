-- ============================================================
-- JOSPIA 2026 - Migration (Paiement Wave & Quiz 6 Banques)
-- ============================================================

-- 1. Ajout du système de paiement
CREATE TABLE IF NOT EXISTS paiements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    seminariste_id INT NOT NULL,
    reference_transaction VARCHAR(255) NOT NULL,
    statut ENUM('en attente', 'validé', 'rejeté') NOT NULL DEFAULT 'en attente',
    motif_rejet TEXT NULL,
    admin_validateur_id INT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (seminariste_id) REFERENCES seminaristes(id) ON DELETE CASCADE,
    FOREIGN KEY (admin_validateur_id) REFERENCES comptes(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 2. Système de 6 banques de questions
ALTER TABLE questions ADD COLUMN banque INT NOT NULL DEFAULT 1 COMMENT 'ID de la banque (1 à 6)';

CREATE TABLE IF NOT EXISTS config_quiz (
    banque INT PRIMARY KEY,
    nom_banque VARCHAR(100) NOT NULL,
    nb_questions_a_tirer INT NOT NULL DEFAULT 3
) ENGINE=InnoDB;

INSERT INTO config_quiz (banque, nom_banque, nb_questions_a_tirer) VALUES 
(1, 'Connaissance du Coran', 3),
(2, 'Jurisprudence (Fiqh)', 4),
(3, 'Dogme (Aqida)', 3),
(4, 'Culture générale islamique', 4),
(5, 'Gestion associative AEEMCI', 3),
(6, 'Culture académique / Autres', 3);

-- Stocker les questions tirées pour chaque séminariste (ajouté à reponses_test)
-- La table reponses_test contient déjà (seminariste_id, question_id, reponse_donnee, correcte)
-- Donc elle suffit pour stocker le tirage et les réponses de chaque séminariste.
