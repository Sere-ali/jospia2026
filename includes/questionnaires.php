<?php
/**
 * Test d'entrée JOSPIA 2026 : 50 questionnaires de 10 questions (fichiers « Questionnaires 1 à 50 »).
 * ANTI-TRICHE : chaque séminariste reçoit un test PERSONNALISÉ de 10 questions, une par rubrique
 * (rubrique n = question n des questionnaires : Fiqh, Doctrine, Coran, Hadith, Vie du Prophète, … Thème JOSPIA).
 * Pour chaque rubrique, on prend la question la moins tirée jusqu'ici (au hasard en cas d'égalité) :
 * les 50 variantes tournent équitablement, deux voisins n'ont presque jamais la même question.
 * L'ordre des questions et l'ordre des 4 réponses (A, B, C, D) sont aussi mélangés pour chacun.
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
    if (!questionnairesColonne($pdo, 'questions', 'nb_tirages')) {
        $pdo->exec("ALTER TABLE questions ADD COLUMN nb_tirages INT NOT NULL DEFAULT 0");
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

/**
 * Compose un test personnalisé : 10 questions (une par rubrique 1 à 10), chacune choisie parmi les moins tirées,
 * sans deux énoncés identiques. Retourne [ids mélangés, permutations des réponses (ex. "CADB" : la case A affiche la réponse C)].
 */
function testComposer(PDO $pdo) {
    questionnairesPreparer($pdo);
    $pdo->query("SELECT GET_LOCK('jospia_test_compo', 10)");
    try {
        $ids = []; $vus = [];
        $st = $pdo->prepare("SELECT id, enonce FROM questions WHERE questionnaire IS NOT NULL AND ordre = ? ORDER BY nb_tirages ASC, RAND() LIMIT 12");
        for ($k = 1; $k <= 10; $k++) {
            $st->execute([$k]);
            foreach ($st->fetchAll() as $q) {
                $cle = mb_strtolower(preg_replace('/^\([^)]*\)\s*|\W+/u', '', $q['enonce']), 'UTF-8');
                if (isset($vus[$cle])) continue;
                $vus[$cle] = true; $ids[] = (int)$q['id']; break;
            }
        }
        if ($ids) { $pdo->exec("UPDATE questions SET nb_tirages = nb_tirages + 1 WHERE id IN (" . implode(',', $ids) . ")"); }
    } finally {
        $pdo->query("SELECT RELEASE_LOCK('jospia_test_compo')");
    }
    shuffle($ids);
    $perms = [];
    foreach ($ids as $_) { $l = ['A', 'B', 'C', 'D']; shuffle($l); $perms[] = implode('', $l); }
    return [$ids, $perms];
}
