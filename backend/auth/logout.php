<?php
/**
 * Codeflip Arena - Encerramento de Sessão (Logout)
 * Auditoria: Registra saída do usuário e limpa sessão nativa
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../bd.php';
require_once __DIR__ . '/../helpers/logger.php';
require_once __DIR__ . '/../helpers/toast_helper.php';

$usuario = $_SESSION['usuario'] ?? null;

if ($usuario) {
    // Auditoria: Log de Logout
    logAction($usuario['id'], $usuario['nome'], 'LOGOUT', 'Usuário encerrou a sessão no sistema.');
}

// Limpa todas as variáveis de sessão
$_SESSION = [];

// Invalida o cookie de sessão no cliente
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

// Inicia nova sessão temporária para exibir o Toast de despedida
session_start();
setToast('info', 'Você saiu da sua conta. Até o próximo desafio!', 'Sessão Encerrada');

header('Location: ' . BASE_URL . '/index.php');
exit;
