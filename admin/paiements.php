<?php
require_once __DIR__ . '/../includes/init.php';
exigerRole(['admin', 'superadmin']);

$titrePage = "Gestion des paiements";

// Traitement de la validation / rejet
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['paiement_id'], $_POST['action'])) {
    $paiement_id = (int)$_POST['paiement_id'];
    $action = $_POST['action'];
    $admin_id = $_SESSION['compte_id'];

    if ($action === 'valider') {
        $stmt = $pdo->prepare("UPDATE paiements SET statut = 'validé', admin_validateur_id = ? WHERE id = ?");
        $stmt->execute([$admin_id, $paiement_id]);
        $_SESSION['flash_succes'] = "Paiement validé avec succès.";
    } elseif ($action === 'rejeter') {
        $motif = trim($_POST['motif_rejet'] ?? '');
        $stmt = $pdo->prepare("UPDATE paiements SET statut = 'rejeté', motif_rejet = ?, admin_validateur_id = ? WHERE id = ?");
        $stmt->execute([$motif, $admin_id, $paiement_id]);
        $_SESSION['flash_succes'] = "Paiement rejeté.";
    }
    
    redirect('/admin/paiements.php');
}

// Récupérer la liste des paiements avec infos du séminariste
$query = "SELECT p.*, s.nom_prenoms, s.matricule, s.contact, s.anyama, s.section, c.nom_affiche as admin_nom
          FROM paiements p
          JOIN seminaristes s ON p.seminariste_id = s.id
          LEFT JOIN comptes c ON p.admin_validateur_id = c.id
          ORDER BY FIELD(p.statut, 'en attente') DESC, p.created_at DESC";
$paiements = $pdo->query($query)->fetchAll();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/admin_nav.php';
?>

<section class="section">
    <div class="container">
        <div class="section-titre">
            <h2>Gestion des Paiements Wave</h2>
            <p>Validez ou rejetez les références de transaction soumises par les séminaristes.</p>
        </div>

        <?php
        $stats = ['total' => count($paiements), 'valides' => 0, 'en_attente' => 0, 'rejetes' => 0];
        foreach ($paiements as $p) {
            if ($p['statut'] === 'validé') $stats['valides']++;
            elseif ($p['statut'] === 'en attente') $stats['en_attente']++;
            elseif ($p['statut'] === 'rejeté') $stats['rejetes']++;
        }
        $montantTotal = $stats['valides'] * FRAIS_PARTICIPATION;
        ?>

        <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); margin-bottom: 30px;">
            <div class="carte text-center" style="border-left: 4px solid #1cc6f4;">
                <div style="font-size: 2rem; font-weight: bold; color: #1cc6f4;"><?= number_format($montantTotal, 0, ',', ' ') ?> FCFA</div>
                <div style="color: var(--texte-doux); font-size: 0.9rem;">Total Encaissé (Validé)</div>
            </div>
            <div class="carte text-center" style="border-left: 4px solid var(--couleur-succes);">
                <div style="font-size: 2rem; font-weight: bold; color: var(--couleur-succes);"><?= $stats['valides'] ?></div>
                <div style="color: var(--texte-doux); font-size: 0.9rem;">Paiements Validés</div>
            </div>
            <div class="carte text-center" style="border-left: 4px solid #f39c12;">
                <div style="font-size: 2rem; font-weight: bold; color: #f39c12;"><?= $stats['en_attente'] ?></div>
                <div style="color: var(--texte-doux); font-size: 0.9rem;">En Attente</div>
            </div>
            <div class="carte text-center" style="border-left: 4px solid var(--couleur-erreur);">
                <div style="font-size: 2rem; font-weight: bold; color: var(--couleur-erreur);"><?= $stats['rejetes'] ?></div>
                <div style="color: var(--texte-doux); font-size: 0.9rem;">Rejetés</div>
            </div>
        </div>

        <?php if (!empty($_SESSION['flash_succes'])): ?>
            <div class="alert alert-succes"><?= e($_SESSION['flash_succes']) ?></div>
            <?php unset($_SESSION['flash_succes']); ?>
        <?php endif; ?>

        <div class="carte" style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Séminariste (Matricule)</th>
                        <th>Contact</th>
                        <th>Référence Transaction</th>
                        <th>Statut</th>
                        <th>Action / Info</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($paiements as $p): ?>
                    <tr>
                        <td style="font-size:0.9rem;"><?= date('d/m/Y H:i', strtotime($p['created_at'])) ?></td>
                        <td>
                            <strong><?= e($p['nom_prenoms']) ?></strong><br>
                            <small class="tag"><?= e($p['matricule']) ?></small>
                        </td>
                        <td><?= e($p['contact']) ?></td>
                        <td style="font-family: monospace; font-size: 1.1em;">
                            <strong><?= e($p['reference_transaction']) ?></strong>
                        </td>
                        <td>
                            <?php if ($p['statut'] === 'en attente'): ?>
                                <span class="tag tag-vert" style="background:#ffc107;color:#000;">En attente</span>
                            <?php elseif ($p['statut'] === 'validé'): ?>
                                <span class="tag tag-vert">Validé</span>
                            <?php else: ?>
                                <span class="tag tag-rouge" style="background:#dc3545;">Rejeté</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($p['statut'] === 'en attente'): ?>
                                <div style="display: flex; gap: 5px;">
                                    <form method="post" onsubmit="return confirm('Confirmer la validation ?');">
                                        <input type="hidden" name="paiement_id" value="<?= $p['id'] ?>">
                                        <input type="hidden" name="action" value="valider">
                                        <button type="submit" class="btn btn-primaire btn-sm">Valider</button>
                                    </form>
                                    
                                    <form method="post" onsubmit="var m = prompt('Motif du rejet :'); if(m !== null) { this.motif_rejet.value = m; return true; } return false;">
                                        <input type="hidden" name="paiement_id" value="<?= $p['id'] ?>">
                                        <input type="hidden" name="action" value="rejeter">
                                        <input type="hidden" name="motif_rejet" value="">
                                        <button type="submit" class="btn btn-sm" style="background-color: #dc3545; color: white;">Rejeter</button>
                                    </form>
                                </div>
                            <?php else: ?>
                                <small style="color:var(--texte-doux)">
                                    <?= ucfirst($p['statut']) ?> par <?= e($p['admin_nom'] ?? 'Admin') ?><br>
                                    <?= $p['motif_rejet'] ? 'Motif: ' . e($p['motif_rejet']) : '' ?>
                                </small>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$paiements): ?>
                    <tr><td colspan="6" class="text-center">Aucun paiement trouvé.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
