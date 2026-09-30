<?php
require_once __DIR__ . '/../includes/init.php';
exigerRole(['scientifique', 'superadmin']);

$erreurs = [];
$succes = null;

$banques = $pdo->query("SELECT * FROM config_quiz ORDER BY banque")->fetchAll();
$banquesParId = array_column($banques, 'nom_banque', 'banque');

if (isset($_GET['supprimer'])) {
    $pdo->prepare("DELETE FROM questions WHERE id = ?")->execute([(int)$_GET['supprimer']]);
    redirect('/admin/questions');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $banque = (int)($_POST['banque'] ?? 1);
    $categorie = trim($_POST['categorie'] ?? '');
    $enonce = trim($_POST['enonce'] ?? '');
    $a = trim($_POST['option_a'] ?? '');
    $b = trim($_POST['option_b'] ?? '');
    $c = trim($_POST['option_c'] ?? '');
    $d = trim($_POST['option_d'] ?? '');
    $bonne = $_POST['bonne_reponse'] ?? '';

    if ($categorie === '' || $enonce === '' || $a === '' || $b === '' || $c === '' || $d === '' || !in_array($bonne, ['A','B','C','D'], true) || empty($banquesParId[$banque])) {
        $erreurs[] = "Tous les champs sont obligatoires et la banque doit être valide.";
    } else {
        if ($id > 0) {
            $pdo->prepare("UPDATE questions SET banque=?, categorie=?, enonce=?, option_a=?, option_b=?, option_c=?, option_d=?, bonne_reponse=? WHERE id=?")
                ->execute([$banque, $categorie, $enonce, $a, $b, $c, $d, $bonne, $id]);
            $succes = "Question modifiée.";
        } else {
            $pdo->prepare("INSERT INTO questions (banque, categorie, enonce, option_a, option_b, option_c, option_d, bonne_reponse) VALUES (?,?,?,?,?,?,?,?)")
                ->execute([$banque, $categorie, $enonce, $a, $b, $c, $d, $bonne]);
            $succes = "Question ajoutée.";
        }
    }
}

$questionEnEdition = null;
if (isset($_GET['modifier'])) {
    $stmt = $pdo->prepare("SELECT * FROM questions WHERE id = ?");
    $stmt->execute([(int)$_GET['modifier']]);
    $questionEnEdition = $stmt->fetch();
}

$questions = $pdo->query("SELECT * FROM questions ORDER BY banque, categorie, id")->fetchAll();

$titrePage = "Questions du test";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/admin_nav.php';
?>
<section class="section">
    <div class="container">
        <div class="section-titre" style="text-align:left;">
            <h2>Banque de questions (<?= count($questions) ?>)</h2>
            <p><a href="<?= BASE_URL ?>/admin/config_quiz" class="btn btn-sm btn-outline">⚙️ Configurer le tirage des banques</a></p>
        </div>

        <?php foreach ($erreurs as $err): ?><div class="alert alert-erreur"><?= e($err) ?></div><?php endforeach; ?>
        <?php if ($succes): ?><div class="alert alert-succes"><?= e($succes) ?></div><?php endif; ?>

        <div class="carte" style="margin-bottom:24px;">
            <h3><?= $questionEnEdition ? 'Modifier la question' : 'Ajouter une question' ?></h3>
            <form method="post">
                <input type="hidden" name="id" value="<?= $questionEnEdition['id'] ?? '' ?>">
                <div class="form-row">
                    <div class="form-group">
                        <label>Banque de rattachement</label>
                        <select name="banque" required>
                            <?php foreach ($banques as $b): ?>
                                <option value="<?= $b['banque'] ?>" <?= (($questionEnEdition['banque'] ?? '') == $b['banque']) ? 'selected' : '' ?>>
                                    Banque <?= $b['banque'] ?> - <?= e($b['nom_banque']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Catégorie (thème précis)</label>
                        <input type="text" name="categorie" required value="<?= e($questionEnEdition['categorie'] ?? '') ?>" placeholder="Ex: Fiqh">
                    </div>
                    <div class="form-group">
                        <label>Bonne réponse</label>
                        <select name="bonne_reponse" required>
                            <?php foreach (['A','B','C','D'] as $opt): ?>
                                <option value="<?= $opt ?>" <?= (($questionEnEdition['bonne_reponse'] ?? '') === $opt) ? 'selected' : '' ?>><?= $opt ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Énoncé de la question</label>
                    <textarea name="enonce" rows="2" required><?= e($questionEnEdition['enonce'] ?? '') ?></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group"><label>Option A</label><input type="text" name="option_a" required value="<?= e($questionEnEdition['option_a'] ?? '') ?>"></div>
                    <div class="form-group"><label>Option B</label><input type="text" name="option_b" required value="<?= e($questionEnEdition['option_b'] ?? '') ?>"></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label>Option C</label><input type="text" name="option_c" required value="<?= e($questionEnEdition['option_c'] ?? '') ?>"></div>
                    <div class="form-group"><label>Option D</label><input type="text" name="option_d" required value="<?= e($questionEnEdition['option_d'] ?? '') ?>"></div>
                </div>
                <button class="btn btn-primaire"><?= $questionEnEdition ? 'Enregistrer les modifications' : 'Ajouter la question' ?></button>
                <?php if ($questionEnEdition): ?><a href="<?= BASE_URL ?>/admin/questions" class="btn btn-outline">Annuler</a><?php endif; ?>
            </form>
        </div>

        <div class="table-wrap">
            <table>
                <thead><tr><th>Banque</th><th>Catégorie</th><th>Énoncé</th><th>Bonne réponse</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($questions as $q): ?>
                    <tr>
                        <td><strong>Banque <?= e($q['banque']) ?></strong><br><small><?= e($banquesParId[$q['banque']] ?? '') ?></small></td>
                        <td><span class="pill pill-vert"><?= e($q['categorie']) ?></span></td>
                        <td><?= e($q['enonce']) ?></td>
                        <td><?= e($q['bonne_reponse']) ?></td>
                        <td style="white-space:nowrap;">
                            <a href="<?= BASE_URL ?>/admin/questions?modifier=<?= $q['id'] ?>" class="btn btn-sm btn-outline">✏️</a>
                            <a href="<?= BASE_URL ?>/admin/questions?supprimer=<?= $q['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer cette question ?')">🗑️</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
