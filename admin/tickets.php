<?php
require_once __DIR__ . '/../includes/init.php';
exigerRole(['finance', 'admin', 'superadmin']);
ticketsPreparer($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = $_POST['action'] ?? '';
    if ($act === 'generer') {
        $r = ticketsGenererLot($pdo);
        if (!$r) { $_SESSION['flash_erreur'] = "Les 500 tickets (25 lots) sont déjà générés."; redirect('/admin/tickets'); }
        journaliser($pdo, "Lot de tickets généré : n° {$r[0]} à {$r[1]}", '');
        redirect('/admin/tickets_pdf?de=' . $r[0] . '&a=' . $r[1]);
    }
    if ($act === 'reinitialiser') {
        ticketsReinitialiser($pdo);
        journaliser($pdo, "Tickets réinitialisés (tous supprimés)", '');
        redirect('/admin/tickets');
    }
}

$total = (int)$pdo->query("SELECT COUNT(*) FROM tickets")->fetchColumn();
$max = (int)$pdo->query("SELECT COALESCE(MAX(numero), 0) FROM tickets")->fetchColumn();
$codes = $pdo->query("SELECT numero, code FROM tickets ORDER BY numero")->fetchAll(PDO::FETCH_KEY_PAIR);
$derniers = $pdo->query("SELECT numero, code, created_at FROM tickets ORDER BY numero DESC LIMIT 12")->fetchAll();

$titrePage = "Tickets de paiement";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/' . (estAdmin() ? 'admin_nav.php' : 'finance_nav.php');
?>
<section class="section">
    <div class="container" style="max-width:820px;">
        <div class="section-titre"><span class="eyebrow">Commission finance</span><h2>Tickets de paiement</h2>
            <p>Les tickets sont <strong>vendus</strong> (prix imprimé : <?= number_format(FRAIS_PARTICIPATION, 0, ',', ' ') ?> FCFA). Chaque ticket a un numéro codé. La <strong>souche</strong> reste avec nous (nom, prénoms, sous-comité, section, contact, contact d'un parent, date de paiement). Le <strong>ticket</strong> remis à l'acheteur porte son nom, son contact et la date, avec un QR code qui ouvre l'inscription sur le site.</p>
        </div>
        <?php if (!empty($_SESSION['flash_erreur'])): ?><div class="alert alert-erreur"><?= e($_SESSION['flash_erreur']) ?></div><?php unset($_SESSION['flash_erreur']); endif; ?>

        <div class="carte" style="margin-bottom:20px;">
            <h3>Générer un lot de 20 tickets</h3>
            <p style="margin:0 0 10px;">Lots générés : <strong><?= intdiv($total, TICKETS_PAR_SOUCHE) ?> / <?= count(TICKETS_SERIES) * TICKETS_SOUCHES_PAR_SERIE ?></strong> (<?= $total ?> tickets sur 500).</p>
            <form method="post" data-no-ajax style="display:inline;">
                <input type="hidden" name="action" value="generer">
                <button type="submit" class="btn btn-primaire" <?= $total >= 500 ? 'disabled' : '' ?>><?= $total ? 'Générer le lot suivant (20 tickets)' : 'Générer le premier lot (20 tickets)' ?></button>
            </form>
            <small style="color:var(--texte-doux);display:block;margin-top:8px;">On commence <strong>obligatoirement par 20 tickets</strong> (une souche), puis on ajoute 20 tickets à chaque fois, jusqu'à 25 lots = 500 tickets. Chaque lot porte une seule lettre (<strong>KI, AS, MS, OK, NS</strong>, 5 lots chacune). Les lettres sortent dans un ordre aléatoire, par tours de 5 lots : après une lettre, on passe obligatoirement par les 4 autres avant de la retrouver. Le PDF du lot se télécharge directement. Montant imprimé : <?= number_format(FRAIS_PARTICIPATION, 0, ',', ' ') ?> FCFA.</small>
        </div>

        <?php if ($total): ?>
        <div class="carte" style="margin-bottom:20px;">
            <h3>Les 25 lots (souches de 20 tickets)</h3>
            <p style="margin:0 0 10px;color:var(--texte-doux);">Un <strong>lot = une souche de 20 tickets d'une même lettre</strong>. Utilisez les lots dans l'ordre <strong>1, 2, 3…</strong> : la lettre change à chaque lot (jamais deux lots de la même lettre à la suite). Chaque lettre a 5 lots : <strong>KI001–KI100</strong>, <strong>AS001–AS100</strong>, <strong>MS001–MS100</strong>, <strong>OK001–OK100</strong>, <strong>NS001–NS100</strong>.</p>
            <div class="table-wrap" style="box-shadow:none;"><table>
                <thead><tr><th>Lot n°</th><th>Tickets</th><th>Lettre</th><th></th></tr></thead>
                <tbody><?php for ($n0 = 1; $n0 <= $max; $n0 += TICKETS_PAR_SOUCHE): $i0 = ticketInfoCode($codes[$n0] ?? 'KI001'); ?>
                    <tr><td><strong><?= intdiv($n0 - 1, TICKETS_PAR_SOUCHE) + 1 ?></strong></td><td class="mono"><?= e($i0['plage']) ?></td><td><strong><?= e($i0['serie']) ?></strong> (souche <?= (int)$i0['souche'] ?>/<?= TICKETS_SOUCHES_PAR_SERIE ?>)</td>
                    <td><a class="btn btn-outline btn-sm" href="<?= BASE_URL ?>/admin/tickets_pdf?de=<?= $n0 ?>&a=<?= $n0 + TICKETS_PAR_SOUCHE - 1 ?>">Télécharger le lot</a></td></tr>
                <?php endfor; ?></tbody>
            </table></div>
        </div>
        <?php endif; ?>

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
            <form method="post" data-no-ajax style="margin-top:14px;" onsubmit="return confirm('Supprimer TOUS les tickets générés et recommencer à zéro ? À ne faire que si rien n\'a été imprimé.');">
                <input type="hidden" name="action" value="reinitialiser">
                <button type="submit" class="btn btn-outline btn-sm">Recommencer à zéro (supprime tous les tickets)</button>
            </form>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
