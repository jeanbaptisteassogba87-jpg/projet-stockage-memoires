<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Vérifie que l'utilisateur est authentifié
 * Redirige vers login sinon
 */
function requireAuth(): void {
    if (empty($_SESSION['user_id'])) {
        header('Location: /views/auth/login.php');
        exit;
    }
}

/**
 * Vérifie que l'utilisateur a un rôle spécifique
 */
function requireRole(string $role): void {
    requireAuth();

    if ($_SESSION['user_role'] !== $role) {
        header('Location: /index.php?error=acces_refuse');
        exit;
    }
}

/**
 * Vérifie que l'utilisateur a l'un des rôles spécifiés
 */
function requireRoles(array $roles): void {
    requireAuth();

    if (!in_array($_SESSION['user_role'], $roles)) {
        header('Location: /index.php?error=acces_refuse');
        exit;
    }
}

/**
 * Crée une session utilisateur sécurisée après authentification
 */
function loginUser(array $user): void {
    session_regenerate_id(true);
    
    $_SESSION['user_id'] = $user['id_utilisateur'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['user_nom'] = $user['nom'];
    $_SESSION['centre_id'] = $user['centre_id'] ?? null;
    $_SESSION['doit_changer_mdp'] = $user['doit_changer_mdp'] ?? false;
}

/**
 * Détruit la session utilisateur (déconnexion)
 */
function logoutUser(): void {
    session_unset();
    session_destroy();
    header('Location: /views/auth/login.php');
    exit;
}
