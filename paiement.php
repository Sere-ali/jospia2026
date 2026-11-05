<?php
$titrePage = "Paiement en espèces";
require_once __DIR__ . '/includes/header.php';

// Seuls les séminaristes ont besoin de payer (ou les membres connectés, selon les specs, ici on assume les séminaristes connectés)
if (!estConnecte() || $_SESSION['compte']['role'] !== 'seminariste') {
    redirect('/login');
}

$compte = $_SESSION['compte'];
$seminariste_id = $compte['seminariste_id'];

$nouveau = isset($_GET['nouveau']);
$flashPaiement = $_SESSION['flash_paiement'] ?? null;
unset($_SESSION['flash_paiement']);
$matriculeNouveau = null;
if ($nouveau) {
    $stM = $pdo->prepare("SELECT matricule FROM seminaristes WHERE id = ?");
    $stM->execute([$seminariste_id]);
    $matriculeNouveau = $stM->fetchColumn() ?: null;
}

// Récupérer le statut actuel du paiement
$stmt = $pdo->prepare("SELECT * FROM paiements WHERE seminariste_id = ? ORDER BY created_at DESC LIMIT 1");
$stmt->execute([$seminariste_id]);
$paiement = $stmt->fetch();

$erreur = null;
$succes = null;

?>

<div class="form-page" style="padding:2rem 0 4rem;"><div class="container" style="max-width: 640px;">
    <div class="card p-4">
        <?php if ($matriculeNouveau): ?>
            <div class="alert alert-succes">Inscription enregistrée - matricule <strong><?= e($matriculeNouveau) ?></strong>. Il reste à remettre les frais en espèces à la commission Finance pour la finaliser.</div>
        <?php endif; ?>
        <h2 style="color:var(--primaire);text-align:center;margin-bottom:1rem;">Paiement en espèces</h2>
        
        <?php if ($flashPaiement): ?><div class="alert alert-erreur"><?= e($flashPaiement) ?></div><?php endif; ?>
        <?php if ($erreur): ?><div class="alert alert-erreur"><?= e($erreur) ?></div><?php endif; ?>
        <?php if ($succes): ?><div class="alert alert-succes"><?= e($succes) ?></div><?php endif; ?>

        <?php if ($paiement && $paiement['statut'] === 'validé'): ?>
            <div class="alert alert-succes text-center">
                <strong>Votre paiement a été validé !</strong><br>
                Votre reçu avec QR code est disponible sur votre espace.
            </div>
            <div class="text-center mt-3">
                <a href="<?= BASE_URL ?>/espace/fiche" class="btn btn-primaire">Voir mon reçu et mon espace</a>
            </div>
        <?php else: ?>
        
            <?php if ($paiement && $paiement['statut'] === 'rejeté'): ?>
                <div class="alert alert-erreur text-center">
                    <strong>Paiement rejeté.</strong><br>
                    Motif : <?= e($paiement['motif_rejet'] ?: 'Paiement non reçu par la commission Finance.') ?>
                </div>
            <?php elseif ($paiement && $paiement['statut'] === 'en attente'): ?>
                <div class="alert alert-info text-center" style="background-color: #e2f3f5; color: #0056b3; border: 1px solid #b8daff; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                    <strong>Paiement en attente.</strong><br>
                    Remettez vos frais en espèces à la commission Finance, qui validera votre paiement.
                </div>
            <?php else: ?>
                <div class="alert alert-info text-center" style="background-color: #e2f3f5; color: #0056b3; border: 1px solid #b8daff; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                    Afin d'accéder à votre espace et passer le test d'entrée, vous devez d'abord régler les frais de participation de <strong><?= number_format(FRAIS_PARTICIPATION, 0, ',', ' ') ?> FCFA</strong> en espèces auprès de la commission Finance.
                </div>
            <?php endif; ?>

            <?php require __DIR__ . '/includes/paiement_bloc.php'; ?>
            <div class="text-center mt-3">
                <a href="<?= BASE_URL ?>/espace/fiche<?= $nouveau ? '?inscrit=1' : '' ?>" class="btn btn-primaire btn-lg">Paiement en espèces</a>
                <small style="display:block;color:var(--texte-doux);margin-top:8px;">Cliquez pour continuer vers votre page. Remettez ensuite vos frais en espèces à la commission Finance.</small>
            </div>
        <?php endif; ?>
    </div>
</div></div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
