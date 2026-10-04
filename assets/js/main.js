// JOSPIA 2026 - Scripts front-end

document.addEventListener('DOMContentLoaded', function () {

    var sections = {
        'Anyama 1': ['LYMA', 'SAINT MICHEL', 'ATLAS', 'LYMAO', 'YVAC', 'GAOUSSOU', 'LA PERRUCHE', 'Autre'],
        'Anyama 2': ['GSAMAT', 'BUTHMAAN', 'SOUNDJATA KEÏTA', 'Autre']
    };
    var EXT = 'Autre (extérieur)';
    var sectionGroupe = document.getElementById('section_groupe');
    var sectionAutreLabel = document.getElementById('section_autre_label');
    var LIB_AUTRE = sectionAutreLabel ? sectionAutreLabel.innerHTML : '';
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
            var champ = sectionAutreWrap ? sectionAutreWrap.querySelector('input') : null;
            if (selAnyama.value === EXT) {
                // personne venant de l'extérieur : pas de section à choisir, précision facultative
                if (sectionGroupe) sectionGroupe.style.display = 'none';
                selSection.required = false; selSection.value = '';
                if (sectionAutreWrap) sectionAutreWrap.style.display = 'block';
                if (sectionAutreLabel) sectionAutreLabel.textContent = "D'où venez-vous ? (ville, association, structure... - facultatif)";
                if (champ) champ.required = false;
                return;
            }
            if (sectionGroupe) sectionGroupe.style.display = '';
            selSection.required = true;
            if (sectionAutreLabel) sectionAutreLabel.innerHTML = LIB_AUTRE;
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
        var finTest = Date.now() + parseInt(timerEl.dataset.seconds, 10) * 1000;
        var formulaireQuiz = document.getElementById('quiz-form');
        var soumis = false;
        if (formulaireQuiz) {
            formulaireQuiz.addEventListener('submit', function (ev) {
                if (soumis) { return; }
                var total = formulaireQuiz.querySelectorAll('.question-card').length;
                var repondues = formulaireQuiz.querySelectorAll('input[type=radio]:checked').length;
                if (repondues < total && !confirm('Il vous reste ' + (total - repondues) + ' question(s) sans réponse. Soumettre quand même ?')) {
                    ev.preventDefault();
                    return;
                }
                soumis = true;
            });
        }
        var afficher = function () {
            var restant = Math.max(0, Math.round((finTest - Date.now()) / 1000));
            var m = Math.floor(restant / 60), s = restant % 60;
            timerEl.textContent = '⏱ Temps restant : ' + m + ':' + (s < 10 ? '0' : '') + s;
            if (restant <= 60) { timerEl.style.background = '#c0392b'; }
            if (restant <= 0 && !soumis) {
                soumis = true;
                clearInterval(interval);
                timerEl.textContent = '⏱ Temps écoulé : envoi de vos réponses...';
                if (formulaireQuiz) { formulaireQuiz.submit(); }
            }
        };
        var interval = setInterval(afficher, 500);
        afficher();
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

/* ---------- Photos légères : réduction automatique (max 1000 px, JPEG) avant l'envoi ---------- */
(function () {
    document.addEventListener('DOMContentLoaded', function () {
        var input = document.querySelector('input[type=file][name=photo]');
        if (!input) { return; }
        var occupe = false;
        input.addEventListener('change', function () {
            var f = input.files && input.files[0];
            if (occupe || !f || f.name.indexOf('photo_c_') === 0 || f.size < 300 * 1024 && f.type === 'image/jpeg') { return; }
            occupe = true;
            var url = URL.createObjectURL(f), img = new Image();
            img.onload = function () {
                var k = Math.min(1, 1000 / Math.max(img.naturalWidth, img.naturalHeight));
                var c = document.createElement('canvas'); c.width = Math.round(img.naturalWidth * k); c.height = Math.round(img.naturalHeight * k);
                var x = c.getContext('2d'); x.fillStyle = '#fff'; x.fillRect(0, 0, c.width, c.height); x.drawImage(img, 0, 0, c.width, c.height);
                c.toBlob(function (b) {
                    URL.revokeObjectURL(url);
                    if (b && b.size < f.size) {
                        var dt = new DataTransfer(); dt.items.add(new File([b], 'photo_c_' + Date.now() + '.jpg', { type: 'image/jpeg' })); input.files = dt.files;
                    }
                    occupe = false;
                }, 'image/jpeg', 0.85);
            };
            img.onerror = function () { occupe = false; };
            img.src = url;
        });
    });
})();

/* ---------- Œil pour afficher / masquer le mot de passe ---------- */
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('input[type=password]').forEach(function (champ) {
        if (champ.dataset.oeil) { return; }
        champ.dataset.oeil = '1';
        var enveloppe = document.createElement('div');
        enveloppe.className = 'mdp-wrap';
        champ.parentNode.insertBefore(enveloppe, champ);
        enveloppe.appendChild(champ);
        var bouton = document.createElement('button');
        bouton.type = 'button';
        bouton.className = 'mdp-oeil';
        bouton.setAttribute('aria-label', 'Afficher le mot de passe');
        bouton.innerHTML = '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>';
        bouton.addEventListener('click', function () {
            var visible = champ.type === 'text';
            champ.type = visible ? 'password' : 'text';
            bouton.classList.toggle('actif', !visible);
            bouton.setAttribute('aria-label', visible ? 'Afficher le mot de passe' : 'Masquer le mot de passe');
        });
        enveloppe.appendChild(bouton);
    });
});

/* ---------- Export Excel (.xlsx) des listes admin / finance ---------- */
(function () {
    var chemin = location.pathname;
    if (!/\/(admin|finance|securite)\//.test(chemin) || /\/(dashboard|matieres|questions|config_quiz|scanner|verifier)(\.php)?$/.test(chemin)) { return; }

    var crcTable = (function () {
        var t = [], c, n, k;
        for (n = 0; n < 256; n++) { c = n; for (k = 0; k < 8; k++) { c = c & 1 ? 0xEDB88320 ^ (c >>> 1) : c >>> 1; } t[n] = c >>> 0; }
        return t;
    })();
    function crc32(b) { var c = 0xFFFFFFFF; for (var i = 0; i < b.length; i++) { c = crcTable[(c ^ b[i]) & 0xFF] ^ (c >>> 8); } return (c ^ 0xFFFFFFFF) >>> 0; }
    var enc = new TextEncoder();
    function u16(n) { return [n & 255, (n >>> 8) & 255]; }
    function u32(n) { return [n & 255, (n >>> 8) & 255, (n >>> 16) & 255, (n >>> 24) & 255]; }

    function zip(fichiers) {
        var morceaux = [], centre = [], offset = 0;
        fichiers.forEach(function (f) {
            var nom = enc.encode(f.nom), data = enc.encode(f.contenu), crc = crc32(data);
            var loc = [].concat(u32(0x04034b50), u16(20), u16(0x0800), u16(0), u16(0), u16(0x21), u32(crc), u32(data.length), u32(data.length), u16(nom.length), u16(0));
            morceaux.push(new Uint8Array(loc), nom, data);
            var cen = [].concat(u32(0x02014b50), u16(20), u16(20), u16(0x0800), u16(0), u16(0), u16(0x21), u32(crc), u32(data.length), u32(data.length), u16(nom.length), u16(0), u16(0), u16(0), u16(0), u32(0), u32(offset));
            centre.push(new Uint8Array(cen), nom);
            offset += loc.length + nom.length + data.length;
        });
        var tailleCentre = 0;
        centre.forEach(function (c) { tailleCentre += c.length; });
        var fin = [].concat(u32(0x06054b50), u16(0), u16(0), u16(fichiers.length), u16(fichiers.length), u32(tailleCentre), u32(offset), u16(0));
        return new Blob(morceaux.concat(centre, [new Uint8Array(fin)]), { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
    }

    function xml(s) { return String(s).replace(/[\u0000-\u0008\u000B\u000C\u000E-\u001F]/g, '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;'); }
    function colonne(i) { var s = ''; i++; while (i > 0) { var m = (i - 1) % 26; s = String.fromCharCode(65 + m) + s; i = Math.floor((i - 1) / 26); } return s; }

    function feuilleXml(lignes) {
        var out = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews><cols><col min="1" max="30" width="24" customWidth="1"/></cols><sheetData>';
        lignes.forEach(function (l, r) {
            out += '<row r="' + (r + 1) + '">';
            l.forEach(function (v, c) {
                out += '<c r="' + colonne(c) + (r + 1) + '" t="inlineStr"' + (r === 0 ? ' s="1"' : '') + '><is><t xml:space="preserve">' + xml(v) + '</t></is></c>';
            });
            out += '</row>';
        });
        return out + '</sheetData></worksheet>';
    }

    function construire(feuilles) {
        var noms = [], f = [];
        feuilles.forEach(function (fe, i) {
            var nom = (fe.nom || 'Feuille ' + (i + 1)).replace(/[\[\]:*?\/\\]/g, ' ').trim().substring(0, 31) || 'Feuille ' + (i + 1);
            var base = nom, n = 2;
            while (noms.indexOf(nom.toLowerCase()) !== -1) { nom = base.substring(0, 28) + ' ' + n++; }
            noms.push(nom.toLowerCase());
            fe.nomFinal = nom;
        });
        var ct = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>';
        var wb = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets>';
        var rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';
        feuilles.forEach(function (fe, i) {
            var k = i + 1;
            ct += '<Override PartName="/xl/worksheets/sheet' + k + '.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
            wb += '<sheet name="' + xml(fe.nomFinal) + '" sheetId="' + k + '" r:id="rId' + k + '"/>';
            rels += '<Relationship Id="rId' + k + '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet' + k + '.xml"/>';
            f.push({ nom: 'xl/worksheets/sheet' + k + '.xml', contenu: feuilleXml(fe.lignes) });
        });
        ct += '</Types>'; wb += '</sheets></workbook>';
        rels += '<Relationship Id="rId' + (feuilles.length + 1) + '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>';
        var styles = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="11"/><name val="Calibri"/></font></fonts><fills count="2"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill></fills><borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders><cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs><cellXfs count="2"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1"/></cellXfs><cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles></styleSheet>';
        return zip([
            { nom: '[Content_Types].xml', contenu: ct },
            { nom: '_rels/.rels', contenu: '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>' },
            { nom: 'xl/workbook.xml', contenu: wb },
            { nom: 'xl/_rels/workbook.xml.rels', contenu: rels },
            { nom: 'xl/styles.xml', contenu: styles }
        ].concat(f));
    }

    function texte(el) { return (el.textContent || '').replace(/\s+/g, ' ').trim(); }
    var IGNORES = ['', 'photo', 'action', 'actions', 'action / info'];

    function lignesTable(table) {
        var ths = [].slice.call(table.querySelectorAll('thead th'));
        var garder = [];
        ths.forEach(function (th, i) { if (IGNORES.indexOf(texte(th).toLowerCase()) === -1) { garder.push(i); } });
        var lignes = [garder.map(function (i) { return texte(ths[i]); })];
        [].slice.call(table.querySelectorAll('tbody tr')).forEach(function (tr) {
            var tds = tr.children;
            if (tds.length === 1 && tds[0].colSpan > 1) { return; }
            lignes.push(garder.map(function (i) { return tds[i] ? texte(tds[i]) : ''; }));
        });
        return lignes;
    }

    function titreDe(table) {
        var carte = table.closest('.carte');
        var h = carte && carte.querySelector('h2, h3, h4');
        if (h) { var c = h.cloneNode(true); [].slice.call(c.querySelectorAll('.pill, .tag')).forEach(function (p) { p.remove(); }); return texte(c).replace(/^[^\wÀ-ÿ]+/, ''); }
        return '';
    }

    function telecharger(feuilles, nomFichier) {
        var blob = construire(feuilles);
        var a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = nomFichier + '.xlsx';
        document.body.appendChild(a); a.click();
        setTimeout(function () { URL.revokeObjectURL(a.href); a.remove(); }, 1500);
    }

    function bouton(libelle, action) {
        var b = document.createElement('button');
        b.type = 'button';
        b.className = 'btn btn-outline btn-sm no-print btn-export-excel';
        b.style.margin = '0 0 10px 0';
        b.textContent = '📥 ' + libelle;
        b.addEventListener('click', action);
        return b;
    }

    function initExcel() {
        [].slice.call(document.querySelectorAll('.btn-export-excel')).forEach(function (b) { b.remove(); });
        var page = (document.querySelector('h1, h2') || {}).textContent || document.title || 'export';
        var dateJour = new Date().toISOString().slice(0, 10);
        var nomPage = texte({ textContent: page }).replace(/[^\wÀ-ÿ]+/g, '_').replace(/^_|_$/g, '').substring(0, 40) || 'export';

        var panneaux = document.querySelectorAll('[data-panneau]');
        var dejaGeres = [];
        panneaux.forEach(function (p) {
            var tables = [].slice.call(p.querySelectorAll('table'));
            if (!tables.length) { return; }
            tables.forEach(function (t) { dejaGeres.push(t); });
            var nom = 'Liste_' + p.getAttribute('data-panneau');
            p.insertBefore(bouton('Exporter en Excel', function () {
                telecharger(tables.map(function (t) { return { nom: titreDe(t), lignes: lignesTable(t) }; }), nom + '_' + dateJour);
            }), p.firstChild);
        });

        [].slice.call(document.querySelectorAll('main table, section table, .container table')).forEach(function (t, i, tous) {
            if (dejaGeres.indexOf(t) !== -1 || t.dataset.noExport !== undefined) { return; }
            var cible = t.closest('.table-wrap') || t;
            cible.parentNode.insertBefore(bouton('Exporter en Excel', function () {
                telecharger([{ nom: titreDe(t) || nomPage, lignes: lignesTable(t) }], nomPage + (titreDe(t) ? '_' + titreDe(t).replace(/[^\wÀ-ÿ]+/g, '_') : '') + '_' + dateJour);
            }), cible);
        });
    }
    window.josExcelInit = initExcel;
    if (document.readyState !== 'loading') { initExcel(); } else { document.addEventListener('DOMContentLoaded', initExcel); }
})();

/* =====================================================================
   Thème : menu mobile, révélations au défilement, compteurs, compte à rebours
   ===================================================================== */
(function () {
    'use strict';
    var reduit = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function init() {
        // Menu mobile
        var bouton = document.querySelector('.nav-toggle');
        var menu = document.getElementById('menu-principal');
        if (bouton && menu) {
            bouton.addEventListener('click', function () {
                var ouvert = menu.classList.toggle('ouvert');
                bouton.setAttribute('aria-expanded', ouvert ? 'true' : 'false');
                bouton.setAttribute('aria-label', ouvert ? 'Fermer le menu' : 'Ouvrir le menu');
            });
            menu.addEventListener('click', function (e) { if (e.target.closest('a')) { menu.classList.remove('ouvert'); bouton.setAttribute('aria-expanded', 'false'); } });
        }

        // Ombre de la barre au défilement
        var nav = document.querySelector('.navbar');
        if (nav) {
            var suivi = function () { nav.classList.toggle('defile', window.scrollY > 8); };
            window.addEventListener('scroll', suivi, { passive: true });
            suivi();
        }

        // Tableaux : défilement horizontal sur petit écran
        document.querySelectorAll('table').forEach(function (t) {
            var p = t.parentElement;
            if (!p || p.classList.contains('table-scroll') || p.classList.contains('table-wrap')) { return; }
            var enveloppe = document.createElement('div');
            enveloppe.className = 'table-scroll';
            p.insertBefore(enveloppe, t);
            enveloppe.appendChild(t);
        });

        // Révélations au défilement (par lots, avec décalage)
        var cibles = document.querySelectorAll('.section-titre, .carte, fieldset, .stat-card, .fiche, .login-box, .badge-card, .jos-doc');
        cibles.forEach(function (el) { if (!el.closest('.hero') && !el.classList.contains('rv') && !el.closest('.jos-doc')) { el.classList.add('rv'); } });
        var rv = document.querySelectorAll('.rv');
        if (reduit || !('IntersectionObserver' in window)) {
            rv.forEach(function (el) { el.classList.add('vu'); });
        } else {
            var io = new IntersectionObserver(function (entrees) {
                var lot = 0;
                entrees.forEach(function (en) {
                    if (!en.isIntersecting) { return; }
                    var el = en.target;
                    if (!el.style.getPropertyValue('--d')) { el.style.setProperty('--d', Math.min(lot * 0.07, 0.35) + 's'); lot++; }
                    el.classList.add('vu');
                    io.unobserve(el);
                    setTimeout(function () { el.style.removeProperty('--d'); }, 1500);
                });
            }, { threshold: 0.08, rootMargin: '0px 0px -40px 0px' });
            rv.forEach(function (el) { io.observe(el); });
        }

        // Compteurs animés (statistiques)
        document.querySelectorAll('.stat-card .chiffre').forEach(function (el) {
            var texte = el.textContent.trim();
            var m = texte.match(/^(≤\s*)?([\d\s]+)(.*)$/);
            if (!m || reduit) { return; }
            var cible = parseInt(m[2].replace(/\s+/g, ''), 10);
            if (isNaN(cible) || cible === 0) { return; }
            var suffixe = m[3], prefixe = m[1] || '';
            var lance = false;
            var depart = function () {
                if (lance) { return; } lance = true;
                var t0 = performance.now(), duree = 1100;
                (function pas(t) {
                    var k = Math.min(1, (t - t0) / duree), v = Math.round(cible * (1 - Math.pow(1 - k, 3)));
                    el.textContent = prefixe + v.toLocaleString('fr-FR') + suffixe;
                    if (k < 1) { requestAnimationFrame(pas); } else { el.textContent = texte; }
                })(t0);
            };
            if ('IntersectionObserver' in window) {
                var o2 = new IntersectionObserver(function (es) { if (es[0].isIntersecting) { depart(); o2.disconnect(); } }, { threshold: 0.4 });
                o2.observe(el);
            } else { depart(); }
        });

        // Compte à rebours
        var cr = document.querySelector('.compte-rebours[data-cible]');
        if (cr) {
            var fin = new Date(cr.getAttribute('data-cible')).getTime();
            var cases = {};
            ['j', 'h', 'm', 's'].forEach(function (u) { cases[u] = cr.querySelector('[data-u=' + u + ']'); });
            var dernier = {};
            var maj = function () {
                var reste = Math.max(0, fin - Date.now());
                var v = { j: Math.floor(reste / 864e5), h: Math.floor(reste / 36e5) % 24, m: Math.floor(reste / 6e4) % 60, s: Math.floor(reste / 1e3) % 60 };
                Object.keys(v).forEach(function (u) {
                    if (dernier[u] === v[u] || !cases[u]) { return; }
                    dernier[u] = v[u];
                    cases[u].textContent = (u === 'j' ? String(v[u]) : String(v[u]).padStart(2, '0'));
                    if (!reduit) { cases[u].classList.remove('tic'); void cases[u].offsetWidth; cases[u].classList.add('tic'); }
                });
                if (reste === 0) { var l = cr.querySelector('.compte-legende'); if (l) { l.textContent = "C'est parti !"; } }
            };
            maj(); setInterval(maj, 1000);
        }
    }
    if (document.readyState !== 'loading') { init(); } else { document.addEventListener('DOMContentLoaded', init); }
})();

// ============================================================
// Formulaire d'inscription : progression + étapes validées
// ============================================================
document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('form-inscription');
    if (!form) return;
    var barre = document.querySelector('[data-progress-bar]');
    var txt = document.querySelector('[data-progress-txt]');

    function rempli(champ) {
        if (champ.type === 'radio') {
            return !!form.querySelector('input[type=radio][name="' + champ.name + '"]:checked');
        }
        if (champ.type === 'file') return champ.files && champ.files.length > 0;
        return champ.value.trim() !== '';
    }
    function maj() {
        var vus = {}, total = 0, ok = 0;
        form.querySelectorAll('[required]').forEach(function (c) {
            if (c.offsetParent === null && c.type !== 'radio' && c.type !== 'file') return;
            var cle = c.type === 'radio' ? 'r:' + c.name : c.name;
            if (vus[cle]) return;
            vus[cle] = true; total++;
            if (rempli(c)) ok++;
        });
        var pct = total ? Math.round(ok * 100 / total) : 0;
        if (barre) barre.style.width = pct + '%';
        if (txt) txt.textContent = pct + '%';
        form.querySelectorAll('fieldset').forEach(function (fs) {
            var req = fs.querySelectorAll('[required]');
            var tout = req.length > 0, vu = {};
            req.forEach(function (c) {
                if (c.type === 'radio') { if (vu[c.name]) return; vu[c.name] = 1; }
                if (!rempli(c)) tout = false;
            });
            fs.classList.toggle('ok', tout);
        });
    }
    form.addEventListener('input', maj);
    form.addEventListener('change', maj);
    setTimeout(maj, 50);
});

// ============================================================
// Mise à jour automatique (toutes les 2 s) et opérations sans rechargement
// Pages concernées : tout le back-office, la commission finance/sécurité, l espace personnel et les visiteurs (voir PAGES_LIVE).
// Seules les parties modifiées sont remplacées ; ce que vous tapez n'est jamais effacé.
// ============================================================
(function () {
    'use strict';
    var PAGES_LIVE = /\/(admin|finance|securite|espace)\/(?!edit_|modifier|critiquer|download|pdf|rapport_pdf|bulletin\b|bulletin_pdf|quiz|compte|migrer|correction|parametres|seuils_niveaux|programme|liens)[a-z_]+(\.php)?$|\/(visiteur|paiement|statut)(\.php)?$/;
    var chemin = location.pathname;
    if (!PAGES_LIVE.test(chemin)) return;
    var ACTIONS = ['supprimer', 'desactiver', 'publier', 'nouveau', 'activer', 'reinit'];
    var enCours = false, delai = 2000, minuteur = null;

    function zone() { return document.getElementById('contenu-page'); }

    function urlSurveillee() {
        var u = new URL(location.href);
        ACTIONS.forEach(function (a) { u.searchParams.delete(a); });
        return u.toString();
    }

    function toast(texte, erreur) {
        if (!texte) return;
        var t = document.createElement('div');
        t.className = 'jos-toast' + (erreur ? ' erreur' : '');
        t.textContent = texte;
        document.body.appendChild(t);
        setTimeout(function () { t.classList.add('visible'); }, 20);
        setTimeout(function () { t.classList.remove('visible'); setTimeout(function () { t.remove(); }, 400); }, erreur ? 6000 : 4000);
    }

    var IGNORES = '.btn-export-excel, .alert-succes, .alert-erreur, .jos-toast, script, style, noscript';
    function enfants(el) {
        return [].filter.call(el.children, function (c) { return !c.matches(IGNORES); });
    }
    // signature de contenu, insensible aux ajouts faits par le JavaScript (classes d'animation, styles...)
    function sig(el) {
        var c = el.cloneNode(true);
        [].forEach.call(c.querySelectorAll(IGNORES), function (n) { n.remove(); });
        var tous = [c].concat([].slice.call(c.querySelectorAll('*')));
        tous.forEach(function (n) {
            n.removeAttribute('style'); n.removeAttribute('data-label');
            if (n.classList) { n.classList.remove('vu', 'visible', 'rv', 'rwd'); if (!n.getAttribute('class')) n.removeAttribute('class'); }
        });
        return c.innerHTML !== undefined ? c.outerHTML : '';
    }
    function aSaisie(el) {
        if (el.contains(document.activeElement) && /^(INPUT|SELECT|TEXTAREA)$/.test((document.activeElement || {}).tagName || '')) return true;
        var champs = el.querySelectorAll('input, select, textarea');
        for (var i = 0; i < champs.length; i++) {
            var c = champs[i];
            if (c.type === 'checkbox' || c.type === 'radio') { if (c.checked !== c.defaultChecked) return true; }
            else if (c.tagName === 'SELECT') { if (c.selectedIndex !== -1 && !c.options[c.selectedIndex].defaultSelected) return true; }
            else if (c.type !== 'hidden' && c.type !== 'submit' && c.type !== 'button' && c.value !== c.defaultValue) return true;
        }
        return false;
    }
    function figee(el) { return !!el.querySelector('input[type=password], input[type=file], #apercu-photo'); }
    function sansRv(n) {
        if (n.classList) n.classList.remove('rv');
        [].forEach.call(n.querySelectorAll('.rv'), function (x) { x.classList.remove('rv'); });
        return n;
    }

    function remplacer(ancien, nouveau) {
        if (aSaisie(ancien) || figee(ancien)) return false;
        ancien.parentNode.replaceChild(sansRv(document.importNode(nouveau, true)), ancien);
        return true;
    }

    function comparer(ancien, nouveau) {
        var a = enfants(ancien), n = enfants(nouveau), change = false;
        if (a.length !== n.length || !a.length) {
            if (ancien.innerHTML !== nouveau.innerHTML && sig(ancien) !== sig(nouveau) && !aSaisie(ancien) && !figee(ancien)) {
                [].slice.call(ancien.children).filter(function (c) { return !c.matches(IGNORES); }).forEach(function (c) { c.remove(); });
                [].slice.call(ancien.childNodes).forEach(function (t) { if (t.nodeType === 3) t.remove(); });
                [].slice.call(nouveau.childNodes).forEach(function (c) {
                    if (c.nodeType === 1 && c.matches(IGNORES)) return;
                    ancien.appendChild(sansRv(document.importNode(c, true)));
                });
                return true;
            }
            return false;
        }
        for (var i = 0; i < a.length; i++) {
            if (sig(a[i]) === sig(n[i])) continue;
            if (a[i].tagName === n[i].tagName && enfants(a[i]).length && enfants(n[i]).length === enfants(a[i]).length) {
                if (comparer(a[i], n[i])) change = true;
            } else if (remplacer(a[i], n[i])) { change = true; }
        }
        return change;
    }

    function appliquer(doc) {
        var z = zone(), nz = doc.getElementById('contenu-page');
        if (!z || !nz) return false;
        if (sig(z) === sig(nz)) return false;
        var c = comparer(z, nz);
        if (c) {
            if (window.josExcelInit) window.josExcelInit();
            document.dispatchEvent(new CustomEvent('jos:maj'));
        }
        return c;
    }

    function planifier() { clearTimeout(minuteur); minuteur = setTimeout(actualiser, delai); }

    function actualiser() {
        if (enCours || document.hidden) { planifier(); return; }
        enCours = true;
        fetch(urlSurveillee(), { credentials: 'same-origin', headers: { 'X-Requested-With': 'fetch' }, cache: 'no-store' })
            .then(function (r) {
                if (r.redirected && new URL(r.url).pathname !== chemin) { location.href = r.url; return ''; }
                if (r.status === 403) { location.reload(); return ''; } // droits modifiés : la page s'adapte
                return r.ok ? r.text() : '';
            })
            .then(function (h) {
                if (!h) return;
                delai = h.length > 400000 ? 5000 : 2000; // grandes listes : un peu moins souvent
                var doc = new DOMParser().parseFromString(h, 'text/html');
                // menu du haut : suit le rôle de la personne (changé par un administrateur)
                var navA = document.getElementById('menu-principal'), navN = doc.getElementById('menu-principal');
                if (navA && navN) {
                    var liens = function (n) { return [].map.call(n.querySelectorAll('a'), function (a) { return a.getAttribute('href') + '|' + a.textContent.trim(); }).join(';'); };
                    if (liens(navA) !== liens(navN)) navA.innerHTML = navN.innerHTML;
                }
                appliquer(doc);
            })
            .catch(function () {})
            .then(function () { enCours = false; planifier(); });
    }

    function majHeures(f) {
        var d = new Date(), p = function (n) { return (n < 10 ? '0' : '') + n; };
        var v = d.getFullYear() + '-' + p(d.getMonth() + 1) + '-' + p(d.getDate()) + 'T' + p(d.getHours()) + ':' + p(d.getMinutes());
        f.querySelectorAll('input[type=datetime-local]').forEach(function (i) { i.value = v; });
    }

    function init() {
        planifier();
        document.addEventListener('visibilitychange', function () { if (!document.hidden) actualiser(); });

        document.addEventListener('submit', function (e) {
            var f = e.target;
            if (e.defaultPrevented || !f.matches || (f.method || '').toLowerCase() !== 'post') return;
            if (f.hasAttribute('data-no-ajax') || f.querySelector('input[type=file]') || (f.target && f.target !== '_self')) return;
            e.preventDefault();
            var data;
            try { data = new FormData(f, e.submitter || undefined); } catch (x) { data = new FormData(f); }
            var boutons = f.querySelectorAll('button[type=submit], button:not([type])');
            boutons.forEach(function (b) { b.disabled = true; });
            fetch(f.getAttribute('action') || location.href, { method: 'POST', body: data, credentials: 'same-origin', headers: { 'X-Requested-With': 'fetch' } })
                .then(function (r) {
                    var type = r.headers.get('Content-Type') || '';
                    if ((r.redirected && new URL(r.url).pathname !== chemin) || type.indexOf('text/html') === -1) { location.href = r.redirected ? r.url : location.href; return null; }
                    return r.text();
                })
                .then(function (h) {
                    if (h === null) return;
                    var doc = new DOMParser().parseFromString(h, 'text/html');
                    var ok = doc.querySelector('.alert-succes'), ko = doc.querySelectorAll('.alert-erreur');
                    if (ko.length) { toast([].map.call(ko, function (a) { return a.textContent.trim(); }).join(' '), true); }
                    else if (ok) { toast(ok.textContent.trim(), false); if (f.hasAttribute('data-ajax')) { f.reset(); majHeures(f); } }
                    appliquer(doc);
                })
                .catch(function () { toast('Connexion impossible : réessayez.', true); })
                .then(function () { boutons.forEach(function (b) { b.disabled = false; }); });
        });
    }

    if (document.readyState !== 'loading') { init(); } else { document.addEventListener('DOMContentLoaded', init); }
})();


/* Tableaux adaptés aux petits écrans : chaque ligne devient une fiche (libellés repris de l'en-tête) */
(function () {
    function rwd() {
        [].forEach.call(document.querySelectorAll('table'), function (t) {
            if (t.hasAttribute('data-no-rwd') || t.closest('.no-rwd')) return;
            var ths = t.querySelectorAll('thead th');
            if (!ths.length) return;
            var libs = [].map.call(ths, function (th) { return th.textContent.replace(/\s+/g, ' ').trim(); });
            [].forEach.call(t.querySelectorAll('tbody tr'), function (tr) {
                var col = 0;
                [].forEach.call(tr.children, function (td) {
                    if (td.tagName !== 'TD') return;
                    var span = td.colSpan || 1;
                    if (span > 1) { td.setAttribute('data-label', ''); td.classList.add('rwd-plein'); col += span; return; }
                    if (td.getAttribute('data-label') !== (libs[col] || '')) td.setAttribute('data-label', libs[col] || '');
                    col++;
                });
            });
            t.classList.add('rwd');
        });
    }
    window.josRwd = rwd;
    document.addEventListener('jos:maj', rwd);
    if (document.readyState !== 'loading') { rwd(); } else { document.addEventListener('DOMContentLoaded', rwd); }
})();


/* Alerte « heure de sortie épuisée » : s'affiche toute seule à l'heure de retour prévue */
(function () {
    function init() {
        var a = document.getElementById('alerte-sortie');
        if (!a) return;
        var retour = parseInt(a.getAttribute('data-retour'), 10) * 1000;
        var decalage = parseInt(a.getAttribute('data-now'), 10) * 1000 - Date.now(); // horloge du serveur
        function verifier() {
            if (Date.now() + decalage >= retour && a.hasAttribute('hidden')) {
                a.removeAttribute('hidden');
                if (navigator.vibrate) navigator.vibrate([300, 150, 300]);
            }
        }
        verifier();
        setInterval(verifier, 5000);
    }
    if (document.readyState !== 'loading') { init(); } else { document.addEventListener('DOMContentLoaded', init); }
})();


/* Envoi de fichiers fiable sur téléphone : le fichier choisi est copié en mémoire (et les photos réduites) dès la sélection,
   puis c'est cette copie qui est envoyée. Évite l'erreur Chrome « ERR_UPLOAD_FILE_CHANGED ». */
(function () {
    if (!window.fetch || !window.FormData || !window.Promise) return;
    var copies = new WeakMap();

    function copier(file) {
        var lireBrut = function () { return new Promise(function (ok, ko) {
            var r = new FileReader();
            r.onload = function () { ok({ blob: new Blob([r.result], { type: file.type }), nom: file.name }); };
            r.onerror = function () { ko(r.error); };
            r.readAsArrayBuffer(file);
        }); };
        if (!/^image\/(jpeg|png|webp)$/i.test(file.type) || !window.createImageBitmap) return lireBrut();
        return lireBrut().then(function (brut) {
            return createImageBitmap(brut.blob).then(function (img) {
                var max = 1600, k = Math.min(1, max / Math.max(img.width, img.height));
                if (k === 1 && brut.blob.size < 1500000) return brut;
                var c = document.createElement('canvas');
                c.width = Math.round(img.width * k); c.height = Math.round(img.height * k);
                c.getContext('2d').drawImage(img, 0, 0, c.width, c.height);
                return new Promise(function (ok) {
                    c.toBlob(function (b) { ok(b ? { blob: b, nom: file.name.replace(/\.[^.]+$/, '') + '.jpg' } : brut); }, 'image/jpeg', 0.88);
                });
            }).catch(function () { return brut; });
        });
    }

    document.addEventListener('change', function (e) {
        var i = e.target;
        if (!i || i.type !== 'file') return;
        if (!i.files || !i.files[0]) { copies.delete(i); return; }
        var p = copier(i.files[0]); p.catch(function () {});
        copies.set(i, p);
    });

    document.addEventListener('submit', function (e) {
        var f = e.target;
        if (!f || !f.matches || (f.method || '').toLowerCase() !== 'post' || f.hasAttribute('data-no-file-fix')) return;
        var champs = [].filter.call(f.querySelectorAll('input[type=file]'), function (i) { return i.files && i.files.length; });
        if (!champs.length) return;
        e.preventDefault();
        var sub = e.submitter, fd0;
        try { fd0 = new FormData(f, sub || undefined); } catch (x) { fd0 = new FormData(f); } // avant de désactiver les boutons (sinon le bouton cliqué est perdu)
        var boutons = f.querySelectorAll('button[type=submit], button:not([type])');
        var textes = [].map.call(boutons, function (b) { return b.innerHTML; });
        boutons.forEach(function (b) { b.disabled = true; });
        if (sub) sub.innerHTML = 'Envoi en cours…';
        var rendre = function () { boutons.forEach(function (b, k) { b.disabled = false; b.innerHTML = textes[k]; }); };
        Promise.all(champs.map(function (i) {
            var p = copies.get(i) || copier(i.files[0]);
            return p.then(function (c) { return { input: i, c: c }; });
        })).then(function (liste) {
            var fd = fd0;
            liste.forEach(function (x) { fd.delete(x.input.name); fd.append(x.input.name, x.c.blob, x.c.nom); });
            return fetch(f.getAttribute('action') || location.href, { method: 'POST', body: fd, credentials: 'same-origin' });
        }).then(function (r) {
            if (r.redirected) { location.href = r.url; return; }
            return r.text().then(function (h) { document.open(); document.write(h); document.close(); });
        }).catch(function () {
            rendre();
            alert("Envoi impossible. Choisissez à nouveau le fichier (depuis « Fichiers » ou « Galerie ») puis réessayez.");
            champs.forEach(function (i) { i.value = ''; copies.delete(i); });
        });
    }, true);
})();


/* Champs téléphone : « +225 » fixe devant le champ, chiffres uniquement, 10 chiffres maximum (collage « +225… » accepté) */
(function () {
    function nettoyer(v) {
        var n = String(v).replace(/\D+/g, '');
        if (n.indexOf('00225') === 0) n = n.slice(5);
        else if (n.indexOf('225') === 0 && n.length >= 11) n = n.slice(3);
        return n.slice(0, 10);
    }
    function preparer(i) {
        if (i.getAttribute('data-tel') === '1' || i.type !== 'tel') return;
        i.setAttribute('data-tel', '1');
        i.setAttribute('maxlength', '10'); i.setAttribute('inputmode', 'numeric'); i.setAttribute('pattern', '[0-9]{10}');
        i.setAttribute('title', '10 chiffres, sans le +225');
        if (!i.getAttribute('placeholder')) i.setAttribute('placeholder', 'Ex : 0700000000');
        var w = document.createElement('span'); w.className = 'tel-wrap';
        var p = document.createElement('span'); p.className = 'tel-pref'; p.textContent = '+225'; p.setAttribute('aria-hidden', 'true');
        i.parentNode.insertBefore(w, i); w.appendChild(p); w.appendChild(i);
        i.value = nettoyer(i.value);
        i.addEventListener('input', function () { var n = nettoyer(i.value); if (n !== i.value) i.value = n; });
        i.addEventListener('paste', function (e) {
            var t = (e.clipboardData || window.clipboardData || {}).getData ? (e.clipboardData || window.clipboardData).getData('text') : null;
            if (t === null) return;
            e.preventDefault(); i.value = nettoyer(t); i.dispatchEvent(new Event('input', { bubbles: true }));
        });
    }
    function tous() { [].forEach.call(document.querySelectorAll('input[type=tel]'), preparer); }
    if (document.readyState !== 'loading') tous(); else document.addEventListener('DOMContentLoaded', tous);
    document.addEventListener('jos:maj', tous);
})();
