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
        redirect('/login');
    }
    
    // Si l'utilisateur est un séminariste, on bloque l'accès aux pages protégées tant que son paiement n'est pas validé
    $u = utilisateurCourant();
    if ($u && $u['role'] === 'seminariste') {
        // Ne pas bloquer s'il est déjà sur la page de paiement ou de logout
        $currentPage = basename($_SERVER['PHP_SELF']);
        if (!in_array($currentPage, ['paiement.php', 'logout.php', 'fiche.php'])) {
            global $pdo;
            if (isset($pdo)) {
                $stmt = $pdo->prepare("SELECT statut FROM paiements WHERE seminariste_id = ? ORDER BY created_at DESC LIMIT 1");
                $stmt->execute([$u['seminariste_id']]);
                $paiement = $stmt->fetch();
                
                if (!$paiement || $paiement['statut'] !== 'validé') {
                    redirect('/paiement');
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
                <a href="' . BASE_URL . '/index">Retour à l\'accueil</a>
             </div>');
    }
}

/** Comité managérial : administrateurs et comptes MG / MGA. */
function estComiteManagerial() {
    $u = utilisateurCourant();
    return $u && in_array($u['role'], ['admin', 'superadmin', 'mg'], true);
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

/** Commission sécurité : gestion des visiteurs (membres de la commission SÉCURITÉ, admins, super admin). */
function estSecurite() {
    static $cache = null;
    if ($cache !== null) return $cache;
    $u = utilisateurCourant();
    if (!$u) return $cache = false;
    if (in_array($u['role'], ['admin', 'superadmin', 'securite'], true)) return $cache = true;
    if ($u['role'] === 'membre' && !empty($u['membre_id'])) {
        global $pdo;
        $st = $pdo->prepare("SELECT commission FROM membres_commission WHERE id = ?");
        $st->execute([$u['membre_id']]);
        $c = mb_strtoupper(strtr((string)$st->fetchColumn(), ['É' => 'E', 'È' => 'E', 'é' => 'E', 'è' => 'E']), 'UTF-8');
        return $cache = (trim($c) === 'SECURITE');
    }
    return $cache = false;
}

function exigerSecurite() {
    exigerConnexion();
    if (!estSecurite()) {
        http_response_code(403);
        die('<div style="font-family:sans-serif;padding:40px;text-align:center;color:#8a1f1f;"><h2>Accès refusé</h2><p>Cette page est réservée à la commission Sécurité.</p><a href="' . BASE_URL . '/index">Retour à l\'accueil</a></div>');
    }
}

/** Commission scientifique : notes, bulletins, matières, questions et quiz (rôle « scientifique », super admin). */
function estScientifique() {
    $u = utilisateurCourant();
    return $u && in_array($u['role'], ['scientifique', 'superadmin'], true);
}
