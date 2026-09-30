<?php
require_once __DIR__ . '/../includes/init.php';
exigerConnexion();
$u = utilisateurCourant();

if (in_array($u['role'], ['admin','superadmin'], true) && !$u['membre_id'] && !$u['seminariste_id']) {
    redirect('/admin/dashboard.php');
}

if ($u['role'] === 'finance') {
    redirect('/finance/paiements.php');
}

$membre = null; $seminariste = null;
$recu = null;
if ($u['role'] === 'membre' && $u['membre_id']) {
    $stmt = $pdo->prepare("SELECT * FROM membres_commission WHERE id = ?");
    $stmt->execute([$u['membre_id']]);
    $membre = $stmt->fetch();
}
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
        </div>

        <div class="grid grid-2" style="align-items:start;">
            <div class="carte">
                <h3>Mes informations</h3>
                <p><strong>Matricule :</strong> <span class="mono"><?= e($membre['matricule']) ?></span><br>
                   <strong>Commission :</strong> <?= e($membre['commission']) ?><br>
                   <strong>Contact :</strong> <?= e($membre['contact']) ?><br>
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
        </div>

        <div class="fiche" id="ficheImprimable">
            <div class="fiche-header">
                <img src="<?= BASE_URL ?>/assets/img/logo.jpg" class="logo-fiche" alt="Logo">
                <div>
                    <h3 style="margin:0;">Fiche d'inscription — Séminariste</h3>
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
                <dt>Maladie / allergie</dt><dd><?= e($seminariste['maladie']) ?><?= $seminariste['maladie_autre'] ? ' — ' . e($seminariste['maladie_autre']) : '' ?></dd>
                <dt>Contact d'urgence</dt><dd><?= e($seminariste['parent_nom']) ?> (<?= e($seminariste['parent_lien']) ?>) — <?= e($seminariste['parent_contact']) ?></dd>
            </dl>
            <div class="dortoir-box">
                <div class="label">Dortoir attribué</div>
                <div class="valeur"><?= e($seminariste['dortoir']) ?></div>
            </div>
        </div>
        <div style="text-align:center;margin-top:18px;" class="no-print">
            <button onclick="window.print()" class="btn btn-primaire">🖨️ Imprimer ma fiche d'inscription</button>
        </div>
        <?php require __DIR__ . '/../includes/astuce_impression.php'; ?>

        <div style="max-width:340px;margin:30px auto 0;">
            <h3 style="text-align:center;">Mon badge</h3>
            <?php $s = $seminariste; require __DIR__ . '/../includes/badge_seminariste_carte.php'; ?>
            <div class="acces-restreint">
                🔒 Le téléchargement / impression du badge est réservé aux administrateurs.
            </div>
        </div>

        <?php if ($recu): $s = $seminariste; require __DIR__ . '/../includes/recu_paiement.php'; endif; ?>

        <div class="carte" style="max-width:720px;margin:30px auto 0;">
            <h3>Test d'entrée</h3>
            <?php if ($seminariste['dortoir'] === 'Pépinière'): ?>
                <p style="color:var(--texte-doux);">Les séminaristes du sous-comité <strong>Pépinière</strong> ne composent pas de test d'entrée. Niveau : <span class="pill pill-vert">Pépinière</span></p>
            <?php elseif (!$seminariste['test_complete']): ?>
                <p>Vous n'avez pas encore composé le test d'entrée en ligne. Il détermine automatiquement votre niveau d'affectation (Primaire, Secondaire, Universitaire, Leader).</p>
                <a href="<?= BASE_URL ?>/quiz.php" class="btn btn-or">Composer le test maintenant</a>
            <?php else: ?>
                <p><strong>Note obtenue :</strong> <?= e($seminariste['note_test']) ?> / 20<br>
                   <strong>Niveau d'affectation :</strong> <span class="pill pill-vert"><?= e($seminariste['niveau_affecte']) ?></span></p>
                <a href="<?= BASE_URL ?>/espace/correction.php" class="btn btn-outline btn-sm">🔍 Voir la correction détaillée de mon test</a>

                <h3 style="margin-top:26px;">Mon diplôme</h3>
                <div class="diplome-card" style="transform:scale(.7);transform-origin:top left;margin-bottom:-140px;">
                    <div class="mention">Certificat de participation</div>
                    <h1><?= e(EVENT_FULL) ?></h1>
                    <div>Ce diplôme est décerné à</div>
                    <div class="nom-diplome"><?= e($seminariste['nom_prenoms']) ?></div>
                    <p class="texte">pour sa participation active aux <?= e(EVENT_NAME) ?>, ayant atteint le niveau <strong><?= e($seminariste['niveau_affecte']) ?></strong>.</p>
                    <div class="signatures"><span>Le Coordonnateur</span><span>Le Super Administrateur</span></div>
                </div>
                <div class="acces-restreint" style="margin-top:150px;">
                    🔒 Le téléchargement / impression du diplôme est réservé aux administrateurs.
                </div>
            <?php endif; ?>
        </div>

        <div class="carte" style="max-width:720px;margin:24px auto 0;">
            <h3>Bulletin de notes</h3>
            <?php if (resultatsPublies($pdo)): ?>
                <p style="color:var(--texte-doux);">Les résultats des compositions du séminaire sont disponibles.</p>
                <a href="<?= BASE_URL ?>/espace/bulletin.php" class="btn btn-primaire">📄 Voir et imprimer mon bulletin</a>
            <?php else: ?>
                <p style="color:var(--texte-doux);">Le bulletin de notes des compositions sera disponible ici dès que le comité d'organisation aura publié les résultats.</p>
            <?php endif; ?>
        </div>

        <div class="carte" style="max-width:720px;margin:24px auto 0;">
            <h3>Votre avis compte</h3>
            <p style="color:var(--texte-doux);">Vous pouvez donner votre avis sur le travail d'une commission durant les JOSPIA. Votre message sera transmis directement à ses membres.</p>
            <a href="<?= BASE_URL ?>/espace/critiquer.php" class="btn btn-outline">💬 Critiquer une commission</a>
        </div>
    <?php else: ?>
        <div class="alert alert-info">Aucune fiche associée à ce compte.</div>
    <?php endif; ?>

    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
