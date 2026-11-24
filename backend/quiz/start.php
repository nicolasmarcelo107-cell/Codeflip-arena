<?php
/**
 * Codeflip Arena - Inicializador de Partida de Quiz
 * Seleciona perguntas aleatórias da categoria escolhida e inicializa o estado do jogo na sessão.
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../bd.php';
require_once __DIR__ . '/../helpers/logger.php';
require_once __DIR__ . '/../helpers/toast_helper.php';
require_once __DIR__ . '/../helpers/auth_helper.php';

requireAuth();

$categoria_id = filter_input(INPUT_GET, 'categoria', FILTER_VALIDATE_INT);
$limite_perguntas = 5; // Rodadas ágeis e dinâmicas

$pdo = getDB();

try {
    $categoria = null;
    if ($categoria_id) {
        $stmtCat = $pdo->prepare("SELECT id, nome, slug, cor, icone FROM categorias WHERE id = :id AND ativo = 1");
        $stmtCat->execute([':id' => $categoria_id]);
        $categoria = $stmtCat->fetch();
    }

    if (!$categoria) {
        // Modo Todos os Temas / Batalha Mista
        $categoria = [
            'id'    => 0,
            'nome'  => 'Mix de Todas as Categorias',
            'slug'  => 'mix-arena',
            'cor'   => 'from-purple-600 via-indigo-600 to-pink-600',
            'icone' => 'fa-shuffle'
        ];

        // Busca perguntas de todas as categorias ativas
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        $randomFunc = ($driver === 'sqlite') ? 'RANDOM()' : 'RAND()';

        $stmtQ = $pdo->prepare("
            SELECT p.id, p.pergunta, p.dificuldade, p.pontos, p.explicacao, c.nome as categoria_nome
            FROM perguntas p
            JOIN categorias c ON p.categoria_id = c.id
            WHERE p.ativo = 1 AND c.ativo = 1
            ORDER BY {$randomFunc}
            LIMIT :limite
        ");
        $stmtQ->bindValue(':limite', $limite_perguntas, PDO::PARAM_INT);
        $stmtQ->execute();
        $perguntas = $stmtQ->fetchAll();
    } else {
        // Busca perguntas da categoria específica
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        $randomFunc = ($driver === 'sqlite') ? 'RANDOM()' : 'RAND()';

        $stmtQ = $pdo->prepare("
            SELECT p.id, p.pergunta, p.dificuldade, p.pontos, p.explicacao, :cat_nome as categoria_nome
            FROM perguntas p
            WHERE p.categoria_id = :cat_id AND p.ativo = 1
            ORDER BY {$randomFunc}
            LIMIT :limite
        ");
        $stmtQ->bindValue(':cat_id', $categoria['id'], PDO::PARAM_INT);
        $stmtQ->bindValue(':cat_nome', $categoria['nome'], PDO::PARAM_STR);
        $stmtQ->bindValue(':limite', $limite_perguntas, PDO::PARAM_INT);
        $stmtQ->execute();
        $perguntas = $stmtQ->fetchAll();
    }

    if (empty($perguntas)) {
        setToast('warning', 'Nenhuma pergunta disponível nesta categoria no momento. Escolha outro tema!', 'Categoria Vazia');
        header('Location: ' . BASE_URL . '/arena.php');
        exit;
    }

    // Carrega as opções de cada pergunta e embaralha
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    $randomFunc = ($driver === 'sqlite') ? 'RANDOM()' : 'RAND()';
    $stmtOp = $pdo->prepare("SELECT id, texto, is_correta FROM opcoes WHERE pergunta_id = :p_id ORDER BY {$randomFunc}");

    $quizQuestions = [];
    foreach ($perguntas as $p) {
        $stmtOp->execute([':p_id' => $p['id']]);
        $opcoes = $stmtOp->fetchAll();

        $quizQuestions[] = [
            'id'             => (int)$p['id'],
            'pergunta'       => $p['pergunta'],
            'categoria_nome' => $p['categoria_nome'],
            'dificuldade'    => $p['dificuldade'],
            'pontos'         => (int)$p['pontos'],
            'explicacao'     => $p['explicacao'],
            'opcoes'         => $opcoes
        ];
    }

    // Inicializa a sessão do Quiz
    $_SESSION['active_quiz'] = [
        'categoria_id'   => $categoria['id'],
        'categoria_nome' => $categoria['nome'],
        'categoria_cor'  => $categoria['cor'],
        'questions'      => $quizQuestions,
        'current_index'  => 0,
        'score'          => 0,
        'correct'        => 0,
        'streak'         => 0,
        'max_streak'     => 0,
        'total'          => count($quizQuestions),
        'start_time'     => time(),
        'history'        => []
    ];

    $u = currentUser();
    logAction($u['id'], $u['nome'], 'QUIZ_INICIADO', "Iniciou desafio na categoria: {$categoria['nome']} com " . count($quizQuestions) . " questões.");

    header('Location: ' . BASE_URL . '/play.php');
    exit;

} catch (PDOException $e) {
    error_log("Erro ao iniciar quiz: " . $e->getMessage());
    setToast('error', 'Falha ao inicializar o quiz no banco de dados.', 'Erro');
    header('Location: ' . BASE_URL . '/arena.php');
    exit;
}
