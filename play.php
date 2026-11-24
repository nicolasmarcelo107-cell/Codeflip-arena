<?php
/**
 * Codeflip Arena - Interface de Gameplay do Quiz
 * Mobile-First com Tailwind CSS, cronômetro regressivo, combo streaks e áudio
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/bd.php';
require_once __DIR__ . '/backend/helpers/auth_helper.php';

requireAuth();

if (empty($_SESSION['active_quiz'])) {
    header('Location: ' . BASE_URL . '/arena.php');
    exit;
}

$quiz = &$_SESSION['active_quiz'];
$currentIndex = $quiz['current_index'];
$totalQuestions = $quiz['total'];

if ($currentIndex >= $totalQuestions) {
    header('Location: ' . BASE_URL . '/backend/quiz/finish.php');
    exit;
}

$currentQuestion = $quiz['questions'][$currentIndex];
$pageTitle = "Questão " . ($currentIndex + 1) . " - " . $quiz['categoria_nome'];

require_once __DIR__ . '/includes/header.php';
?>

<div class="min-h-screen flex flex-col bg-slate-950">
    <!-- Barra Superior da Arena -->
    <header class="bg-slate-900/90 border-b border-slate-800 px-4 py-3.5 sticky top-0 z-30 backdrop-blur-md">
        <div class="max-w-4xl mx-auto flex items-center justify-between gap-4">
            <!-- Botão de Sair -->
            <a href="<?= BASE_URL ?>/arena.php" data-confirm="Deseja abandonar a partida? O progresso da rodada não será salvo." data-title="Desistir da Partida"
               class="text-xs font-bold text-slate-400 hover:text-rose-400 transition flex items-center gap-1.5 py-1.5 px-3 rounded-lg hover:bg-rose-950/40 border border-transparent hover:border-rose-500/30">
                <i class="fa-solid fa-arrow-left"></i> Sair
            </a>

            <!-- Badge do Tema -->
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-indigo-500 animate-pulse"></span>
                <span class="text-xs font-extrabold text-white tracking-wide uppercase truncate max-w-[140px] sm:max-w-none">
                    <?= htmlspecialchars($quiz['categoria_nome']) ?>
                </span>
            </div>

            <!-- Placar & Combo -->
            <div class="flex items-center gap-3">
                <div id="quiz-streak-badge" class="<?= ($quiz['streak'] ?? 0) > 1 ? '' : 'hidden' ?> px-2.5 py-1 rounded-full bg-gradient-to-r from-amber-500 to-orange-500 text-slate-950 text-[11px] font-black uppercase tracking-wider animate-bounce">
                    <span id="quiz-streak-text"><?= $quiz['streak'] ?? 0 ?>x Combo! 🔥</span>
                </div>
                <div class="px-3 py-1 rounded-xl bg-slate-800 border border-slate-700/70 text-xs font-black text-indigo-400">
                    <span id="quiz-score-text"><?= $quiz['score'] ?? 0 ?> pts</span>
                </div>
            </div>
        </div>
    </header>

    <!-- Barra de Progresso do Cronômetro -->
    <div class="w-full bg-slate-900 h-2 relative overflow-hidden">
        <div id="quiz-timer-bar" class="h-full bg-indigo-500 transition-all duration-1000 ease-linear" style="width: 100%;"></div>
    </div>

    <!-- Conteúdo Central do Quiz -->
    <main class="flex-1 flex items-center justify-center py-6 sm:py-10 px-4 sm:px-6">
        <div class="max-w-2xl w-full">
            <!-- Cabeçalho da Pergunta -->
            <div class="flex items-center justify-between mb-4 text-xs font-bold text-slate-400">
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-1 rounded-lg bg-slate-900 border border-slate-800 text-white">
                        Pergunta <strong class="text-indigo-400"><?= $currentIndex + 1 ?></strong> de <?= $totalQuestions ?>
                    </span>
                    <span class="px-2 py-0.5 rounded text-[10px] uppercase font-bold
                        <?= $currentQuestion['dificuldade'] === 'facil' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : ($currentQuestion['dificuldade'] === 'dificil' ? 'bg-rose-500/20 text-rose-300 border border-rose-500/30' : 'bg-amber-500/20 text-amber-300 border border-amber-500/30') ?>">
                        <?= htmlspecialchars($currentQuestion['dificuldade']) ?>
                    </span>
                </div>

                <!-- Tempo Restante -->
                <div class="flex items-center gap-1.5 text-slate-300 font-mono text-sm bg-slate-900 px-3 py-1 rounded-lg border border-slate-800">
                    <i class="fa-regular fa-clock text-indigo-400"></i>
                    <span id="quiz-timer-text" class="font-bold">20s</span>
                </div>
            </div>

            <!-- Card da Pergunta -->
            <div class="p-6 sm:p-8 rounded-2xl sm:rounded-3xl bg-slate-900/90 border border-slate-800 shadow-2xl mb-6 backdrop-blur-md">
                <h2 class="text-lg sm:text-2xl font-black text-white leading-snug">
                    <?= htmlspecialchars($currentQuestion['pergunta']) ?>
                </h2>
            </div>

            <!-- Grade de Opções de Resposta -->
            <div class="grid grid-cols-1 gap-3.5 mb-6" id="options-container">
                <?php
                $letters = ['A', 'B', 'C', 'D'];
                foreach ($currentQuestion['opcoes'] as $idx => $opt):
                    $letter = $letters[$idx] ?? ($idx + 1);
                ?>
                    <button type="button"
                            class="quiz-option-btn option-btn w-full text-left p-4 sm:p-5 rounded-2xl bg-slate-900 hover:bg-slate-800/90 border border-slate-800 hover:border-indigo-500/40 text-slate-200 flex items-center justify-between gap-4 transition shadow-md group focus:outline-none"
                            data-option-id="<?= $opt['id'] ?>">
                        <div class="flex items-center gap-3.5">
                            <span class="w-8 h-8 rounded-xl bg-slate-800 group-hover:bg-indigo-600/30 border border-slate-700/80 group-hover:border-indigo-500/40 text-slate-300 group-hover:text-indigo-300 flex items-center justify-center text-xs font-black shrink-0 transition">
                                <?= $letter ?>
                            </span>
                            <span class="text-xs sm:text-sm font-semibold leading-relaxed">
                                <?= htmlspecialchars($opt['texto']) ?>
                            </span>
                        </div>
                        <div class="option-indicator w-6 h-6 rounded-full flex items-center justify-center text-sm shrink-0"></div>
                    </button>
                <?php endforeach; ?>
            </div>

            <!-- Área de Explicação / Avanço (Inicialmente oculta) -->
            <div id="quiz-feedback-area" class="hidden p-5 sm:p-6 rounded-2xl bg-slate-900/95 border border-slate-800 shadow-2xl animate-fade-in">
                <h3 id="quiz-feedback-title" class="text-base font-bold mb-1"></h3>
                <p id="quiz-feedback-text" class="text-xs sm:text-sm text-slate-300 leading-relaxed mb-5"></p>

                <button type="button" id="quiz-next-btn"
                        class="w-full py-3.5 px-6 rounded-xl font-black text-sm text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 shadow-xl shadow-indigo-600/25 transition duration-150 flex items-center justify-center">
                    Próxima Pergunta <i class="fa-solid fa-arrow-right ml-2"></i>
                </button>
            </div>
        </div>
    </main>
</div>

<!-- Modal e Scripts -->
<?php require_once __DIR__ . '/includes/modal.php'; ?>
<script src="<?= BASE_URL ?>/assets/js/app.js"></script>
<script src="<?= BASE_URL ?>/assets/js/quiz.js"></script>
<?php require_once __DIR__ . '/includes/toast.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', () => {
    new ArenaQuizEngine({
        submitUrl: '<?= BASE_URL ?>/backend/quiz/submit_answer.php',
        questionDuration: 20
    });
});
</script>
</body>
</html>
