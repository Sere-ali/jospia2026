<?php
$titrePage = "Paiement Wave";
require_once __DIR__ . '/includes/header.php';

// Seuls les séminaristes ont besoin de payer (ou les membres connectés, selon les specs, ici on assume les séminaristes connectés)
if (!estConnecte() || $_SESSION['compte']['role'] !== 'seminariste') {
    redirect('/login.php');
}

$compte = $_SESSION['compte'];
$seminariste_id = $compte['seminariste_id'];

// Récupérer le statut actuel du paiement
$stmt = $pdo->prepare("SELECT * FROM paiements WHERE seminariste_id = ? ORDER BY created_at DESC LIMIT 1");
$stmt->execute([$seminariste_id]);
$paiement = $stmt->fetch();

$erreur = null;
$succes = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reference = trim($_POST['reference_transaction'] ?? '');
    
    if (empty($reference)) {
        $erreur = "Veuillez saisir la référence de transaction Wave.";
    } else {
        if ($paiement && $paiement['statut'] === 'en attente') {
            // Mise à jour de la référence
            $stmt = $pdo->prepare("UPDATE paiements SET reference_transaction = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$reference, $paiement['id']]);
        } else {
            // Nouveau paiement
            $stmt = $pdo->prepare("INSERT INTO paiements (seminariste_id, reference_transaction, statut) VALUES (?, ?, 'en attente')");
            $stmt->execute([$seminariste_id, $reference]);
        }
        $succes = "Votre référence de paiement a été soumise avec succès. Elle est en attente de validation par l'administration.";
        
        // Recharger le paiement
        $stmt = $pdo->prepare("SELECT * FROM paiements WHERE seminariste_id = ? ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([$seminariste_id]);
        $paiement = $stmt->fetch();
    }
}
?>

<div class="container" style="max-width: 600px; margin-top: 2rem;">
    <div class="card p-4">
        <h2 style="color:var(--primaire);text-align:center;margin-bottom:1rem;">Validation de votre paiement</h2>
        
        <?php if ($erreur): ?><div class="alert alert-erreur"><?= e($erreur) ?></div><?php endif; ?>
        <?php if ($succes): ?><div class="alert alert-succes"><?= e($succes) ?></div><?php endif; ?>

        <?php if ($paiement && $paiement['statut'] === 'validé'): ?>
            <div class="alert alert-succes text-center">
                <strong>Votre paiement a été validé !</strong><br>
                Vous pouvez maintenant accéder à votre espace et au test.
            </div>
            <div class="text-center mt-3">
                <a href="<?= BASE_URL ?>/espace/fiche.php" class="btn btn-primaire">Accéder à mon espace</a>
            </div>
        <?php else: ?>
        
            <?php if ($paiement && $paiement['statut'] === 'rejeté'): ?>
                <div class="alert alert-erreur text-center">
                    <strong>Paiement rejeté.</strong><br>
                    Motif : <?= e($paiement['motif_rejet'] ?: 'Référence invalide ou introuvable.') ?>
                </div>
            <?php elseif ($paiement && $paiement['statut'] === 'en attente'): ?>
                <div class="alert alert-info text-center" style="background-color: #e2f3f5; color: #0056b3; border: 1px solid #b8daff; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                    <strong>Paiement en attente de validation.</strong><br>
                    Référence soumise : <strong><?= e($paiement['reference_transaction']) ?></strong>
                </div>
            <?php else: ?>
                <div class="alert alert-info text-center" style="background-color: #e2f3f5; color: #0056b3; border: 1px solid #b8daff; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                    Afin d'accéder à votre espace et passer le test d'entrée, vous devez d'abord régler les frais de participation de <strong><?= number_format(FRAIS_PARTICIPATION, 0, ',', ' ') ?> FCFA</strong> via Wave, puis renseigner la référence de transaction ci-dessous.
                </div>
            <?php endif; ?>

            <div style="text-align:center; margin-bottom: 2rem;">
                <a href="<?= e(WAVE_PAYMENT_LINK) ?>" target="_blank" rel="noopener noreferrer" class="btn" style="background-color: #1cc6f4; color: white; font-weight: bold; border-radius: 50px; padding: 12px 24px; display: inline-flex; align-items: center; gap: 8px;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" fill="white"/>
                        <path d="M7 10.5C7 10.5 8.5 13.5 12 13.5C15.5 13.5 17 10.5 17 10.5" stroke="#1cc6f4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Payer <?= number_format(FRAIS_PARTICIPATION, 0, ',', ' ') ?> FCFA avec Wave
                </a>
            </div>

            <form method="post" style="border-top: 1px solid #eee; padding-top: 1.5rem;">
                <div class="form-group">
                    <label style="font-weight:bold;">Référence de la transaction Wave</label>
                    <input type="text" name="reference_transaction" placeholder="Ex: CI2309..." required>
                    <small style="color:var(--texte-doux); display:block; margin-top:4px;">Cette référence se trouve dans le SMS de confirmation ou l'application Wave après le paiement.</small>
                </div>
                
                <button type="submit" class="btn btn-primaire btn-block">Soumettre la référence</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
