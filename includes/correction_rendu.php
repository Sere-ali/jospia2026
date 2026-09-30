<?php
/**
 * Partiel réutilisable : affiche la correction détaillée du test d'entrée
 * pour un séminariste donné (question par question, réponse donnée vs
 * bonne réponse), afin de justifier la note obtenue.
 * Attend en entrée : $pdo (PDO) et $seminariste (ligne de la table seminaristes).
 */
$stmtCorrection = $pdo->prepare("
    SELECT q.*, rt.reponse_donnee, rt.correcte
    FROM reponses_test rt
    JOIN questions q ON q.id = rt.question_id
    WHERE rt.seminariste_id = ?
    ORDER BY q.id ASC
");
$stmtCorrection->execute([$seminariste['id']]);
$reponsesDetail = $stmtCorrection->fetchAll();
$totalQuestions = count($reponsesDetail);
$totalBonnes = count(array_filter($reponsesDetail, fn($r) => (int)$r['correcte'] === 1));
$totalMauvaises = $totalQuestions - $totalBonnes;
$lettres = ['A' => 'option_a', 'B' => 'option_b', 'C' => 'option_c', 'D' => 'option_d'];
?>
<div class="recap-note">
    <div class="bloc"><div class="chiffre"><?= e($seminariste['note_test']) ?>/20</div><div class="label">Note obtenue</div></div>
    <div class="bloc"><div class="chiffre"><?= $totalBonnes ?></div><div class="label">Bonnes réponses</div></div>
    <div class="bloc"><div class="chiffre"><?= $totalMauvaises ?></div><div class="label">Réponses incorrectes / non trouvées</div></div>
    <div class="bloc"><div class="chiffre"><?= e($seminariste['niveau_affecte']) ?></div><div class="label">Niveau attribué</div></div>
</div>

<?php if (!$reponsesDetail): ?>
    <div class="alert alert-info">Aucun détail de réponse n'a été enregistré pour ce test.</div>
<?php endif; ?>

<?php foreach ($reponsesDetail as $i => $r): ?>
    <?php $estBonne = (int)$r['correcte'] === 1; ?>
    <div class="carte question-correction <?= $estBonne ? 'bonne' : 'mauvaise' ?>">
        <span class="statut"><?= $estBonne ? '✅ Bonne réponse' : '❌ Réponse incorrecte / non trouvée' ?></span>
        <div class="categorie"><?= e($r['categorie']) ?></div>
        <h3>Q<?= $i + 1 ?>. <?= e($r['enonce']) ?></h3>
        <?php if (!$r['reponse_donnee']): ?>
            <p style="color:var(--texte-doux);font-size:.85rem;font-style:italic;margin-top:-6px;">Aucune réponse fournie par le séminariste (temps écoulé ou question non traitée).</p>
        <?php endif; ?>
        <?php foreach ($lettres as $lettre => $champ): ?>
            <?php
                $estLaBonneReponse = ($lettre === $r['bonne_reponse']);
                $estChoisieEtFausse = (!$estBonne && $lettre === $r['reponse_donnee']);
                $classesOption = 'option-item';
                if ($estLaBonneReponse) $classesOption .= ' rep-correcte';
                if ($estChoisieEtFausse) $classesOption .= ' rep-choisie-fausse';
            ?>
            <div class="<?= $classesOption ?>">
                <?= e($r[$champ]) ?>
                <?php if ($estLaBonneReponse): ?><span class="etiquette-option" style="color:var(--vert-fonce);">✔ Bonne réponse</span><?php endif; ?>
                <?php if ($estChoisieEtFausse): ?><span class="etiquette-option" style="color:var(--danger);">✘ Réponse donnée par le séminariste</span><?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endforeach; ?>
