<?php
require_once __DIR__ . '/includes/init.php';
exigerRole(['seminariste']);
$u = utilisateurCourant();

$stmt = $pdo->prepare("SELECT * FROM seminaristes WHERE id = ?");
$stmt->execute([$u['seminariste_id']]);
$seminariste = $stmt->fetch();

if (!$seminariste) { die("Fiche introuvable."); }

if ($seminariste['test_complete']) {
    redirect('/espace/fiche');
}

if ($seminariste['dortoir'] === 'Pépinière') {
    redirect('/espace/fiche');
}

if (!testOuvert($pdo)) {
    redirect('/espace/fiche');
}

const DUREE_TEST = DUREE_TEST_MINUTES * 60;
$cleSession = 'quiz_' . (int)$seminariste['id'];
$pdo->exec("CREATE TABLE IF NOT EXISTS parametres (cle VARCHAR(50) PRIMARY KEY, valeur VARCHAR(255) NOT NULL) ENGINE=InnoDB");
// L'état du test (début + questions) est gardé en base : se déconnecter/reconnecter ne remet pas le chrono à zéro.
$stEtat = $pdo->prepare("SELECT valeur FROM parametres WHERE cle = ?");
$stEtat->execute([$cleSession]);
$etat = $stEtat->fetchColumn();
if ($etat && strpos($etat, '|') !== false) {
    [$debutDb, $listeDb] = explode('|', $etat, 2);
    $_SESSION[$cleSession] = ['debut' => (int)$debutDb, 'ids' => array_values(array_filter(array_map('intval', explode(',', $listeDb))))];
} else {
    unset($_SESSION[$cleSession]);
}

// Les questions et l'heure de début sont fixées côté serveur dès l'ouverture du test
// (le chrono continue même si la page est rechargée, et la correction porte sur les mêmes questions).
if (empty($_SESSION[$cleSession]['ids'])) {
    $configBanques = $pdo->query("SELECT * FROM config_quiz")->fetchAll();
    $ids = [];
    foreach ($configBanques as $banque) {
        if ($banque['nb_questions_a_tirer'] > 0) {
            $st = $pdo->prepare("SELECT id FROM questions WHERE banque = ? ORDER BY RAND() LIMIT ?");
            $st->bindValue(1, $banque['banque'], PDO::PARAM_INT);
            $st->bindValue(2, $banque['nb_questions_a_tirer'], PDO::PARAM_INT);
            $st->execute();
            foreach ($st->fetchAll(PDO::FETCH_COLUMN) as $qid) { $ids[] = (int)$qid; }
        }
    }
    shuffle($ids);
    $_SESSION[$cleSession] = ['ids' => $ids, 'debut' => time()];
    $pdo->prepare("INSERT INTO parametres (cle, valeur) VALUES (?, ?) ON DUPLICATE KEY UPDATE valeur = VALUES(valeur)")
        ->execute([$cleSession, time() . '|' . implode(',', $ids)]);
}
$idsQuiz = $_SESSION[$cleSession]['ids'];
$questions = [];
if ($idsQuiz) {
    $in = implode(',', array_fill(0, count($idsQuiz), '?'));
    $st = $pdo->prepare("SELECT * FROM questions WHERE id IN ($in)");
    $st->execute($idsQuiz);
    $parId = [];
    foreach ($st->fetchAll() as $q) { $parId[(int)$q['id']] = $q; }
    foreach ($idsQuiz as $qid) { if (isset($parId[$qid])) $questions[] = $parId[$qid]; }
}

if (empty($questions)) {
    unset($_SESSION[$cleSession]);
    die("Aucune question configurée pour le test.");
}

$tempsRestant = max(0, DUREE_TEST - (time() - (int)$_SESSION[$cleSession]['debut']));
// Temps écoulé (ex. page rechargée après les 20 minutes) : soumission automatique
$tempsEcoule = $tempsRestant <= 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' || $tempsEcoule) {
    $bonnesReponses = 0;
    $totalQuestionsTirees = count($questions);
    $insertReponse = $pdo->prepare("INSERT INTO reponses_test (seminariste_id, question_id, reponse_donnee, correcte) VALUES (?,?,?,?)");
    
    foreach ($questions as $q) {
        $rep = $_POST['q' . $q['id']] ?? null;
        if (!in_array($rep, ['A','B','C','D'], true)) { $rep = null; }
        $correcte = ($rep !== null && $rep === $q['bonne_reponse']) ? 1 : 0;
        if ($correcte) $bonnesReponses++;
        $insertReponse->execute([$seminariste['id'], $q['id'], $rep, $correcte]);
    }
    
    // Calcul proportionnel sur 20
    $note = round(($bonnesReponses / $totalQuestionsTirees) * 20, 2);
    $niveau = determinerNiveauTest($note);

    $pdo->prepare("UPDATE seminaristes SET test_complete = 1, note_test = ?, niveau_affecte = ? WHERE id = ?")
        ->execute([$note, $niveau, $seminariste['id']]);

    unset($_SESSION[$cleSession]);
    $pdo->prepare("DELETE FROM parametres WHERE cle = ?")->execute([$cleSession]);
    redirect('/espace/fiche');
}

$titrePage = "Test d'entrée";
require_once __DIR__ . '/includes/header.php';
?>
<section class="section">
    <div class="container form-wrap">
        <div class="section-titre">
            <span class="eyebrow">Test d'entrée - Noté sur 20</span>
            <h2>Bonne chance, <?= e($seminariste['nom_prenoms']) ?> !</h2>
            <p style="color:var(--texte-doux);">Répondez aux <?= count($questions) ?> questions ci-dessous. Vous avez <strong><?= DUREE_TEST_MINUTES ?> minutes</strong> : à la fin du temps, le test est soumis automatiquement avec les réponses déjà données.</p>
        </div>

        <div id="quiz-timer" class="timer-box" data-seconds="<?= $tempsRestant ?>">⏱ Temps restant : <?= floor($tempsRestant / 60) ?>:<?= str_pad($tempsRestant % 60, 2, '0', STR_PAD_LEFT) ?></div>

        <form method="post" id="quiz-form">
            <?php foreach ($questions as $i => $q): ?>
                <div class="carte question-card">
                    <span class="categorie"><?= e($q['categorie']) ?></span>
                    <h3>Q<?= $i + 1 ?>. <?= e($q['enonce']) ?></h3>
                    <label class="option-item"><input type="radio" name="q<?= $q['id'] ?>" value="A"> <?= e($q['option_a']) ?></label>
                    <label class="option-item"><input type="radio" name="q<?= $q['id'] ?>" value="B"> <?= e($q['option_b']) ?></label>
                    <label class="option-item"><input type="radio" name="q<?= $q['id'] ?>" value="C"> <?= e($q['option_c']) ?></label>
                    <label class="option-item"><input type="radio" name="q<?= $q['id'] ?>" value="D"> <?= e($q['option_d']) ?></label>
                </div>
            <?php endforeach; ?>
            <button type="submit" class="btn btn-primaire btn-block">Soumettre mes réponses</button>
        </form>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
