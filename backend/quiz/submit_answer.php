<?php
/**
 * Codeflip Arena - Validador de Respostas do Quiz
 * Processa resposta via Fetch/AJAX ou POST normal, calcula pontos com bônus de streak e tempo.
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../bd.php';
require_once __DIR__ . '/../helpers/auth_helper.php';

if (!isLoggedIn() || empty($_SESSION['active_quiz'])) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Sessão de jogo expirada ou inexistente. Reinicie a partida.'
    ]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

$opcao_id = filter_var($input['opcao_id'] ?? 0, FILTER_VALIDATE_INT);
$time_left = filter_var($input['time_left'] ?? 0, FILTER_VALIDATE_INT);

$quiz = &$_SESSION['active_quiz'];
$currentIndex = $quiz['current_index'];
$questions = $quiz['questions'];

if ($currentIndex >= count($questions)) {
    echo json_encode([
        'status'      => 'finished',
        'is_finished' => true,
        'redirect'    => BASE_URL . '/backend/quiz/finish.php'
    ]);
    exit;
}

$currentQuestion = $questions[$currentIndex];
$pdo = getDB();

try {
    // Busca opção correta no banco via PDO
    $stmt = $pdo->prepare("SELECT id, texto, is_correta FROM opcoes WHERE pergunta_id = :p_id");
    $stmt->execute([':p_id' => $currentQuestion['id']]);
    $allOptions = $stmt->fetchAll();

    $correctOption = null;
    $selectedOption = null;

    foreach ($allOptions as $opt) {
        if ($opt['is_correta'] == 1) {
            $correctOption = $opt;
        }
        if ($opt['id'] == $opcao_id) {
            $selectedOption = $opt;
        }
    }

    $isCorrect = ($selectedOption && $selectedOption['is_correta'] == 1);
    $earnedPoints = 0;

    if ($isCorrect) {
        $quiz['correct']++;
        $quiz['streak']++;
        if ($quiz['streak'] > $quiz['max_streak']) {
            $quiz['max_streak'] = $quiz['streak'];
        }

        // Pontuação = Pontos base + Bônus de Streak + Bônus de tempo restante
        $basePoints = $currentQuestion['pontos'];
        $streakBonus = ($quiz['streak'] - 1) * 3;
        $timeBonus = max(0, min(15, (int)$time_left));

        $earnedPoints = $basePoints + $streakBonus + $timeBonus;
        $quiz['score'] += $earnedPoints;
    } else {
        $quiz['streak'] = 0;
    }

    // Registra detalhe da resposta no histórico temporário
    $quiz['history'][] = [
        'pergunta'          => $currentQuestion['pergunta'],
        'categoria'         => $currentQuestion['categoria_nome'],
        'opcao_selecionada' => $selectedOption ? $selectedOption['texto'] : 'Tempo Esgotado / Não respondeu',
        'opcao_correta'     => $correctOption ? $correctOption['texto'] : '',
        'is_correta'        => $isCorrect,
        'pontos_ganhos'     => $earnedPoints,
        'explicacao'        => $currentQuestion['explicacao']
    ];

    $quiz['current_index']++;
    $isFinished = ($quiz['current_index'] >= count($questions));

    echo json_encode([
        'status'         => 'success',
        'is_correct'     => $isCorrect,
        'correct_id'     => $correctOption ? (int)$correctOption['id'] : null,
        'earned_points'  => $earnedPoints,
        'current_score'  => $quiz['score'],
        'streak'         => $quiz['streak'],
        'correct_count'  => $quiz['correct'],
        'current_index'  => $quiz['current_index'],
        'total'          => $quiz['total'],
        'explanation'    => $currentQuestion['explicacao'],
        'is_finished'    => $isFinished,
        'redirect'       => $isFinished ? BASE_URL . '/backend/quiz/finish.php' : null
    ]);
    exit;

} catch (PDOException $e) {
    error_log("Erro ao validar resposta: " . $e->getMessage());
    echo json_encode([
        'status'  => 'error',
        'message' => 'Erro interno ao validar alternativa.'
    ]);
    exit;
}
