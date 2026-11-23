<?php
/** Fiches des responsables de commission (présentation « Les responsables »). */
function respPresPreparer(PDO $pdo) {
    static $ok = false; if ($ok) return; $ok = true;
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS presentation_responsables (
            id INT AUTO_INCREMENT PRIMARY KEY,
            commission VARCHAR(100) NOT NULL,
            nom_prenoms VARCHAR(190) NOT NULL,
            profil VARCHAR(190) NOT NULL DEFAULT '',
            annee_debut SMALLINT NULL,
            niveau_fonction VARCHAR(255) NOT NULL DEFAULT '',
            photo VARCHAR(255) NOT NULL DEFAULT '',
            cree_le TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uq_resp (commission, nom_prenoms)
        ) DEFAULT CHARSET=utf8mb4");
    } catch (Throwable $e) { error_log('Migration responsables : ' . $e->getMessage()); }
}
/** Liste triée selon l'ordre des commissions. */
function respPresListe(PDO $pdo) {
    respPresPreparer($pdo);
    $l = $pdo->query("SELECT * FROM presentation_responsables")->fetchAll();
    $ordre = array_flip(listeCommissions());
    usort($l, function ($a, $b) use ($ordre) {
        $x = $ordre[$a['commission']] ?? 99; $y = $ordre[$b['commission']] ?? 99;
        return $x <=> $y ?: strcasecmp($a['nom_prenoms'], $b['nom_prenoms']);
    });
    return $l;
}
