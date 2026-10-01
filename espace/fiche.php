<?php
require_once __DIR__ . '/../includes/init.php';
exigerConnexion();
$u = utilisateurCourant();

if (in_array($u['role'], ['admin','superadmin'], true) && !$u['membre_id'] && !$u['seminariste_id']) {
    redirect('/admin/dashboard');
}

if ($u['role'] === 'finance') {
    redirect('/finance/paiements');
}
if ($u['role'] === 'securite') {
    redirect('/securite/visiteurs');
}
if ($u['role'] === 'scientifique') {
    redirect('/admin/commission_scientifique');
}

$membre = null; $seminariste = null;
$recu = null; $idsInitiaux = null;
if ($u['role'] === 'membre' && $u['membre_id']) {
    $stmt = $pdo->prepare("SELECT * FROM membres_commission WHERE id = ?");
    $stmt->execute([$u['membre_id']]);
    $membre = $stmt->fetch();
}
$stI0 = $pdo->prepare("SELECT identifiant, mdp_initial FROM comptes WHERE id = ? AND mdp_initial IS NOT NULL");
$stI0->execute([$u['id']]);
$idsInitiaux = $stI0->fetch() ?: null;
$critiquesRecues = [];
if ($membre) {
    $stmt = $pdo->prepare("SELECT * FROM critiques WHERE commission = ? ORDER BY created_at DESC");
    $stmt->execute([$membre['commission']]);
    $critiquesRecues = $stmt->fetchAll();
}
if ($u['role'] === 'seminariste' && $u['seminariste_id']) {
    $stmt = $pdo->prepare("SELECT * FROM seminaristes WHERE id = ?");
    $stmt->execute([$u['seminariste_id']]);
    $seminariste = $stmt->fetch();
    $stR = $pdo->prepare("SELECT p.*, c.nom_affiche AS valideur FROM paiements p LEFT JOIN comptes c ON c.id = p.admin_validateur_id WHERE p.seminariste_id = ? AND p.statut = 'validé' ORDER BY p.id DESC LIMIT 1");
    $stR->execute([$u['seminariste_id']]);
    $recu = $stR->fetch() ?: null;
}
$titrePage = "Mon espace";
require_once __DIR__ . '/../includes/header.php';
?>
<section class="section">
    <div class="container">

    <?php if ($membre): ?>
        <div class="section-titre">
            <span class="eyebrow">Espace membre</span>
            <h2>Bonjour, <?= e($membre['nom_prenoms']) ?></h2>
            <p><a href="<?= BASE_URL ?>/espace/modifier" class="btn btn-outline btn-sm">✏️ Modifier mon formulaire</a>
            <a href="<?= BASE_URL ?>/espace/rapport" class="btn btn-or btn-sm">📝 Rapport journalier</a>
            <a href="<?= BASE_URL ?>/espace/sortie" class="btn btn-or btn-sm">🚪 Autorisation de sortie</a>
            <?php if (estMG()): ?> <a href="<?= BASE_URL ?>/espace/sorties_mg" class="btn btn-primaire btn-sm">🚪 Valider les sorties (MG)</a><?php endif; ?>
            <?php if (estSortieSecurite()): ?> <a href="<?= BASE_URL ?>/securite/sorties" class="btn btn-primaire btn-sm">🛡️ Sorties sécurité</a><?php endif; ?>
            <?php if (estSecurite()): ?> <a href="<?= BASE_URL ?>/securite/visiteurs" class="btn btn-primaire btn-sm">🛡️ Gestion des visiteurs</a><?php endif; ?></p>
        </div>
        <?php if (isset($_GET['inscrit'])): ?><div class="alert alert-succes" style="max-width:720px;margin:0 auto 16px;">✔ Inscription enregistrée. Voici vos identifiants de connexion : notez-les.</div><?php endif; ?>
        <?php if ($idsInitiaux): ?>
        <div class="carte" style="max-width:720px;margin:0 auto 20px;border:2px solid var(--primaire,#1b7a3d);">
            <h3>🔑 Vos identifiants de connexion</h3>
            <p class="mono"><strong>Identifiant :</strong> <?= e($idsInitiaux['identifiant']) ?><br>
               <strong>Mot de passe :</strong> <?= e($idsInitiaux['mdp_initial']) ?></p>
            <p style="color:var(--texte-doux);">Conservez-les (ils sont aussi dans le PDF de votre fiche) : ils vous permettent de revenir sur le site pour voir si la commission Finance a validé votre paiement. Vous pouvez choisir votre propre mot de passe dans <a href="<?= BASE_URL ?>/compte">Mon compte</a> (ce bloc disparaîtra alors).</p>
        </div>
        <?php endif; ?>

        <div class="grid grid-2" style="align-items:start;">
            <div class="carte">
                <h3>Mes informations</h3>
                <p><strong>Matricule :</strong> <span class="mono"><?= e($membre['matricule']) ?></span><br>
                   <strong>Commission :</strong> <?= e($membre['commission']) ?><br>
                   <strong>Contact :</strong> <?= e($membre['contact']) ?><br>
                   <strong>Identifiant de connexion :</strong> <span class="mono"><?= e(utilisateurCourant()['identifiant'] ?? $membre['contact']) ?></span><br>
                   <strong>Inscrit le :</strong> <?= date('d/m/Y', strtotime($membre['created_at'])) ?></p>
            </div>
            <div>
                <h3 style="text-align:center;">Mon badge</h3>
                <?php require __DIR__ . '/../includes/badge_commission_carte.php'; ?>
                <div class="acces-restreint">
                    🔒 Le téléchargement / impression du badge est réservé aux administrateurs.
                </div>
            </div>
        </div>

        <div class="carte" style="margin-top:24px;">
            <h3>Mon diplôme</h3>
            <?php $nomCertificat = $membre['nom_prenoms']; $qualiteCertificat = 'MEMBRE DE LA COMMISSION ' . nomCommissionComplet($membre['commission']); require __DIR__ . '/../includes/certificat_carte.php'; ?>
            <div class="acces-restreint" style="margin-top:14px;">
                🔒 Le téléchargement / impression du diplôme est réservé aux administrateurs.
            </div>
        </div>

        <div class="carte" style="margin-top:24px;">
            <h3>Critiques reçues pour la commission <?= e($membre['commission']) ?> (<?= count($critiquesRecues) ?>)</h3>
            <?php if (!$critiquesRecues): ?>
                <p style="color:var(--texte-doux);">Aucune critique reçue pour le moment.</p>
            <?php else: ?>
                <div class="table-wrap" style="box-shadow:none;">
                    <table>
                        <thead><tr><th>Date</th><th>Message</th></tr></thead>
                        <tbody>
                        <?php foreach ($critiquesRecues as $c): ?>
                            <tr>
                                <td style="white-space:nowrap;"><?= date('d/m/Y à H:i', strtotime($c['created_at'])) ?></td>
                                <td><?= nl2br(e($c['contenu'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

    <?php elseif ($seminariste): ?>
        <div class="section-titre">
            <span class="eyebrow">Espace séminariste</span>
            <h2>Bonjour, <?= e($seminariste['nom_prenoms']) ?></h2>
            <p><a href="<?= BASE_URL ?>/espace/modifier" class="btn btn-outline btn-sm">✏️ Modifier mon formulaire</a>
            <a href="<?= BASE_URL ?>/espace/sortie" class="btn btn-or btn-sm">🚪 Autorisation de sortie</a></p>
        </div>

        <?php if (isset($_GET['inscrit'])): ?><div class="alert alert-succes" style="max-width:720px;margin:0 auto 16px;">✔ Inscription enregistrée. Voici vos identifiants de connexion ci-dessous : notez-les. Vous pouvez télécharger votre fiche (PDF) avec son QR code.</div><?php endif; ?>
        <div class="fiche" id="ficheImprimable">
            <div class="fiche-header">
                <img src="<?= BASE_URL ?>/assets/img/logo.jpg" class="logo-fiche" alt="Logo">
                <div>
                    <h3 style="margin:0;">Fiche d'inscription - Séminariste</h3>
                    <div style="color:var(--texte-doux);font-size:.85rem;"><?= e(EVENT_FULL) ?></div>
                </div>
            </div>
            <?php if ($seminariste['photo']): ?>
                <img class="fiche-photo" src="<?= BASE_URL ?>/uploads/photos/<?= e($seminariste['photo']) ?>" alt="Photo">
            <?php endif; ?>
            <dl>
                <dt>Matricule</dt><dd class="mono"><?= e($seminariste['matricule']) ?></dd>
                <dt>Nom et prénoms</dt><dd><?= e($seminariste['nom_prenoms']) ?></dd>
                <dt>Genre</dt><dd><?= e($seminariste['genre']) ?></dd>
                <dt>Âge</dt><dd><?= e($seminariste['age']) ?> ans</dd>
                <dt>Niveau d'études</dt><dd><?= e($seminariste['niveau_etude']) ?></dd>
                <dt>Sous-comité</dt><dd><?= e($seminariste['anyama']) ?></dd>
                <dt>Section</dt><dd><?= e($seminariste['section']) ?></dd>
                <dt>Lieu de résidence</dt><dd><?= e($seminariste['lieu_residence']) ?></dd>
                <dt>Contact</dt><dd><?= e($seminariste['contact']) ?></dd>
                <dt>Maladie / allergie</dt><dd><?= e($seminariste['maladie']) ?><?= $seminariste['maladie_autre'] ? ' - ' . e($seminariste['maladie_autre']) : '' ?></dd>
                <dt>Contact d'urgence</dt><dd><?= e($seminariste['parent_nom']) ?> (<?= e($seminariste['parent_lien']) ?>) - <?= e($seminariste['parent_contact']) ?></dd>
            </dl>
            <div class="dortoir-box">
                <div class="label">Dortoir attribué</div>
                <div class="valeur"><?= e($seminariste['dortoir']) ?></div>
            </div>
            <?= blocQrFiche($pdo, $seminariste['id']) ?>
        </div>
        <div style="text-align:center;margin-top:18px;" class="no-print">
            <a href="<?= BASE_URL ?>/espace/download_fiche" class="btn btn-primaire">📄 Télécharger ma fiche d'inscription (PDF)</a>
        </div>

        <div style="max-width:340px;margin:30px auto 0;">
            <h3 style="text-align:center;">Mon badge</h3>
            <?php $s = $seminariste; require __DIR__ . '/../includes/badge_seminariste_carte.php'; ?>
            <div class="acces-restreint">
                🔒 Le téléchargement / impression du badge est réservé aux administrateurs.
            </div>
        </div>

        <?php if ($idsInitiaux): ?>
        <div class="carte" style="max-width:720px;margin:30px auto 0;border:2px solid var(--primaire,#1b7a3d);">
            <h3>🔑 Vos identifiants de connexion</h3>
            <p class="mono"><strong>Identifiant :</strong> <?= e($idsInitiaux['identifiant']) ?><br>
               <strong>Mot de passe :</strong> <?= e($idsInitiaux['mdp_initial']) ?></p>
            <p style="color:var(--texte-doux);">Conservez-les (ils sont aussi dans le PDF de votre fiche) : ils vous permettent de revenir sur le site pour voir si la commission Finance a validé votre paiement. Vous pouvez choisir votre propre mot de passe dans <a href="<?= BASE_URL ?>/compte">Mon compte</a> (ce bloc disparaîtra alors).</p>
        </div>
        <?php endif; ?>

        <?php if ($recu): $s = $seminariste; require __DIR__ . '/../includes/recu_paiement.php'; else: ?>
        <div class="carte" style="max-width:720px;margin:30px auto 0;border-left:5px solid var(--orange);">
            <h3>⏳ Paiement en attente</h3>
            <p>Frais : <strong><?= number_format(FRAIS_PARTICIPATION, 0, ',', ' ') ?> FCFA</strong>. Votre QR code est <strong style="color:#c81e1e;">rouge (refusé)</strong> tant que le paiement n'est pas validé.</p>
            <a href="<?= lienWavePaiement() ? e(lienWavePaiement()) : BASE_URL . '/paiement' ?>" class="btn btn-wave">💙 Payer <?= number_format(FRAIS_PARTICIPATION, 0, ',', ' ') ?> FCFA avec Wave</a>
        </div>
        <?php endif; ?>

        <div class="carte" style="max-width:720px;margin:30px auto 0;">
            <h3>Test d'entrée</h3>
            <?php if ($seminariste['dortoir'] === 'Pépinière'): ?>
                <p style="color:var(--texte-doux);">Les séminaristes du sous-comité <strong>Pépinière</strong> ne composent pas de test d'entrée. Niveau : <span class="pill pill-vert">Pépinière</span></p>
            <?php elseif (!$seminariste['test_complete']): ?>
                <p>Vous n'avez pas encore composé le test d'entrée en ligne. Il détermine automatiquement votre niveau d'affectation (Primaire, Secondaire, Universitaire, Leader).</p>
                <?php if (testOuvert($pdo)): ?>
                    <a href="<?= BASE_URL ?>/quiz" class="btn btn-or">Composer le test maintenant</a>
                <?php else: ?>
                    <div class="acces-restreint">🔒 Le test d'entrée est verrouillé pour le moment. Il sera ouvert par la commission scientifique : revenez sur cette page à ce moment-là.</div>
                <?php endif; ?>
            <?php else: ?>
                <p><strong>Note obtenue :</strong> <?= e($seminariste['note_test']) ?> / 20<br>
                   <strong>Niveau d'affectation :</strong> <span class="pill pill-vert"><?= e($seminariste['niveau_affecte']) ?></span></p>
                <?php if (correctionOuverte($pdo)): ?>
                    <a href="<?= BASE_URL ?>/espace/correction" class="btn btn-outline btn-sm">🔍 Voir la correction détaillée de mon test</a>
                <?php else: ?>
                    <div class="acces-restreint">🔒 La correction détaillée du test est verrouillée pour le moment.</div>
                <?php endif; ?>

            <?php endif; ?>
        </div>

        <div class="carte" style="max-width:900px;margin:24px auto 0;">
            <h3>Mon diplôme</h3>
            <?php $nomCertificat = $seminariste['nom_prenoms']; require __DIR__ . '/../includes/certificat_seminariste_carte.php'; ?>
            <div class="acces-restreint" style="margin-top:14px;">
                🔒 Le téléchargement / impression du diplôme est réservé aux administrateurs.
            </div>
        </div>

        <div class="carte" style="max-width:720px;margin:24px auto 0;">
            <h3>Bulletin de notes</h3>
            <?php if (resultatsPublies($pdo)): ?>
                <p style="color:var(--texte-doux);">Les résultats des compositions du séminaire sont disponibles.</p>
                <a href="<?= BASE_URL ?>/espace/bulletin" class="btn btn-primaire">📄 Voir et télécharger mon bulletin</a>
            <?php else: ?>
                <p style="color:var(--texte-doux);">Le bulletin de notes des compositions sera disponible ici dès que le comité d'organisation aura publié les résultats.</p>
            <?php endif; ?>
        </div>

        <div class="carte" style="max-width:720px;margin:24px auto 0;">
            <h3>Votre avis compte</h3>
            <p style="color:var(--texte-doux);">Vous pouvez donner votre avis sur le travail d'une commission durant les JOSPIA. Votre message sera transmis directement à ses membres.</p>
            <a href="<?= BASE_URL ?>/espace/critiquer" class="btn btn-outline">💬 Critiquer une commission</a>
        </div>
    <?php else: ?>
        <div class="alert alert-info">Aucune fiche associée à ce compte.</div>
    <?php endif; ?>

    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
