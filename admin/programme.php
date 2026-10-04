<?php
require_once __DIR__ . '/../includes/init.php';
exigerRole(['scientifique', 'superadmin']);
programmePreparer($pdo);
$erreurs = []; $succes = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'ajouter';
    $id = (int)($_POST['id'] ?? 0);
    if ($action === 'publier' && $id) {
        $pdo->prepare("UPDATE programmes_journaliers SET publie = 0")->execute();
        $pdo->prepare("UPDATE programmes_journaliers SET publie = 1 WHERE id = ?")->execute([$id]);
        $succes = "Programme publié : il est visible par tout le monde.";
    } elseif ($action === 'depublier' && $id) {
        $pdo->prepare("UPDATE programmes_journaliers SET publie = 0 WHERE id = ?")->execute([$id]);
        $succes = "Programme retiré de la publication.";
    } elseif ($action === 'supprimer' && $id) {
        $pdo->prepare("DELETE FROM programmes_journaliers WHERE id = ?")->execute([$id]);
        $succes = "Programme supprimé.";
    } else {
        $titre = trim($_POST['titre'] ?? '');
        $f = $_FILES['fichier'] ?? null;
        if ($titre === '') $erreurs[] = "Le titre est obligatoire (ex. Programme du jour 1).";
        if (!$f || $f['error'] !== UPLOAD_ERR_OK) $erreurs[] = "Choisissez un fichier (PDF ou image, 6 Mo maximum).";
        elseif ($f['size'] > 6 * 1024 * 1024) $erreurs[] = "Fichier trop volumineux (6 Mo maximum).";
        else {
            $types = ['pdf' => 'application/pdf', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png'];
            $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
            $contenu = file_get_contents($f['tmp_name']);
            $ok = isset($types[$ext]) && (
                ($ext === 'pdf' && strncmp($contenu, '%PDF', 4) === 0) ||
                ($ext !== 'pdf' && @getimagesizefromstring($contenu) !== false));
            if (!$ok) $erreurs[] = "Format non accepté : PDF, JPG ou PNG uniquement.";
        }
        if (!$erreurs) {
            $pdo->prepare("INSERT INTO programmes_journaliers (titre, nom_fichier, mime, donnees, publie, auteur) VALUES (?,?,?,?,0,?)")
                ->execute([$titre, 'programme_' . date('Ymd_His') . '.' . $ext, $types[$ext], $contenu, $_SESSION['compte']['nom_affiche'] ?? '']);
            $succes = "Fichier ajouté. Cliquez sur « Publier » pour l'afficher à tout le monde.";
            $_POST = [];
        }
    }
}
$liste = $pdo->query("SELECT id, titre, nom_fichier, publie, auteur, created_at FROM programmes_journaliers ORDER BY id DESC")->fetchAll();
$titrePage = "Programme journalier";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/admin_nav.php';
?>
<section class="section">
    <div class="container">
        <div class="section-titre">
            <span class="eyebrow">Commission scientifique</span>
            <h2>Programme journalier</h2>
        </div>
        <?php foreach ($erreurs as $er): ?><div class="alert alert-erreur"><?= e($er) ?></div><?php endforeach; ?>
        <?php if ($succes): ?><div class="alert alert-succes"><?= e($succes) ?></div><?php endif; ?>
        <div class="carte" style="margin-bottom:18px;">
            <h3>Ajouter un programme</h3>
            <form method="post" enctype="multipart/form-data" id="form-prog">
                <div class="form-groupe"><label>Titre</label><input type="text" name="titre" maxlength="150" placeholder="Programme du jour 1" required></div>
                <div class="form-groupe"><label>Fichier (PDF, JPG ou PNG, 6 Mo max)</label><input type="file" name="fichier" accept=".pdf,.jpg,.jpeg,.png" required></div>
                <button class="btn btn-primaire" type="submit">Ajouter le fichier</button>
            </form>
        </div>
        <div class="carte">
            <h3>Programmes enregistrés</h3>
            <?php if (!$liste): ?><p>Aucun programme pour le moment.</p><?php endif; ?>
            <?php foreach ($liste as $p): ?>
            <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;padding:10px 0;border-bottom:1px solid #eee;">
                <div style="flex:1;min-width:180px;"><strong><?= e($p['titre']) ?></strong> <?= $p['publie'] ? '<span class="badge badge-succes">Publié</span>' : '<span class="badge">Non publié</span>' ?><br><small><?= e($p['created_at']) ?> · <?= e($p['auteur']) ?></small></div>
                <a class="btn btn-outline btn-sm" target="_blank" href="<?= BASE_URL ?>/programme?id=<?= (int)$p['id'] ?>">Voir</a>
                <form method="post" style="display:inline"><input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                    <?php if ($p['publie']): ?><button class="btn btn-sm btn-outline" name="action" value="depublier">Dépublier</button>
                    <?php else: ?><button class="btn btn-sm btn-primaire" name="action" value="publier">Publier</button><?php endif; ?>
                    <button class="btn btn-sm btn-outline" name="action" value="supprimer" onclick="return confirm('Supprimer ce programme ?')">Supprimer</button>
                </form>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
