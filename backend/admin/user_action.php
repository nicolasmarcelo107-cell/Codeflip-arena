<?php
/**
 * Codeflip Arena - Gestão de Usuários (Administração)
 * Requisitos:
 * - Apenas Administradores (requireAdmin)
 * - Prepared Statements PDO
 * - Auditoria completa em cada ação (logAction)
 * - Retorno com TOAST
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../bd.php';
require_once __DIR__ . '/../helpers/logger.php';
require_once __DIR__ . '/../helpers/toast_helper.php';
require_once __DIR__ . '/../helpers/auth_helper.php';

requireAdmin();

$admin = currentUser();
$pdo = getDB();

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'change_role') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        setToast('error', 'Token de autenticidade inválido.', 'Erro');
        header('Location: ' . BASE_URL . '/admin/usuarios.php');
        exit;
    }

    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $novo_role = in_array($_POST['role'] ?? '', ['user', 'admin']) ? $_POST['role'] : 'user';

    if ($id == $admin['id']) {
        setToast('error', 'Você não pode alterar o seu próprio cargo de administrador.', 'Ação Bloqueada');
        header('Location: ' . BASE_URL . '/admin/usuarios.php');
        exit;
    }

    try {
        $stmtUser = $pdo->prepare("SELECT nome, email, role FROM users WHERE id = :id");
        $stmtUser->execute([':id' => $id]);
        $targetUser = $stmtUser->fetch();

        if ($targetUser) {
            $stmtUpdate = $pdo->prepare("UPDATE users SET role = :role WHERE id = :id");
            $stmtUpdate->execute([':role' => $novo_role, ':id' => $id]);

            logAction($admin['id'], $admin['nome'], 'USUARIO_ATUALIZADO', "Cargo do usuário #{$id} ({$targetUser['nome']}) alterado de '{$targetUser['role']}' para '{$novo_role}'.");
            setToast('success', "Cargo de {$targetUser['nome']} alterado para {$novo_role} com sucesso!", 'Cargo Alterado');
        }
    } catch (PDOException $e) {
        error_log("Erro ao alterar cargo do usuário: " . $e->getMessage());
        setToast('error', 'Falha ao atualizar o cargo do usuário.', 'Erro');
    }

    header('Location: ' . BASE_URL . '/admin/usuarios.php');
    exit;
}

if ($action === 'delete') {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    $token = $_GET['token'] ?? '';

    if (!$id || !validateCsrfToken($token)) {
        setToast('error', 'Token de confirmação inválido.', 'Erro');
        header('Location: ' . BASE_URL . '/admin/usuarios.php');
        exit;
    }

    if ($id == $admin['id']) {
        setToast('error', 'Você não pode excluir sua própria conta enquanto estiver logado.', 'Ação Bloqueada');
        header('Location: ' . BASE_URL . '/admin/usuarios.php');
        exit;
    }

    try {
        $stmtUser = $pdo->prepare("SELECT nome, email FROM users WHERE id = :id");
        $stmtUser->execute([':id' => $id]);
        $targetUser = $stmtUser->fetch();

        if ($targetUser) {
            $stmtDel = $pdo->prepare("DELETE FROM users WHERE id = :id");
            $stmtDel->execute([':id' => $id]);

            logAction($admin['id'], $admin['nome'], 'USUARIO_EXCLUIDO', "Conta do usuário #{$id} ({$targetUser['nome']} - {$targetUser['email']}) foi excluída definitivamente.");
            setToast('success', "A conta de {$targetUser['nome']} foi removida do sistema.", 'Usuário Excluído');
        }
    } catch (PDOException $e) {
        error_log("Erro ao deletar usuário: " . $e->getMessage());
        setToast('error', 'Falha ao remover o usuário.', 'Erro');
    }

    header('Location: ' . BASE_URL . '/admin/usuarios.php');
    exit;
}

header('Location: ' . BASE_URL . '/admin/usuarios.php');
exit;
