<?php
$titrePage = "Suivre mon paiement";
require_once __DIR__ . '/includes/header.php';

$resultat = null;
$erreur = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['statut_essais'] = ($_SESSION['statut_essais'] ?? 0) + 1;
    if ($_SESSION['statut_essais'] > 10) {
        $erreur = "Trop de tentatives. Réessayez plus tard.";
    } else {
        $matricule = strtoupper(trim($_POST['matricule'] ?? ''));
        $contact = preg_replace('/\D+/', '', $_POST['contact'] ?? '');
        $st = $pdo->prepare("SELECT s.id, s.nom_prenoms, s.matricule FROM seminaristes s WHERE s.matricule = ? AND s.contact = ? LIMIT 1");
        $st->execute([$matricule, $contact]);
        $sem = $st->fetch();
        if (!$sem) {
            $erreur = "Aucune inscription trouvée avec ce matricule et ce numéro.";
        } else {
            $stP = $pdo->prepare("SELECT statut FROM paiements WHERE seminariste_id = ? ORDER BY id DESC LIMIT 1");
            $stP->execute([$sem['id']]);
            $p = $stP->fetch();
            $ids = null;
            if ($p && $p['statut'] === 'validé') {
                $stC = $pdo->prepare("SELECT identifiant, mdp_initial FROM comptes WHERE seminariste_id = ? LIMIT 1");
                $stC->execute([$sem['id']]);
                $ids = $stC->fetch() ?: null;
            }
            $resultat = ['nom' => $sem['nom_prenoms'], 'statut' => $p['statut'] ?? 'aucun', 'ids' => $ids];
        }
    }
}
?>
<section class="section">
    <div class="container form-wrap">
        <div class="section-titre">
            <span class="eyebrow">Séminariste</span>
            <h2>Suivre mon paiement</h2>
        </div>

        <?php if ($erreur): ?><div class="alert alert-erreur"><?= e($erreur) ?></div><?php endif; ?>

        <?php if ($resultat): ?>
            <div class="carte" style="margin-bottom:24px;">
                <h3><?= e($resultat['nom']) ?></h3>
                <?php if ($resultat['statut'] === 'validé'): ?>
                    <div class="alert alert-succes">✔ Paiement validé.</div>
                    <?php if ($resultat['ids'] && $resultat['ids']['mdp_initial'] !== null): ?>
                        <p class="mono"><strong>Identifiant :</strong> <?= e($resultat['ids']['identifiant']) ?><br>
                           <strong>Mot de passe :</strong> <?= e($resultat['ids']['mdp_initial']) ?></p>
                    <?php else: ?>
                        <p>Connectez-vous avec votre identifiant (votre numéro) et le mot de passe que vous avez choisi.</p>
                    <?php endif; ?>
                    <a href="<?= BASE_URL ?>/login" class="btn btn-primaire">Me connecter et voir mon reçu</a>
                <?php elseif ($resultat['statut'] === 'en attente'): ?>
                    <div class="alert alert-info" style="background:#fff8e1;border:1px solid #ffe08a;padding:12px;border-radius:8px;">⏳ Paiement en attente de validation par la commission Finance. Revenez un peu plus tard.</div>
                <?php elseif ($resultat['statut'] === 'rejeté'): ?>
                    <div class="alert alert-erreur">Paiement rejeté. Reconnectez-vous pour soumettre une nouvelle référence.</div>
                <?php else: ?>
                    <div class="alert alert-erreur">Aucun paiement enregistré. Vos identifiants seront disponibles après paiement et validation.</div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <form method="post" class="carte">
            <div class="form-group">
                <label>Matricule</label>
                <input type="text" name="matricule" placeholder="Ex : JOS-002" required value="<?= e($_POST['matricule'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Numéro de téléphone utilisé à l'inscription</label>
                <input type="tel" name="contact" inputmode="numeric" pattern="[0-9]{8,15}" maxlength="15" required value="<?= e($_POST['contact'] ?? '') ?>">
            </div>
            <button class="btn btn-primaire btn-block">Vérifier</button>
        </form>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
