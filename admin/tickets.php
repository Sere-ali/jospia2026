<?php
require_once __DIR__ . '/../includes/init.php';
exigerRole(['finance', 'admin', 'superadmin']);
ticketsPreparer($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'generer') {
    $n = (int)($_POST['quantite'] ?? 0);
    if ($n < 1 || $n > 400) {
        $_SESSION['flash_erreur'] = "Indiquez une quantité entre 1 et 400.";
        redirect('/admin/tickets');
    }
    [$de, $a] = ticketsGenerer($pdo, $n);
    journaliser($pdo, "Tickets générés : n° $de à $a", '');
    redirect('/admin/tickets_pdf?de=' . $de . '&a=' . $a);
}

$total = (int)$pdo->query("SELECT COUNT(*) FROM tickets")->fetchColumn();
$max = (int)$pdo->query("SELECT COALESCE(MAX(numero), 0) FROM tickets")->fetchColumn();
$derniers = $pdo->query("SELECT numero, code, created_at FROM tickets ORDER BY numero DESC LIMIT 12")->fetchAll();

$titrePage = "Tickets de paiement";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/' . (estAdmin() ? 'admin_nav.php' : 'finance_nav.php');
?>
<section class="section">
    <div class="container" style="max-width:820px;">
        <div class="section-titre"><span class="eyebrow">Commission finance</span><h2>Tickets de paiement</h2>
            <p>Chaque ticket a un numéro codé, imprimé sur la <strong>souche</strong> (à garder, avec le nom, la section, le contact et le contact d'un parent) et sur le <strong>ticket</strong> remis à la personne. Les tickets portent l'entête des badges avec les logos AEEMCI et JOSPIA.</p>
        </div>
        <?php if (!empty($_SESSION['flash_erreur'])): ?><div class="alert alert-erreur"><?= e($_SESSION['flash_erreur']) ?></div><?php unset($_SESSION['flash_erreur']); endif; ?>

        <div class="carte" style="margin-bottom:20px;">
            <h3>Générer des tickets</h3>
            <form method="post" data-no-ajax style="display:flex;gap:10px;flex-wrap:wrap;align-items:end;">
                <input type="hidden" name="action" value="generer">
                <div><label for="quantite" style="display:block;font-weight:600;">Nombre de tickets</label>
                <input type="number" name="quantite" id="quantite" min="1" max="400" value="40" required style="padding:10px;border:1px solid #ccc;border-radius:8px;width:140px;"></div>
                <button type="submit" class="btn btn-primaire">Générer et télécharger le PDF</button>
            </form>
            <small style="color:var(--texte-doux);display:block;margin-top:8px;">Les numéros continuent à la suite des précédents (aucun numéro n'est réutilisé). PDF : 4 tickets par page A4, à découper et à détacher le long du pointillé. Montant imprimé : <?= number_format(FRAIS_PARTICIPATION, 0, ',', ' ') ?> FCFA.</small>
        </div>

        <div class="carte">
            <h3>Retélécharger des tickets déjà générés</h3>
            <p style="margin:0 0 10px;"><?= $total ?> ticket(s) généré(s)<?= $max ? ' (n° 1 à ' . $max . ')' : '' ?>.</p>
            <?php if ($total): ?>
            <form method="get" action="<?= BASE_URL ?>/admin/tickets_pdf" style="display:flex;gap:10px;flex-wrap:wrap;align-items:end;">
                <div><label for="de" style="display:block;font-weight:600;">Du n°</label><input type="number" name="de" id="de" min="1" max="<?= $max ?>" value="1" required style="padding:10px;border:1px solid #ccc;border-radius:8px;width:120px;"></div>
                <div><label for="a" style="display:block;font-weight:600;">Au n°</label><input type="number" name="a" id="a" min="1" max="<?= $max ?>" value="<?= min($max, 40) ?>" required style="padding:10px;border:1px solid #ccc;border-radius:8px;width:120px;"></div>
                <button type="submit" class="btn btn-or">Télécharger le PDF</button>
            </form>
            <div class="table-wrap" style="box-shadow:none;margin-top:14px;"><table>
                <thead><tr><th>N°</th><th>Code</th><th>Généré le</th></tr></thead>
                <tbody><?php foreach ($derniers as $t): ?><tr><td><?= (int)$t['numero'] ?></td><td class="mono"><strong><?= e($t['code']) ?></strong></td><td><?= e(date('d/m/Y H:i', strtotime($t['created_at']))) ?></td></tr><?php endforeach; ?></tbody>
            </table></div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
