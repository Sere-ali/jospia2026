<?php
$pageAdmin = basename($_SERVER['PHP_SELF']);
// Pages regroupées sous le bouton « Commission scientifique »
$pagesScientifique = ['commission_scientifique.php', 'notes.php', 'bulletins_impression.php', 'bulletin.php', 'test_entree.php', 'matieres.php', 'questions.php', 'config_quiz.php'];
$dansScientifique = in_array($pageAdmin, $pagesScientifique, true) || ($pageAdmin === 'listes.php' && !estAdmin());
// Pages regroupées sous le bouton « Administration »
$pagesAdministration = ['administration.php', 'commissions.php', 'edit_membre.php', 'seminaristes.php', 'edit_seminariste.php', 'seminariste_detail.php', 'identifiants.php', 'dortoirs.php', 'listes.php', 'badges_commission.php', 'badges_seminaristes.php', 'download_badge.php', 'download_badge_seminariste.php', 'download_diplome.php', 'download_diplome_membre.php'];
$dansFinance = strpos($_SERVER['PHP_SELF'], '/finance/') !== false;
$dansAdministration = in_array($pageAdmin, $pagesAdministration, true) || $dansScientifique || $dansFinance;
?>
<div class="container" style="margin-top:18px;">
    <?php if (!estAdmin()): ?>
    <div class="carte" style="padding:12px 18px;display:flex;gap:8px;flex-wrap:wrap;">
        <a href="<?= BASE_URL ?>/admin/commission_scientifique" class="btn btn-sm btn-primaire">🔬 Commission scientifique</a>
    </div>
    <?php else: ?>
    <div class="carte" style="padding:12px 18px;display:flex;gap:8px;flex-wrap:wrap;">
        <a href="<?= BASE_URL ?>/admin/dashboard" class="btn btn-sm <?= $pageAdmin==='dashboard.php'?'btn-primaire':'btn-outline' ?>">📊 Tableau de bord</a>
        <a href="<?= BASE_URL ?>/admin/administration" class="btn btn-sm <?= $dansAdministration ? 'btn-primaire' : 'btn-or' ?>">🗂️ Administration</a>
        <a href="<?= BASE_URL ?>/finance/paiements" class="btn btn-sm <?= $dansFinance ? 'btn-primaire' : 'btn-outline' ?>">💰 Commission finance</a>
        <a href="<?= BASE_URL ?>/admin/commission_scientifique" class="btn btn-sm <?= $dansScientifique ? 'btn-primaire' : 'btn-outline' ?>">🔬 Commission scientifique</a>
        <a href="<?= BASE_URL ?>/securite/visiteurs" class="btn btn-sm <?= strpos($_SERVER['PHP_SELF'], '/securite/') !== false ? 'btn-primaire' : 'btn-outline' ?>">🛡️ Commission sécurité</a>
        <?php if (estSuperAdmin()): ?>
            <a href="<?= BASE_URL ?>/admin/critiques" class="btn btn-sm <?= $pageAdmin==='critiques.php'?'btn-primaire':'btn-outline' ?>">💬 Critiques</a>
            <a href="<?= BASE_URL ?>/admin/users" class="btn btn-sm <?= $pageAdmin==='users.php'?'btn-primaire':'btn-outline' ?>">🔑 Comptes admin</a>
            <a href="<?= BASE_URL ?>/admin/activite" class="btn btn-sm <?= $pageAdmin==='activite.php'?'btn-primaire':'btn-outline' ?>">📋 Activité journalière</a>
            <a href="<?= BASE_URL ?>/admin/parametres" class="btn btn-sm <?= $pageAdmin==='parametres.php'?'btn-primaire':'btn-outline' ?>">⚙️ Paramètres du site</a>
        <?php endif; ?>
    </div>
    <?php if ($dansAdministration): ?>
        <div class="carte" style="padding:10px 18px;margin-top:10px;display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
            <strong style="margin-right:6px;">🗂️ Administration :</strong>
            <a href="<?= BASE_URL ?>/admin/commissions" class="btn btn-sm <?= $pageAdmin==='commissions.php'?'btn-primaire':'btn-outline' ?>">👥 Membres commission</a>
            <a href="<?= BASE_URL ?>/admin/seminaristes" class="btn btn-sm <?= $pageAdmin==='seminaristes.php'?'btn-primaire':'btn-outline' ?>">🎓 Séminaristes</a>
            <a href="<?= BASE_URL ?>/admin/identifiants" class="btn btn-sm <?= $pageAdmin==='identifiants.php'?'btn-primaire':'btn-outline' ?>">🔑 Identifiants</a>
            <a href="<?= BASE_URL ?>/admin/dortoirs" class="btn btn-sm <?= $pageAdmin==='dortoirs.php'?'btn-primaire':'btn-outline' ?>">🛏️ Dortoirs</a>
            <a href="<?= BASE_URL ?>/admin/listes" class="btn btn-sm <?= $pageAdmin==='listes.php'?'btn-primaire':'btn-outline' ?>">📋 Listes dortoir/niveau</a>
            <a href="<?= BASE_URL ?>/admin/commission_scientifique" class="btn btn-sm <?= $dansScientifique ? 'btn-primaire' : 'btn-outline' ?>">🔬 Commission scientifique</a>
            <a href="<?= BASE_URL ?>/finance/paiements" class="btn btn-sm <?= $dansFinance ? 'btn-primaire' : 'btn-outline' ?>">💰 Commission finance</a>
        </div>
    <?php endif; ?>
    <?php endif; ?>
    <?php if ($dansScientifique): ?>
        <div class="carte" style="padding:10px 18px;margin-top:10px;display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
            <strong style="margin-right:6px;">🔬 Commission scientifique :</strong>
            <a href="<?= BASE_URL ?>/admin/notes" class="btn btn-sm <?= $pageAdmin==='notes.php'?'btn-primaire':'btn-outline' ?>">📝 Saisie des notes</a>
            <a href="<?= BASE_URL ?>/admin/test_entree" class="btn btn-sm <?= $pageAdmin==='test_entree.php'?'btn-primaire':'btn-outline' ?>">🧪 Test d'entrée</a>
            <a href="<?= BASE_URL ?>/admin/listes" class="btn btn-sm <?= $pageAdmin==='listes.php'?'btn-primaire':'btn-outline' ?>">📋 Listes dortoir/niveau</a>
            <a href="<?= BASE_URL ?>/admin/bulletins_impression" class="btn btn-sm <?= in_array($pageAdmin, ['bulletins_impression.php','bulletin.php'], true)?'btn-primaire':'btn-outline' ?>">📄 Bulletins PDF (2/feuille)</a>
            <?php if (estScientifique()): ?>
                <a href="<?= BASE_URL ?>/admin/matieres" class="btn btn-sm <?= $pageAdmin==='matieres.php'?'btn-primaire':'btn-outline' ?>">📚 Matières & résultats</a>
                <a href="<?= BASE_URL ?>/admin/questions" class="btn btn-sm <?= $pageAdmin==='questions.php'?'btn-primaire':'btn-outline' ?>">📝 Questions du test</a>
                <a href="<?= BASE_URL ?>/admin/config_quiz" class="btn btn-sm <?= $pageAdmin==='config_quiz.php'?'btn-primaire':'btn-outline' ?>">⚙️ Config Quiz (6 Banques)</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
