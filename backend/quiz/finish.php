<?php
/**
 * Codeflip Arena - Finalizador de Partida de Quiz
 * Persiste a pontuação na tabela de partidas, concede XP ao usuário, atualiza nível e grava log de auditoria.
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../bd.php';
require_once __DIR__ . '/../helpers/logger.php';
require_once __DIR__ . '/../helpers/toast_helper.php';
require_once __DIR__ . '/../helpers/auth_helper.php';

requireAuth();

if (empty($_SESSION['active_quiz'])) {
    header('Location: ' . BASE_URL . '/arena.php');
    exit;
}

$quiz = $_SESSION['active_quiz'];
$user = currentUser();
$pdo = getDB();

$duracao = max(1, time() - ($quiz['start_time'] ?? time()));
$pontos = (int)($quiz['score'] ?? 0);
$acertos = (int)($quiz['correct'] ?? 0);
$total = (int)($quiz['total'] ?? 0);
$categoria_id = (int)($quiz['categoria_id'] ?? 0);

// Se for categoria 0 (Mix), associa à primeira categoria existente para integridade de FK
if ($categoria_id <= 0) {
    $stmtFirstCat = $pdo->query("SELECT id FROM categorias LIMIT 1");
    $categoria_id = (int)$stmtFirstCat->fetchColumn();
}

try {
    // 1. Grava histórico na tabela partidas
    $stmtPartida = $pdo->prepare("
        INSERT INTO partidas (usuario_id, categoria_id, pontuacao_total, acertos, total_perguntas, tempo_gasto_segundos, created_at)
        VALUES (:usuario_id, :categoria_id, :pontos, :acertos, :total, :tempo, :created_at)
    ");
    $stmtPartida->execute([
        ':usuario_id'   => $user['id'],
        ':categoria_id' => $categoria_id,
        ':pontos'       => $pontos,
        ':acertos'      => $acertos,
        ':total'        => $total,
        ':tempo'        => $duracao,
        ':created_at'   => date('Y-m-d H:i:s')
    ]);

    // 2. Concede XP e atualiza estatísticas do usuário
    $stmtUser = $pdo->prepare("SELECT xp, nivel, partidas_jogadas, acertos_total FROM users WHERE id = :id");
    $stmtUser->execute([':id' => $user['id']]);
    $userData = $stmtUser->fetch();

    $novoXP = (int)$userData['xp'] + $pontos;
    $novoNivel = calculateLevel($novoXP);
    $novasPartidas = (int)$userData['partidas_jogadas'] + 1;
    $novosAcertos = (int)$userData['acertos_total'] + $acertos;

    $stmtUpdateUser = $pdo->prepare("
        UPDATE users
        SET xp = :xp, nivel = :nivel, partidas_jogadas = :partidas, acertos_total = :acertos
        WHERE id = :id
    ");
    $stmtUpdateUser->execute([
        ':xp'       => $novoXP,
        ':nivel'    => $novoNivel,
        ':partidas' => $novasPartidas,
        ':acertos'  => $novosAcertos,
        ':id'       => $user['id']
    ]);

    // Atualiza a sessão do usuário
    $_SESSION['usuario']['xp'] = $novoXP;
    $_SESSION['usuario']['nivel'] = $novoNivel;

    // 3. Auditoria: Log de partida concluída (Requisito CORE)
    $detalhesLog = sprintf(
        "Concluiu quiz '%s': %d/%d acertos, %d pontos em %d segundos. Ganhou +%d XP.",
        $quiz['categoria_nome'],
        $acertos,
        $total,
        $pontos,
        $duracao,
        $pontos
    );
    logAction($user['id'], $user['nome'], 'QUIZ_JOGADO', $detalhesLog);

    // Salva resumo para a tela de resultado
    $_SESSION['last_game_result'] = [
        'categoria_nome' => $quiz['categoria_nome'],
        'categoria_cor'  => $quiz['categoria_cor'],
        'score'          => $pontos,
        'correct'        => $acertos,
        'total'          => $total,
        'max_streak'     => $quiz['max_streak'] ?? 0,
        'duration'       => $duracao,
        'history'        => $quiz['history'] ?? [],
        'level_up'       => ($novoNivel > (int)$userData['nivel']),
        'current_level'  => $novoNivel,
        'total_xp'       => $novoXP
    ];

    // Limpa o jogo ativo
    unset($_SESSION['active_quiz']);

    header('Location: ' . BASE_URL . '/resultado.php');
    exit;

} catch (PDOException $e) {
    error_log("Erro ao salvar resultado da partida: " . $e->getMessage());
    setToast('error', 'Ocorreu um erro ao gravar sua pontuação.', 'Erro');
    header('Location: ' . BASE_URL . '/arena.php');
    exit;
}
