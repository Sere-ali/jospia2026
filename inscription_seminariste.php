<?php
$titrePage = "Inscription Séminariste";
require_once __DIR__ . '/includes/header.php';

$erreurs = [];
$succes = null;
$identifiantsGeneres = null;
$sections = sectionsParAnyama();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom_prenoms'] ?? '');
    $genre = $_POST['genre'] ?? '';
    $niveauEtude = mb_strtoupper(trim($_POST['niveau_etude'] ?? ''), 'UTF-8');
    $anyama = $_POST['anyama'] ?? '';
    $section = $_POST['section'] ?? '';
    $sectionAutre = trim($_POST['section_autre'] ?? '');
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
    elseif (!in_array($section, $sections[$anyama], true)) $erreurs[] = "Veuillez choisir une section valide.";
    if ($section === 'Autre' && $sectionAutre === '') $erreurs[] = "Veuillez préciser le nom de la section.";
    if ($lieuResidence === '') $erreurs[] = "Le lieu de résidence est obligatoire.";
    if ($age < 5 || $age > 100) $erreurs[] = "Veuillez indiquer un âge valide.";
    if ($contact === '' || !preg_match('/^[0-9]{8,15}$/', $contact)) $erreurs[] = "Le contact doit contenir uniquement des chiffres (8 à 15).";
    if ($maladie === 'Autre' && $maladieAutre === '') $erreurs[] = "Veuillez préciser la maladie.";
    if ($parentNom === '') $erreurs[] = "Le nom du parent/tuteur (contact d'urgence) est obligatoire.";
    if ($parentContact === '' || !preg_match('/^[0-9]{8,15}$/', $parentContact)) $erreurs[] = "Le contact du parent/tuteur doit contenir uniquement des chiffres (8 à 15).";
    if (empty($_FILES['photo']['name'])) $erreurs[] = "La photo est obligatoire.";

    if (empty($erreurs)) {
        $nomPhoto = uploadPhoto($_FILES['photo']);
        if (!$nomPhoto) $erreurs[] = "La photo n'a pas pu être enregistrée (formats acceptés : jpg, jpeg, png, webp — 5 Mo max).";
    }

    if (empty($erreurs)) {
        $sectionFinale = ($section === 'Autre') ? $sectionAutre : $section;
        $sousComiteFinal = $sectionFinale; // le sous-comité n'est jamais modifié automatiquement
        $dortoir = affecterDortoir($pdo, $genre, $age);
        $matricule = genererMatriculeSeminariste($pdo);
        // Les séminaristes du dortoir Pépinière ne composent pas de test d'entrée :
        // leur niveau est directement "Pépinière".
        $niveauAffecte = ($dortoir === 'Pépinière') ? 'Pépinière' : null;

        $stmt = $pdo->prepare("INSERT INTO seminaristes
            (nom_prenoms, genre, niveau_etude, anyama, section, sous_comite_final, lieu_residence, maladie, maladie_autre, age, contact, photo, parent_nom, parent_lien, parent_contact, matricule, dortoir, niveau_affecte)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([
            $nom, $genre, $niveauEtude, $anyama, $sectionFinale, $sousComiteFinal, $lieuResidence,
            $maladie, $maladie === 'Autre' ? $maladieAutre : null, $age, $contact, $nomPhoto,
            $parentNom, $parentLien, $parentContact, $matricule, $dortoir, $niveauAffecte
        ]);
        $seminaristeId = $pdo->lastInsertId();

        [$identifiant, $motDePasse] = genererIdentifiantMotDePasse($contact);
        $chk = $pdo->prepare("SELECT COUNT(*) FROM comptes WHERE identifiant = ?");
        $chk->execute([$identifiant]);
        if ($chk->fetchColumn() > 0) $identifiant .= '_' . $seminaristeId;

        $hash = password_hash($motDePasse, PASSWORD_DEFAULT);
        $pdo->prepare("INSERT INTO comptes (identifiant, mot_de_passe, mdp_initial, role, seminariste_id, nom_affiche) VALUES (?,?,?,?,?,?)")
            ->execute([$identifiant, $hash, $motDePasse, 'seminariste', $seminaristeId, $nom]);

        // Paiement en attente, avec le numéro Wave du payeur (la Finance le retrouve dans son compte Wave)
        $pdo->prepare("INSERT INTO paiements (seminariste_id, reference_transaction, statut, numero_wave, montant) VALUES (?, '', 'en attente', ?, ?)")
            ->execute([$seminaristeId, $contact, FRAIS_PARTICIPATION]);

        // Connexion automatique pour enchaîner directement sur le paiement
        if (!estConnecte()) {
            $stC = $pdo->prepare("SELECT * FROM comptes WHERE identifiant = ?");
            $stC->execute([$identifiant]);
            if ($compteNew = $stC->fetch()) {
                session_regenerate_id(true);
                $_SESSION['compte_id'] = $compteNew['id'];
                $_SESSION['compte'] = $compteNew;
            }
        }

        if (estConnecte()) {
            redirect('/paiement.php?nouveau=1');
        }

        $succes = "Inscription réussie ! Votre dortoir a été attribué automatiquement : $dortoir.";
        $identifiantsGeneres = ['id' => $identifiant, 'mdp' => $motDePasse, 'matricule' => $matricule, 'dortoir' => $dortoir, 'anyama' => $anyama, 'section' => $sectionFinale];
    }
}
?>
<section class="section">
    <div class="container form-wrap">
        <div class="section-titre">
            <span class="eyebrow">Séminariste</span>
            <h2>Inscription — Séminariste</h2>
        </div>

        <?php if ($succes): ?>
            <div class="alert alert-succes">Inscription enregistrée ! Il reste à effectuer le paiement pour la finaliser.</div>
            <div class="carte" style="margin-bottom:24px;">
                <h3>Votre inscription</h3>
                <p class="mono"><strong>Matricule :</strong> <?= e($identifiantsGeneres['matricule']) ?><br>
                   <strong>Sous-comité :</strong> <?= e($identifiantsGeneres['anyama']) ?><br>
                   <strong>Section :</strong> <?= e($identifiantsGeneres['section']) ?></p>
                <p>🔒 Vos <strong>identifiants de connexion</strong> et votre <strong>reçu</strong> seront disponibles dès que la commission Finance aura <strong>validé votre paiement</strong>. Notez votre matricule <strong><?= e($identifiantsGeneres['matricule']) ?></strong> : il vous permettra de les récupérer sur la page <a href="<?= BASE_URL ?>/statut.php">« Suivre mon paiement »</a>.</p>
            </div>
            <?php if (estConnecte()): require __DIR__ . '/includes/paiement_bloc.php'; else: ?>
                <a href="<?= BASE_URL ?>/login.php" class="btn btn-primaire">Me connecter et payer</a>
            <?php endif; ?>
        <?php else: ?>

            <?php foreach ($erreurs as $err): ?>
                <div class="alert alert-erreur"><?= e($err) ?></div>
            <?php endforeach; ?>

            <form method="post" enctype="multipart/form-data" novalidate>
                <fieldset>
                    <legend>Identité</legend>
                    <div class="form-group">
                        <label>Nom et prénoms <span class="req">*</span></label>
                        <input type="text" name="nom_prenoms" required value="<?= e($_POST['nom_prenoms'] ?? '') ?>">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Genre <span class="req">*</span></label>
                            <div class="radio-group">
                                <label><input type="radio" name="genre" value="Masculin" required <?= (($_POST['genre'] ?? '') === 'Masculin') ? 'checked' : '' ?>> Masculin</label>
                                <label><input type="radio" name="genre" value="Féminin" <?= (($_POST['genre'] ?? '') === 'Féminin') ? 'checked' : '' ?>> Féminin</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Âge <span class="req">*</span></label>
                            <input type="number" name="age" min="5" max="100" required value="<?= e($_POST['age'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Niveau d'études <span class="req">*</span></label>
                        <input type="text" name="niveau_etude" placeholder="Ex : CM2, 3ème, Terminale, Licence 2..." required value="<?= e($_POST['niveau_etude'] ?? '') ?>" style="text-transform: uppercase;">
                    </div>
                    <div class="form-group">
                        <label>Lieu de résidence <span class="req">*</span></label>
                        <input type="text" name="lieu_residence" required value="<?= e($_POST['lieu_residence'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Contact (téléphone) <span class="req">*</span></label>
                        <input type="tel" name="contact" inputmode="numeric" pattern="[0-9]{8,15}" maxlength="15" autocomplete="tel" title="Chiffres uniquement (8 à 15)" required value="<?= e($_POST['contact'] ?? '') ?>">
                    </div>
                </fieldset>

                <fieldset>
                    <legend>Sous-comité</legend>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Anyama <span class="req">*</span></label>
                            <select name="anyama" id="anyama" required>
                                <option value="">— Choisir —</option>
                                <option value="Anyama 1" <?= (($_POST['anyama'] ?? '') === 'Anyama 1') ? 'selected' : '' ?>>Anyama 1</option>
                                <option value="Anyama 2" <?= (($_POST['anyama'] ?? '') === 'Anyama 2') ? 'selected' : '' ?>>Anyama 2</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Section <span class="req">*</span></label>
                            <select name="section" id="section" required>
                                <option value="">— Choisir une section —</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group" id="section_autre_wrap" style="display:none;">
                        <label>Précisez le nom de la section <span class="req">*</span></label>
                        <input type="text" name="section_autre" value="<?= e($_POST['section_autre'] ?? '') ?>">
                    </div>
                    <div class="help-text">ℹ️ Le sous-comité choisi ci-dessus n'est jamais modifié automatiquement. En revanche, jusqu'à <?= AGE_PEPINIERE_SEUIL ?> ans inclus, le <strong>dortoir</strong> attribué sera automatiquement <strong>Pépinière</strong> (voir section suivante).</div>
                </fieldset>

                <fieldset>
                    <legend>Santé</legend>
                    <div class="form-group">
                        <label>Maladie / allergie connue</label>
                        <select name="maladie" id="maladie">
                            <?php foreach (['Aucune', 'Paludisme', 'Asthme', 'Allergie', 'Autre'] as $m): ?>
                                <option value="<?= $m ?>" <?= (($_POST['maladie'] ?? 'Aucune') === $m) ? 'selected' : '' ?>><?= $m ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" id="maladie_autre_wrap">
                        <label>Précisez</label>
                        <input type="text" name="maladie_autre" value="<?= e($_POST['maladie_autre'] ?? '') ?>">
                    </div>
                </fieldset>

                <fieldset>
                    <legend>Contact d'urgence (parent / tuteur)</legend>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Nom du parent/tuteur <span class="req">*</span></label>
                            <input type="text" name="parent_nom" required value="<?= e($_POST['parent_nom'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>Lien de parenté</label>
                            <input type="text" name="parent_lien" placeholder="Ex : Père, Mère, Tuteur..." value="<?= e($_POST['parent_lien'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Contact du parent/tuteur <span class="req">*</span></label>
                        <input type="tel" name="parent_contact" inputmode="numeric" pattern="[0-9]{8,15}" maxlength="15" autocomplete="tel" title="Chiffres uniquement (8 à 15)" required value="<?= e($_POST['parent_contact'] ?? '') ?>">
                    </div>
                </fieldset>

                <fieldset>
                    <legend>Photo</legend>
                    <div class="form-group">
                        <label>Photo d'identité <span class="req">*</span></label>
                        <input type="file" name="photo" accept="image/*" required>
                        <img id="apercu-photo" style="display:none;margin-top:10px;width:100px;height:100px;object-fit:cover;border-radius:8px;">
                    </div>
                </fieldset>

                <button type="submit" class="btn btn-primaire btn-block">Valider mon inscription et payer par Wave</button>
            </form>
        <?php endif; ?>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
