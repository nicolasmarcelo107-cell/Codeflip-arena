<?php
/**
 * Codeflip Arena - Ações Administrativas de Categorias
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        setToast('error', 'Token de validação inválido.', 'Acesso Rejeitado');
        header('Location: ' . BASE_URL . '/admin/categorias.php');
        exit;
    }

    if ($action === 'create') {
        $nome = trim(strip_tags($_POST['nome'] ?? ''));
        $slug = trim(strip_tags($_POST['slug'] ?? ''));
        $descricao = trim(strip_tags($_POST['descricao'] ?? ''));
        $icone = trim($_POST['icone'] ?? 'fa-gamepad');
        $cor = trim($_POST['cor'] ?? 'from-blue-600 to-indigo-700');

        if (empty($nome)) {
            setToast('error', 'O nome da categoria é obrigatório.', 'Campo Obrigatório');
            header('Location: ' . BASE_URL . '/admin/categorias.php');
            exit;
        }

        if (empty($slug)) {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $nome)));
        }

        try {
            $stmt = $pdo->prepare("
                INSERT INTO categorias (nome, slug, descricao, icone, cor, ativo)
                VALUES (:nome, :slug, :descricao, :icone, :cor, 1)
            ");
            $stmt->execute([
                ':nome'      => $nome,
                ':slug'      => $slug,
                ':descricao' => $descricao,
                ':icone'     => $icone,
                ':cor'       => $cor
            ]);

            $id = $pdo->lastInsertId();
            logAction($admin['id'], $admin['nome'], 'CATEGORIA_CRIADA', "Categoria #{$id} ({$nome}) criada pelo administrador.");
            setToast('success', "Categoria '{$nome}' criada com sucesso!", 'Sucesso');
        } catch (PDOException $e) {
            error_log("Erro ao criar categoria: " . $e->getMessage());
            setToast('error', 'Não foi possível cadastrar a categoria. Verifique se o slug já existe.', 'Erro');
        }

        header('Location: ' . BASE_URL . '/admin/categorias.php');
        exit;
    }

    if ($action === 'update') {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $nome = trim(strip_tags($_POST['nome'] ?? ''));
        $slug = trim(strip_tags($_POST['slug'] ?? ''));
        $descricao = trim(strip_tags($_POST['descricao'] ?? ''));
        $icone = trim($_POST['icone'] ?? 'fa-gamepad');
        $cor = trim($_POST['cor'] ?? 'from-blue-600 to-indigo-700');
        $ativo = isset($_POST['ativo']) ? 1 : 0;

        if (!$id || empty($nome)) {
            setToast('error', 'Dados insuficientes para atualização.', 'Erro');
            header('Location: ' . BASE_URL . '/admin/categorias.php');
            exit;
        }

        try {
            $stmt = $pdo->prepare("
                UPDATE categorias
                SET nome = :nome, slug = :slug, descricao = :descricao, icone = :icone, cor = :cor, ativo = :ativo
                WHERE id = :id
            ");
            $stmt->execute([
                ':nome'      => $nome,
                ':slug'      => $slug,
                ':descricao' => $descricao,
                ':icone'     => $icone,
                ':cor'       => $cor,
                ':ativo'     => $ativo,
                ':id'        => $id
            ]);

            logAction($admin['id'], $admin['nome'], 'CATEGORIA_EDITADA', "Categoria #{$id} alterada para '{$nome}' (Ativo: {$ativo}).");
            setToast('success', "Categoria '{$nome}' atualizada com sucesso!", 'Salvo');
        } catch (PDOException $e) {
            error_log("Erro ao atualizar categoria: " . $e->getMessage());
            setToast('error', 'Falha ao atualizar a categoria.', 'Erro');
        }

        header('Location: ' . BASE_URL . '/admin/categorias.php');
        exit;
    }
}

if ($action === 'delete') {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    $token = $_GET['token'] ?? '';

    if (!$id || !validateCsrfToken($token)) {
        setToast('error', 'Requisição inválida ou token expirado.', 'Erro');
        header('Location: ' . BASE_URL . '/admin/categorias.php');
        exit;
    }

    try {
        $stmtCheck = $pdo->prepare("SELECT nome FROM categorias WHERE id = :id");
        $stmtCheck->execute([':id' => $id]);
        $catNome = $stmtCheck->fetchColumn();

        if ($catNome) {
            $stmtDel = $pdo->prepare("DELETE FROM categorias WHERE id = :id");
            $stmtDel->execute([':id' => $id]);

            logAction($admin['id'], $admin['nome'], 'CATEGORIA_EXCLUIDA', "Categoria #{$id} ({$catNome}) foi excluída pelo administrador.");
            setToast('success', "A categoria '{$catNome}' foi excluída permanentemente.", 'Excluído');
        }
    } catch (PDOException $e) {
        error_log("Erro ao deletar categoria: " . $e->getMessage());
        setToast('error', 'Não foi possível excluir esta categoria pois ela pode conter perguntas vinculadas.', 'Erro');
    }

    header('Location: ' . BASE_URL . '/admin/categorias.php');
    exit;
}

header('Location: ' . BASE_URL . '/admin/categorias.php');
exit;
