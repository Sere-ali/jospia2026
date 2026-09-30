-- ============================================================
-- JOSPIA 2026 - Migration : ajout du système de critiques
-- des séminaristes envers les commissions.
-- À importer sur une base EXISTANTE (n'affecte aucune donnée déjà présente).
-- ============================================================

CREATE TABLE IF NOT EXISTS critiques (
    id INT AUTO_INCREMENT PRIMARY KEY,
    seminariste_id INT NOT NULL,
    commission VARCHAR(50) NOT NULL,
    contenu TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (seminariste_id) REFERENCES seminaristes(id) ON DELETE CASCADE
) ENGINE=InnoDB;
