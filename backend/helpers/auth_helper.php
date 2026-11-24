<?php
/**
 * Codeflip Arena - Auxiliar de Autenticação e Controle de Acesso
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/toast_helper.php';

/**
 * Verifica se há um usuário autenticado na sessão
 * @return bool
 */
function isLoggedIn() {
    return !empty($_SESSION['usuario']) && !empty($_SESSION['usuario']['id']);
}

/**
 * Retorna os dados do usuário autenticado
 * @return array|null
 */
function currentUser() {
    return $_SESSION['usuario'] ?? null;
}

/**
 * Verifica se o usuário autenticado possui perfil de Administrador
 * @return bool
 */
function isAdmin() {
    return isLoggedIn() && (($_SESSION['usuario']['role'] ?? '') === 'admin');
}

/**
 * Exige autenticação. Redireciona para o login caso não esteja logado.
 */
function requireAuth() {
    if (!isLoggedIn()) {
        setToast('warning', 'Você precisa estar logado para acessar esta página.', 'Acesso Restrito');
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

/**
 * Exige perfil de administrador.
 */
function requireAdmin() {
    requireAuth();
    if (!isAdmin()) {
        setToast('error', 'Acesso negado: privilégios de administrador necessários.', 'Permissão Insuficiente');
        header('Location: ' . BASE_URL . '/index.php');
        exit;
    }
}

/**
 * Gera ou recupera um token CSRF seguro na sessão
 * @return string
 */
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Valida o token CSRF recebido na requisição
 * @param string|null $token
 * @return bool
 */
function validateCsrfToken($token) {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Calcula o nível do jogador baseado em seu XP acumulado
 * Fórmula de progressão de nível: Nível = floor(sqrt(XP / 50)) + 1
 * @param int $xp
 * @return int
 */
function calculateLevel($xp) {
    return (int) floor(sqrt(max(0, (int)$xp) / 50)) + 1;
}

/**
 * Calcula a porcentagem para o próximo nível
 * @param int $xp
 * @return array ['level' => int, 'current' => int, 'next_needed' => int, 'percentage' => int]
 */
function calculateLevelProgress($xp) {
    $level = calculateLevel($xp);
    $current_level_xp = ($level - 1) * ($level - 1) * 50;
    $next_level_xp = $level * $level * 50;
    $xp_in_level = $xp - $current_level_xp;
    $xp_needed = $next_level_xp - $current_level_xp;
    $percentage = ($xp_needed > 0) ? min(100, max(0, round(($xp_in_level / $xp_needed) * 100))) : 100;

    return [
        'level'      => $level,
        'current_xp' => $xp,
        'xp_in_lvl'  => $xp_in_level,
        'xp_needed'  => $xp_needed,
        'percentage' => $percentage
    ];
}
