// JOSPIA 2026 - Scripts front-end

document.addEventListener('DOMContentLoaded', function () {

    var sections = {
        'Anyama 1': ['LYMA', 'SAINT MICHEL', 'ATLAS', 'LYMAO', 'YVAC', 'GAOUSSOU', 'LA PERRUCHE', 'Autre'],
        'Anyama 2': ['GSAMAT', 'BUTHMAAN', 'SOUNTIATA KEÏTA', 'Autre']
    };
    var selAnyama = document.getElementById('anyama');
    var selSection = document.getElementById('section');
    var sectionAutreWrap = document.getElementById('section_autre_wrap');
    if (selAnyama && selSection) {
        function majSections() {
            var val = selAnyama.value;
            selSection.innerHTML = '<option value="">- Choisir une section -</option>';
            (sections[val] || []).forEach(function (s) {
                var opt = document.createElement('option');
                opt.value = s;
                opt.textContent = s;
                selSection.appendChild(opt);
            });
            toggleSectionAutre();
        }
        function toggleSectionAutre() {
            if (sectionAutreWrap) sectionAutreWrap.style.display = (selSection.value === 'Autre') ? 'block' : 'none';
        }
        selAnyama.addEventListener('change', majSections);
        selSection.addEventListener('change', toggleSectionAutre);
        if (selAnyama.value) majSections();
    }

    var maladieSelect = document.getElementById('maladie');
    var maladieAutreWrap = document.getElementById('maladie_autre_wrap');
    if (maladieSelect && maladieAutreWrap) {
        function toggleMaladieAutre() {
            maladieAutreWrap.style.display = (maladieSelect.value === 'Autre') ? 'block' : 'none';
        }
        maladieSelect.addEventListener('change', toggleMaladieAutre);
        toggleMaladieAutre();
    }

    var timerEl = document.getElementById('quiz-timer');
    if (timerEl) {
        var dureeRestante = parseInt(timerEl.dataset.seconds, 10);
        var formulaireQuiz = document.getElementById('quiz-form');
        var interval = setInterval(function () {
            dureeRestante--;
            var m = Math.floor(dureeRestante / 60);
            var s = dureeRestante % 60;
            timerEl.textContent = '⏱ Temps restant : ' + m + ':' + (s < 10 ? '0' : '') + s;
            if (dureeRestante <= 0) {
                clearInterval(interval);
                if (formulaireQuiz) formulaireQuiz.submit();
            }
        }, 1000);
    }

    var inputPhoto = document.querySelector('input[type=file][name=photo]');
    var apercu = document.getElementById('apercu-photo');
    if (inputPhoto && apercu) {
        inputPhoto.addEventListener('change', function () {
            var f = this.files[0];
            if (f) {
                apercu.src = URL.createObjectURL(f);
                apercu.style.display = 'block';
            }
        });
    }

    // Onglets simples (listes admin, etc.)
    document.querySelectorAll('[data-onglets]').forEach(function (groupe) {
        var boutons = groupe.querySelectorAll('[data-onglet]');
        var panneaux = document.querySelectorAll('[data-panneau]');
        boutons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                boutons.forEach(function (b) { b.classList.remove('btn-primaire', 'actif'); b.classList.add('btn-outline'); });
                btn.classList.remove('btn-outline'); btn.classList.add('btn-primaire', 'actif');
                var cible = btn.getAttribute('data-onglet');
                panneaux.forEach(function (p) {
                    p.style.display = (p.getAttribute('data-panneau') === cible) ? '' : 'none';
                });
            });
        });
    });
});

// ============================================================
// Champs téléphone : chiffres uniquement (frappe, collage, saisie mobile)
// ============================================================
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('input[type=tel]').forEach(function (champ) {
        champ.setAttribute('inputmode', 'numeric');
        champ.addEventListener('keypress', function (e) {
            if (e.key && e.key.length === 1 && !/[0-9]/.test(e.key)) e.preventDefault();
        });
        champ.addEventListener('input', function () {
            var propre = this.value.replace(/\D+/g, '');
            if (this.value !== propre) this.value = propre;
        });
        champ.value = champ.value.replace(/\D+/g, '');
    });
});

// ============================================================
// Photo : choisir un fichier OU prendre une photo (webcam / caméra)
// ============================================================
document.addEventListener('DOMContentLoaded', function () {
    var input = document.querySelector('input[type=file][name=photo]');
    if (!input) return;

    var apercu = document.getElementById('apercu-photo');
    if (!apercu) {
        apercu = document.createElement('img');
        apercu.id = 'apercu-photo';
        apercu.style.cssText = 'display:none;margin-top:10px;width:100px;height:100px;object-fit:cover;border-radius:8px;';
        input.parentNode.appendChild(apercu);
        input.addEventListener('change', function () {
            if (this.files[0]) { apercu.src = URL.createObjectURL(this.files[0]); apercu.style.display = 'block'; }
        });
    }

    var barre = document.createElement('div');
    barre.className = 'photo-actions';
    barre.innerHTML = '<span class="photo-ou">ou</span>';
    var btnCam = document.createElement('button');
    btnCam.type = 'button';
    btnCam.className = 'btn btn-outline btn-sm';
    btnCam.textContent = '📷 Prendre une photo';
    barre.appendChild(btnCam);
    input.parentNode.insertBefore(barre, apercu);

    function donnerFichier(blob) {
        var fichier = new File([blob], 'photo_camera.jpg', { type: 'image/jpeg' });
        var dt = new DataTransfer();
        dt.items.add(fichier);
        input.files = dt.files;
        input.dispatchEvent(new Event('change', { bubbles: true }));
    }

    // Repli (navigateur sans accès caméra) : ouvre l'appareil photo du téléphone
    function repliCapture() {
        input.setAttribute('capture', 'user');
        input.click();
        setTimeout(function () { input.removeAttribute('capture'); }, 1000);
    }

    btnCam.addEventListener('click', function () {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) { repliCapture(); return; }

        var flux = null, facing = 'user';
        var fond = document.createElement('div');
        fond.className = 'cam-fond';
        fond.innerHTML =
            '<div class="cam-boite">' +
            '<video autoplay playsinline muted></video>' +
            '<div class="cam-btns">' +
            '<button type="button" class="btn btn-primaire" data-a="prendre">📸 Capturer</button>' +
            '<button type="button" class="btn btn-outline" data-a="tourner">🔄 Changer de caméra</button>' +
            '<button type="button" class="btn btn-outline" data-a="fermer">Annuler</button>' +
            '</div><p class="cam-msg"></p></div>';
        document.body.appendChild(fond);
        var video = fond.querySelector('video');
        var msg = fond.querySelector('.cam-msg');

        function arreter() { if (flux) flux.getTracks().forEach(function (t) { t.stop(); }); flux = null; }
        function fermer() { arreter(); fond.remove(); }
        function demarrer() {
            arreter();
            navigator.mediaDevices.getUserMedia({ video: { facingMode: facing, width: { ideal: 1280 } }, audio: false })
                .then(function (s) { flux = s; video.srcObject = s; msg.textContent = ''; })
                .catch(function () {
                    fermer();
                    alert("Impossible d'accéder à la caméra (autorisation refusée ou aucune caméra). Utilisez « Choisir un fichier ».");
                });
        }

        fond.addEventListener('click', function (e) {
            var a = e.target.getAttribute && e.target.getAttribute('data-a');
            if (e.target === fond || a === 'fermer') { fermer(); }
            else if (a === 'tourner') { facing = (facing === 'user') ? 'environment' : 'user'; demarrer(); }
            else if (a === 'prendre') {
                if (!video.videoWidth) { msg.textContent = 'Caméra pas encore prête…'; return; }
                var c = document.createElement('canvas');
                var max = 1024, r = Math.min(1, max / Math.max(video.videoWidth, video.videoHeight));
                c.width = Math.round(video.videoWidth * r);
                c.height = Math.round(video.videoHeight * r);
                c.getContext('2d').drawImage(video, 0, 0, c.width, c.height);
                c.toBlob(function (blob) { if (blob) donnerFichier(blob); fermer(); }, 'image/jpeg', 0.88);
            }
        });
        demarrer();
    });
});

/* ---------- Ajustement automatique du texte des certificats / badges JOSPIA (aucun débordement) ---------- */
(function () {
    function largeurMax(zone, sel) {
        var max = 0;
        zone.querySelectorAll(sel).forEach(function (n) { max = Math.max(max, n.getBoundingClientRect().width); });
        return max;
    }
    function ajuster() {
        document.querySelectorAll('.jos-doc [data-fit]').forEach(function (zone) {
            var cible = zone.querySelectorAll('.valeur, span').length ? '.valeur, span' : null;
            if (!cible) { return; }
            var ratio = parseFloat(zone.getAttribute('data-fit')) || 1;
            zone.classList.remove('large');
            zone.style.setProperty('--fit', 1);
            var w = largeurMax(zone, cible);
            if (!w) { return; }
            var dispo = zone.clientWidth * ratio;
            if (zone.classList.contains('jos-badge__com')) {
                // place restante entre les deux filets décoratifs ; si trop serré → mode large (sans filets)
                var f = (zone.clientWidth * 0.62) / w;
                if (f < 0.6) {
                    zone.classList.add('large');
                    zone.style.setProperty('--fit', 1);
                    w = largeurMax(zone, cible);
                    f = (zone.clientWidth * ratio) / w;
                }
                zone.style.setProperty('--fit', Math.min(1, f));
                return;
            }
            zone.style.setProperty('--fit', Math.min(1, dispo / w));
        });
    }
    function lancer() { ajuster(); if (document.fonts && document.fonts.ready) { document.fonts.ready.then(ajuster); } }
    if (document.readyState !== 'loading') { lancer(); } else { document.addEventListener('DOMContentLoaded', lancer); }
    window.addEventListener('load', ajuster);
    window.addEventListener('resize', ajuster);
    window.addEventListener('beforeprint', ajuster);
    window.addEventListener('afterprint', ajuster);
})();

/* ---------- Détourage automatique de la photo (suppression de l'arrière-plan, dans le navigateur) ---------- */
(function () {
    document.addEventListener('DOMContentLoaded', function () {
        var input = document.querySelector('input[type=file][name=photo][data-detourage]');
        if (!input) { return; }
        var base = (input.getAttribute('data-base') || '') + '/assets/js/vendor/selfie/';
        var occupe = false, segmenteur = null;
        var etat = document.createElement('div');
        etat.className = 'help-text';
        etat.style.cssText = 'margin-top:6px;font-weight:700;';
        input.parentNode.appendChild(etat);

        function chargerLib() {
            return new Promise(function (ok, ko) {
                if (window.SelfieSegmentation) { return ok(); }
                var s = document.createElement('script');
                s.src = base + 'selfie_segmentation.js';
                s.onload = ok; s.onerror = ko;
                document.head.appendChild(s);
            });
        }
        function lireImage(fichier) {
            return new Promise(function (ok, ko) {
                var url = URL.createObjectURL(fichier), img = new Image();
                img.onload = function () { URL.revokeObjectURL(url); ok(img); };
                img.onerror = ko; img.src = url;      // l'orientation EXIF est appliquée par le navigateur
            });
        }
        function segmenter(canvas) {
            return new Promise(function (ok, ko) {
                if (!segmenteur) {
                    segmenteur = new window.SelfieSegmentation({ locateFile: function (f) { return base + f; } });
                    segmenteur.setOptions({ modelSelection: 0 });
                }
                segmenteur.onResults(function (r) { ok(r.segmentationMask); });
                segmenteur.send({ image: canvas }).catch(ko);
            });
        }
        async function detourer(fichier) {
            var img = await lireImage(fichier);
            var max = 1000, k = Math.min(1, max / Math.max(img.naturalWidth, img.naturalHeight));
            var w = Math.round(img.naturalWidth * k), h = Math.round(img.naturalHeight * k);
            var src = document.createElement('canvas'); src.width = w; src.height = h;
            src.getContext('2d').drawImage(img, 0, 0, w, h);
            await chargerLib();
            var masque = await segmenter(src);
            // Masque : lissé puis converti en transparence douce
            var mc = document.createElement('canvas'); mc.width = w; mc.height = h;
            var mx = mc.getContext('2d');
            mx.filter = 'blur(1.5px)';
            mx.drawImage(masque, 0, 0, w, h);
            var md = mx.getImageData(0, 0, w, h).data;
            var sx = src.getContext('2d'), px = sx.getImageData(0, 0, w, h), d = px.data, tot = 0;
            for (var i = 0; i < d.length; i += 4) {
                var p = md[i] / 255;                                   // probabilité « personne » (canal rouge)
                var a = Math.min(1, Math.max(0, (p - 0.55) / 0.28));    // seuil doux
                a = a * a * (3 - 2 * a);
                d[i + 3] = Math.round(a * 255); tot += a;
            }
            // Si presque rien (ou presque tout) n'est détecté, on garde la photo d'origine
            var part = tot / (w * h);
            if (part < 0.08 || part > 0.97) { return null; }
            sx.putImageData(px, 0, 0);
            return new Promise(function (ok) { src.toBlob(function (b) { ok(b); }, 'image/png'); });
        }

        input.addEventListener('change', async function () {
            if (occupe || !input.files || !input.files[0]) { return; }
            var f = input.files[0];
            if (f.type === 'image/png' && f.name.indexOf('detoure') === 0) { return; }
            occupe = true;
            etat.style.color = '#0C5B3A'; etat.textContent = '⏳ Suppression automatique de l\'arrière-plan…';
            try {
                var blob = await detourer(f);
                if (blob) {
                    var nouveau = new File([blob], 'detoure_' + Date.now() + '.png', { type: 'image/png' });
                    var dt = new DataTransfer(); dt.items.add(nouveau); input.files = dt.files;
                    var ap = document.getElementById('apercu-photo');
                    if (ap) { ap.src = URL.createObjectURL(nouveau); ap.style.display = 'block'; ap.style.background = 'repeating-conic-gradient(#e8e8e8 0% 25%, #fff 0% 50%) 50% / 14px 14px'; }
                    etat.textContent = '✅ Arrière-plan supprimé automatiquement.';
                } else {
                    etat.style.color = '#8a5a00'; etat.textContent = 'Photo gardée telle quelle (personne non détectée). Utilisez de préférence une photo de face, bien éclairée.';
                }
            } catch (e) {
                etat.style.color = '#8a5a00'; etat.textContent = 'Photo gardée telle quelle.';
            }
            occupe = false;
        });
    });
})();
