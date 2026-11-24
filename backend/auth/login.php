<?php
/**
 * Codeflip Arena - Processamento de Login
 * Regras: Isolamento em script de backend, verificação bcrypt, registro em logs de auditoria e Toast.
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../bd.php';
require_once __DIR__ . '/../helpers/logger.php';
require_once __DIR__ . '/../helpers/toast_helper.php';
require_once __DIR__ . '/../helpers/auth_helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

// Validação de CSRF Token
if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
    setToast('error', 'Token de segurança expirado ou inválido. Tente novamente.', 'Falha de Segurança');
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$senha = trim($_POST['senha'] ?? '');

if (!$email || empty($senha)) {
    setToast('error', 'Por favor, informe seu e-mail e sua senha de acesso.', 'Campos Obrigatórios');
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$pdo = getDB();

try {
    $stmt = $pdo->prepare("SELECT id, nome, email, senha, role, avatar, xp, nivel FROM users WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    if ($user && password_verify($senha, $user['senha'])) {
        // Regenera ID de sessão para prevenir Session Fixation
        session_regenerate_id(true);

        $_SESSION['usuario'] = [
            'id'     => (int)$user['id'],
            'nome'   => $user['nome'],
            'email'  => $user['email'],
            'role'   => $user['role'],
            'avatar' => $user['avatar'] ?? 'user-astronaut',
            'xp'     => (int)$user['xp'],
            'nivel'  => (int)$user['nivel']
        ];

        // Auditoria: Log de Login
        logAction($user['id'], $user['nome'], 'LOGIN', 'Usuário autenticou-se com sucesso na plataforma.');

        setToast('success', "Bem-vindo de volta, {$user['nome']}! Prepare-se para o desafio.", 'Login Realizado!');
        
        $redirect = !empty($_SESSION['redirect_after_login']) ? $_SESSION['redirect_after_login'] : BASE_URL . '/arena.php';
        unset($_SESSION['redirect_after_login']);
        header('Location: ' . $redirect);
        exit;
    } else {
        // Auditoria: Tentativa de invasão ou erro
        logAction(null, $email, 'FALHA_LOGIN', "Tentativa de login frustrada para o e-mail: {$email}");
        setToast('error', 'E-mail ou senha incorretos. Verifique suas credenciais e tente novamente.', 'Acesso Negado');
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
} catch (PDOException $e) {
    error_log("Erro de login: " . $e->getMessage());
    setToast('error', 'Ocorreu um erro no servidor ao validar suas credenciais. Tente mais tarde.', 'Erro Interno');
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}
