<?php
/**
 * Diagrammes (SVG, sans bibliothèque externe) :
 *  1. effectif par commission (barres) ;
 *  2. évolution des inscriptions dans le temps (courbes cumulées : membres de commission et séminaristes).
 * Variable requise : $pdo.
 */
$__parCom = $pdo->query("SELECT commission, COUNT(*) n FROM membres_commission GROUP BY commission ORDER BY n DESC, commission")->fetchAll();
$__jM = $pdo->query("SELECT DATE(created_at) j, COUNT(*) n FROM membres_commission WHERE created_at IS NOT NULL GROUP BY DATE(created_at)")->fetchAll(PDO::FETCH_KEY_PAIR);
$__jS = $pdo->query("SELECT DATE(created_at) j, COUNT(*) n FROM seminaristes WHERE created_at IS NOT NULL GROUP BY DATE(created_at)")->fetchAll(PDO::FETCH_KEY_PAIR);
$__jours = array_unique(array_merge(array_keys($__jM), array_keys($__jS)));
sort($__jours);
if (count($__jours) === 1) { array_unshift($__jours, date('Y-m-d', strtotime($__jours[0] . ' -1 day'))); }
$__cumM = []; $__cumS = []; $__m = 0; $__s = 0;
foreach ($__jours as $__j) { $__m += (int)($__jM[$__j] ?? 0); $__s += (int)($__jS[$__j] ?? 0); $__cumM[] = $__m; $__cumS[] = $__s; }
?>
<div class="grid grid-2" style="margin-bottom:30px;align-items:start;">
    <div class="carte">
        <h3>📊 Effectif par commission</h3>
        <?php if (!$__parCom): ?><p style="color:var(--texte-doux);">Aucune donnée.</p><?php else:
            $__max = max(array_column($__parCom, 'n')); $__h = 30; $__W = 560; $__lab = 210; $__H = count($__parCom) * $__h + 10; ?>
            <svg viewBox="0 0 <?= $__W ?> <?= $__H ?>" style="width:100%;height:auto;" role="img" aria-label="Effectif par commission">
                <?php foreach ($__parCom as $__i => $__r): $__y = $__i * $__h + 5; $__bw = ($__W - $__lab - 50) * $__r['n'] / max(1, $__max); ?>
                    <text x="<?= $__lab - 8 ?>" y="<?= $__y + 18 ?>" text-anchor="end" font-size="12" fill="currentColor"><?= e(mb_strimwidth($__r['commission'], 0, 30, '…', 'UTF-8')) ?></text>
                    <rect x="<?= $__lab ?>" y="<?= $__y + 4 ?>" width="<?= max(2, $__bw) ?>" height="18" rx="4" fill="#0C5B3A"/>
                    <text x="<?= $__lab + $__bw + 6 ?>" y="<?= $__y + 18 ?>" font-size="12" font-weight="700" fill="currentColor"><?= (int)$__r['n'] ?></text>
                <?php endforeach; ?>
            </svg>
        <?php endif; ?>
    </div>
    <div class="carte">
        <h3>📈 Évolution des inscriptions</h3>
        <?php if (count($__jours) < 2): ?><p style="color:var(--texte-doux);">Pas encore assez de données.</p><?php else:
            $__W = 560; $__H = 260; $__gx = 44; $__gy = 16; $__gw = $__W - $__gx - 14; $__gh = $__H - $__gy - 42;
            $__top = max(1, max($__cumM), max($__cumS)); $__n = count($__jours);
            $__px = function ($i) use ($__gx, $__gw, $__n) { return $__gx + ($__n > 1 ? $__gw * $i / ($__n - 1) : 0); };
            $__py = function ($v) use ($__gy, $__gh, $__top) { return $__gy + $__gh - $__gh * $v / $__top; };
            $__pts = function ($serie) use ($__px, $__py) { $o = []; foreach ($serie as $i => $v) { $o[] = round($__px($i), 1) . ',' . round($__py($v), 1); } return implode(' ', $o); };
            $__pas = max(1, (int)ceil($__n / 7)); ?>
            <svg viewBox="0 0 <?= $__W ?> <?= $__H ?>" style="width:100%;height:auto;" role="img" aria-label="Évolution des inscriptions">
                <?php for ($__t = 0; $__t <= 4; $__t++): $__v = $__top * $__t / 4; $__yy = $__py($__v); ?>
                    <line x1="<?= $__gx ?>" x2="<?= $__W - 14 ?>" y1="<?= $__yy ?>" y2="<?= $__yy ?>" stroke="currentColor" stroke-opacity=".12"/>
                    <text x="<?= $__gx - 6 ?>" y="<?= $__yy + 4 ?>" text-anchor="end" font-size="11" fill="currentColor"><?= (int)round($__v) ?></text>
                <?php endfor; ?>
                <?php foreach ($__jours as $__i => $__j): if ($__i % $__pas !== 0 && $__i !== $__n - 1) continue; ?>
                    <text x="<?= $__px($__i) ?>" y="<?= $__H - 22 ?>" text-anchor="middle" font-size="11" fill="currentColor"><?= date('d/m', strtotime($__j)) ?></text>
                <?php endforeach; ?>
                <polyline points="<?= $__pts($__cumS) ?>" fill="none" stroke="#E07B00" stroke-width="3" stroke-linejoin="round"/>
                <polyline points="<?= $__pts($__cumM) ?>" fill="none" stroke="#0C5B3A" stroke-width="3" stroke-linejoin="round"/>
                <circle cx="<?= $__px($__n - 1) ?>" cy="<?= $__py(end($__cumS)) ?>" r="4" fill="#E07B00"/>
                <circle cx="<?= $__px($__n - 1) ?>" cy="<?= $__py(end($__cumM)) ?>" r="4" fill="#0C5B3A"/>
                <rect x="<?= $__gx ?>" y="<?= $__H - 14 ?>" width="10" height="10" fill="#0C5B3A"/><text x="<?= $__gx + 14 ?>" y="<?= $__H - 5 ?>" font-size="11" fill="currentColor">Membres de commission (<?= end($__cumM) ?>)</text>
                <rect x="<?= $__gx + 210 ?>" y="<?= $__H - 14 ?>" width="10" height="10" fill="#E07B00"/><text x="<?= $__gx + 224 ?>" y="<?= $__H - 5 ?>" font-size="11" fill="currentColor">Séminaristes (<?= end($__cumS) ?>)</text>
            </svg>
            <p style="color:var(--texte-doux);font-size:.85rem;margin:6px 0 0;">Total cumulé, jour après jour.</p>
        <?php endif; ?>
    </div>
</div>
