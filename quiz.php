<?php
require_once __DIR__ . '/includes/init.php';
exigerRole(['seminariste']);
$u = utilisateurCourant();

$stmt = $pdo->prepare("SELECT * FROM seminaristes WHERE id = ?");
$stmt->execute([$u['seminariste_id']]);
$seminariste = $stmt->fetch();

if (!$seminariste) { die("Fiche introuvable."); }

if ($seminariste['test_complete']) {
    redirect('/espace/fiche.php');
}

if ($seminariste['dortoir'] === 'Pépinière') {
    redirect('/espace/fiche.php');
}

// Récupération de la configuration des banques
$configBanques = $pdo->query("SELECT * FROM config_quiz")->fetchAll();
$questions = [];

// Tirage aléatoire par banque
foreach ($configBanques as $banque) {
    if ($banque['nb_questions_a_tirer'] > 0) {
        $stmt = $pdo->prepare("SELECT * FROM questions WHERE banque = ? ORDER BY RAND() LIMIT ?");
        // On bind PDO::PARAM_INT car LIMIT accepte mal les string par défaut
        $stmt->bindValue(1, $banque['banque'], PDO::PARAM_INT);
        $stmt->bindValue(2, $banque['nb_questions_a_tirer'], PDO::PARAM_INT);
        $stmt->execute();
        $questionsBanque = $stmt->fetchAll();
        $questions = array_merge($questions, $questionsBanque);
    }
}
// Mélanger le tout
shuffle($questions);

if (empty($questions)) {
    die("Aucune question configurée pour le test.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bonnesReponses = 0;
    $totalQuestionsTirees = count($questions);
    $insertReponse = $pdo->prepare("INSERT INTO reponses_test (seminariste_id, question_id, reponse_donnee, correcte) VALUES (?,?,?,?)");
    
    foreach ($questions as $q) {
        $rep = $_POST['q' . $q['id']] ?? null;
        $correcte = ($rep !== null && $rep === $q['bonne_reponse']) ? 1 : 0;
        if ($correcte) $bonnesReponses++;
        $insertReponse->execute([$seminariste['id'], $q['id'], $rep, $correcte]);
    }
    
    // Calcul proportionnel sur 20
    $note = round(($bonnesReponses / $totalQuestionsTirees) * 20, 2);
    $niveau = determinerNiveauTest($note);

    $pdo->prepare("UPDATE seminaristes SET test_complete = 1, note_test = ?, niveau_affecte = ? WHERE id = ?")
        ->execute([$note, $niveau, $seminariste['id']]);

    redirect('/espace/fiche.php');
}

$titrePage = "Test d'entrée";
require_once __DIR__ . '/includes/header.php';
?>
<section class="section">
    <div class="container form-wrap">
        <div class="section-titre">
            <span class="eyebrow">Test d'entrée — Noté sur 20</span>
            <h2>Bonne chance, <?= e($seminariste['nom_prenoms']) ?> !</h2>
            <p style="color:var(--texte-doux);">Répondez aux <?= count($questions) ?> questions ci-dessous. Le test se soumet automatiquement à la fin du temps imparti.</p>
        </div>

        <div id="quiz-timer" class="timer-box" data-seconds="1200">⏱ Temps restant : 20:00</div>

        <form method="post" id="quiz-form">
            <?php foreach ($questions as $i => $q): ?>
                <div class="carte question-card">
                    <span class="categorie"><?= e($q['categorie']) ?></span>
                    <h3>Q<?= $i + 1 ?>. <?= e($q['enonce']) ?></h3>
                    <label class="option-item"><input type="radio" name="q<?= $q['id'] ?>" value="A" required> <?= e($q['option_a']) ?></label>
                    <label class="option-item"><input type="radio" name="q<?= $q['id'] ?>" value="B"> <?= e($q['option_b']) ?></label>
                    <label class="option-item"><input type="radio" name="q<?= $q['id'] ?>" value="C"> <?= e($q['option_c']) ?></label>
                    <label class="option-item"><input type="radio" name="q<?= $q['id'] ?>" value="D"> <?= e($q['option_d']) ?></label>
                </div>
            <?php endforeach; ?>
            <button type="submit" class="btn btn-primaire btn-block">Soumettre mes réponses</button>
        </form>
    </div>
</section>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
