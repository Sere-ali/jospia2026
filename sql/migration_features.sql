-- ============================================================
-- JOSPIA 2026 - Migration (Paiement Wave & Quiz 6 Banques)
-- Idempotente : peut être exécutée plusieurs fois sans erreur.
-- ============================================================

-- 1. Système de paiement
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
ALTER TABLE questions ADD COLUMN IF NOT EXISTS banque INT NOT NULL DEFAULT 1 COMMENT 'ID de la banque (1 à 6)';

CREATE TABLE IF NOT EXISTS config_quiz (
    banque INT PRIMARY KEY,
    nom_banque VARCHAR(100) NOT NULL,
    nb_questions_a_tirer INT NOT NULL DEFAULT 3
) ENGINE=InnoDB;

INSERT IGNORE INTO config_quiz (banque, nom_banque, nb_questions_a_tirer) VALUES
(1, 'Connaissance du Coran', 3),
(2, 'Jurisprudence (Fiqh)', 4),
(3, 'Dogme (Aqida)', 3),
(4, 'Culture générale islamique', 4),
(5, 'Gestion associative AEEMCI', 3),
(6, 'Culture académique / Autres', 3);

-- 3. Répartition des questions existantes dans les banques (selon leur catégorie)
UPDATE questions SET banque = 2 WHERE banque = 1 AND categorie LIKE 'Jurisprudence%';
UPDATE questions SET banque = 3 WHERE banque = 1 AND categorie LIKE 'Dogme%';
UPDATE questions SET banque = 4 WHERE banque = 1 AND categorie LIKE 'Culture g%';
UPDATE questions SET banque = 5 WHERE banque = 1 AND categorie LIKE 'Gestion%';

-- 4. Reçu de paiement avec QR code + rôle "finance"
ALTER TABLE paiements ADD COLUMN IF NOT EXISTS code_recu VARCHAR(40) NULL;
ALTER TABLE paiements ADD COLUMN IF NOT EXISTS montant INT NOT NULL DEFAULT 5000;
ALTER TABLE paiements ADD COLUMN IF NOT EXISTS date_validation DATETIME NULL;
CREATE UNIQUE INDEX IF NOT EXISTS uq_paiements_code_recu ON paiements (code_recu);
UPDATE paiements SET code_recu = REPLACE(UUID(), '-', ''), date_validation = COALESCE(updated_at, NOW()) WHERE statut = 'validé' AND code_recu IS NULL;
ALTER TABLE comptes MODIFY role ENUM('membre','seminariste','admin','superadmin','finance') NOT NULL DEFAULT 'membre';
