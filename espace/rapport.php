<?php
require_once __DIR__ . '/../includes/init.php';
exigerConnexion();
$propre = commissionPropre($pdo);
$autorisees = ($propre && (estResponsableCommission($pdo) || estAdmin())) ? [$propre] : [];
if (!$autorisees) {
    http_response_code(403);
    die('<div style="font-family:sans-serif;padding:40px;text-align:center;color:#8a1f1f;"><h2>Accès refusé</h2><p>La saisie des rapports journaliers est réservée aux responsables de commission.</p><a href="' . BASE_URL . '/index">Retour à l\'accueil</a></div>');
}
date_default_timezone_set('Africa/Abidjan');
rapportsPreparer($pdo);
$u = utilisateurCourant();
$estAdminRapport = false;
$titrePage = "Rapports journaliers";
$erreurs = []; $succes = null;

$commission = $autorisees[0];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $commission !== '') {
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    $peutModifier = function ($r) use ($estAdminRapport, $u) { return $estAdminRapport || ((int)$r['auteur_id'] === (int)$u['id']); };
    if ($action === 'supprimer' && $id) {
        $st = $pdo->prepare("SELECT * FROM rapports_journaliers WHERE id = ? AND commission = ?"); $st->execute([$id, $commission]); $r = $st->fetch();
        if ($r && $peutModifier($r)) { $pdo->prepare("DELETE FROM rapports_journaliers WHERE id = ?")->execute([$id]); journaliser($pdo, 'Rapport supprimé', $commission . ' ' . $r['date_rapport']); $succes = "Rapport supprimé."; }
        else { $erreurs[] = "Suppression non autorisée."; }
    } elseif ($action === 'enregistrer') {
        $date = preg_match('/^\d{4}-\d{2}-\d{2}$/', $_POST['date_rapport'] ?? '') ? $_POST['date_rapport'] : date('Y-m-d');
        $act = trim($_POST['activites'] ?? ''); $dif = trim($_POST['difficultes'] ?? ''); $pre = trim($_POST['previsions'] ?? '');
        if ($act === '') { $erreurs[] = "Décrivez les activités réalisées."; }
        if (!$erreurs) {
            if ($id) {
                $st = $pdo->prepare("SELECT * FROM rapports_journaliers WHERE id = ? AND commission = ?"); $st->execute([$id, $commission]); $r = $st->fetch();
                if ($r && $peutModifier($r)) {
                    $pdo->prepare("UPDATE rapports_journaliers SET date_rapport=?, activites=?, difficultes=?, previsions=?, updated_at=? WHERE id=?")->execute([$date, $act, $dif, $pre, date('Y-m-d H:i:s'), $id]);
                    journaliser($pdo, 'Rapport modifié', $commission . ' ' . $date); $succes = "Rapport modifié.";
                } else { $erreurs[] = "Modification non autorisée."; }
            } else {
                $pdo->prepare("INSERT INTO rapports_journaliers (commission, date_rapport, activites, difficultes, previsions, auteur_id, auteur_nom) VALUES (?,?,?,?,?,?,?)")
                    ->execute([$commission, $date, $act, $dif, $pre, $u['id'], (string)($u['nom_affiche'] ?? $u['identifiant'] ?? '')]);
                journaliser($pdo, 'Rapport journalier ajouté', $commission . ' ' . $date); $succes = "Rapport du " . date('d/m/Y', strtotime($date)) . " enregistré."; $_POST = [];
            }
        }
    }
}

$aujourdhui = date('Y-m-d');

$rapports = []; $edit = null;
if ($commission !== '') {
    $filtreDate = preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['jour'] ?? '') ? $_GET['jour'] : '';
    $sql = "SELECT * FROM rapports_journaliers WHERE commission = ?" . ($filtreDate ? " AND date_rapport = ?" : "") . " ORDER BY date_rapport DESC, id DESC LIMIT 300";
    $st = $pdo->prepare($sql); $st->execute($filtreDate ? [$commission, $filtreDate] : [$commission]);
    $rapports = $st->fetchAll();
    if (isset($_GET['modifier'])) {
        $se = $pdo->prepare("SELECT * FROM rapports_journaliers WHERE id = ? AND commission = ?"); $se->execute([(int)$_GET['modifier'], $commission]);
        $edit = $se->fetch();
        if ($edit && !($estAdminRapport || (int)$edit['auteur_id'] === (int)$u['id'])) $edit = null;
    }
}

require_once __DIR__ . '/../includes/header.php';
if (($u['role'] ?? '') === 'finance') require_once __DIR__ . '/../includes/finance_nav.php';
?>
<section class="section">
    <div class="container">
        <div class="section-titre"><span class="eyebrow">Espace commission</span><h2>📝 Rapport journalier</h2></div>
        <?php foreach ($erreurs as $err): ?><div class="alert alert-erreur"><?= e($err) ?></div><?php endforeach; ?>
        <?php if ($succes): ?><div class="alert alert-succes"><?= e($succes) ?></div><?php endif; ?>


            <p class="no-print" style="margin-bottom:14px;">
                <?php if (count($autorisees) > 1): ?><?php endif; ?>
            </p>
            <h3 style="text-align:center;margin-bottom:16px;">Commission <?= e($commission) ?> <?php if (nomCommissionComplet($commission) !== $commission): ?><small style="color:var(--texte-doux);font-weight:400;">- <?= e(nomCommissionComplet($commission)) ?></small><?php endif; ?></h3>

            <form method="post" id="formulaire" class="carte form-pro" style="margin-bottom:22px;" <?= $edit ? 'data-no-ajax' : 'data-ajax' ?>>
                <input type="hidden" name="action" value="enregistrer">
                <?php if ($edit): ?><input type="hidden" name="id" value="<?= (int)$edit['id'] ?>"><?php endif; ?>
                <h3><?= $edit ? '✏️ Modifier le rapport' : '➕ Nouveau rapport' ?></h3>
                <div class="form-group"><label>Date du rapport</label>
                    <input type="date" name="date_rapport" required value="<?= e($edit['date_rapport'] ?? $aujourdhui) ?>" style="max-width:220px;"></div>
                <div class="form-group"><label>Activités réalisées <span class="req">*</span></label>
                    <textarea name="activites" rows="5" required placeholder="Ce qui a été fait aujourd'hui..."><?= e($edit['activites'] ?? '') ?></textarea></div>
                <div class="form-group"><label>Difficultés rencontrées</label>
                    <textarea name="difficultes" rows="3" placeholder="Problèmes, manques, incidents..."><?= e($edit['difficultes'] ?? '') ?></textarea></div>
                <div class="form-group"><label>Prévisions / besoins pour demain</label>
                    <textarea name="previsions" rows="3" placeholder="Ce qui est prévu, matériel ou aide nécessaire..."><?= e($edit['previsions'] ?? '') ?></textarea></div>
                <button class="btn btn-primaire"><?= $edit ? 'Enregistrer les modifications' : 'Déposer le rapport' ?></button>
                <?php if ($edit): ?><a href="?x=1" class="btn btn-outline">Annuler</a><?php endif; ?>
            </form>

            <form method="get" class="no-print" style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px;">
                                <input type="date" name="jour" value="<?= e($filtreDate) ?>" onchange="this.form.submit()" style="max-width:200px;">
                <?php if ($filtreDate): ?><a href="?x=1" class="btn btn-outline btn-sm">Tous les jours</a><?php endif; ?>
            </form>

            <div data-live="rapports">
                <?php foreach ($rapports as $r): $mien = $estAdminRapport || (int)$r['auteur_id'] === (int)$u['id'];
                    $act = '';
                    if ($mien) $act = '<a href="?modifier=' . (int)$r['id'] . '#formulaire" class="btn btn-primaire btn-sm">✏️ Modifier ce rapport</a>'
                        . '<form method="post" onsubmit="return confirm(\'Supprimer ce rapport ?\');" style="display:inline;"><input type="hidden" name="action" value="supprimer"><input type="hidden" name="id" value="' . (int)$r['id'] . '"><button class="btn btn-sm btn-danger">🗑️ Supprimer</button></form>';
                    echo lettreRapportHtml($r, $act);
                endforeach; ?>
                <?php if (!$rapports): ?><div class="carte" style="text-align:center;color:var(--texte-doux);">Aucun rapport pour le moment.</div><?php endif; ?>
            </div>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
