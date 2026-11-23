<?php
require_once __DIR__ . '/../includes/init.php';
exigerRole(['finance', 'admin', 'superadmin']);
ticketsPreparer($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'generer') {
    [$de, $a] = ticketsGenerer($pdo);
    journaliser($pdo, "Tickets générés : n° $de à $a (KI, AS, MS, OK, NS)", '');
    redirect('/admin/tickets');
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
            <p>Les tickets sont <strong>vendus</strong> (prix imprimé : <?= number_format(FRAIS_PARTICIPATION, 0, ',', ' ') ?> FCFA). Chaque ticket a un numéro codé. La <strong>souche</strong> reste avec nous (nom, prénoms, sous-comité, section, contact, contact d'un parent, date de paiement). Le <strong>ticket</strong> remis à l'acheteur porte son nom, son contact et la date, avec un QR code qui ouvre l'inscription sur le site.</p>
        </div>
        <?php if (!empty($_SESSION['flash_erreur'])): ?><div class="alert alert-erreur"><?= e($_SESSION['flash_erreur']) ?></div><?php unset($_SESSION['flash_erreur']); endif; ?>

        <div class="carte" style="margin-bottom:20px;">
            <h3>Générer les 500 tickets</h3>
            <form method="post" data-no-ajax>
                <input type="hidden" name="action" value="generer">
                <button type="submit" class="btn btn-primaire">Générer / actualiser les 500 tickets</button>
            </form>
            <small style="color:var(--texte-doux);display:block;margin-top:8px;">5 séries <strong>KI, AS, MS, OK, NS</strong>, chacune de 5 souches de 20 tickets (ex. KI001 à KI020 = 1re souche KI, KI021 à KI040 = 2e souche…). Montant imprimé : <?= number_format(FRAIS_PARTICIPATION, 0, ',', ' ') ?> FCFA. PDF : 4 tickets par page A4, à découper le long du pointillé.</small>
        </div>

        <?php if ($total): ?>
        <div class="carte" style="margin-bottom:20px;">
            <h3>Télécharger par souche</h3>
            <div class="table-wrap" style="box-shadow:none;"><table>
                <thead><tr><th>Série</th><th>Souche</th><th>Numéros</th><th></th></tr></thead>
                <tbody><?php for ($n0 = 1; $n0 <= $max; $n0 += TICKETS_PAR_SOUCHE): $i0 = ticketInfo($n0); $i1 = ticketInfo($n0 + TICKETS_PAR_SOUCHE - 1); ?>
                    <tr><td><strong><?= e($i0['serie']) ?></strong></td><td><?= (int)$i0['souche'] ?> / <?= TICKETS_SOUCHES_PAR_SERIE ?></td><td class="mono"><?= e($i0['plage']) ?></td>
                    <td><a class="btn btn-outline btn-sm" href="<?= BASE_URL ?>/admin/tickets_pdf?de=<?= $n0 ?>&a=<?= $n0 + TICKETS_PAR_SOUCHE - 1 ?>">PDF</a></td></tr>
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
            <?php endif; ?>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
