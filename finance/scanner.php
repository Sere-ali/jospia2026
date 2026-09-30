<?php
require_once __DIR__ . '/../includes/init.php';
exigerRole(['finance', 'admin', 'superadmin']);
$titrePage = "Scanner un reçu";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . (estAdmin() ? '/../includes/admin_nav.php' : '/../includes/finance_nav.php');
?>
<section class="section">
    <div class="container">
        <div class="section-titre">
            <h2>Vérification des reçus (QR code)</h2>
            <p>Scannez le QR code du reçu : <strong style="color:#0a8f3c;">✔ vert</strong> si le séminariste existe avec un paiement validé, <strong style="color:#c81e1e;">✖ rouge</strong> sinon.</p>
        </div>

        <div class="scan-zone">
            <div class="scan-video-wrap">
                <video id="scan-video" playsinline muted></video>
                <div class="scan-cadre"></div>
            </div>
            <p id="scan-etat" style="color:var(--texte-doux);margin:10px 0;">Démarrage de la caméra…</p>
            <div style="display:flex;gap:8px;justify-content:center;flex-wrap:wrap;">
                <button type="button" class="btn btn-outline btn-sm" id="scan-tourner">🔄 Changer de caméra</button>
            </div>

            <form id="scan-manuel" class="carte" style="margin-top:18px;text-align:left;">
                <label style="font-weight:bold;">Ou saisir le code du reçu</label>
                <div style="display:flex;gap:8px;">
                    <input type="text" id="scan-code" placeholder="Code du reçu" style="flex:1;" autocomplete="off">
                    <button class="btn btn-primaire">Vérifier</button>
                </div>
            </form>
        </div>
    </div>
</section>

<div class="scan-resultat" id="scan-resultat">
    <div class="scan-carte">
        <div class="scan-icone" id="scan-icone"></div>
        <div class="scan-verdict" id="scan-verdict"></div>
        <div id="scan-details"></div>
        <button type="button" class="btn btn-primaire btn-block" id="scan-suivant" style="margin-top:18px;">Scanner le suivant</button>
    </div>
</div>

<script src="<?= BASE_URL ?>/assets/js/vendor/jsQR.js"></script>
<script>
(function () {
    var video = document.getElementById('scan-video');
    var etat = document.getElementById('scan-etat');
    var overlay = document.getElementById('scan-resultat');
    var canvas = document.createElement('canvas');
    var ctx = canvas.getContext('2d', { willReadFrequently: true });
    var flux = null, facing = 'environment', actif = true, enVerif = false;
    var URL_API = '<?= BASE_URL ?>/finance/verifier';

    function esc(t) { var d = document.createElement('div'); d.textContent = t == null ? '' : t; return d.innerHTML; }

    function afficher(res) {
        actif = false;
        overlay.className = 'scan-resultat ' + (res.valide ? 'ok' : 'ko');
        overlay.style.display = 'flex';
        document.getElementById('scan-icone').textContent = res.valide ? '✔' : '✖';
        document.getElementById('scan-verdict').textContent = res.valide ? 'VALIDÉ' : 'REFUSÉ';
        var h = '';
        if (res.valide) {
            if (res.photo) h += '<img class="scan-photo" src="' + esc(res.photo) + '" alt="">';
            h += '<div style="font-size:1.25rem;font-weight:800;">' + esc(res.nom) + '</div>' +
                 '<div class="mono">' + esc(res.matricule) + '</div>' +
                 '<div>' + esc(res.section) + '</div>' +
                 '<div>Dortoir : ' + esc(res.dortoir) + '</div>' +
                 '<div style="margin-top:6px;">Payé : <strong>' + esc(res.montant) + ' FCFA</strong>' + (res.date ? ' le ' + esc(res.date) : '') + '</div>';
        } else {
            h = '<div style="font-size:1.1rem;">' + esc(res.message) + '</div>';
        }
        document.getElementById('scan-details').innerHTML = h;
        if (navigator.vibrate) navigator.vibrate(res.valide ? 120 : [200, 100, 200]);
    }

    function verifier(code) {
        if (enVerif) return;
        enVerif = true;
        var fd = new FormData(); fd.append('code', code);
        fetch(URL_API, { method: 'POST', body: fd, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(afficher)
            .catch(function () { afficher({ valide: false, message: 'Erreur de connexion, réessayez.' }); })
            .then(function () { enVerif = false; });
    }

    document.getElementById('scan-suivant').addEventListener('click', function () {
        overlay.style.display = 'none'; actif = true; boucle();
    });
    document.getElementById('scan-manuel').addEventListener('submit', function (e) {
        e.preventDefault();
        var v = document.getElementById('scan-code').value.trim();
        if (v) verifier(v);
    });

    function boucle() {
        if (!actif) return;
        if (video.readyState === video.HAVE_ENOUGH_DATA && video.videoWidth) {
            canvas.width = video.videoWidth; canvas.height = video.videoHeight;
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
            var img = ctx.getImageData(0, 0, canvas.width, canvas.height);
            var q = jsQR(img.data, img.width, img.height, { inversionAttempts: 'dontInvert' });
            if (q && q.data) { verifier(q.data); }
        }
        requestAnimationFrame(boucle);
    }

    function demarrer() {
        if (flux) flux.getTracks().forEach(function (t) { t.stop(); });
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            etat.textContent = "Caméra non disponible sur ce navigateur : utilisez la saisie manuelle du code.";
            return;
        }
        navigator.mediaDevices.getUserMedia({ video: { facingMode: facing, width: { ideal: 1280 } }, audio: false })
            .then(function (s) {
                flux = s; video.srcObject = s; video.play();
                etat.textContent = 'Placez le QR code du reçu devant la caméra.';
                boucle();
            })
            .catch(function () {
                etat.textContent = "Accès à la caméra refusé : autorisez-la dans le navigateur, ou utilisez la saisie manuelle.";
            });
    }
    document.getElementById('scan-tourner').addEventListener('click', function () {
        facing = (facing === 'environment') ? 'user' : 'environment'; demarrer();
    });
    demarrer();
})();
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
