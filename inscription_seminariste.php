<?php
$titrePage = "Inscription Séminariste";
require_once __DIR__ . '/includes/header.php';

if (parametre('inscriptions_seminaristes', '1') === '0') {
    echo '<div class="container" style="max-width:640px;margin:40px auto;"><div class="carte" style="text-align:center;"><h2>Inscriptions fermées</h2><p>Les inscriptions des séminaristes sont closes pour le moment.</p><a href="' . BASE_URL . '/" class="btn btn-outline">&larr; Accueil</a></div></div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$erreurs = [];
$succes = null;
$identifiantsGeneres = null;
$sections = sectionsParAnyama();
anyamaPreparer($pdo);

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
    $exterieur = ($anyama === ANYAMA_EXTERIEUR);
    if (!isset($sections[$anyama])) $erreurs[] = "Veuillez choisir Anyama 1, Anyama 2 ou Autre (extérieur).";
    elseif (!$exterieur && !in_array($section, $sections[$anyama], true)) $erreurs[] = "Veuillez choisir une section valide.";
    if (!$exterieur && $section === 'Autre' && $sectionAutre === '') $erreurs[] = "Veuillez préciser le nom de la section.";
    if ($lieuResidence === '') $erreurs[] = "Le lieu de résidence est obligatoire.";
    if ($age < 5 || $age > 100) $erreurs[] = "Veuillez indiquer un âge valide.";
    if ($contact === '' || !preg_match('/^[0-9]{8,15}$/', $contact)) $erreurs[] = "Le contact doit contenir uniquement des chiffres (8 à 15).";
    if ($maladie === 'Autre' && $maladieAutre === '') $erreurs[] = "Veuillez préciser la maladie.";
    if ($parentNom === '') $erreurs[] = "Le nom du parent/tuteur (contact d'urgence) est obligatoire.";
    if ($parentContact === '' || !preg_match('/^[0-9]{8,15}$/', $parentContact)) $erreurs[] = "Le contact du parent/tuteur doit contenir uniquement des chiffres (8 à 15).";
    if (empty($_FILES['photo']['name'])) $erreurs[] = "La photo est obligatoire.";

    // PAIEMENT : l'identifiant de transaction est facultatif. Sans identifiant, le paiement reste « en attente » (non validable par la Finance).
    $modeApi = waveApiActive();
    $referenceTx = trim($_POST['reference_transaction'] ?? '');
    if ($referenceTx !== '') {
        if (!preg_match('/^[A-Za-z0-9_\-]{6,60}$/', $referenceTx)) { $erreurs[] = "L'identifiant de transaction Wave est invalide (6 à 60 caractères, lettres et chiffres) - laissez vide si vous n'avez pas encore payé."; }
        else {
            $dejaUtilisee = $pdo->prepare("SELECT COUNT(*) FROM paiements WHERE reference_transaction = ?");
            $dejaUtilisee->execute([$referenceTx]);
            if ($dejaUtilisee->fetchColumn() > 0) $erreurs[] = "Cet identifiant de transaction a déjà été utilisé pour une autre inscription.";
        }
    }

    if (empty($erreurs)) {
        $nomPhoto = uploadPhoto($_FILES['photo']);
        if (!$nomPhoto) $erreurs[] = "La photo n'a pas pu être enregistrée (formats acceptés : jpg, jpeg, png, webp - 5 Mo max).";
    }

    if (empty($erreurs)) {
        $sectionFinale = $exterieur ? ($sectionAutre !== '' ? $sectionAutre : 'Extérieur') : (($section === 'Autre') ? $sectionAutre : $section);
        $donnees = ['nom' => $nom, 'genre' => $genre, 'niveauEtude' => $niveauEtude, 'anyama' => $anyama, 'section' => $sectionFinale,
            'lieuResidence' => $lieuResidence, 'maladie' => $maladie, 'maladieAutre' => $maladieAutre, 'age' => $age, 'contact' => $contact,
            'parentNom' => $parentNom, 'parentLien' => $parentLien, 'parentContact' => $parentContact];

        if ($modeApi) {
            // Paiement d'abord : le dossier n'est créé qu'après confirmation de Wave (wave_retour_inscription / webhook).
            $jeton = inscriptionEnAttente($pdo, $donnees, $nomPhoto);
            redirect('/wave_inscription?t=' . $jeton);
        }

        // Mode manuel : le paiement a été fait par la personne, la commission Finance contrôle l'identifiant de transaction.
        $r = creerInscriptionSeminariste($pdo, $donnees, $nomPhoto, $referenceTx);
        $stC = $pdo->prepare("SELECT * FROM comptes WHERE seminariste_id = ? AND role = 'seminariste' LIMIT 1");
        $stC->execute([$r['id']]);
        if (!estConnecte() && ($compteNew = $stC->fetch())) {
            session_regenerate_id(true);
            $_SESSION['compte_id'] = $compteNew['id'];
            $_SESSION['compte'] = $compteNew;
        }
        if (estConnecte()) { redirect('/espace/fiche?inscrit=1'); }

        $succes = "Inscription reçue. Votre dortoir a été attribué automatiquement : " . $r['dortoir'] . ".";
        $identifiantsGeneres = ['id' => $r['identifiant'], 'mdp' => $r['mdp'], 'matricule' => $r['matricule'], 'dortoir' => $r['dortoir'], 'anyama' => $anyama, 'section' => $sectionFinale];
    }
}
?>
<section class="section form-page">
    <div class="container <?= $succes ? 'form-wrap' : 'form-layout' ?>">
        <?php if (!$succes): $asideType = 'seminariste'; require __DIR__ . '/includes/form_aside.php'; endif; ?>
        <div class="<?= $succes ? '' : 'form-main' ?>">
        <div class="section-titre form-titre">
            <span class="eyebrow">Séminariste</span>
            <h2>Inscription - Séminariste</h2>
            <p class="form-intro">Les champs marqués <span class="req">*</span> sont obligatoires.</p>
        </div>

        <?php if ($succes): ?>
            <div class="alert alert-succes">Inscription enregistrée ! Il reste à effectuer le paiement pour la finaliser.</div>
            <div class="carte" style="margin-bottom:24px;">
                <h3>Votre inscription</h3>
                <p class="mono"><strong>Matricule :</strong> <?= e($identifiantsGeneres['matricule']) ?><br>
                   <strong>Sous-comité :</strong> <?= e($identifiantsGeneres['anyama']) ?><br>
                   <strong>Section :</strong> <?= e($identifiantsGeneres['section']) ?></p>
                <p>🔒 Vos <strong>identifiants de connexion</strong> et votre <strong>reçu</strong> seront disponibles dès que la commission Finance aura <strong>validé votre paiement</strong>. Notez votre matricule <strong><?= e($identifiantsGeneres['matricule']) ?></strong> : il vous permettra de les récupérer sur la page <a href="<?= BASE_URL ?>/statut">« Suivre mon paiement »</a>.</p>
            </div>
            <?php if (estConnecte()): require __DIR__ . '/includes/paiement_bloc.php'; else: ?>
                <a href="<?= BASE_URL ?>/login" class="btn btn-primaire">Me connecter et payer</a>
            <?php endif; ?>
        <?php else: ?>

            <?php foreach ($erreurs as $err): ?>
                <div class="alert alert-erreur"><?= e($err) ?></div>
            <?php endforeach; ?>

            <form method="post" enctype="multipart/form-data" novalidate class="form-pro" id="form-inscription">
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
                                <option value="">- Choisir -</option>
                                <option value="Anyama 1" <?= (($_POST['anyama'] ?? '') === 'Anyama 1') ? 'selected' : '' ?>>Anyama 1</option>
                                <option value="Anyama 2" <?= (($_POST['anyama'] ?? '') === 'Anyama 2') ? 'selected' : '' ?>>Anyama 2</option>
                                <option value="<?= e(ANYAMA_EXTERIEUR) ?>" <?= (($_POST['anyama'] ?? '') === ANYAMA_EXTERIEUR) ? 'selected' : '' ?>>Autre (personne venant de l'extérieur)</option>
                            </select>
                        </div>
                        <div class="form-group" id="section_groupe">
                            <label>Section <span class="req">*</span></label>
                            <select name="section" id="section" required>
                                <option value="">- Choisir une section -</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group" id="section_autre_wrap" style="display:none;">
                        <label id="section_autre_label">Précisez le nom de la section <span class="req">*</span></label>
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
                        <div class="help-text">Photo claire, visage bien visible (jpg, png, webp - 5 Mo max). Elle servira à votre badge.</div>
                        <img id="apercu-photo" alt="Aperçu de la photo" style="display:none;">
                    </div>
                </fieldset>

                <?php if (!waveApiActive()): ?>
                <fieldset>
                    <legend>Paiement Wave</legend>
                    <div class="help-text" style="margin-bottom:12px;">💙 Frais de participation : <strong><?= number_format(FRAIS_PARTICIPATION, 0, ',', ' ') ?> FCFA</strong>, à payer par Wave au <strong><?= e(numeroWaveAffiche()) ?></strong><?php if (lienWavePaiement()): ?> - <a href="<?= e(lienWavePaiement()) ?>" target="_blank" rel="noopener"><strong>cliquer ici pour payer</strong></a><?php endif; ?>. Si vous avez déjà payé, recopiez l'identifiant de la transaction. <strong>Sans paiement, votre inscription reste « paiement en attente » et ne peut pas être validée.</strong></div>
                    <div class="form-group">
                        <label>Identifiant de la transaction Wave <small>(si vous avez déjà payé)</small></label>
                        <input type="text" name="reference_transaction" maxlength="60" autocomplete="off" placeholder="Ex : T_XXXXXXXXXXXX" value="<?= e($_POST['reference_transaction'] ?? '') ?>">
                    </div>
                </fieldset>
                <?php endif; ?>

                <button type="submit" class="btn btn-primaire btn-block btn-envoi"><span><?= waveApiActive() ? 'Valider et payer par Wave' : 'Valider mon inscription' ?></span><i aria-hidden="true">→</i></button>
                <p class="form-note">💙 Paiement Wave · <?= number_format(FRAIS_PARTICIPATION, 0, ',', ' ') ?> FCFA · <?= waveApiActive() ? "votre inscription n'est enregistrée qu'après le paiement" : "sans paiement, l'inscription reste en attente" ?></p>
            </form>
        <?php endif; ?>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
