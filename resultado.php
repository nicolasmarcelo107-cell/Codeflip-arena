<?php
/**
 * Codeflip Arena - Resultado da Partida de Quiz
 * Mobile-First com Tailwind CSS e revisão de acertos/erros
 */

$pageTitle = 'Resultado da Partida';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/backend/helpers/auth_helper.php';

requireAuth();

$resultado = $_SESSION['last_game_result'] ?? null;
if (!$resultado) {
    header('Location: ' . BASE_URL . '/arena.php');
    exit;
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

$acertos = $resultado['correct'];
$total = $resultado['total'];
$porcentagem = ($total > 0) ? round(($acertos / $total) * 100) : 0;
?>

<main class="flex-1 py-8 sm:py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-3xl mx-auto">
        <!-- Card de Celebração Principal -->
        <div class="p-6 sm:p-10 rounded-3xl bg-slate-900/90 border border-slate-800 shadow-2xl text-center mb-8 relative overflow-hidden backdrop-blur-md">
            <div class="absolute -top-10 -right-10 w-48 h-48 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>

            <!-- Ícone e Título -->
            <div class="w-20 h-20 mx-auto rounded-3xl bg-gradient-to-tr from-amber-400 to-orange-500 flex items-center justify-center text-slate-950 text-4xl mb-4 shadow-xl shadow-amber-500/25 animate-bounce">
                <i class="fa-solid fa-trophy"></i>
            </div>

            <h1 class="text-3xl sm:text-4xl font-black text-white tracking-tight">Partida Concluída!</h1>
            <p class="text-sm text-slate-400 mt-1">
                Você finalizou o quiz de <strong class="text-indigo-400"><?= htmlspecialchars($resultado['categoria_nome']) ?></strong>
            </p>

            <!-- Se subiu de nível, exibe alerta comemorativo -->
            <?php if (!empty($resultado['level_up'])): ?>
                <div class="mt-6 inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-purple-600 to-indigo-600 text-white font-extrabold text-sm shadow-lg shadow-purple-600/30 animate-pulse">
                    <i class="fa-solid fa-star text-yellow-300"></i> LEVEL UP! Você alcançou o Nível <?= $resultado['current_level'] ?>!
                </div>
            <?php endif; ?>

            <!-- Estatísticas da Rodada em Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mt-8">
                <div class="p-4 rounded-2xl bg-slate-950/80 border border-slate-800/80">
                    <div class="text-2xl sm:text-3xl font-black text-indigo-400">+<?= $resultado['score'] ?></div>
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mt-1">XP Ganho</div>
                </div>
                <div class="p-4 rounded-2xl bg-slate-950/80 border border-slate-800/80">
                    <div class="text-2xl sm:text-3xl font-black text-emerald-400"><?= $acertos ?>/<?= $total ?></div>
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mt-1">Acertos</div>
                </div>
                <div class="p-4 rounded-2xl bg-slate-950/80 border border-slate-800/80">
                    <div class="text-2xl sm:text-3xl font-black text-purple-400"><?= $porcentagem ?>%</div>
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mt-1">Precisão</div>
                </div>
                <div class="p-4 rounded-2xl bg-slate-950/80 border border-slate-800/80">
                    <div class="text-2xl sm:text-3xl font-black text-amber-400"><?= $resultado['max_streak'] ?>x</div>
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mt-1">Maior Combo</div>
                </div>
            </div>

            <!-- Botões de Ação Mobile-First -->
            <div class="flex flex-col sm:flex-row items-center justify-center gap-3 mt-8">
                <a href="<?= BASE_URL ?>/arena.php" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl font-bold text-sm text-white bg-indigo-600 hover:bg-indigo-500 shadow-lg shadow-indigo-600/30 transition">
                    <i class="fa-solid fa-rotate-right"></i> Jogar Outro Quiz
                </a>
                <a href="<?= BASE_URL ?>/ranking.php" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl font-bold text-sm text-slate-300 bg-slate-800 hover:bg-slate-700 transition">
                    <i class="fa-solid fa-trophy text-yellow-400"></i> Ver Ranking
                </a>
                <a href="<?= BASE_URL ?>/historico.php" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl font-bold text-sm text-slate-400 hover:text-white transition">
                    <i class="fa-solid fa-clock-rotate-left"></i> Histórico
                </a>
            </div>
        </div>

        <!-- Revisão Detalhada das Respostas -->
        <?php if (!empty($resultado['history'])): ?>
            <div class="space-y-4">
                <h2 class="text-xl font-black text-white tracking-tight flex items-center gap-2">
                    <i class="fa-solid fa-list-check text-indigo-400"></i> Revisão das Questões
                </h2>

                <?php foreach ($resultado['history'] as $idx => $item): ?>
                    <div class="p-5 rounded-2xl bg-slate-900/60 border <?= $item['is_correta'] ? 'border-emerald-500/30' : 'border-rose-500/30' ?>">
                        <div class="flex items-start justify-between gap-3 mb-2">
                            <span class="text-xs font-bold text-slate-400">#<?= $idx + 1 ?></span>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold <?= $item['is_correta'] ? 'bg-emerald-500/20 text-emerald-300' : 'bg-rose-500/20 text-rose-300' ?>">
                                <?= $item['is_correta'] ? '<i class="fa-solid fa-check mr-1"></i> Acertou (+ ' . $item['pontos_ganhos'] . ' pts)' : '<i class="fa-solid fa-xmark mr-1"></i> Errou' ?>
                            </span>
                        </div>

                        <h3 class="text-sm sm:text-base font-bold text-white mb-3">
                            <?= htmlspecialchars($item['pergunta']) ?>
                        </h3>

                        <div class="space-y-1.5 text-xs">
                            <div class="flex items-center gap-2 text-slate-300">
                                <span class="font-bold text-slate-400">Sua Resposta:</span>
                                <span class="<?= $item['is_correta'] ? 'text-emerald-400 font-semibold' : 'text-rose-400 font-semibold' ?>">
                                    <?= htmlspecialchars($item['opcao_selecionada']) ?>
                                </span>
                            </div>

                            <?php if (!$item['is_correta']): ?>
                                <div class="flex items-center gap-2 text-slate-300">
                                    <span class="font-bold text-slate-400">Resposta Correta:</span>
                                    <span class="text-emerald-400 font-semibold">
                                        <?= htmlspecialchars($item['opcao_correta']) ?>
                                    </span>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($item['explicacao'])): ?>
                                <div class="mt-2 pt-2 border-t border-slate-800 text-[11px] text-slate-400 leading-relaxed italic">
                                    <strong>Explicação:</strong> <?= htmlspecialchars($item['explicacao']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
    ArenaAudio.success();
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
