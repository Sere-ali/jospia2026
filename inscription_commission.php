<?php
$titrePage = "Inscription Commission";
require_once __DIR__ . '/includes/header.php';

if (parametre('inscriptions_commission', '1') === '0') {
    echo '<div class="container" style="max-width:640px;margin:40px auto;"><div class="carte" style="text-align:center;"><h2>Inscriptions fermées</h2><p>Les inscriptions des membres de commission sont closes pour le moment.</p><a href="' . BASE_URL . '/" class="btn btn-outline">&larr; Accueil</a></div></div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$erreurs = [];
$succes = null;
$identifiantsGeneres = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom_prenoms'] ?? '');
    $commission = trim($_POST['commission'] ?? '');
    $contact = numeroLocal($_POST['contact'] ?? '');

    if ($nom === '') $erreurs[] = "Le nom et prénoms sont obligatoires.";
    if (!in_array($commission, listeCommissions(), true)) $erreurs[] = "Veuillez choisir une commission valide.";
    if ($contact === '' || !preg_match('/^[0-9]{8,15}$/', $contact)) $erreurs[] = "Le contact doit contenir uniquement des chiffres (8 à 15).";
    if (empty($_FILES['photo']['name'])) $erreurs[] = "La photo est obligatoire.";

    if (empty($erreurs)) {
        $nomPhoto = uploadPhoto($_FILES['photo']);
        if (!$nomPhoto) $erreurs[] = "La photo n'a pas pu être enregistrée (formats acceptés : jpg, jpeg, png, webp - 5 Mo max).";
    }

    if (empty($erreurs)) {
        $matricule = genererMatricule('CM');
        $stmt = $pdo->prepare("INSERT INTO membres_commission (nom_prenoms, commission, contact, photo, matricule) VALUES (?,?,?,?,?)");
        $stmt->execute([$nom, $commission, $contact, $nomPhoto, $matricule]);
        $membreId = $pdo->lastInsertId();

        [$identifiant, $motDePasse] = genererIdentifiantMotDePasse($contact);
        $chk = $pdo->prepare("SELECT COUNT(*) FROM comptes WHERE identifiant = ?");
        $chk->execute([$identifiant]);
        if ($chk->fetchColumn() > 0) $identifiant .= '_' . $membreId;

        $hash = password_hash($motDePasse, PASSWORD_DEFAULT);
        $pdo->prepare("INSERT INTO comptes (identifiant, mot_de_passe, mdp_initial, role, membre_id, nom_affiche) VALUES (?,?,?,?,?,?)")
            ->execute([$identifiant, $hash, $motDePasse, 'membre', $membreId, $nom]);

        // Connexion automatique : la page personnelle s'ouvre tout de suite avec les identifiants.
        $stC = $pdo->prepare("SELECT * FROM comptes WHERE identifiant = ? LIMIT 1");
        $stC->execute([$identifiant]);
        if (!estConnecte() && ($compteNew = $stC->fetch())) {
            session_regenerate_id(true);
            $_SESSION['compte_id'] = $compteNew['id'];
            $_SESSION['compte'] = $compteNew;
            redirect('/espace/fiche?inscrit=1');
        }
        $succes = "Inscription réussie ! Votre badge sera généré automatiquement et sera visible sur votre espace personnel (téléchargement réservé aux administrateurs).";
        $identifiantsGeneres = ['id' => $identifiant, 'mdp' => $motDePasse, 'matricule' => $matricule];
    }
}
?>
<section class="section form-page">
    <div class="container <?= $succes ? 'form-wrap' : 'form-layout' ?>">
        <?php if (!$succes): $asideType = 'commission'; require __DIR__ . '/includes/form_aside.php'; endif; ?>
        <div class="<?= $succes ? '' : 'form-main' ?>">
        <div class="section-titre form-titre">
            <span class="eyebrow">Commission</span>
            <h2>Inscription - Membre de commission</h2>
            <p class="form-intro">Les champs marqués <span class="req">*</span> sont obligatoires.</p>
        </div>

        <?php if ($succes): ?>
            <div class="alert alert-succes"><?= e($succes) ?></div>
            <div class="carte" style="margin-bottom:24px;">
                <h3>Vos identifiants de connexion</h3>
                <p>Conservez-les précieusement pour accéder à votre espace personnel.</p>
                <p class="mono"><strong>Matricule :</strong> <?= e($identifiantsGeneres['matricule']) ?><br>
                   <strong>Identifiant (votre numéro, sans 225) :</strong> <?= e($identifiantsGeneres['id']) ?><br>
                   <strong>Mot de passe :</strong> <?= e($identifiantsGeneres['mdp']) ?></p>
                <a href="<?= BASE_URL ?>/login" class="btn btn-primaire">Me connecter maintenant</a>
            </div>
        <?php else: ?>

            <?php foreach ($erreurs as $err): ?>
                <div class="alert alert-erreur"><?= e($err) ?></div>
            <?php endforeach; ?>

            <form method="post" enctype="multipart/form-data" novalidate class="form-pro" id="form-inscription">
                <fieldset>
                    <legend>Informations personnelles</legend>
                    <div class="form-group">
                        <label>Nom et prénoms <span class="req">*</span></label>
                        <input type="text" name="nom_prenoms" required value="<?= e($_POST['nom_prenoms'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Commission <span class="req">*</span></label>
                        <select name="commission" required>
                            <option value="">- Choisir une commission -</option>
                            <?php foreach (listeCommissions() as $c): ?>
                                <option value="<?= e($c) ?>" <?= (($_POST['commission'] ?? '') === $c) ? 'selected' : '' ?>><?= e($c) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Contact (téléphone) <span class="req">*</span></label>
                        <input type="tel" name="contact" inputmode="numeric" pattern="[0-9]{8,15}" maxlength="15" autocomplete="tel" title="Chiffres uniquement (8 à 15)" required placeholder="Ex : 0700000000" value="<?= e($_POST['contact'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Photo d'identité <span class="req">*</span></label>
                        <input type="file" name="photo" accept="image/*" required>
                        <div class="help-text">Format jpg/png/webp, 5 Mo max. Utilisée pour générer votre badge.</div>
                        <img id="apercu-photo" alt="Aperçu de la photo" style="display:none;">
                    </div>
                </fieldset>
                <button type="submit" class="btn btn-primaire btn-block btn-envoi"><span>Valider mon inscription</span><i aria-hidden="true">→</i></button>
            </form>
        <?php endif; ?>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
