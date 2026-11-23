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
    // Une même personne inscrite deux fois dans le groupe des managers (ex. Manager Général Adjoint + responsable de la commission Protocole) :
    // la 1re fiche reste « Manager Général Adjoint », la 2e est classée dans la commission Protocole.
    $norm = function ($t) { return strtr(mb_strtoupper(trim($t), 'UTF-8'), ['É' => 'E', 'È' => 'E', 'Ï' => 'I']); };
    $protocole = 'PROTOCOLE';
    foreach (listeCommissions() as $c) { if (stripos(normaliserCommission($c), 'PROTOCOLE') !== false) { $protocole = $c; break; } }
    $vus = [];
    usort($l, function ($a, $b) { return (int)$a['id'] <=> (int)$b['id']; });
    foreach ($l as $i => $r) {
        if (respPresRang($r['commission']) > 1) continue;
        $nom = $norm($r['nom_prenoms']);
        if (isset($vus[$nom])) { $l[$i]['commission'] = $protocole; }
        else { $vus[$nom] = true; if (strpos($nom, 'KONE OUSMANE') !== false) $l[$i]['commission'] = 'MANAGER GÉNÉRAL ADJOINT'; }
    }
    usort($l, function ($a, $b) {
        return respPresRang($b['commission']) <=> respPresRang($a['commission']) ?: strcasecmp($a['nom_prenoms'], $b['nom_prenoms']);
    });
    return $l;
}
