<?php
/**
 * Test d'entrée JOSPIA 2026 : 50 questionnaires de 10 questions (fichiers « Questionnaires 1 à 50 »).
 * Chaque séminariste qui ouvre le test reçoit un questionnaire TIRÉ AU SORT parmi les 50
 * (attribué à la première ouverture du test, puis conservé).
 */
const TEST_NB_QUESTIONNAIRES = 50;
const TEST_CATEGORIE = "Test d'entrée JOSPIA 2026";

function questionnairesColonne(PDO $pdo, $table, $col) {
    $st = $pdo->prepare("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?");
    $st->execute([$table, $col]);
    return (int)$st->fetchColumn() > 0;
}

/** Colonnes nécessaires + import des 500 questions (une seule fois). */
function questionnairesPreparer(PDO $pdo) {
    static $fait = false;
    if ($fait) return;
    $fait = true;
    if (!questionnairesColonne($pdo, 'questions', 'questionnaire')) {
        $pdo->exec("ALTER TABLE questions ADD COLUMN questionnaire INT NULL, ADD COLUMN ordre INT NULL, ADD INDEX idx_questionnaire (questionnaire, ordre)");
    }
    if (!questionnairesColonne($pdo, 'seminaristes', 'questionnaire_test')) {
        $pdo->exec("ALTER TABLE seminaristes ADD COLUMN questionnaire_test INT NULL, ADD COLUMN rang_test INT NULL");
    }
    $nb = (int)$pdo->query("SELECT COUNT(*) FROM questions WHERE questionnaire IS NOT NULL")->fetchColumn();
    if ($nb === 0) {
        $data = json_decode(file_get_contents(__DIR__ . '/data/test_entree_2026.json'), true) ?: [];
        $ins = $pdo->prepare("INSERT INTO questions (categorie, enonce, option_a, option_b, option_c, option_d, bonne_reponse, banque, questionnaire, ordre) VALUES (?,?,?,?,?,?,?,0,?,?)");
        $pdo->beginTransaction();
        foreach ($data as $q) {
            $ins->execute([TEST_CATEGORIE, $q['enonce'], $q['a'], $q['b'], $q['c'], $q['d'], $q['rep'], (int)$q['questionnaire'], (int)$q['ordre']]);
        }
        $pdo->commit();
    }
}

/** Questionnaire du séminariste : tiré au sort à la première ouverture du test, puis conservé. */
function questionnaireAttribuer(PDO $pdo, $seminaristeId) {
    questionnairesPreparer($pdo);
    $st = $pdo->prepare("SELECT questionnaire_test FROM seminaristes WHERE id = ?");
    $st->execute([$seminaristeId]);
    $q = $st->fetchColumn();
    if ($q) return (int)$q;
    $pdo->query("SELECT GET_LOCK('jospia_questionnaire', 10)");
    try {
        $st->execute([$seminaristeId]);
        $q = $st->fetchColumn();
        if ($q) return (int)$q;
        $rang = (int)$pdo->query("SELECT COALESCE(MAX(rang_test), 0) FROM seminaristes")->fetchColumn() + 1;
        $q = random_int(1, TEST_NB_QUESTIONNAIRES); // tirage au sort
        $pdo->prepare("UPDATE seminaristes SET questionnaire_test = ?, rang_test = ? WHERE id = ?")->execute([$q, $rang, $seminaristeId]);
        return $q;
    } finally {
        $pdo->query("SELECT RELEASE_LOCK('jospia_questionnaire')");
    }
}

/** Ids des 10 questions d'un questionnaire, dans l'ordre du fichier. */
function questionnaireQuestions(PDO $pdo, $numero) {
    questionnairesPreparer($pdo);
    $st = $pdo->prepare("SELECT id FROM questions WHERE questionnaire = ? ORDER BY ordre, id");
    $st->execute([(int)$numero]);
    return array_map('intval', $st->fetchAll(PDO::FETCH_COLUMN));
}
