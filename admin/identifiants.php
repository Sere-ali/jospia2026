<?php
require_once __DIR__ . '/../includes/init.php';
exigerRole(['admin', 'superadmin']);

$message = null; $nouveau = null;

// Réinitialisation du mot de passe d'une personne (nouveau code à 6 chiffres à lui remettre)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reinit'])) {
    $cid = (int)$_POST['reinit'];
    $st = $pdo->prepare("SELECT * FROM comptes WHERE id = ? AND role IN ('membre','seminariste')");
    $st->execute([$cid]);
    $c = $st->fetch();
    if ($c) {
        $mdp = (string)random_int(100000, 999999);
        $pdo->prepare("UPDATE comptes SET mot_de_passe = ?, mdp_initial = ?, actif = 1 WHERE id = ?")
            ->execute([password_hash($mdp, PASSWORD_DEFAULT), $mdp, $cid]);
        $nouveau = ['id' => $cid, 'nom' => $c['nom_affiche'], 'identifiant' => $c['identifiant'], 'mdp' => $mdp];
    }
}

$q = trim($_GET['q'] ?? '');
$sql = "SELECT c.id, c.identifiant, c.mdp_initial, c.role, c.actif, c.nom_affiche,
               COALESCE(m.matricule, s.matricule) AS matricule,
               COALESCE(m.contact, s.contact) AS contact,
               m.commission, s.dortoir
        FROM comptes c
        LEFT JOIN membres_commission m ON m.id = c.membre_id
        LEFT JOIN seminaristes s ON s.id = c.seminariste_id
        WHERE c.role IN ('membre','seminariste')";
$params = [];
if ($q !== '') {
    $sql .= " AND (c.nom_affiche LIKE ? OR c.identifiant LIKE ? OR COALESCE(m.contact, s.contact) LIKE ? OR COALESCE(m.matricule, s.matricule) LIKE ?)";
    $like = '%' . $q . '%';
    array_push($params, $like, $like, $like, $like);
}
$sql .= " ORDER BY c.id DESC LIMIT 200";
$st = $pdo->prepare($sql);
$st->execute($params);
$lignes = $st->fetchAll();

$titrePage = "Identifiants des utilisateurs";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/admin_nav.php';
?>
<section class="section">
    <div class="container">
        <div class="section-titre">
            <span class="eyebrow">Assistance</span>
            <h2>Retrouver l'identifiant d'une personne</h2>
            <p>Cherchez par nom, numéro de téléphone ou matricule, puis remettez l'identifiant à la personne. Si elle a perdu son mot de passe, réinitialisez-le pour lui en donner un nouveau.</p>
        </div>

        <?php if ($nouveau): ?>
            <div class="alert alert-succes">
                <strong>Nouveaux identifiants pour <?= e($nouveau['nom']) ?> (à lui communiquer) :</strong><br>
                Identifiant : <span class="mono"><strong><?= e($nouveau['identifiant']) ?></strong></span> &nbsp;|&nbsp;
                Mot de passe : <span class="mono"><strong><?= e($nouveau['mdp']) ?></strong></span>
            </div>
        <?php endif; ?>

        <form method="get" class="no-print" style="display:flex;gap:8px;margin-bottom:18px;flex-wrap:wrap;">
            <input type="text" name="q" value="<?= e($q) ?>" placeholder="Nom, numéro de téléphone ou matricule" style="flex:1;min-width:220px;" autofocus>
            <button class="btn btn-primaire">🔍 Rechercher</button>
        </form>

        <?php if (!$lignes): ?>
            <div class="alert alert-info">Aucun utilisateur trouvé.</div>
        <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Nom</th><th>Type</th><th>Matricule</th><th>Identifiant de connexion</th><th>Mot de passe initial</th><th>Action</th></tr></thead>
                <tbody>
                <?php foreach ($lignes as $l): ?>
                    <tr>
                        <td><?= e($l['nom_affiche']) ?><br><small><?= e($l['contact']) ?></small></td>
                        <td><?= $l['role'] === 'membre' ? 'Commission ' . e($l['commission']) : 'Séminariste' ?></td>
                        <td class="mono"><?= e($l['matricule']) ?></td>
                        <td class="mono"><strong><?= e($l['identifiant']) ?></strong></td>
                        <td class="mono"><?= $l['mdp_initial'] !== null ? e($l['mdp_initial']) : '<small>modifié par la personne</small>' ?></td>
                        <td>
                            <form method="post" onsubmit="return confirm('Réinitialiser le mot de passe de cette personne ?');">
                                <input type="hidden" name="reinit" value="<?= (int)$l['id'] ?>">
                                <button class="btn btn-sm btn-outline">🔄 Nouveau mot de passe</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
