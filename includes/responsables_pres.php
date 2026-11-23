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
/** Rang d'affichage d'une commission (ordre de la présentation des responsables). */
function respPresRang($commission) {
    $n = normaliserCommission($commission);
    if ($n === 'MG' || $n === 'MANAGER GENERAL') return 0;
    if ($n === 'MGA' || strpos($n, 'MANAGER GENERAL') === 0 || strpos($n, 'ADJOINT') !== false) return 1;
    foreach ([['SCIENTIFIQUE', 2], ['ADMINISTRATION', 3], ['LOGISTIQUE', 4], ['MOBILISATION', 5], ['MIC', 5], ['INFORMATION', 5], ['COMMUNICATION', 5],
              ['RESTAURATION', 6], ['PROTOCOLE', 7], ['SECURITE', 8], ['SANTE', 9], ['HYGIENE', 10], ['PEPINIERE', 11]] as [$k, $r]) {
        if (strpos($n, $k) !== false) return $r;
    }
    return 99;
}
/** Liste triée dans l'ordre de passage : de la 11e commission aux Managers généraux (MGA puis MG). */
function respPresListe(PDO $pdo) {
    respPresPreparer($pdo);
    $l = $pdo->query("SELECT * FROM presentation_responsables")->fetchAll();
    usort($l, function ($a, $b) {
        return respPresRang($b['commission']) <=> respPresRang($a['commission']) ?: strcasecmp($a['nom_prenoms'], $b['nom_prenoms']);
    });
    return $l;
}
