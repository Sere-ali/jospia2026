<?php
require_once __DIR__ . '/../includes/init.php';
exigerConnexion();
date_default_timezone_set('Africa/Abidjan');
sortiesPreparer($pdo);
$u = utilisateurCourant();
[$type, $personneId, $nomPersonne, $groupe] = sortiePersonne($pdo, $u);
$titrePage = "Autorisation de sortie";
$erreurs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    if ($action === 'demander') {
        $motif = mb_substr(trim($_POST['motif'] ?? ''), 0, 255);
        $hs = dateHeureSaisie($_POST['heure_sortie'] ?? ''); $hr = dateHeureSaisie($_POST['heure_retour'] ?? '');
        $actif = $pdo->prepare("SELECT COUNT(*) FROM sorties WHERE compte_id = ? AND statut IN ('attente_mg','attente_securite','autorisee')");
        $actif->execute([$u['id']]);
        if ($motif === '') $erreurs[] = "Indiquez le motif de la sortie.";
        if (!$hs || !$hr) $erreurs[] = "Indiquez l'heure de sortie et l'heure de retour sur le camp.";
        elseif (strtotime($hr) <= strtotime($hs)) $erreurs[] = "L'heure de retour doit être après l'heure de sortie.";
        elseif (strtotime($hr) <= time()) $erreurs[] = "L'heure de retour est déjà passée.";
        if ($actif->fetchColumn() > 0) $erreurs[] = "Vous avez déjà une demande en cours : attendez qu'elle soit terminée (ou annulez-la).";
        if (!$erreurs) {
            $pdo->prepare("INSERT INTO sorties (compte_id, type, personne_id, nom, groupe, motif, heure_sortie, heure_retour) VALUES (?,?,?,?,?,?,?,?)")
                ->execute([$u['id'], $type, $personneId, $nomPersonne, $groupe, $motif, $hs, $hr]);
            journaliser($pdo, 'Demande de sortie du camp', $nomPersonne . ' - ' . $motif);
            $_SESSION['flash_succes'] = "Demande envoyée au MG / MGA. Vous verrez ici la réponse.";
            redirect('/espace/sortie');
        }
    } elseif ($action === 'annuler' && $id) {
        $pdo->prepare("UPDATE sorties SET statut = 'annulee' WHERE id = ? AND compte_id = ? AND statut IN ('attente_mg','attente_securite')")->execute([$id, $u['id']]);
        redirect('/espace/sortie');
    } elseif ($action === 'vu' && $id) {
        $pdo->prepare("UPDATE sorties SET refus_vu = 1 WHERE id = ? AND compte_id = ?")->execute([$id, $u['id']]);
        redirect('/espace/sortie');
    } elseif ($action === 'rentre' && $id) {
        $pdo->prepare("UPDATE sorties SET statut = 'rentre', rentre_at = ? WHERE id = ? AND compte_id = ? AND statut = 'autorisee'")->execute([date('Y-m-d H:i:s'), $id, $u['id']]);
        journaliser($pdo, 'Retour sur le camp', $nomPersonne);
        $_SESSION['flash_succes'] = "Bon retour ! Votre retour sur le camp est signalé.";
        redirect('/espace/sortie');
    }
}

$st = $pdo->prepare("SELECT * FROM sorties WHERE compte_id = ? ORDER BY id DESC LIMIT 30");
$st->execute([$u['id']]);
$mes = $st->fetchAll();
$maintenant = time();

require_once __DIR__ . '/../includes/header.php';
if (($u['role'] ?? '') === 'finance') require_once __DIR__ . '/../includes/finance_nav.php';
?>
<section class="section">
    <div class="container" style="max-width:820px;">
        <div class="section-titre"><span class="eyebrow">Camp</span><h2>🚪 Autorisation de sortie</h2>
            <p>Pour quitter le camp, faites une demande : elle doit être acceptée par le <strong>MG ou le MGA</strong>, puis confirmée par la <strong>commission Sécurité</strong>.</p></div>
        <?php foreach ($erreurs as $err): ?><div class="alert alert-erreur"><?= e($err) ?></div><?php endforeach; ?>
        <?php if (!empty($_SESSION['flash_succes'])): ?><div class="alert alert-succes"><?= e($_SESSION['flash_succes']) ?></div><?php unset($_SESSION['flash_succes']); endif; ?>

        <form method="post" class="carte form-pro" style="margin-bottom:22px;">
            <input type="hidden" name="action" value="demander">
            <h3>➕ Nouvelle demande de sortie</h3>
            <div class="form-group"><label>Motif de la sortie <span class="req">*</span></label>
                <input type="text" name="motif" maxlength="255" required placeholder="Ex : rendez-vous médical, course urgente..." value="<?= e($_POST['motif'] ?? '') ?>"></div>
            <div class="form-row">
                <div class="form-group"><label>Heure de sortie <span class="req">*</span></label>
                    <input type="datetime-local" name="heure_sortie" required value="<?= e($_POST['heure_sortie'] ?? date('Y-m-d\TH:i')) ?>"></div>
                <div class="form-group"><label>Heure de retour sur le camp <span class="req">*</span></label>
                    <input type="datetime-local" name="heure_retour" required value="<?= e($_POST['heure_retour'] ?? '') ?>"></div>
            </div>
            <button class="btn btn-primaire">Envoyer ma demande</button>
        </form>

        <h3 style="margin:0 0 12px;">Mes demandes</h3>
        <div data-live="sorties">
            <?php foreach ($mes as $r): [$lib, $cls] = libelleStatutSortie($r['statut']); $retard = ($r['statut'] === 'autorisee' && strtotime($r['heure_retour']) < $maintenant); ?>
                <div class="carte" style="margin-bottom:14px;<?= $retard ? 'border:2px solid #c81e1e;' : '' ?>">
                    <div style="display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;align-items:center;">
                        <strong><?= e($r['motif']) ?></strong>
                        <span class="pill <?= $cls ?>"><?= e($lib) ?></span>
                    </div>
                    <p style="margin:8px 0;color:var(--texte-doux);">Sortie : <strong><?= e(dateHeureAffiche($r['heure_sortie'])) ?></strong> · Retour prévu : <strong><?= e(dateHeureAffiche($r['heure_retour'])) ?></strong></p>
                    <?php if ($r['statut'] === 'refusee'): ?><div class="alert alert-erreur" style="margin:8px 0;">✖ <strong>Votre requête a été refusée.</strong><?= $r['refus_motif'] ? ' Motif : ' . e($r['refus_motif']) : '' ?></div><?php endif; ?>
                    <?php if ($r['statut'] === 'attente_securite'): ?><p>✔ Acceptée par <?= e($r['mg_nom']) ?>. La commission Sécurité a été avertie.</p><?php endif; ?>
                    <?php if ($r['statut'] === 'autorisee'): ?>
                        <div class="alert alert-succes" style="margin:8px 0;">✅ <strong>Vous êtes autorisé à sortir.</strong> S'il vous plaît, rentrez dans le délai : avant <strong><?= e(dateHeureAffiche($r['heure_retour'])) ?></strong>. (Accepté par <?= e($r['mg_nom']) ?>, confirmé par la Sécurité.)</div>
                        <?php if ($retard): ?><div class="alert alert-erreur" style="margin:8px 0;">⏰ <strong>Votre heure de sortie est épuisée. Veuillez retourner sur le camp, s'il vous plaît.</strong></div><?php endif; ?>
                        <form method="post"><input type="hidden" name="action" value="rentre"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><button class="btn btn-primaire">🏠 Je suis rentré sur le camp</button></form>
                    <?php elseif ($r['statut'] === 'rentre'): ?><p>Retour signalé le <?= e(dateHeureAffiche($r['rentre_at'])) ?>.</p>
                    <?php elseif (in_array($r['statut'], ['attente_mg', 'attente_securite'], true)): ?>
                        <form method="post" onsubmit="return confirm('Annuler cette demande ?');"><input type="hidden" name="action" value="annuler"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><button class="btn btn-sm btn-outline">Annuler la demande</button></form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            <?php if (!$mes): ?><div class="carte" style="text-align:center;color:var(--texte-doux);">Aucune demande pour le moment.</div><?php endif; ?>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
