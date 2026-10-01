<?php
require_once __DIR__ . '/../includes/init.php';
exigerConnexion();
date_default_timezone_set('Africa/Abidjan');
sortiesPreparer($pdo);
if (!estSortieSecurite()) { http_response_code(403); die('<div style="font-family:sans-serif;padding:40px;text-align:center;color:#8a1f1f;"><h2>Accès refusé</h2><p>Cette page est réservée à la commission Sécurité.</p><a href="' . BASE_URL . '/index">Retour à l\'accueil</a></div>'); }
$u = utilisateurCourant();
$titrePage = "Commission Sécurité - Sorties du camp";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'ok') {
    $id = (int)($_POST['id'] ?? 0);
    $st = $pdo->prepare("UPDATE sorties SET statut = 'autorisee', secu_nom = ?, secu_at = ? WHERE id = ? AND statut = 'attente_securite'");
    $st->execute([(string)($u['nom_affiche'] ?: $u['identifiant']), date('Y-m-d H:i:s'), $id]);
    if ($st->rowCount()) { journaliser($pdo, 'Sortie confirmée (Sécurité)', '#' . $id); $_SESSION['flash_succes'] = "OK : la personne est informée qu'elle a l'autorisation de sortir."; }
    redirect('/securite/sorties');
}
$aFaire = $pdo->query("SELECT * FROM sorties WHERE statut = 'attente_securite' ORDER BY heure_sortie")->fetchAll();
$dehors = $pdo->query("SELECT * FROM sorties WHERE statut = 'autorisee' ORDER BY heure_retour")->fetchAll();
$rentres = $pdo->query("SELECT * FROM sorties WHERE statut = 'rentre' ORDER BY rentre_at DESC LIMIT 15")->fetchAll();
$maintenant = time();

require_once __DIR__ . '/../includes/header.php';
if (estAdmin()) require_once __DIR__ . '/../includes/admin_nav.php';
?>
<section class="section">
    <div class="container" style="max-width:960px;">
        <div class="section-titre"><span class="eyebrow">Commission Sécurité</span><h2>🚪 Sorties du camp</h2></div>
        
        <?php if (!empty($_SESSION['flash_succes'])): ?><div class="alert alert-succes"><?= e($_SESSION['flash_succes']) ?></div><?php unset($_SESSION['flash_succes']); endif; ?>

        <div data-live="secu">
            <h3>🔔 À autoriser (<?= count($aFaire) ?>) <small style="font-weight:400;color:var(--texte-doux);">- déjà acceptées par le MG / MGA</small></h3>
            <?php foreach ($aFaire as $r): ?>
                <div class="carte" style="margin-bottom:14px;border-left:5px solid var(--orange);">
                    <div style="display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;"><strong><?= e($r['nom']) ?></strong><span class="pill pill-gris"><?= e($r['groupe']) ?></span></div>
                    <p style="margin:8px 0;">Motif : <strong><?= e($r['motif']) ?></strong><br>Sortie : <strong><?= e(dateHeureAffiche($r['heure_sortie'])) ?></strong> · Retour prévu : <strong><?= e(dateHeureAffiche($r['heure_retour'])) ?></strong><br><small>Accepté par <?= e($r['mg_nom']) ?></small></p>
                    <form method="post"><input type="hidden" name="action" value="ok"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><button class="btn btn-primaire">✅ OK - laisser sortir</button></form>
                </div>
            <?php endforeach; ?>
            <?php if (!$aFaire): ?><div class="carte" style="text-align:center;color:var(--texte-doux);">Aucune sortie à autoriser pour le moment.</div><?php endif; ?>

            <h3 style="margin-top:28px;">🚶 Hors du camp en ce moment (<?= count($dehors) ?>)</h3>
            <div class="table-wrap"><table>
                <thead><tr><th>Nom</th><th>Groupe</th><th>Motif</th><th>Sortie</th><th>Retour prévu</th><th>État</th></tr></thead>
                <tbody>
                <?php foreach ($dehors as $r): $retard = strtotime($r['heure_retour']) < $maintenant; ?>
                    <tr style="<?= $retard ? 'background:#fde8e8;' : '' ?>"><td><strong><?= e($r['nom']) ?></strong></td><td><?= e($r['groupe']) ?></td><td><?= e($r['motif']) ?></td><td><?= e(dateHeureAffiche($r['heure_sortie'])) ?></td><td><?= e(dateHeureAffiche($r['heure_retour'])) ?></td>
                        <td><?= $retard ? '<span class="pill" style="background:#c81e1e;color:#fff;">⏰ En retard</span>' : '<span class="pill pill-vert">Dans les temps</span>' ?></td></tr>
                <?php endforeach; ?>
                <?php if (!$dehors): ?><tr><td colspan="6" class="text-center">Personne n'est dehors.</td></tr><?php endif; ?>
                </tbody></table></div>

            <h3 style="margin-top:28px;">🏠 Derniers retours</h3>
            <div class="table-wrap"><table>
                <thead><tr><th>Nom</th><th>Groupe</th><th>Retour prévu</th><th>Rentré le</th></tr></thead>
                <tbody>
                <?php foreach ($rentres as $r): ?>
                    <tr><td><?= e($r['nom']) ?></td><td><?= e($r['groupe']) ?></td><td><?= e(dateHeureAffiche($r['heure_retour'])) ?></td><td><?= e(dateHeureAffiche($r['rentre_at'])) ?></td></tr>
                <?php endforeach; ?>
                <?php if (!$rentres): ?><tr><td colspan="4" class="text-center">Aucun retour enregistré.</td></tr><?php endif; ?>
                </tbody></table></div>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
