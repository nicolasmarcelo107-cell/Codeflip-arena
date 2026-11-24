<?php
/**
 * Codeflip Arena - Ações Administrativas de Perguntas e Respostas
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
        setToast('error', 'Token de validação inválido.', 'Erro');
        header('Location: ' . BASE_URL . '/admin/perguntas.php');
        exit;
    }

    if ($action === 'create') {
        $categoria_id = filter_input(INPUT_POST, 'categoria_id', FILTER_VALIDATE_INT);
        $pergunta = trim($_POST['pergunta'] ?? '');
        $dificuldade = in_array($_POST['dificuldade'] ?? '', ['facil', 'medio', 'dificil']) ? $_POST['dificuldade'] : 'medio';
        $pontos = filter_input(INPUT_POST, 'pontos', FILTER_VALIDATE_INT) ?: 10;
        $explicacao = trim($_POST['explicacao'] ?? '');

        $opcoes = $_POST['opcoes'] ?? [];
        $correta_index = filter_input(INPUT_POST, 'correta_index', FILTER_VALIDATE_INT);

        if (!$categoria_id || empty($pergunta) || count($opcoes) < 2) {
            setToast('error', 'Preencha a pergunta, selecione uma categoria e forneça ao menos 2 alternativas.', 'Dados Incompletos');
            header('Location: ' . BASE_URL . '/admin/perguntas.php');
            exit;
        }

        try {
            $pdo->beginTransaction();

            $stmtQ = $pdo->prepare("
                INSERT INTO perguntas (categoria_id, pergunta, dificuldade, pontos, explicacao, ativo)
                VALUES (:categoria_id, :pergunta, :dificuldade, :pontos, :explicacao, 1)
            ");
            $stmtQ->execute([
                ':categoria_id' => $categoria_id,
                ':pergunta'     => $pergunta,
                ':dificuldade'  => $dificuldade,
                ':pontos'       => $pontos,
                ':explicacao'   => $explicacao
            ]);
            $pergunta_id = $pdo->lastInsertId();

            $stmtOp = $pdo->prepare("INSERT INTO opcoes (pergunta_id, texto, is_correta) VALUES (:pergunta_id, :texto, :is_correta)");
            foreach ($opcoes as $idx => $texto) {
                $texto = trim($texto);
                if (!empty($texto)) {
                    $isCorreta = ($idx === $correta_index) ? 1 : 0;
                    $stmtOp->execute([
                        ':pergunta_id' => $pergunta_id,
                        ':texto'       => $texto,
                        ':is_correta'  => $isCorreta
                    ]);
                }
            }

            $pdo->commit();

            logAction($admin['id'], $admin['nome'], 'PERGUNTA_CRIADA', "Pergunta #{$pergunta_id} cadastrada na categoria #{$categoria_id}: " . substr($pergunta, 0, 80));
            setToast('success', 'Nova pergunta e alternativas cadastradas com sucesso!', 'Pergunta Criada');

        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Erro ao criar pergunta: " . $e->getMessage());
            setToast('error', 'Erro ao cadastrar a pergunta no banco de dados.', 'Erro');
        }

        header('Location: ' . BASE_URL . '/admin/perguntas.php');
        exit;
    }

    if ($action === 'update') {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $categoria_id = filter_input(INPUT_POST, 'categoria_id', FILTER_VALIDATE_INT);
        $pergunta = trim($_POST['pergunta'] ?? '');
        $dificuldade = in_array($_POST['dificuldade'] ?? '', ['facil', 'medio', 'dificil']) ? $_POST['dificuldade'] : 'medio';
        $pontos = filter_input(INPUT_POST, 'pontos', FILTER_VALIDATE_INT) ?: 10;
        $explicacao = trim($_POST['explicacao'] ?? '');

        $opcoes = $_POST['opcoes'] ?? [];
        $correta_index = filter_input(INPUT_POST, 'correta_index', FILTER_VALIDATE_INT);

        if (!$id || !$categoria_id || empty($pergunta)) {
            setToast('error', 'Dados insuficientes para atualização da questão.', 'Erro');
            header('Location: ' . BASE_URL . '/admin/perguntas.php');
            exit;
        }

        try {
            $pdo->beginTransaction();

            $stmtQ = $pdo->prepare("
                UPDATE perguntas
                SET categoria_id = :categoria_id, pergunta = :pergunta, dificuldade = :dificuldade,
                    pontos = :pontos, explicacao = :explicacao
                WHERE id = :id
            ");
            $stmtQ->execute([
                ':categoria_id' => $categoria_id,
                ':pergunta'     => $pergunta,
                ':dificuldade'  => $dificuldade,
                ':pontos'       => $pontos,
                ':explicacao'   => $explicacao,
                ':id'           => $id
            ]);

            // Deleta opções anteriores e recria atualizadas
            $stmtDelOp = $pdo->prepare("DELETE FROM opcoes WHERE pergunta_id = :pergunta_id");
            $stmtDelOp->execute([':pergunta_id' => $id]);

            $stmtOp = $pdo->prepare("INSERT INTO opcoes (pergunta_id, texto, is_correta) VALUES (:pergunta_id, :texto, :is_correta)");
            foreach ($opcoes as $idx => $texto) {
                $texto = trim($texto);
                if (!empty($texto)) {
                    $isCorreta = ($idx === $correta_index) ? 1 : 0;
                    $stmtOp->execute([
                        ':pergunta_id' => $id,
                        ':texto'       => $texto,
                        ':is_correta'  => $isCorreta
                    ]);
                }
            }

            $pdo->commit();

            logAction($admin['id'], $admin['nome'], 'PERGUNTA_EDITADA', "Pergunta #{$id} atualizada com sucesso.");
            setToast('success', 'Pergunta e alternativas atualizadas com sucesso!', 'Atualizado');

        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Erro ao atualizar pergunta: " . $e->getMessage());
            setToast('error', 'Falha ao atualizar a pergunta.', 'Erro');
        }

        header('Location: ' . BASE_URL . '/admin/perguntas.php');
        exit;
    }
}

if ($action === 'delete') {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    $token = $_GET['token'] ?? '';

    if (!$id || !validateCsrfToken($token)) {
        setToast('error', 'Token de confirmação inválido.', 'Erro');
        header('Location: ' . BASE_URL . '/admin/perguntas.php');
        exit;
    }

    try {
        $stmtCheck = $pdo->prepare("SELECT pergunta FROM perguntas WHERE id = :id");
        $stmtCheck->execute([':id' => $id]);
        $textoP = $stmtCheck->fetchColumn();

        if ($textoP) {
            $stmtDel = $pdo->prepare("DELETE FROM perguntas WHERE id = :id");
            $stmtDel->execute([':id' => $id]);

            logAction($admin['id'], $admin['nome'], 'PERGUNTA_EXCLUIDA', "Pergunta #{$id} excluída: " . substr($textoP, 0, 70));
            setToast('success', 'Pergunta excluída permanentemente.', 'Removido');
        }
    } catch (PDOException $e) {
        error_log("Erro ao deletar pergunta: " . $e->getMessage());
        setToast('error', 'Erro ao deletar a pergunta.', 'Erro');
    }

    header('Location: ' . BASE_URL . '/admin/perguntas.php');
    exit;
}

header('Location: ' . BASE_URL . '/admin/perguntas.php');
exit;
