<?php
$titrePage = "Paiement Wave";
require_once __DIR__ . '/includes/header.php';

// Seuls les séminaristes ont besoin de payer (ou les membres connectés, selon les specs, ici on assume les séminaristes connectés)
if (!estConnecte() || $_SESSION['compte']['role'] !== 'seminariste') {
    redirect('/login');
}

$compte = $_SESSION['compte'];
$seminariste_id = $compte['seminariste_id'];

// Juste après l'inscription : envoi direct vers Wave (si un lien de paiement est configuré)
$nouveau = isset($_GET['nouveau']);
if ($nouveau && waveApiActive() && empty($_SESSION['wave_redirige'])) {
    $_SESSION['wave_redirige'] = 1;
    redirect('/wave_pay');
}
if ($nouveau && !waveApiActive() && lienWavePaiement() && empty($_SESSION['wave_redirige'])) {
    $_SESSION['wave_redirige'] = 1;
    header('Location: ' . lienWavePaiement());
    exit;
}
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Le paiement est signalé sans identifiant de transaction : la commission Finance vérifie sur son compte Wave et valide.
    if ($paiement && $paiement['statut'] === 'en attente') {
        $pdo->prepare("UPDATE paiements SET paye_declare = 1, updated_at = NOW() WHERE id = ?")->execute([$paiement['id']]);
    } elseif (!$paiement || $paiement['statut'] === 'rejeté') {
        $pdo->prepare("INSERT INTO paiements (seminariste_id, reference_transaction, statut, numero_wave, montant, paye_declare) VALUES (?, '', 'en attente', ?, ?, 1)")
            ->execute([$seminariste_id, $paiement['numero_wave'] ?? null, FRAIS_PARTICIPATION]);
    }
    if (!$paiement || $paiement['statut'] !== 'validé') { $succes = "Paiement signalé. Il est en attente de validation par la commission Finance."; }
    $stmt = $pdo->prepare("SELECT * FROM paiements WHERE seminariste_id = ? ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([$seminariste_id]);
    $paiement = $stmt->fetch();
}
?>

<div class="form-page" style="padding:2rem 0 4rem;"><div class="container" style="max-width: 640px;">
    <div class="card p-4">
        <?php if ($matriculeNouveau): ?>
            <div class="alert alert-succes">✔ Inscription enregistrée - matricule <strong><?= e($matriculeNouveau) ?></strong>. Il reste à payer par Wave pour la finaliser.</div>
        <?php endif; ?>
        <h2 style="color:var(--primaire);text-align:center;margin-bottom:1rem;">Validation de votre paiement</h2>
        
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
                    Motif : <?= e($paiement['motif_rejet'] ?: 'Paiement introuvable sur le compte Wave.') ?>
                </div>
            <?php elseif ($paiement && $paiement['statut'] === 'en attente'): ?>
                <div class="alert alert-info text-center" style="background-color: #e2f3f5; color: #0056b3; border: 1px solid #b8daff; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                    <strong>Paiement en attente de validation.</strong><br>
                    Numéro Wave du payeur : <strong><?= e($paiement['numero_wave'] ?: '-') ?></strong>
                </div>
            <?php else: ?>
                <div class="alert alert-info text-center" style="background-color: #e2f3f5; color: #0056b3; border: 1px solid #b8daff; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                    Afin d'accéder à votre espace et passer le test d'entrée, vous devez d'abord régler les frais de participation de <strong><?= number_format(FRAIS_PARTICIPATION, 0, ',', ' ') ?> FCFA</strong> via Wave, puis cliquer sur « J'ai effectué le paiement » ci-dessous.
                </div>
            <?php endif; ?>

            <?php require __DIR__ . '/includes/paiement_bloc.php'; ?>
        <?php endif; ?>
    </div>
</div></div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
