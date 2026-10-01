<?php
require_once __DIR__ . '/../includes/init.php';
exigerConnexion();
if (!estMG()) { http_response_code(403); die('<div style="font-family:sans-serif;padding:40px;text-align:center;color:#8a1f1f;"><h2>Accès refusé</h2><p>Cette page est réservée aux comptes à qui le super administrateur a attribué le MG / MGA.</p><a href="' . BASE_URL . '/index">Retour à l\'accueil</a></div>'); }
date_default_timezone_set('Africa/Abidjan');
sortiesPreparer($pdo);
$u = utilisateurCourant();
$titrePage = "Demandes de sortie (MG / MGA)";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0); $action = $_POST['action'] ?? '';
    $st = $pdo->prepare("SELECT * FROM sorties WHERE id = ? AND statut = 'attente_mg'");
    $st->execute([$id]);
    if ($r = $st->fetch()) {
        $moi = (string)($u['nom_affiche'] ?: $u['identifiant']);
        if ((int)$r['compte_id'] === (int)$u['id'] && !estSuperAdmin()) {
            $_SESSION['flash_erreur'] = "Vous ne pouvez pas valider votre propre demande : un autre MG / MGA doit le faire.";
        } elseif ($action === 'accepter') {
            $pdo->prepare("UPDATE sorties SET statut = 'attente_securite', mg_nom = ?, mg_at = ? WHERE id = ?")->execute([$moi, date('Y-m-d H:i:s'), $id]);
            journaliser($pdo, 'Sortie acceptée (MG)', $r['nom']);
            $_SESSION['flash_succes'] = "Demande acceptée : la commission Sécurité est avertie.";
        } elseif ($action === 'refuser') {
            $pdo->prepare("UPDATE sorties SET statut = 'refusee', mg_nom = ?, mg_at = ?, refus_motif = ? WHERE id = ?")->execute([$moi, date('Y-m-d H:i:s'), mb_substr(trim($_POST['refus_motif'] ?? ''), 0, 255), $id]);
            journaliser($pdo, 'Sortie refusée (MG)', $r['nom']);
            $_SESSION['flash_succes'] = "Demande refusée.";
        }
    }
    redirect('/espace/sorties_mg');
}
$attente = $pdo->query("SELECT * FROM sorties WHERE statut = 'attente_mg' ORDER BY created_at")->fetchAll();
$recentes = $pdo->query("SELECT * FROM sorties WHERE statut <> 'attente_mg' AND statut <> 'annulee' ORDER BY id DESC LIMIT 15")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
if (estAdmin()) require_once __DIR__ . '/../includes/admin_nav.php';
?>
<section class="section">
    <div class="container" style="max-width:900px;">
        <div class="section-titre"><span class="eyebrow">MG / MGA</span><h2>🚪 Demandes de sortie du camp</h2><?php if (estSuperAdmin()): ?><p><a href="<?= BASE_URL ?>/admin/sorties_roles" class="btn btn-outline btn-sm">👤 Choisir les MG / MGA</a></p><?php endif; ?></div>
        <?php if (!empty($_SESSION['flash_erreur'])): ?><div class="alert alert-erreur"><?= e($_SESSION['flash_erreur']) ?></div><?php unset($_SESSION['flash_erreur']); endif; ?>
        <?php if (!empty($_SESSION['flash_succes'])): ?><div class="alert alert-succes"><?= e($_SESSION['flash_succes']) ?></div><?php unset($_SESSION['flash_succes']); endif; ?>

        <div data-live="mg">
            <h3>À traiter (<?= count($attente) ?>)</h3>
            <?php foreach ($attente as $r): ?>
                <div class="carte" style="margin-bottom:14px;border-left:5px solid var(--orange);">
                    <div style="display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;"><strong><?= e($r['nom']) ?></strong><span class="pill pill-gris"><?= e($r['groupe']) ?></span></div>
                    <p style="margin:8px 0;">Motif : <strong><?= e($r['motif']) ?></strong><br>Sortie : <strong><?= e(dateHeureAffiche($r['heure_sortie'])) ?></strong> · Retour prévu : <strong><?= e(dateHeureAffiche($r['heure_retour'])) ?></strong></p>
                    <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                        <form method="post"><input type="hidden" name="action" value="accepter"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><button class="btn btn-primaire btn-sm">✔ Accepter</button></form>
                        <form method="post" style="display:flex;gap:6px;flex-wrap:wrap;"><input type="hidden" name="action" value="refuser"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><input type="text" name="refus_motif" placeholder="Motif du refus (facultatif)" maxlength="255" style="min-width:200px;"><button class="btn btn-danger btn-sm">✖ Refuser</button></form>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if (!$attente): ?><div class="carte" style="text-align:center;color:var(--texte-doux);">Aucune demande en attente.</div><?php endif; ?>

            <h3 style="margin-top:28px;">Dernières décisions</h3>
            <div class="table-wrap"><table>
                <thead><tr><th>Nom</th><th>Motif</th><th>Sortie</th><th>Retour prévu</th><th>État</th></tr></thead>
                <tbody>
                <?php foreach ($recentes as $r): [$lib, $cls] = libelleStatutSortie($r['statut']); ?>
                    <tr><td><?= e($r['nom']) ?></td><td><?= e($r['motif']) ?></td><td><?= e(dateHeureAffiche($r['heure_sortie'])) ?></td><td><?= e(dateHeureAffiche($r['heure_retour'])) ?></td><td><span class="pill <?= $cls ?>"><?= e($lib) ?></span></td></tr>
                <?php endforeach; ?>
                <?php if (!$recentes): ?><tr><td colspan="5" class="text-center">Rien pour le moment.</td></tr><?php endif; ?>
                </tbody></table></div>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
