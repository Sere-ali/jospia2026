<?php $pageFin = basename($_SERVER['PHP_SELF']); ?>
<div class="container" style="margin-top:18px;">
    <div class="carte" style="padding:12px 18px;display:flex;gap:8px;flex-wrap:wrap;">
        <a href="<?= BASE_URL ?>/finance/paiements" class="btn btn-sm <?= $pageFin==='paiements.php'?'btn-primaire':'btn-outline' ?>">💳 Paiements à valider</a>
        <a href="<?= BASE_URL ?>/finance/scanner" class="btn btn-sm <?= $pageFin==='scanner.php'?'btn-primaire':'btn-outline' ?>">📷 Scanner un reçu (QR)</a>
        <?php if (estAdmin()): ?><a href="<?= BASE_URL ?>/admin/dashboard" class="btn btn-sm btn-outline">⬅ Administration</a><?php endif; ?>
    </div>
</div>
