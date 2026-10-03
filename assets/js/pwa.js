/* Application mobile (PWA) : enregistrement du service worker + bouton « Installer l'application » */
(function () {
    var base = (document.currentScript && document.currentScript.getAttribute('data-base')) || '';
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () { navigator.serviceWorker.register(base + '/sw.js', { scope: base + '/' }).catch(function () {}); });
    }
    var installee = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
    if (installee) return;
    var evt = null;
    window.addEventListener('beforeinstallprompt', function (e) { e.preventDefault(); evt = e; });
    window.addEventListener('appinstalled', function () { var b = document.getElementById('btn-pwa'); if (b) b.remove(); });

    function aide() {
        var ios = /iphone|ipad|ipod/i.test(navigator.userAgent);
        var m = document.createElement('div');
        m.className = 'pwa-modal';
        m.innerHTML = '<div class="pwa-boite"><h3>📲 Installer l\'application</h3>' + (ios
            ? '<p>Sur iPhone / iPad (Safari) :</p><ol><li>Touchez le bouton <b>Partager</b> ⬆️</li><li>Choisissez <b>« Sur l\'écran d\'accueil »</b></li><li>Touchez <b>Ajouter</b></li></ol>'
            : '<p>Sur Android (Chrome) :</p><ol><li>Touchez le menu <b>⋮</b> en haut à droite</li><li>Choisissez <b>« Installer l\'application »</b> ou <b>« Ajouter à l\'écran d\'accueil »</b></li></ol>')
            + '<button type="button" class="btn btn-primaire">OK</button></div>';
        m.addEventListener('click', function (e) { if (e.target === m || e.target.tagName === 'BUTTON') m.remove(); });
        document.body.appendChild(m);
    }
    function bouton() {
        var b = document.createElement('button');
        b.id = 'btn-pwa'; b.type = 'button'; b.className = 'btn-pwa';
        b.innerHTML = '<span>📲</span> Installer l\'application';
        b.addEventListener('click', function () {
            if (evt) { evt.prompt(); evt.userChoice.then(function (c) { if (c.outcome === 'accepted') b.remove(); evt = null; }); }
            else aide();
        });
        document.body.appendChild(b);
    }
    if (document.readyState !== 'loading') bouton(); else document.addEventListener('DOMContentLoaded', bouton);
})();
