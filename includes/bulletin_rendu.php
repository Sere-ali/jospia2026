<?php
/**
 * Partiel réutilisable : affiche le bulletin de notes d'un séminariste.
 * Attend en entrée : $pdo (PDO) et $seminariste (ligne de la table seminaristes).
 */
$stmtNotes = $pdo->prepare("
    SELECT m.nom, m.note_max, m.ordre, n.note
    FROM matieres m
    LEFT JOIN notes n ON n.matiere_id = m.id AND n.seminariste_id = ?
    ORDER BY m.ordre, m.nom
");
$stmtNotes->execute([$seminariste['id']]);
$lignesBulletin = $stmtNotes->fetchAll();

$sommeNormalisee = 0;
$nbNotees = 0;
foreach ($lignesBulletin as $l) {
    if ($l['note'] !== null) {
        $sommeNormalisee += ((float)$l['note'] / (float)$l['note_max']) * 20;
        $nbNotees++;
    }
}
$moyenneGenerale = $nbNotees > 0 ? round($sommeNormalisee / $nbNotees, 2) : null;
[$mentionGenerale, $couleurMention] = $moyenneGenerale !== null ? appreciationNote($moyenneGenerale) : ['-', 'gris'];
?>
<div class="fiche-doc bulletin-doc" style="max-width:680px;">
    <div class="entete-fiche">
        <img src="<?= BASE_URL ?>/assets/img/logo.jpg" alt="Logo">
        <div>
            <h3 style="margin:0;">Bulletin de notes</h3>
            <div style="color:var(--texte-doux);font-size:.85rem;"><?= e(EVENT_FULL) ?></div>
        </div>
    </div>

    <?php if ($seminariste['photo']): ?>
        <img class="fiche-photo" src="<?= BASE_URL ?>/uploads/photos/<?= e($seminariste['photo']) ?>" alt="Photo">
    <?php endif; ?>

    <dl>
        <dt>Nom et prénoms</dt><dd><?= e($seminariste['nom_prenoms']) ?></dd>
        <dt>Matricule</dt><dd class="mono"><?= e($seminariste['matricule']) ?></dd>
        <dt>Sous-comité</dt><dd><?= e($seminariste['anyama']) ?></dd>
        <dt>Section</dt><dd><?= e($seminariste['section']) ?></dd>
        <dt>Dortoir</dt><dd><?= e($seminariste['dortoir']) ?></dd>
        <dt>Test d'entrée</dt>
        <dd>
            <?php if ($seminariste['dortoir'] === 'Pépinière'): ?>
                Non applicable (sous-comité Pépinière)
            <?php elseif ($seminariste['test_complete']): ?>
                <?= e($seminariste['note_test']) ?> / 20 - Niveau <?= e($seminariste['niveau_affecte']) ?>
            <?php else: ?>
                Non composé
            <?php endif; ?>
        </dd>
    </dl>

    <div style="clear:both;"></div>

    <div class="table-wrap" style="box-shadow:none;margin-top:18px;">
        <table>
            <thead><tr><th>Matière</th><th>Note</th><th>Barème</th><th>Appréciation</th></tr></thead>
            <tbody>
            <?php foreach ($lignesBulletin as $l): ?>
                <?php
                    if ($l['note'] !== null) {
                        $ratio = ((float)$l['note'] / (float)$l['note_max']) * 20;
                        [$appreciation, $couleur] = appreciationNote($ratio);
                    } else {
                        $appreciation = 'Non noté'; $couleur = 'gris';
                    }
                ?>
                <tr>
                    <td><strong><?= e($l['nom']) ?></strong></td>
                    <td><?= $l['note'] !== null ? e($l['note']) : '<span style="color:var(--texte-doux);">-</span>' ?></td>
                    <td>/ <?= e($l['note_max']) ?></td>
                    <td><span class="pill pill-<?= $couleur ?>"><?= e($appreciation) ?></span></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$lignesBulletin): ?><tr><td colspan="4">Aucune matière définie.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="dortoir-box" style="margin-top:20px;display:flex;justify-content:space-around;align-items:center;flex-wrap:wrap;gap:10px;">
        <div>
            <div class="label">Moyenne générale</div>
            <div class="valeur"><?= $moyenneGenerale !== null ? e($moyenneGenerale) . ' / 20' : 'En attente de notes' ?></div>
        </div>
        <?php if ($moyenneGenerale !== null): ?>
        <div>
            <div class="label">Mention</div>
            <div class="valeur" style="font-size:1.1rem;"><?= e($mentionGenerale) ?></div>
        </div>
        <?php endif; ?>
    </div>

    <div style="margin-top:22px;border-top:1px dashed var(--bordure);padding-top:12px;font-size:.78rem;color:var(--texte-doux);text-align:center;">
        Bulletin généré automatiquement - <?= e(EVENT_FULL) ?>
    </div>
</div>
