<footer>
    <div class="container">
        <div class="pied-grille">
            <div>
                <div class="pied-marque">
                    <img src="<?= BASE_URL ?>/assets/img/logo.jpg" alt="Logo JOSPIA">
                    <div><strong><?= e(EVENT_NAME) ?></strong><?= e(EVENT_FULL) ?></div>
                </div>
                <p style="margin:14px 0 0;max-width:30em;">Du <?= e(EVENT_JOUR_DEBUT) ?> au <?= e(EVENT_JOUR_FIN) ?> <?= e(mb_strtolower(EVENT_MOIS_ANNEE, 'UTF-8')) ?>, au Collège privé Henriette Dagri-Diabaté d'Anyama. Organisé par les sous-comités AEEMCI d'Anyama.</p>
            </div>
            <div>
                <h4>S'inscrire</h4>
                <a href="<?= BASE_URL ?>/inscription_seminariste">Séminariste</a>
                <a href="<?= BASE_URL ?>/inscription_commission">Membre de commission</a>
                <a href="<?= BASE_URL ?>/statut">Suivre mon paiement</a>
                <a href="<?= BASE_URL ?>/visiteur">Visiteurs (arrivée / sortie)</a>
            </div>
            <div>
                <h4>Mon espace</h4>
                <a href="<?= BASE_URL ?>/login">Connexion</a>
                <a href="<?= BASE_URL ?>/">Accueil</a>
            </div>
        </div>
        <div class="pied-bas">&copy; <?= date('Y') ?> <?= e(EVENT_FULL) ?> - Tous droits réservés.</div>
    </div>
</footer>
<script src="<?= BASE_URL ?>/assets/js/main.js?v=<?= @filemtime(__DIR__ . "/../assets/js/main.js") ?>" defer></script>
</body>
</html>
