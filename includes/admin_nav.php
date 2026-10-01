<?php
$pageAdmin = basename($_SERVER['PHP_SELF']);
// Pages regroupées sous le bouton « Commission scientifique »
$pagesScientifique = ['commission_scientifique.php', 'notes.php', 'bulletins_impression.php', 'bulletin.php', 'test_entree.php', 'matieres.php', 'questions.php', 'config_quiz.php'];
$dansScientifique = in_array($pageAdmin, $pagesScientifique, true) || ($pageAdmin === 'listes.php' && !estAdmin());
// Pages regroupées sous le bouton « Administration »
$pagesAdministration = ['administration.php', 'commissions.php', 'edit_membre.php', 'seminaristes.php', 'edit_seminariste.php', 'seminariste_detail.php', 'identifiants.php', 'dortoirs.php', 'listes.php', 'badges_commission.php', 'badges.php', 'diplomes.php', 'apercu_documents.php', 'badges_seminaristes.php', 'download_badge.php', 'download_badge_seminariste.php', 'download_diplome.php', 'download_diplome_membre.php', 'sorties_roles.php', 'sorties_mg.php'];
$dansFinance = strpos($_SERVER['PHP_SELF'], '/finance/') !== false;
$dansAdministration = in_array($pageAdmin, $pagesAdministration, true) || $dansScientifique || $dansFinance;
// Barre complète seulement sur le tableau de bord ; ailleurs un simple bouton « Retour ».
$accueilNav = estAdmin() ? 'dashboard.php' : 'commission_scientifique.php';
$surAccueil = ($pageAdmin === $accueilNav);
if ($pageAdmin === 'sorties.php') { $urlRetour = BASE_URL . '/securite/visiteurs'; }
elseif ($dansScientifique && $pageAdmin !== 'commission_scientifique.php') { $urlRetour = BASE_URL . '/admin/commission_scientifique'; }
elseif ($dansAdministration && !$dansScientifique && !$dansFinance && $pageAdmin !== 'administration.php') { $urlRetour = BASE_URL . '/admin/administration'; }
else { $urlRetour = BASE_URL . (estAdmin() ? '/admin/dashboard' : '/admin/commission_scientifique'); }
?>
<div class="container" style="margin-top:18px;">
    <?php if (!$surAccueil): ?>
        <p class="no-print" style="margin:0 0 4px;"><a href="<?= e($urlRetour) ?>" class="btn btn-outline btn-sm">&larr; Retour</a></p>
    <?php elseif (!estAdmin()): ?>
    <div class="carte" style="padding:12px 18px;display:flex;gap:8px;flex-wrap:wrap;">
        <a href="<?= BASE_URL ?>/admin/commission_scientifique" class="btn btn-sm btn-primaire">🔬 Commission scientifique</a>
    </div>
    <?php elseif (!estSuperAdmin()): ?>
    <div class="carte" style="padding:12px 18px;display:flex;gap:8px;flex-wrap:wrap;">
        <a href="<?= BASE_URL ?>/admin/administration" class="btn btn-sm btn-primaire">🗂️ Administration</a>
    </div>
    <?php else: ?>
    <div class="carte" style="padding:12px 18px;display:flex;gap:8px;flex-wrap:wrap;">
        <a href="<?= BASE_URL ?>/admin/dashboard" class="btn btn-sm <?= $pageAdmin==='dashboard.php'?'btn-primaire':'btn-outline' ?>">📊 Tableau de bord</a>
        <a href="<?= BASE_URL ?>/admin/administration" class="btn btn-sm <?= $dansAdministration ? 'btn-primaire' : 'btn-or' ?>">🗂️ Administration</a>
        <a href="<?= BASE_URL ?>/finance/paiements" class="btn btn-sm <?= $dansFinance ? 'btn-primaire' : 'btn-outline' ?>">💰 Commission finance</a>
        <a href="<?= BASE_URL ?>/admin/commission_scientifique" class="btn btn-sm <?= $dansScientifique ? 'btn-primaire' : 'btn-outline' ?>">🔬 Commission scientifique</a>
        <a href="<?= BASE_URL ?>/securite/visiteurs" class="btn btn-sm <?= strpos($_SERVER['PHP_SELF'], '/securite/') !== false ? 'btn-primaire' : 'btn-outline' ?>">🛡️ Commission sécurité</a>
        <a href="<?= BASE_URL ?>/admin/rapports" class="btn btn-sm <?= $pageAdmin==='rapports.php'?'btn-primaire':'btn-or' ?>">📝 Rapports journaliers</a>
        <?php if (estSuperAdmin()): ?>
            <a href="<?= BASE_URL ?>/admin/critiques" class="btn btn-sm <?= $pageAdmin==='critiques.php'?'btn-primaire':'btn-outline' ?>">💬 Critiques</a>
            <a href="<?= BASE_URL ?>/admin/users" class="btn btn-sm <?= $pageAdmin==='users.php'?'btn-primaire':'btn-outline' ?>">🔑 Comptes admin</a>
            <a href="<?= BASE_URL ?>/admin/activite" class="btn btn-sm <?= $pageAdmin==='activite.php'?'btn-primaire':'btn-outline' ?>">📋 Activité journalière</a>
            <a href="<?= BASE_URL ?>/admin/parametres" class="btn btn-sm <?= $pageAdmin==='parametres.php'?'btn-primaire':'btn-outline' ?>">⚙️ Paramètres du site</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>
