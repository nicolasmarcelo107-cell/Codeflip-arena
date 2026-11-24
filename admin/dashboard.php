<?php
/**
 * Codeflip Arena - Painel Administrativo Geral
 * Mobile-First com Tailwind CSS e métricas em tempo real
 */

$pageTitle = 'Painel Administrativo';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../bd.php';
require_once __DIR__ . '/../backend/helpers/auth_helper.php';

requireAdmin();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

$pdo = getDB();

$totUsers = 0;
$totCategories = 0;
$totQuestions = 0;
$totMatches = 0;
$recentLogs = [];

try {
    $totUsers = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $totCategories = (int)$pdo->query("SELECT COUNT(*) FROM categorias")->fetchColumn();
    $totQuestions = (int)$pdo->query("SELECT COUNT(*) FROM perguntas")->fetchColumn();
    $totMatches = (int)$pdo->query("SELECT COUNT(*) FROM partidas")->fetchColumn();

    $stmtLogs = $pdo->query("SELECT * FROM logs ORDER BY created_at DESC LIMIT 8");
    $recentLogs = $stmtLogs->fetchAll();
} catch (PDOException $e) {
    error_log("Erro no dashboard admin: " . $e->getMessage());
}
?>

<main class="flex-1 py-8 sm:py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-6xl mx-auto">
        <!-- Sub-navegação do Administrador -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-purple-950/80 border border-purple-500/30 text-purple-300 text-xs font-semibold mb-2">
                    <i class="fa-solid fa-shield-halved"></i> Central de Controle
                </div>
                <h1 class="text-3xl font-black text-white tracking-tight">Painel Administrativo</h1>
                <p class="text-sm text-slate-400 mt-1">Gerencie categorias, banco de perguntas, competidores e auditoria.</p>
            </div>

            <!-- Botões de Atalho Rápido -->
            <div class="flex flex-wrap items-center gap-2">
                <a href="<?= BASE_URL ?>/admin/categorias.php" class="px-3.5 py-2 rounded-xl text-xs font-bold bg-slate-900 hover:bg-slate-800 border border-slate-800 text-white transition">
                    <i class="fa-solid fa-folder-tree text-indigo-400 mr-1.5"></i> Categorias
                </a>
                <a href="<?= BASE_URL ?>/admin/perguntas.php" class="px-3.5 py-2 rounded-xl text-xs font-bold bg-slate-900 hover:bg-slate-800 border border-slate-800 text-white transition">
                    <i class="fa-solid fa-circle-question text-emerald-400 mr-1.5"></i> Perguntas
                </a>
                <a href="<?= BASE_URL ?>/admin/usuarios.php" class="px-3.5 py-2 rounded-xl text-xs font-bold bg-slate-900 hover:bg-slate-800 border border-slate-800 text-white transition">
                    <i class="fa-solid fa-users text-purple-400 mr-1.5"></i> Usuários
                </a>
                <a href="<?= BASE_URL ?>/historico.php?tab=logs" class="px-3.5 py-2 rounded-xl text-xs font-bold bg-purple-600 hover:bg-purple-500 text-white shadow-lg shadow-purple-600/25 transition">
                    <i class="fa-solid fa-shield-cat mr-1.5"></i> Auditoria
                </a>
            </div>
        </div>

        <!-- Cards de Métricas Principais -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-md">
                <div class="w-10 h-10 rounded-xl bg-purple-500/20 text-purple-400 flex items-center justify-center text-lg mb-3">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div class="text-2xl sm:text-3xl font-black text-white"><?= $totUsers ?></div>
                <div class="text-xs text-slate-400 font-medium mt-1">Usuários Registrados</div>
            </div>

            <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-md">
                <div class="w-10 h-10 rounded-xl bg-indigo-500/20 text-indigo-400 flex items-center justify-center text-lg mb-3">
                    <i class="fa-solid fa-layer-group"></i>
                </div>
                <div class="text-2xl sm:text-3xl font-black text-white"><?= $totCategories ?></div>
                <div class="text-xs text-slate-400 font-medium mt-1">Categorias Ativas</div>
            </div>

            <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-md">
                <div class="w-10 h-10 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-lg mb-3">
                    <i class="fa-solid fa-clipboard-question"></i>
                </div>
                <div class="text-2xl sm:text-3xl font-black text-white"><?= $totQuestions ?></div>
                <div class="text-xs text-slate-400 font-medium mt-1">Perguntas Cadastradas</div>
            </div>

            <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-md">
                <div class="w-10 h-10 rounded-xl bg-amber-500/20 text-amber-400 flex items-center justify-center text-lg mb-3">
                    <i class="fa-solid fa-trophy"></i>
                </div>
                <div class="text-2xl sm:text-3xl font-black text-white"><?= $totMatches ?></div>
                <div class="text-xs text-slate-400 font-medium mt-1">Partidas Disputadas</div>
            </div>
        </div>

        <!-- Seção de Atividades Recentes de Auditoria -->
        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl overflow-hidden shadow-2xl backdrop-blur-md">
            <div class="p-5 border-b border-slate-800 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-white flex items-center gap-2">
                        <i class="fa-solid fa-clock-rotate-left text-indigo-400"></i> Últimas Alterações e Ações (Auditoria)
                    </h2>
                    <p class="text-xs text-slate-400 mt-0.5">Logs de auditoria em tempo real gerados automaticamente pelo sistema.</p>
                </div>
                <a href="<?= BASE_URL ?>/historico.php?tab=logs" class="text-xs font-bold text-indigo-400 hover:text-indigo-300 transition">
                    Ver todos os logs <i class="fa-solid fa-arrow-right ml-1"></i>
                </a>
            </div>

            <div class="divide-y divide-slate-800/60">
                <?php if (empty($recentLogs)): ?>
                    <div class="text-center py-10 text-slate-500 text-xs">Nenhum log gravado ainda.</div>
                <?php else: ?>
                    <?php foreach ($recentLogs as $l): ?>
                        <div class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-slate-800/30 transition">
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-xl bg-slate-800 flex items-center justify-center text-indigo-400 shrink-0 text-xs">
                                    <i class="fa-solid fa-fingerprint"></i>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-bold text-white"><?= htmlspecialchars($l['usuario_nome'] ?? 'Anônimo') ?></span>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-800 text-slate-300">
                                            <?= htmlspecialchars($l['acao']) ?>
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-400 mt-1 leading-relaxed">
                                        <?= htmlspecialchars($l['detalhes'] ?: 'Sem descrição detalhada') ?>
                                    </p>
                                </div>
                            </div>
                            <div class="text-right shrink-0 text-[11px] text-slate-500 font-mono">
                                <?= date('d/m/Y H:i:s', strtotime($l['created_at'])) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
