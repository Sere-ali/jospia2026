<?php
require_once __DIR__ . '/../includes/init.php';
exigerSecurite();
date_default_timezone_set('Africa/Abidjan');
visiteursPreparer($pdo);
$titrePage = "Commission Sécurité - Visiteurs";
$erreurs = []; $succes = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    if ($action === 'sortie' && $id) {
        $pdo->prepare("UPDATE visiteurs SET heure_sortie = ? WHERE id = ? AND heure_sortie IS NULL")->execute([date('Y-m-d H:i:s'), $id]);
        journaliser($pdo, 'Visiteur : sortie enregistrée', '#' . $id);
        $succes = "Sortie enregistrée.";
    } elseif ($action === 'supprimer' && $id) {
        $pdo->prepare("DELETE FROM visiteurs WHERE id = ?")->execute([$id]);
        journaliser($pdo, 'Visiteur supprimé', '#' . $id);
        $succes = "Visiteur supprimé.";
    } elseif ($action === 'modifier' && $id) {
        $nom = trim($_POST['nom_prenoms'] ?? ''); $contact = numeroLocal($_POST['contact'] ?? ''); $motif = mb_substr(trim($_POST['motif'] ?? ''), 0, 255);
        $arr = dateHeureSaisie($_POST['heure_arrivee'] ?? ''); $sor = dateHeureSaisie($_POST['heure_sortie'] ?? '');
        if ($nom === '' || !preg_match('/^[0-9]{8,15}$/', $contact) || !$arr) { $erreurs[] = "Nom, contact (8 à 15 chiffres) et heure d'arrivée sont obligatoires."; }
        elseif ($sor && strtotime($sor) < strtotime($arr)) { $erreurs[] = "L'heure de sortie ne peut pas être avant l'arrivée."; }
        else {
            $pdo->prepare("UPDATE visiteurs SET nom_prenoms=?, contact=?, motif=?, heure_arrivee=?, heure_sortie=? WHERE id=?")->execute([$nom, $contact, $motif, $arr, $sor, $id]);
            journaliser($pdo, 'Visiteur modifié', $nom);
            $succes = "Visiteur modifié.";
        }
    } elseif ($action === 'ajouter') {
        $nom = trim($_POST['nom_prenoms'] ?? ''); $contact = numeroLocal($_POST['contact'] ?? ''); $motif = mb_substr(trim($_POST['motif'] ?? ''), 0, 255);
        $arr = dateHeureSaisie($_POST['heure_arrivee'] ?? '') ?: date('Y-m-d H:i:s'); $sor = dateHeureSaisie($_POST['heure_sortie'] ?? '');
        if ($nom === '' || !preg_match('/^[0-9]{8,15}$/', $contact)) { $erreurs[] = "Nom et contact (8 à 15 chiffres) obligatoires."; }
        elseif ($sor && strtotime($sor) < strtotime($arr)) { $erreurs[] = "L'heure de sortie ne peut pas être avant l'arrivée."; }
        else {
            $pdo->prepare("INSERT INTO visiteurs (nom_prenoms, contact, motif, heure_arrivee, heure_sortie) VALUES (?,?,?,?,?)")->execute([$nom, $contact, $motif, $arr, $sor]);
            journaliser($pdo, 'Visiteur ajouté', $nom);
            $succes = "Visiteur ajouté."; $_POST = [];
        }
    }
}

$filtre = $_GET['statut'] ?? 'tous';
$q = trim($_GET['q'] ?? '');
$jour = trim($_GET['jour'] ?? '');
$where = ['1=1']; $params = [];
if ($filtre === 'presents') $where[] = 'heure_sortie IS NULL';
if ($filtre === 'sortis') $where[] = 'heure_sortie IS NOT NULL';
if ($q !== '') { $where[] = '(nom_prenoms LIKE ? OR contact LIKE ? OR motif LIKE ?)'; $params[] = "%$q%"; $params[] = "%$q%"; $params[] = "%$q%"; }
if ($jour !== '' && strtotime($jour)) { $where[] = 'DATE(heure_arrivee) = ?'; $params[] = date('Y-m-d', strtotime($jour)); }
$st = $pdo->prepare("SELECT * FROM visiteurs WHERE " . implode(' AND ', $where) . " ORDER BY heure_arrivee DESC LIMIT 1000");
$st->execute($params);
$visiteurs = $st->fetchAll();
$stats = $pdo->query("SELECT COUNT(*) total, SUM(heure_sortie IS NULL) presents, SUM(DATE(heure_arrivee) = CURDATE()) aujourdhui FROM visiteurs")->fetch();
$edit = null;
if (isset($_GET['modifier'])) { $se = $pdo->prepare("SELECT * FROM visiteurs WHERE id = ?"); $se->execute([(int)$_GET['modifier']]); $edit = $se->fetch(); }

require_once __DIR__ . '/../includes/header.php';
if (estAdmin()) require_once __DIR__ . '/../includes/admin_nav.php';
?>
<section class="section">
    <div class="container">
        <div class="section-titre"><span class="eyebrow">Commission Sécurité</span><h2>🛡️ Gestion des visiteurs</h2></div>
        <?php foreach ($erreurs as $err): ?><div class="alert alert-erreur"><?= e($err) ?></div><?php endforeach; ?>
        <?php if ($succes): ?><div class="alert alert-succes"><?= e($succes) ?></div><?php endif; ?>

        <div class="grid grid-3" style="margin-bottom:20px;" data-live="stats">
            <div class="carte" style="text-align:center;"><div class="label">Présents actuellement</div><div class="valeur" style="font-size:2rem;color:var(--orange-fonce);"><?= (int)$stats['presents'] ?></div></div>
            <div class="carte" style="text-align:center;"><div class="label">Visites aujourd'hui</div><div class="valeur" style="font-size:2rem;"><?= (int)$stats['aujourdhui'] ?></div></div>
            <div class="carte" style="text-align:center;"><div class="label">Total des visites</div><div class="valeur" style="font-size:2rem;color:var(--vert);"><?= (int)$stats['total'] ?></div></div>
        </div>

        <p class="no-print" style="text-align:center;margin-bottom:16px;"><a href="<?= BASE_URL ?>/visiteur" class="btn btn-outline">&larr; Retour à la page publique</a> <a href="<?= BASE_URL ?>/espace/rapport" class="btn btn-or">📝 Rapport journalier</a> <a href="<?= BASE_URL ?>/securite/sorties" class="btn btn-primaire">🚪 Sorties du camp</a> <a href="<?= BASE_URL ?>/espace/sortie" class="btn btn-or">🚪 Ma sortie</a></p>

        <form method="post" class="carte form-pro" style="margin-bottom:20px;" <?= $edit ? 'data-no-ajax' : 'data-ajax' ?>>
            <input type="hidden" name="action" value="<?= $edit ? 'modifier' : 'ajouter' ?>">
            <?php if ($edit): ?><input type="hidden" name="id" value="<?= (int)$edit['id'] ?>"><?php endif; ?>
            <h3><?= $edit ? '✏️ Modifier le visiteur' : '➕ Ajouter un visiteur' ?></h3>
            <div class="form-row">
                <div class="form-group"><label>Nom et prénoms</label><input type="text" name="nom_prenoms" required value="<?= e($edit['nom_prenoms'] ?? '') ?>"></div>
                <div class="form-group"><label>Contact</label><input type="tel" name="contact" inputmode="numeric" maxlength="15" required value="<?= e($edit['contact'] ?? '') ?>"></div>
            </div>
            <div class="form-group"><label>Motif de la visite</label><input type="text" name="motif" maxlength="255" value="<?= e($edit['motif'] ?? '') ?>"></div>
            <div class="form-row">
                <div class="form-group"><label>Heure d'arrivée</label><input type="datetime-local" name="heure_arrivee" value="<?= e($edit ? dateHeureChamp($edit['heure_arrivee']) : date('Y-m-d\TH:i')) ?>"></div>
                <div class="form-group"><label>Heure de sortie (vide = encore présent)</label><input type="datetime-local" name="heure_sortie" value="<?= e($edit ? dateHeureChamp($edit['heure_sortie']) : '') ?>"></div>
            </div>
            <button class="btn btn-primaire"><?= $edit ? 'Enregistrer' : 'Ajouter' ?></button>
            <?php if ($edit): ?><a href="<?= BASE_URL ?>/securite/visiteurs" class="btn btn-outline">Annuler</a><?php endif; ?>
        </form>

        <form method="get" class="no-print" style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px;">
            <select name="statut" onchange="this.form.submit()">
                <option value="tous" <?= $filtre==='tous'?'selected':'' ?>>Tous</option>
                <option value="presents" <?= $filtre==='presents'?'selected':'' ?>>Présents (pas encore sortis)</option>
                <option value="sortis" <?= $filtre==='sortis'?'selected':'' ?>>Sortis</option>
            </select>
            <input type="date" name="jour" value="<?= e($jour) ?>" onchange="this.form.submit()" style="max-width:170px;">
            <input type="text" name="q" placeholder="Nom, contact ou motif..." value="<?= e($q) ?>" style="max-width:240px;">
            <button class="btn btn-outline btn-sm">Rechercher</button>
        </form>

        <div class="table-wrap" data-live="liste">
            <table>
                <caption style="display:none;">Visiteurs</caption>
                <thead><tr><th>Nom et prénoms</th><th>Contact</th><th>Motif</th><th>Arrivée</th><th>Sortie</th><th>Statut</th><th class="no-print">Actions</th></tr></thead>
                <tbody>
                <?php foreach ($visiteurs as $v): ?>
                    <tr>
                        <td><strong><?= e($v['nom_prenoms']) ?></strong></td>
                        <td class="mono"><?= e($v['contact']) ?></td>
                        <td><?= e($v['motif'] ?? '') ?></td>
                        <td><?= e(dateHeureAffiche($v['heure_arrivee'])) ?></td>
                        <td><?= e(dateHeureAffiche($v['heure_sortie'])) ?></td>
                        <td><?= $v['heure_sortie'] ? '<span class="pill pill-gris">Sorti</span>' : '<span class="pill pill-vert">Présent</span>' ?></td>
                        <td class="no-print" style="white-space:nowrap;">
                            <?php if (!$v['heure_sortie']): ?>
                                <form method="post" style="display:inline;"><input type="hidden" name="action" value="sortie"><input type="hidden" name="id" value="<?= (int)$v['id'] ?>"><button class="btn btn-sm btn-or">🚪 Sortie maintenant</button></form>
                            <?php endif; ?>
                            <a href="?modifier=<?= (int)$v['id'] ?>" class="btn btn-sm btn-outline">✏️ Modifier</a>
                            <form method="post" style="display:inline;" onsubmit="return confirm('Supprimer ce visiteur ?');"><input type="hidden" name="action" value="supprimer"><input type="hidden" name="id" value="<?= (int)$v['id'] ?>"><button class="btn btn-sm btn-danger">🗑️</button></form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$visiteurs): ?><tr><td colspan="7">Aucun visiteur.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
