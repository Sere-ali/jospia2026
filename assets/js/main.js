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
            selSection.innerHTML = '<option value="">— Choisir une section —</option>';
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
