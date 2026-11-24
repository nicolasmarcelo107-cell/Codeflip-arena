<?php
/**
 * Codeflip Arena - Atualização de Perfil do Usuário
 * Requisitos:
 * - Validação segura via PDO
 * - Hash bcrypt para nova senha
 * - Auditoria: Registra logs de qualquer alteração no histórico
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../bd.php';
require_once __DIR__ . '/../helpers/logger.php';
require_once __DIR__ . '/../helpers/toast_helper.php';
require_once __DIR__ . '/../helpers/auth_helper.php';

requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/perfil.php');
    exit;
}

if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
    setToast('error', 'Token de segurança inválido.', 'Erro');
    header('Location: ' . BASE_URL . '/perfil.php');
    exit;
}

$user = currentUser();
$pdo = getDB();

$nome = trim(strip_tags($_POST['nome'] ?? ''));
$avatar = trim($_POST['avatar'] ?? 'user-astronaut');
$senha_atual = $_POST['senha_atual'] ?? '';
$nova_senha = $_POST['nova_senha'] ?? '';
$confirmar_nova_senha = $_POST['confirmar_nova_senha'] ?? '';

$allowed_avatars = [
    'user-astronaut', 'user-ninja', 'user-secret', 'ghost', 'dragon', 
    'fire', 'bolt', 'gamepad', 'robot', 'brain', 'meteor', 'crown'
];
if (!in_array($avatar, $allowed_avatars)) {
    $avatar = 'user-astronaut';
}

if (empty($nome) || mb_strlen($nome) < 3) {
    setToast('error', 'O nome precisa ter pelo menos 3 caracteres.', 'Nome Inválido');
    header('Location: ' . BASE_URL . '/perfil.php');
    exit;
}

try {
    // 1. Alteração de senha, caso solicitada
    $senhaAlterada = false;
    if (!empty($nova_senha)) {
        if (empty($senha_atual)) {
            setToast('error', 'Para alterar sua senha, você deve informar a sua senha atual.', 'Senha Atual Necessária');
            header('Location: ' . BASE_URL . '/perfil.php');
            exit;
        }

        if (strlen($nova_senha) < 6) {
            setToast('error', 'A nova senha precisa conter no mínimo 6 caracteres.', 'Senha Fraca');
            header('Location: ' . BASE_URL . '/perfil.php');
            exit;
        }

        if ($nova_senha !== $confirmar_nova_senha) {
            setToast('error', 'A nova senha e a confirmação não coincidem.', 'Senhas Divergentes');
            header('Location: ' . BASE_URL . '/perfil.php');
            exit;
        }

        // Verifica senha atual
        $stmtPass = $pdo->prepare("SELECT senha FROM users WHERE id = :id");
        $stmtPass->execute([':id' => $user['id']]);
        $hashAtual = $stmtPass->fetchColumn();

        if (!password_verify($senha_atual, $hashAtual)) {
            logAction($user['id'], $user['nome'], 'FALHA_ALTERACAO_SENHA', 'Tentativa de alteração de senha com senha atual incorreta.');
            setToast('error', 'A senha atual informada está incorreta.', 'Senha Incorreta');
            header('Location: ' . BASE_URL . '/perfil.php');
            exit;
        }

        // Gera novo hash bcrypt
        $novaSenhaHash = password_hash($nova_senha, PASSWORD_BCRYPT);
        $stmtUpdatePass = $pdo->prepare("UPDATE users SET senha = :senha WHERE id = :id");
        $stmtUpdatePass->execute([':senha' => $novaSenhaHash, ':id' => $user['id']]);
        $senhaAlterada = true;

        logAction($user['id'], $nome, 'SENHA_ALTERADA', 'Usuário alterou com sucesso sua senha de acesso (bcrypt).');
    }

    // 2. Atualização de dados cadastrais (nome, avatar)
    $stmtUpdate = $pdo->prepare("UPDATE users SET nome = :nome, avatar = :avatar WHERE id = :id");
    $stmtUpdate->execute([
        ':nome'   => $nome,
        ':avatar' => $avatar,
        ':id'     => $user['id']
    ]);

    // Atualiza dados na sessão
    $_SESSION['usuario']['nome'] = $nome;
    $_SESSION['usuario']['avatar'] = $avatar;

    // Auditoria de alteração de dados
    $detalhesLog = "Perfil atualizado. Novo nome: {$nome}, Novo avatar: {$avatar}" . ($senhaAlterada ? " | Senha alterada com sucesso." : "");
    logAction($user['id'], $nome, 'PERFIL_ATUALIZADO', $detalhesLog);

    setToast('success', 'Seu perfil foi atualizado com sucesso!' . ($senhaAlterada ? ' Sua nova senha já está valendo.' : ''), 'Perfil Salvo');
    header('Location: ' . BASE_URL . '/perfil.php');
    exit;

} catch (PDOException $e) {
    error_log("Erro ao atualizar perfil: " . $e->getMessage());
    setToast('error', 'Ocorreu um erro no servidor ao salvar seus dados.', 'Erro Interno');
    header('Location: ' . BASE_URL . '/perfil.php');
    exit;
}
