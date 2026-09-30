<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function estConnecte() {
    return isset($_SESSION['compte_id']);
}

function utilisateurCourant() {
    return $_SESSION['compte'] ?? null;
}

function exigerConnexion() {
    if (!estConnecte()) {
        redirect('/login.php');
    }
    
    // Si l'utilisateur est un séminariste, on bloque l'accès aux pages protégées tant que son paiement n'est pas validé
    $u = utilisateurCourant();
    if ($u && $u['role'] === 'seminariste') {
        // Ne pas bloquer s'il est déjà sur la page de paiement ou de logout
        $currentPage = basename($_SERVER['PHP_SELF']);
        if (!in_array($currentPage, ['paiement.php', 'logout.php'])) {
            global $pdo;
            if (isset($pdo)) {
                $stmt = $pdo->prepare("SELECT statut FROM paiements WHERE seminariste_id = ? ORDER BY created_at DESC LIMIT 1");
                $stmt->execute([$u['seminariste_id']]);
                $paiement = $stmt->fetch();
                
                if (!$paiement || $paiement['statut'] !== 'validé') {
                    redirect('/paiement.php');
                }
            }
        }
    }
}

function exigerRole(array $rolesAutorises) {
    exigerConnexion();
    $u = utilisateurCourant();
    if (!in_array($u['role'], $rolesAutorises, true)) {
        http_response_code(403);
        die('<div style="font-family:sans-serif;padding:40px;text-align:center;color:#8a1f1f;">
                <h2>Accès refusé</h2>
                <p>Vous n\'avez pas les droits nécessaires pour accéder à cette page.</p>
                <a href="' . BASE_URL . '/index.php">Retour à l\'accueil</a>
             </div>');
    }
}

function estAdmin() {
    $u = utilisateurCourant();
    return $u && in_array($u['role'], ['admin', 'superadmin'], true);
}

function estSuperAdmin() {
    $u = utilisateurCourant();
    return $u && $u['role'] === 'superadmin';
}

/** Accès à la validation des paiements et au scanner de reçus (commission Finance, admins). */
function estFinance() {
    $u = utilisateurCourant();
    return $u && in_array($u['role'], ['finance', 'admin', 'superadmin'], true);
}
