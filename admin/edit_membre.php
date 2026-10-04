<?php
require_once __DIR__ . '/../includes/init.php';
$modeUser = !empty($modifierMoiMeme);
if ($modeUser) {
    exigerConnexion();
    $uc = utilisateurCourant();
    if ($uc['role'] !== 'membre' || empty($uc['membre_id'])) { redirect('/espace/fiche'); }
    $id = (int)$uc['membre_id'];
} else {
    exigerRole(['admin', 'superadmin']);
    $id = (int)($_GET['id'] ?? 0);
}
$retour = $modeUser ? '/espace/fiche' : '/admin/commissions';
$stmt = $pdo->prepare("SELECT * FROM membres_commission WHERE id = ?");
$stmt->execute([$id]);
$membre = $stmt->fetch();
if (!$membre) { die("Membre introuvable."); }

$erreurs = [];
$succes = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom_prenoms'] ?? '');
    $commission = trim($_POST['commission'] ?? '');
    $contact = numeroLocal($_POST['contact'] ?? '');

    if ($nom === '') $erreurs[] = "Le nom et prénoms sont obligatoires.";
    if (!in_array($commission, listeCommissions(), true)) $erreurs[] = "Veuillez choisir une commission valide.";
    if ($contact === '' || !preg_match('/^[0-9]{10}$/', $contact)) $erreurs[] = "Le contact doit contenir uniquement des chiffres (10 chiffres).";

    $nomPhoto = $membre['photo'];
    if (!empty($_FILES['photo']['name'])) {
        $uploaded = uploadPhoto($_FILES['photo']);
        if (!$uploaded) {
            $erreurs[] = "La photo n'a pas pu être enregistrée (formats acceptés : jpg, jpeg, png, webp - 5 Mo max).";
        } else {
            $nomPhoto = $uploaded;
        }
    }

    if (empty($erreurs)) {
        $pdo->prepare("UPDATE membres_commission SET nom_prenoms=?, commission=?, contact=?, photo=? WHERE id=?")
            ->execute([$nom, $commission, $contact, $nomPhoto, $id]);
        // Garder le compte de connexion synchronisé (nom affiché)
        $pdo->prepare("UPDATE comptes SET nom_affiche=? WHERE membre_id=?")->execute([$nom, $id]);
        $nouvelId = synchroniserIdentifiantContact($pdo, 'membre_id', $id, $membre['contact'], $contact);
        if ($modeUser && isset($_SESSION['compte'])) { $_SESSION['compte']['nom_affiche'] = $nom; if ($nouvelId) { $_SESSION['compte']['identifiant'] = $nouvelId; } }

        $stmt = $pdo->prepare("SELECT * FROM membres_commission WHERE id = ?");
        $stmt->execute([$id]);
        $membre = $stmt->fetch();
        $succes = "Les informations ont été mises à jour." . (!empty($nouvelId) ? " Votre identifiant de connexion est maintenant : " . $nouvelId . "." : "");
    }
}

$titrePage = "Modifier - " . $membre['nom_prenoms'];
require_once __DIR__ . '/../includes/header.php';
if (!$modeUser) { require_once __DIR__ . '/../includes/admin_nav.php'; }
?>
<section class="section">
    <div class="container form-wrap">
        <a href="<?= BASE_URL . $retour ?>" class="btn btn-outline btn-sm">&larr; <?= $modeUser ? 'Retour à mon espace' : 'Retour à la liste' ?></a>

        <div class="section-titre" style="text-align:left;margin-top:16px;">
            <h2>Modifier - <?= e($membre['nom_prenoms']) ?></h2>
        </div>

        <?php foreach ($erreurs as $err): ?><div class="alert alert-erreur"><?= e($err) ?></div><?php endforeach; ?>
        <?php if ($succes): ?><div class="alert alert-succes"><?= e($succes) ?></div><?php endif; ?>

        <form method="post" enctype="multipart/form-data" novalidate>
            <fieldset>
                <legend>Informations personnelles</legend>
                <div class="form-group">
                    <label>Nom et prénoms <span class="req">*</span></label>
                    <input type="text" name="nom_prenoms" required value="<?= e($membre['nom_prenoms']) ?>">
                </div>
                <div class="form-group">
                    <label>Commission <span class="req">*</span></label>
                    <select name="commission" required>
                        <?php foreach (listeCommissions() as $c): ?>
                            <option value="<?= e($c) ?>" <?= $membre['commission'] === $c ? 'selected' : '' ?>><?= e($c) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Contact (téléphone) <span class="req">*</span></label>
                    <input type="tel" name="contact" inputmode="numeric" pattern="[0-9]{10}" maxlength="10" autocomplete="tel" title="Chiffres uniquement (10 chiffres)" required value="<?= e($membre['contact']) ?>">
                </div>
                <div class="form-group">
                    <label>Photo actuelle</label><br>
                    <?php if ($membre['photo']): ?>
                        <img src="<?= BASE_URL ?>/uploads/photos/<?= e($membre['photo']) ?>" style="width:90px;height:90px;object-fit:cover;border-radius:8px;border:2px solid var(--vert);margin-bottom:10px;">
                    <?php endif; ?>
                    <label>Remplacer la photo (facultatif)</label>
                    <input type="file" name="photo" accept="image/*">
                </div>
            </fieldset>
            <button type="submit" class="btn btn-primaire btn-block">Enregistrer les modifications</button>
        </form>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
