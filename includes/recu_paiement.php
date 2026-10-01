<?php
/**
 * Reçu de paiement avec QR code.
 * Variables attendues : $s (séminariste), $recu (paiement validé)
 */
$numRecu = 'R-' . str_pad((string)$recu['id'], 5, '0', STR_PAD_LEFT);
$dateRecu = $recu['date_validation'] ?: $recu['updated_at'];
?>
<div class="carte recu-zone" id="recu-zone" style="max-width:720px;margin:30px auto 0;">
    <div class="recu">
        <div class="recu-bandeau"><img src="<?= BASE_URL ?>/assets/img/bulletin_entete.png" alt="AEEMCI - JOSPIA"></div>
        <div class="recu-entete">
            <div>
                <div class="recu-titre">REÇU DE PAIEMENT</div>
                <div class="recu-sous"><?= e(EVENT_FULL) ?></div>
            </div>
            <div class="recu-statut">✔ PAYÉ</div>
        </div>
        <p class="recu-texte">La commission Finance atteste avoir reçu de <strong><?= e($s['nom_prenoms']) ?></strong>, matricule <strong><?= e($s['matricule']) ?></strong>, la somme de <strong><?= number_format((int)$recu['montant'], 0, ',', ' ') ?> FCFA</strong> au titre des frais d'inscription à la <?= e(EVENT_FULL) ?>. Ce paiement a été vérifié et validé le <?= e(date('d/m/Y à H:i', strtotime($dateRecu))) ?>. Le présent reçu est délivré pour servir et valoir ce que de droit.</p>
        <div class="recu-corps">
            <dl class="recu-infos">
                <dt>N° de reçu</dt><dd class="mono"><?= e($numRecu) ?></dd>
                <dt>Date de validation</dt><dd><?= e(date('d/m/Y à H:i', strtotime($dateRecu))) ?></dd>
                <dt>Nom et prénoms</dt><dd><strong><?= e($s['nom_prenoms']) ?></strong></dd>
                <dt>Matricule</dt><dd class="mono"><?= e($s['matricule']) ?></dd>
                <dt>Sous-comité / Section</dt><dd><?= e($s['anyama']) ?> - <?= e($s['section']) ?></dd>
                <dt>Dortoir</dt><dd><?= e($s['dortoir']) ?></dd>
                <dt>Contact</dt><dd><?= e($s['contact']) ?></dd>
                <dt>Payé depuis (Wave)</dt><dd class="mono"><?= e($recu['numero_wave'] ?: '-') ?></dd>
                <?php if ($recu['reference_transaction'] !== ''): ?><dt>ID transaction</dt><dd class="mono"><?= e($recu['reference_transaction']) ?></dd><?php endif; ?>
                <dt>Montant payé</dt><dd><strong><?= number_format((int)$recu['montant'], 0, ',', ' ') ?> FCFA</strong></dd>
                <dt>Validé par</dt><dd><?= e($recu['valideur'] ?: 'Commission Finance') ?></dd>
            </dl>
            <div class="recu-qr">
                <div id="qr-recu" data-code="<?= e($recu['code_recu']) ?>"></div>
                <small>Présentez ce QR code à la commission Finance pour vérification.</small>
            </div>
        </div>
    </div>
    <div class="no-print" style="text-align:center;margin-top:14px;">
        <a href="<?= BASE_URL ?>/espace/download_recu?id=<?= (int)$recu['id'] ?>" class="btn btn-or">📄 Télécharger le reçu en PDF</a>
        <button type="button" class="btn btn-outline" onclick="document.body.classList.add('imprimer-recu');window.print();setTimeout(function(){document.body.classList.remove('imprimer-recu');},500);">🖨️ Imprimer / enregistrer le reçu</button>
    </div>
</div>
<script src="<?= BASE_URL ?>/assets/js/vendor/qrcode.js"></script>
<script>
(function () {
    var el = document.getElementById('qr-recu');
    if (!el || typeof qrcode === 'undefined') return;
    var qr = qrcode(0, 'M');
    qr.addData('JOSPIA:' + el.getAttribute('data-code'));
    qr.make();
    el.innerHTML = qr.createSvgTag(5, 2);
})();
</script>
