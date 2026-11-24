<?php
/**
 * Codeflip Arena - Processamento de Cadastro de Novo Usuário
 * Requisito: Tratar senhas de usuários com hash bcrypt e manter logs de auditoria.
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../bd.php';
require_once __DIR__ . '/../helpers/logger.php';
require_once __DIR__ . '/../helpers/toast_helper.php';
require_once __DIR__ . '/../helpers/auth_helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/register.php');
    exit;
}

if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
    setToast('error', 'Token de segurança inválido. Recarregue a página e tente novamente.', 'Falha de Validação');
    header('Location: ' . BASE_URL . '/register.php');
    exit;
}

$nome = trim(strip_tags($_POST['nome'] ?? ''));
$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$senha = $_POST['senha'] ?? '';
$confirmar_senha = $_POST['confirmar_senha'] ?? '';

// Validações
if (empty($nome) || mb_strlen($nome) < 3) {
    setToast('error', 'O nome de guerreiro deve ter no mínimo 3 caracteres.', 'Nome Inválido');
    header('Location: ' . BASE_URL . '/register.php');
    exit;
}

if (!$email) {
    setToast('error', 'Por favor, informe um endereço de e-mail válido.', 'E-mail Inválido');
    header('Location: ' . BASE_URL . '/register.php');
    exit;
}

if (strlen($senha) < 6) {
    setToast('error', 'A senha precisa ter no mínimo 6 caracteres para sua segurança.', 'Senha Curta');
    header('Location: ' . BASE_URL . '/register.php');
    exit;
}

if ($senha !== $confirmar_senha) {
    setToast('error', 'As senhas digitadas não coincidem. Digite novamente com atenção.', 'Senhas Divergentes');
    header('Location: ' . BASE_URL . '/register.php');
    exit;
}

$pdo = getDB();

try {
    // Verifica se o e-mail já existe
    $stmtCheck = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
    $stmtCheck->execute([':email' => $email]);
    if ($stmtCheck->fetch()) {
        setToast('error', 'Este e-mail já está registrado na Arena. Utilize outro ou realize login.', 'E-mail Já Cadastrado');
        header('Location: ' . BASE_URL . '/register.php');
        exit;
    }

    // Regra CORE: Hash Bcrypt
    $senhaHash = password_hash($senha, PASSWORD_BCRYPT, ['cost' => 10]);

    $stmtInsert = $pdo->prepare("
        INSERT INTO users (nome, email, senha, role, avatar, xp, nivel, created_at)
        VALUES (:nome, :email, :senha, 'user', 'user-astronaut', 0, 1, :created_at)
    ");
    $stmtInsert->execute([
        ':nome'       => $nome,
        ':email'      => $email,
        ':senha'      => $senhaHash,
        ':created_at' => date('Y-m-d H:i:s')
    ]);

    $novoId = $pdo->lastInsertId();

    // Auditoria: Log de Criação de Usuário
    logAction($novoId, $nome, 'CADASTRO', "Novo competidor cadastrado na plataforma: {$email}");

    // Loga automaticamente o usuário
    session_regenerate_id(true);
    $_SESSION['usuario'] = [
        'id'     => (int)$novoId,
        'nome'   => $nome,
        'email'  => $email,
        'role'   => 'user',
        'avatar' => 'user-astronaut',
        'xp'     => 0,
        'nivel'  => 1
    ];

    setToast('success', "Conta criada com sucesso! Seja bem-vindo à Codeflip Arena, {$nome}!", 'Inscrição Concluída');
    header('Location: ' . BASE_URL . '/arena.php');
    exit;

} catch (PDOException $e) {
    error_log("Erro no cadastro: " . $e->getMessage());
    setToast('error', 'Não foi possível concluir o seu cadastro devido a uma falha no servidor. Tente novamente.', 'Erro ao Cadastrar');
    header('Location: ' . BASE_URL . '/register.php');
    exit;
}
