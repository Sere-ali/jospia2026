<?php $pageAdmin = basename($_SERVER['PHP_SELF']); ?>
<div class="container" style="margin-top:18px;">
    <div class="carte" style="padding:12px 18px;display:flex;gap:8px;flex-wrap:wrap;">
        <a href="<?= BASE_URL ?>/admin/dashboard.php" class="btn btn-sm <?= $pageAdmin==='dashboard.php'?'btn-primaire':'btn-outline' ?>">📊 Tableau de bord</a>
        <a href="<?= BASE_URL ?>/admin/commissions.php" class="btn btn-sm <?= $pageAdmin==='commissions.php'?'btn-primaire':'btn-outline' ?>">👥 Membres commission</a>
        <a href="<?= BASE_URL ?>/admin/seminaristes.php" class="btn btn-sm <?= $pageAdmin==='seminaristes.php'?'btn-primaire':'btn-outline' ?>">🎓 Séminaristes</a>
        <a href="<?= BASE_URL ?>/admin/dortoirs.php" class="btn btn-sm <?= $pageAdmin==='dortoirs.php'?'btn-primaire':'btn-outline' ?>">🛏️ Dortoirs</a>
        <a href="<?= BASE_URL ?>/admin/paiements.php" class="btn btn-sm <?= $pageAdmin==='paiements.php'?'btn-primaire':'btn-outline' ?>">💳 Paiements Wave</a>
        <a href="<?= BASE_URL ?>/admin/listes.php" class="btn btn-sm <?= $pageAdmin==='listes.php'?'btn-primaire':'btn-outline' ?>">📋 Listes dortoir/niveau</a>
        <a href="<?= BASE_URL ?>/admin/critiques.php" class="btn btn-sm <?= $pageAdmin==='critiques.php'?'btn-primaire':'btn-outline' ?>">💬 Critiques</a>
        <a href="<?= BASE_URL ?>/admin/notes.php" class="btn btn-sm <?= $pageAdmin==='notes.php'?'btn-primaire':'btn-outline' ?>">📝 Saisie des notes</a>
        <a href="<?= BASE_URL ?>/admin/bulletins_impression.php" class="btn btn-sm <?= $pageAdmin==='bulletins_impression.php'?'btn-primaire':'btn-outline' ?>">🖨️ Bulletins (2/page)</a>
        <?php if (estSuperAdmin()): ?>
            <a href="<?= BASE_URL ?>/admin/matieres.php" class="btn btn-sm <?= $pageAdmin==='matieres.php'?'btn-primaire':'btn-outline' ?>">📚 Matières & résultats</a>
            <a href="<?= BASE_URL ?>/admin/questions.php" class="btn btn-sm <?= $pageAdmin==='questions.php'?'btn-primaire':'btn-outline' ?>">📝 Questions du test</a>
            <a href="<?= BASE_URL ?>/admin/config_quiz.php" class="btn btn-sm <?= $pageAdmin==='config_quiz.php'?'btn-primaire':'btn-outline' ?>">⚙️ Config Quiz (6 Banques)</a>
            <a href="<?= BASE_URL ?>/admin/users.php" class="btn btn-sm <?= $pageAdmin==='users.php'?'btn-primaire':'btn-outline' ?>">🔑 Comptes admin</a>
        <?php endif; ?>
    </div>
</div>
