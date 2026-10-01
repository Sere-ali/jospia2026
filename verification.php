<?php
/** Page publique ouverte par le QR code de la fiche d'inscription : VERT = paiement validé, ROUGE = refusé. */
require_once __DIR__ . '/includes/init.php';
codeFichePreparer($pdo);
$code = preg_replace('/[^a-f0-9]/', '', strtolower((string)($_GET['c'] ?? '')));
$s = null;
if (strlen($code) >= 16) {
    $st = $pdo->prepare("SELECT * FROM seminaristes WHERE code_fiche = ? LIMIT 1");
    $st->execute([$code]);
    $s = $st->fetch() ?: null;
}
$statut = $s ? statutPaiementSeminariste($pdo, $s['id']) : 'aucun';
$ok = ($s && $statut === 'validé');
$titrePage = "Vérification";
$motif = !$s ? "Personne introuvable dans la base" : ($statut === 'rejeté' ? "Paiement rejeté" : "Paiement non effectué (en attente)");
?><!DOCTYPE html>
<html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex">
<title>Vérification - JOSPIA 2026</title>
<style>
body{margin:0;font-family:system-ui,-apple-system,Segoe UI,Roboto,sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:18px;box-sizing:border-box;background:<?= $ok ? '#0a8f3c' : '#c81e1e' ?>;color:#fff;text-align:center}
.c{max-width:420px;width:100%}
.i{font-size:84px;line-height:1}.v{font-size:2.2rem;font-weight:800;letter-spacing:.04em;margin:6px 0 4px}
.m{font-size:1.05rem;opacity:.95;margin-bottom:18px}
.f{background:rgba(255,255,255,.14);border-radius:16px;padding:16px;text-align:left;font-size:1rem;line-height:1.7}
.f img{width:96px;height:96px;object-fit:cover;border-radius:50%;border:3px solid #fff;display:block;margin:0 auto 10px}
.f b{opacity:.85;font-weight:600}
</style></head><body><div class="c">
    <div class="i"><?= $ok ? '✔' : '✖' ?></div>
    <div class="v"><?= $ok ? 'VALIDÉ' : 'REFUSÉ' ?></div>
    <div class="m"><?= $ok ? 'Paiement effectué' : e($motif) ?></div>
    <?php if ($s): ?>
    <div class="f">
        <?php if (!empty($s['photo'])): ?><img src="<?= BASE_URL ?>/uploads/photos/<?= e($s['photo']) ?>" alt=""><?php endif; ?>
        <div><b>Nom :</b> <?= e($s['nom_prenoms']) ?></div>
        <div><b>Matricule :</b> <?= e($s['matricule']) ?></div>
        <div><b>Sous-comité :</b> <?= e($s['anyama']) ?> - <?= e($s['section']) ?></div>
        <div><b>Dortoir :</b> <?= e($s['dortoir']) ?></div>
        <div><b>Niveau :</b> <?= e($s['niveau_affecte'] ?: 'Non affecté') ?></div>
        <div><b>Contact :</b> <?= e($s['contact']) ?></div>
    </div>
    <?php endif; ?>
    <p style="opacity:.85;margin-top:16px;font-size:.9rem;"><?= e(EVENT_FULL) ?></p>
</div></body></html>
