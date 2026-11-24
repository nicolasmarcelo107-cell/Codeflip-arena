<?php
/**
 * Codeflip Arena - Seleção de Temas e Quizzes (A Arena)
 * Mobile-First com Tailwind CSS
 */

$pageTitle = 'Arena de Quizzes';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/bd.php';
require_once __DIR__ . '/backend/helpers/auth_helper.php';

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

$pdo = getDB();
$categorias = [];

try {
    $stmt = $pdo->query("
        SELECT c.*, COUNT(p.id) as total_perguntas
        FROM categorias c
        LEFT JOIN perguntas p ON c.id = p.categoria_id AND p.ativo = 1
        WHERE c.ativo = 1
        GROUP BY c.id
        ORDER BY c.nome ASC
    ");
    $categorias = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Erro ao carregar categorias: " . $e->getMessage());
}
?>

<main class="flex-1 py-8 sm:py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header da Página -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-950/80 border border-indigo-500/30 text-indigo-300 text-xs font-semibold mb-2">
                    <i class="fa-solid fa-gamepad text-indigo-400"></i> Escolha seu Desafio
                </div>
                <h1 class="text-3xl sm:text-4xl font-black text-white tracking-tight">Arena de Batalha</h1>
                <p class="text-sm text-slate-400 mt-1">Selecione uma categoria temática ou encare a roleta mista.</p>
            </div>

            <!-- Campo de Busca Rápida Mobile-First -->
            <div class="w-full sm:w-72">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </div>
                    <input type="text" id="filter-input" placeholder="Buscar categoria..."
                           class="w-full pl-10 pr-4 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 transition">
                </div>
            </div>
        </div>

        <!-- Card Especial: Mix de Todos os Temas -->
        <div class="mb-8 p-6 sm:p-8 rounded-3xl bg-gradient-to-r from-indigo-900/60 via-purple-900/50 to-pink-900/40 border border-indigo-500/30 relative overflow-hidden backdrop-blur-md">
            <div class="absolute right-0 top-0 translate-x-10 -translate-y-10 w-64 h-64 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>
            
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 relative z-10">
                <div class="flex items-start sm:items-center gap-4">
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-indigo-500 via-purple-500 to-pink-500 flex items-center justify-center text-white text-3xl shadow-xl shadow-indigo-500/30 shrink-0">
                        <i class="fa-solid fa-shuffle"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-xl sm:text-2xl font-black text-white">Roleta Mista (Todas as Categorias)</h2>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-pink-500/20 text-pink-300 border border-pink-500/30 uppercase">Especial</span>
                        </div>
                        <p class="text-xs sm:text-sm text-slate-300 mt-1 max-w-xl">
                            Perguntas aleatórias de tecnologia, programação, cultura geek, cinema e ciências reunidas em uma rodada imprevisível.
                        </p>
                    </div>
                </div>

                <a href="<?= BASE_URL ?>/backend/quiz/start.php?categoria=0" class="inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl font-black text-sm text-white bg-white/10 hover:bg-white/20 border border-white/20 shadow-lg hover:scale-105 transition">
                    <i class="fa-solid fa-play"></i> Jogar Mix
                </a>
            </div>
        </div>

        <!-- Grid de Categorias -->
        <div id="categories-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($categorias as $cat): ?>
                <div class="category-card p-6 rounded-2xl bg-slate-900/60 border border-slate-800/80 hover:border-indigo-500/50 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-indigo-500/10 flex flex-col justify-between"
                     data-name="<?= htmlspecialchars(strtolower($cat['nome'])) ?>">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-tr <?= htmlspecialchars($cat['cor']) ?> flex items-center justify-center text-white text-xl shadow-md">
                                <i class="fa-solid <?= htmlspecialchars($cat['icone']) ?>"></i>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-slate-800 text-slate-300 border border-slate-700/60">
                                <?= $cat['total_perguntas'] ?> questões
                            </span>
                        </div>

                        <h3 class="text-lg font-bold text-white mb-2"><?= htmlspecialchars($cat['nome']) ?></h3>
                        <p class="text-xs text-slate-400 leading-relaxed mb-6">
                            <?= htmlspecialchars($cat['descricao'] ?: 'Teste seus reflexos e conhecimentos neste tema emocionante.') ?>
                        </p>
                    </div>

                    <a href="<?= BASE_URL ?>/backend/quiz/start.php?categoria=<?= $cat['id'] ?>" class="w-full inline-flex items-center justify-center gap-2 py-3 px-4 rounded-xl font-bold text-xs text-white bg-slate-800 hover:bg-indigo-600 transition duration-200">
                        <i class="fa-solid fa-play text-[10px]"></i> Iniciar Quiz
                    </a>
                </div>
            <?php endforeach; ?>
        </div>

        <div id="no-results" class="hidden text-center py-16">
            <div class="w-16 h-16 mx-auto rounded-2xl bg-slate-900 flex items-center justify-center text-slate-600 text-2xl mb-3">
                <i class="fa-solid fa-magnifying-glass"></i>
            </div>
            <h3 class="text-lg font-bold text-white">Nenhuma categoria encontrada</h3>
            <p class="text-xs text-slate-400 mt-1">Tente buscar por outro termo.</p>
        </div>
    </div>
</main>

<script>
// Filtro instantâneo de categorias
document.getElementById('filter-input')?.addEventListener('input', function(e) {
    const term = e.target.value.toLowerCase().trim();
    const cards = document.querySelectorAll('.category-card');
    let visibleCount = 0;

    cards.forEach(card => {
        const name = card.getAttribute('data-name');
        if (name.includes(term)) {
            card.classList.remove('hidden');
            visibleCount++;
        } else {
            card.classList.add('hidden');
        }
    });

    const noResults = document.getElementById('no-results');
    if (visibleCount === 0) {
        noResults.classList.remove('hidden');
    } else {
        noResults.classList.add('hidden');
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
