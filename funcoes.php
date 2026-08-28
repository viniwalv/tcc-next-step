<?php
// ============================================================
//  NextStep — Funções auxiliares e controle de sessão
// ============================================================

require_once __DIR__ . '/../config/db.php';

// Inicia sessão de forma segura
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_start();
}

// ---- Autenticação ----

function usuarioLogado(): bool {
    return isset($_SESSION['id_usuario']);
}

function isPerfil(string $perfil): bool {
    return isset($_SESSION['perfil']) && $_SESSION['perfil'] === $perfil;
}

function exigirLogin(): void {
    if (!usuarioLogado()) {
        header('Location: /nextstep/login.php');
        exit;
    }
}

function exigirAdmin(): void {
    exigirLogin();
    if (!isPerfil('admin')) {
        header('Location: /nextstep/usuario/dashboard.php');
        exit;
    }
}

function exigirUsuario(): void {
    exigirLogin();
    if (!isPerfil('usuario') && !isPerfil('admin')) {
        header('Location: /nextstep/login.php');
        exit;
    }
}

function redirecionarPorPerfil(): void {
    if (isPerfil('admin')) {
        header('Location: /nextstep/admin/dashboard.php');
    } else {
        header('Location: /nextstep/usuario/dashboard.php');
    }
    exit;
}

// ---- Segurança ----

function limpar(string $dado): string {
    return htmlspecialchars(trim($dado), ENT_QUOTES, 'UTF-8');
}

function tokenCSRF(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validarCSRF(string $token): bool {
    return isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

// ---- Mensagens flash ----

function setMensagem(string $tipo, string $texto): void {
    $_SESSION['flash'] = ['tipo' => $tipo, 'texto' => $texto];
}

function getMensagem(): ?array {
    if (isset($_SESSION['flash'])) {
        $msg = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $msg;
    }
    return null;
}

// ---- Dados do usuário logado ----

function dadosUsuario(): array {
    return [
        'id'    => $_SESSION['id_usuario'] ?? 0,
        'nome'  => $_SESSION['nome']       ?? '',
        'email' => $_SESSION['email']      ?? '',
        'perfil'=> $_SESSION['perfil']     ?? '',
    ];
}