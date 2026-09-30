<?php
require_once __DIR__ . '/../includes/init.php';
exigerRole(['admin', 'superadmin']);

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM seminaristes WHERE id = ?");
$stmt->execute([$id]);
$s = $stmt->fetch();
if (!$s) { die("Séminariste introuvable."); }

$erreurs = [];
$succes = null;
$sections = sectionsParAnyama();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom_prenoms'] ?? '');
    $genre = $_POST['genre'] ?? '';
    $niveauEtude = trim($_POST['niveau_etude'] ?? '');
    $anyama = $_POST['anyama'] ?? '';
    $section = trim($_POST['section'] ?? '');
    $lieuResidence = trim($_POST['lieu_residence'] ?? '');
    $maladie = $_POST['maladie'] ?? 'Aucune';
    $maladieAutre = trim($_POST['maladie_autre'] ?? '');
    $age = (int)($_POST['age'] ?? 0);
    $contact = preg_replace('/\D+/', '', $_POST['contact'] ?? '');
    $parentNom = trim($_POST['parent_nom'] ?? '');
    $parentLien = trim($_POST['parent_lien'] ?? '');
    $parentContact = preg_replace('/\D+/', '', $_POST['parent_contact'] ?? '');

    if ($nom === '') $erreurs[] = "Le nom et prénoms sont obligatoires.";
    if (!in_array($genre, ['Masculin', 'Féminin'], true)) $erreurs[] = "Veuillez préciser le genre.";
    if ($niveauEtude === '') $erreurs[] = "Le niveau d'études est obligatoire.";
    if (!isset($sections[$anyama])) $erreurs[] = "Veuillez choisir Anyama 1 ou Anyama 2.";
    if ($section === '') $erreurs[] = "La section est obligatoire.";
    if ($lieuResidence === '') $erreurs[] = "Le lieu de résidence est obligatoire.";
    if ($age < 5 || $age > 100) $erreurs[] = "Veuillez indiquer un âge valide.";
    if ($contact === '' || !preg_match('/^[0-9]{8,15}$/', $contact)) $erreurs[] = "Le contact doit contenir uniquement des chiffres (8 à 15).";
    if ($maladie === 'Autre' && $maladieAutre === '') $erreurs[] = "Veuillez préciser la maladie.";
    if ($parentNom === '') $erreurs[] = "Le nom du parent/tuteur est obligatoire.";
    if ($parentContact === '' || !preg_match('/^[0-9]{8,15}$/', $parentContact)) $erreurs[] = "Le contact du parent/tuteur doit contenir uniquement des chiffres (8 à 15).";

    $nomPhoto = $s['photo'];
    if (!empty($_FILES['photo']['name'])) {
        $uploaded = uploadPhoto($_FILES['photo']);
        if (!$uploaded) {
            $erreurs[] = "La photo n'a pas pu être enregistrée (formats acceptés : jpg, jpeg, png, webp — 5 Mo max).";
        } else {
            $nomPhoto = $uploaded;
        }
    }

    if (empty($erreurs)) {
        $recalc = recalculerDortoirSeminariste($pdo, $s, $age, $genre);

        $pdo->prepare("UPDATE seminaristes SET
                nom_prenoms=?, genre=?, niveau_etude=?, anyama=?, section=?, sous_comite_final=?, lieu_residence=?,
                maladie=?, maladie_autre=?, age=?, contact=?, photo=?, parent_nom=?, parent_lien=?, parent_contact=?,
                dortoir=?, niveau_affecte=?
            WHERE id=?")
            ->execute([
                $nom, $genre, $niveauEtude, $anyama, $section, $section, $lieuResidence,
                $maladie, $maladie === 'Autre' ? $maladieAutre : null, $age, $contact, $nomPhoto,
                $parentNom, $parentLien, $parentContact,
                $recalc['dortoir'], $recalc['niveau_affecte'],
                $id
            ]);
        $pdo->prepare("UPDATE comptes SET nom_affiche=? WHERE seminariste_id=?")->execute([$nom, $id]);

        $stmt = $pdo->prepare("SELECT * FROM seminaristes WHERE id = ?");
        $stmt->execute([$id]);
        $s = $stmt->fetch();
        $succes = "Les informations ont été mises à jour. Dortoir actuel : " . e($s['dortoir']) . ".";
    }
}

$titrePage = "Modifier — " . $s['nom_prenoms'];
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/admin_nav.php';
?>
<section class="section">
    <div class="container form-wrap">
        <a href="<?= BASE_URL ?>/admin/seminaristes.php" class="btn btn-outline btn-sm">&larr; Retour à la liste</a>

        <div class="section-titre" style="text-align:left;margin-top:16px;">
            <h2>Modifier — <?= e($s['nom_prenoms']) ?></h2>
        </div>

        <?php foreach ($erreurs as $err): ?><div class="alert alert-erreur"><?= e($err) ?></div><?php endforeach; ?>
        <?php if ($succes): ?><div class="alert alert-succes"><?= $succes ?></div><?php endif; ?>
        <div class="alert alert-info">ℹ️ Si vous modifiez l'âge ou le genre, le dortoir (et le niveau Pépinière) sont recalculés automatiquement et en toute sécurité.</div>

        <form method="post" enctype="multipart/form-data" novalidate>
            <fieldset>
                <legend>Identité</legend>
                <div class="form-group">
                    <label>Nom et prénoms <span class="req">*</span></label>
                    <input type="text" name="nom_prenoms" required value="<?= e($s['nom_prenoms']) ?>">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Genre <span class="req">*</span></label>
                        <div class="radio-group">
                            <label><input type="radio" name="genre" value="Masculin" <?= $s['genre']==='Masculin'?'checked':'' ?>> Masculin</label>
                            <label><input type="radio" name="genre" value="Féminin" <?= $s['genre']==='Féminin'?'checked':'' ?>> Féminin</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Âge <span class="req">*</span></label>
                        <input type="number" name="age" min="5" max="100" required value="<?= e($s['age']) ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label>Niveau d'études <span class="req">*</span></label>
                    <input type="text" name="niveau_etude" required value="<?= e($s['niveau_etude']) ?>">
                </div>
                <div class="form-group">
                    <label>Lieu de résidence <span class="req">*</span></label>
                    <input type="text" name="lieu_residence" required value="<?= e($s['lieu_residence']) ?>">
                </div>
                <div class="form-group">
                    <label>Contact <span class="req">*</span></label>
                    <input type="tel" name="contact" inputmode="numeric" pattern="[0-9]{8,15}" maxlength="15" autocomplete="tel" title="Chiffres uniquement (8 à 15)" required value="<?= e($s['contact']) ?>">
                </div>
            </fieldset>

            <fieldset>
                <legend>Sous-comité</legend>
                <div class="form-row">
                    <div class="form-group">
                        <label>Sous-comité (Anyama) <span class="req">*</span></label>
                        <select name="anyama" required>
                            <option value="Anyama 1" <?= $s['anyama']==='Anyama 1'?'selected':'' ?>>Anyama 1</option>
                            <option value="Anyama 2" <?= $s['anyama']==='Anyama 2'?'selected':'' ?>>Anyama 2</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Section <span class="req">*</span></label>
                        <input type="text" name="section" required value="<?= e($s['section']) ?>">
                    </div>
                </div>
                <div class="help-text">Dortoir actuel : <strong><?= e($s['dortoir']) ?></strong> — recalculé automatiquement à l'enregistrement selon l'âge.</div>
            </fieldset>

            <fieldset>
                <legend>Santé</legend>
                <div class="form-group">
                    <label>Maladie / allergie</label>
                    <select name="maladie" id="maladie">
                        <?php foreach (['Aucune', 'Paludisme', 'Asthme', 'Allergie', 'Autre'] as $m): ?>
                            <option value="<?= $m ?>" <?= $s['maladie']===$m?'selected':'' ?>><?= $m ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" id="maladie_autre_wrap">
                    <label>Précisez</label>
                    <input type="text" name="maladie_autre" value="<?= e($s['maladie_autre'] ?? '') ?>">
                </div>
            </fieldset>

            <fieldset>
                <legend>Contact d'urgence</legend>
                <div class="form-row">
                    <div class="form-group">
                        <label>Nom du parent/tuteur <span class="req">*</span></label>
                        <input type="text" name="parent_nom" required value="<?= e($s['parent_nom']) ?>">
                    </div>
                    <div class="form-group">
                        <label>Lien de parenté</label>
                        <input type="text" name="parent_lien" value="<?= e($s['parent_lien']) ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label>Contact du parent/tuteur <span class="req">*</span></label>
                    <input type="tel" name="parent_contact" inputmode="numeric" pattern="[0-9]{8,15}" maxlength="15" autocomplete="tel" title="Chiffres uniquement (8 à 15)" required value="<?= e($s['parent_contact']) ?>">
                </div>
            </fieldset>

            <fieldset>
                <legend>Photo</legend>
                <?php if ($s['photo']): ?>
                    <img src="<?= BASE_URL ?>/uploads/photos/<?= e($s['photo']) ?>" style="width:90px;height:90px;object-fit:cover;border-radius:8px;border:2px solid var(--vert);margin-bottom:10px;">
                <?php endif; ?>
                <div class="form-group">
                    <label>Remplacer la photo (facultatif)</label>
                    <input type="file" name="photo" accept="image/*">
                </div>
            </fieldset>

            <button type="submit" class="btn btn-primaire btn-block">Enregistrer les modifications</button>
        </form>
    </div>
</section>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
