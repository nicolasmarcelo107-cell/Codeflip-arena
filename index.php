<?php
/**
 * Codeflip Arena - Página Inicial / Landing Page
 * Mobile-First com Tailwind CSS
 */

$pageTitle = 'Desafie sua Mente na Arena';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/bd.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

$pdo = getDB();

// Métricas da plataforma para exibição na Home
$totalPerguntas = 0;
$totalCategorias = 0;
$totalPartidas = 0;
$categoriasDestaque = [];

try {
    $totalPerguntas = (int)$pdo->query("SELECT COUNT(*) FROM perguntas WHERE ativo = 1")->fetchColumn();
    $totalCategorias = (int)$pdo->query("SELECT COUNT(*) FROM categorias WHERE ativo = 1")->fetchColumn();
    $totalPartidas = (int)$pdo->query("SELECT COUNT(*) FROM partidas")->fetchColumn();

    $stmtCat = $pdo->query("SELECT id, nome, slug, descricao, icone, cor FROM categorias WHERE ativo = 1 ORDER BY id ASC LIMIT 6");
    $categoriasDestaque = $stmtCat->fetchAll();
} catch (PDOException $e) {
    error_log("Erro ao carregar métricas da home: " . $e->getMessage());
}
?>

<main class="flex-1">
    <!-- Hero Section -->
    <section class="relative overflow-hidden py-12 sm:py-20 lg:py-28 px-4 sm:px-6 lg:px-8">
        <!-- Background Glows -->
        <div class="absolute top-1/4 left-1/2 -translate-x-1/2 w-96 h-96 bg-indigo-600/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute top-1/3 left-1/4 w-72 h-72 bg-purple-600/15 rounded-full blur-3xl pointer-events-none"></div>

        <div class="max-w-5xl mx-auto text-center relative z-10">
            <!-- Badge -->
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-indigo-950/80 border border-indigo-500/30 text-indigo-300 text-xs font-semibold mb-6 shadow-inner">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                <span>Temporada Oficial Aberta • Quizzes de Diversos Temas</span>
            </div>

            <!-- Título Principal -->
            <h1 class="text-4xl sm:text-6xl lg:text-7xl font-black tracking-tight text-white leading-tight sm:leading-none mb-6">
                Teste seu conhecimento na <br class="hidden sm:inline">
                <span class="bg-gradient-to-r from-indigo-400 via-purple-400 to-pink-500 bg-clip-text text-transparent">Codeflip Arena</span>
            </h1>

            <p class="max-w-2xl mx-auto text-base sm:text-lg text-slate-400 mb-10 leading-relaxed">
                Participe de batalhas de quiz sobre Programação, Banco de Dados, Cultura Geek, Tecnologia e Conhecimentos Gerais. Ganhe XP, suba de nível e lidere o placar!
            </p>

            <!-- Ações Principais -->
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 max-w-md mx-auto mb-16">
                <a href="<?= BASE_URL ?>/arena.php" class="w-full sm:w-auto inline-flex items-center justify-center gap-3 px-8 py-4 rounded-2xl font-black text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 shadow-xl shadow-indigo-600/30 hover:scale-105 transition duration-200">
                    <i class="fa-solid fa-play"></i> Jogar Agora
                </a>
                <a href="<?= BASE_URL ?>/ranking.php" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-4 rounded-2xl font-bold text-slate-300 bg-slate-900/80 hover:bg-slate-800 border border-slate-800 hover:text-white transition">
                    <i class="fa-solid fa-trophy text-yellow-400"></i> Ver Ranking
                </a>
            </div>

            <!-- Métricas em Cards -->
            <div class="grid grid-cols-3 gap-3 sm:gap-6 max-w-3xl mx-auto">
                <div class="p-4 sm:p-6 rounded-2xl bg-slate-900/50 border border-slate-800/80 backdrop-blur-md">
                    <div class="text-2xl sm:text-4xl font-black text-indigo-400 mb-1"><?= $totalPerguntas ?>+</div>
                    <div class="text-xs sm:text-sm text-slate-400 font-medium">Perguntas Ativas</div>
                </div>
                <div class="p-4 sm:p-6 rounded-2xl bg-slate-900/50 border border-slate-800/80 backdrop-blur-md">
                    <div class="text-2xl sm:text-4xl font-black text-purple-400 mb-1"><?= $totalCategorias ?></div>
                    <div class="text-xs sm:text-sm text-slate-400 font-medium">Categorias Temáticas</div>
                </div>
                <div class="p-4 sm:p-6 rounded-2xl bg-slate-900/50 border border-slate-800/80 backdrop-blur-md">
                    <div class="text-2xl sm:text-4xl font-black text-emerald-400 mb-1"><?= $totalPartidas ?>+</div>
                    <div class="text-xs sm:text-sm text-slate-400 font-medium">Partidas Jogadas</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Seção de Categorias em Destaque -->
    <section class="py-12 sm:py-16 px-4 sm:px-6 lg:px-8 border-t border-slate-900 bg-slate-950/40">
        <div class="max-w-7xl mx-auto">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-10 gap-4">
                <div>
                    <h2 class="text-2xl sm:text-3xl font-black text-white tracking-tight">Categorias da Arena</h2>
                    <p class="text-sm text-slate-400 mt-1">Escolha seu tema preferido e teste sua velocidade de raciocínio.</p>
                </div>
                <a href="<?= BASE_URL ?>/arena.php" class="text-sm font-bold text-indigo-400 hover:text-indigo-300 inline-flex items-center gap-1.5 transition">
                    Ver todas as categorias <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($categoriasDestaque as $cat): ?>
                    <a href="<?= BASE_URL ?>/backend/quiz/start.php?categoria=<?= $cat['id'] ?>" class="group block p-6 rounded-2xl bg-slate-900/60 border border-slate-800/80 hover:border-indigo-500/50 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-indigo-500/10">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-tr <?= htmlspecialchars($cat['cor']) ?> flex items-center justify-center text-white text-xl mb-4 shadow-md group-hover:scale-110 transition-transform">
                            <i class="fa-solid <?= htmlspecialchars($cat['icone']) ?>"></i>
                        </div>
                        <h3 class="text-lg font-bold text-white group-hover:text-indigo-400 transition mb-2">
                            <?= htmlspecialchars($cat['nome']) ?>
                        </h3>
                        <p class="text-xs text-slate-400 leading-relaxed mb-4 line-clamp-2">
                            <?= htmlspecialchars($cat['descricao']) ?>
                        </p>
                        <div class="flex items-center justify-between text-xs font-semibold text-indigo-400 group-hover:translate-x-1 transition-transform">
                            <span>Jogar este tema</span>
                            <i class="fa-solid fa-chevron-right"></i>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Como Funciona -->
    <section class="py-12 sm:py-16 px-4 sm:px-6 lg:px-8 border-t border-slate-900">
        <div class="max-w-7xl mx-auto">
            <div class="text-center max-w-2xl mx-auto mb-12">
                <h2 class="text-2xl sm:text-3xl font-black text-white">Como Funciona a Arena?</h2>
                <p class="text-sm text-slate-400 mt-2">Simples, rápido e competitivo para desafiar amigos ou jogar solo.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-8">
                <div class="p-6 rounded-2xl bg-slate-900/40 border border-slate-800/60 text-center">
                    <div class="w-14 h-14 mx-auto rounded-2xl bg-indigo-600/20 border border-indigo-500/30 flex items-center justify-center text-indigo-400 text-2xl mb-4">
                        <i class="fa-solid fa-crosshairs"></i>
                    </div>
                    <h3 class="text-base font-bold text-white mb-2">1. Escolha o Tema</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Selecione seu nicho preferido ou enfrente a roleta mista com perguntas surpresa de todos os assuntos.
                    </p>
                </div>

                <div class="p-6 rounded-2xl bg-slate-900/40 border border-slate-800/60 text-center">
                    <div class="w-14 h-14 mx-auto rounded-2xl bg-purple-600/20 border border-purple-500/30 flex items-center justify-center text-purple-400 text-2xl mb-4">
                        <i class="fa-solid fa-bolt"></i>
                    </div>
                    <h3 class="text-base font-bold text-white mb-2">2. Responda Rápido</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Cada pergunta possui um cronômetro. Quanto mais rápido responder e manter sequências de acertos (streaks), mais pontos acumula.
                    </p>
                </div>

                <div class="p-6 rounded-2xl bg-slate-900/40 border border-slate-800/60 text-center">
                    <div class="w-14 h-14 mx-auto rounded-2xl bg-emerald-600/20 border border-emerald-500/30 flex items-center justify-center text-emerald-400 text-2xl mb-4">
                        <i class="fa-solid fa-crown"></i>
                    </div>
                    <h3 class="text-base font-bold text-white mb-2">3. Suba de Nível</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Ganhe experiência (XP), aumente o nível do seu guerreiro no perfil e dispute as primeiras posições do ranking global.
                    </p>
                </div>
            </div>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
