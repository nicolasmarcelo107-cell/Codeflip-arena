<?php
/**
 * Codeflip Arena - Histórico de Partidas e Logs de Auditoria
 * Requisito CORE: O sistema deve ter uma página de históricos e manter sempre os logs de qualquer alteração feita por qualquer usuário, para auditorias futuras.
 */

$pageTitle = 'Histórico & Logs de Auditoria';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/bd.php';
require_once __DIR__ . '/backend/helpers/auth_helper.php';

requireAuth();

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

$pdo = getDB();
$user = currentUser();
$isAdmin = isAdmin();

$tab = $_GET['tab'] ?? 'logs';
$filtroAcao = trim($_GET['acao'] ?? '');

// 1. Busca Histórico de Partidas
$partidas = [];
try {
    if ($isAdmin) {
        $stmtP = $pdo->query("
            SELECT p.*, c.nome as categoria_nome, c.icone as categoria_icone, u.nome as usuario_nome
            FROM partidas p
            JOIN categorias c ON p.categoria_id = c.id
            JOIN users u ON p.usuario_id = u.id
            ORDER BY p.created_at DESC
            LIMIT 50
        ");
    } else {
        $stmtP = $pdo->prepare("
            SELECT p.*, c.nome as categoria_nome, c.icone as categoria_icone, :usuario_nome as usuario_nome
            FROM partidas p
            JOIN categorias c ON p.categoria_id = c.id
            WHERE p.usuario_id = :u_id
            ORDER BY p.created_at DESC
            LIMIT 50
        ");
        $stmtP->execute([
            ':u_id'         => $user['id'],
            ':usuario_nome' => $user['nome']
        ]);
    }
    $partidas = $stmtP->fetchAll();
} catch (PDOException $e) {
    error_log("Erro ao carregar partidas: " . $e->getMessage());
}

// 2. Busca Logs de Auditoria
$logs = [];
$acoesDisponiveis = [];

try {
    // Lista de ações distintas para o filtro
    $stmtAcoes = $pdo->query("SELECT DISTINCT acao FROM logs ORDER BY acao ASC");
    $acoesDisponiveis = $stmtAcoes->fetchAll(PDO::FETCH_COLUMN);

    if ($isAdmin) {
        // Administrador visualiza todo o rastro de auditoria do sistema
        if (!empty($filtroAcao)) {
            $stmtL = $pdo->prepare("SELECT * FROM logs WHERE acao = :acao ORDER BY created_at DESC LIMIT 100");
            $stmtL->execute([':acao' => $filtroAcao]);
        } else {
            $stmtL = $pdo->query("SELECT * FROM logs ORDER BY created_at DESC LIMIT 100");
        }
    } else {
        // Usuário comum visualiza suas próprias atividades e eventos vinculados
        if (!empty($filtroAcao)) {
            $stmtL = $pdo->prepare("SELECT * FROM logs WHERE usuario_id = :u_id AND acao = :acao ORDER BY created_at DESC LIMIT 100");
            $stmtL->execute([':u_id' => $user['id'], ':acao' => $filtroAcao]);
        } else {
            $stmtL = $pdo->prepare("SELECT * FROM logs WHERE usuario_id = :u_id ORDER BY created_at DESC LIMIT 100");
            $stmtL->execute([':u_id' => $user['id']]);
        }
    }
    $logs = $stmtL->fetchAll();
} catch (PDOException $e) {
    error_log("Erro ao carregar logs de auditoria: " . $e->getMessage());
}
?>

<main class="flex-1 py-8 sm:py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-6xl mx-auto">
        <!-- Header da Página -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-950/80 border border-indigo-500/30 text-indigo-300 text-xs font-semibold mb-2">
                    <i class="fa-solid fa-shield-halved text-indigo-400"></i> Rastreabilidade & Auditoria
                </div>
                <h1 class="text-3xl sm:text-4xl font-black text-white tracking-tight">Históricos do Sistema</h1>
                <p class="text-sm text-slate-400 mt-1">
                    <?= $isAdmin ? 'Visualização global de atividades e trilha de auditoria para compliance.' : 'Seu histórico pessoal de partidas, pontuações e registros de segurança.' ?>
                </p>
            </div>

            <!-- Abas de Navegação Mobile-First -->
            <div class="flex items-center bg-slate-900 p-1.5 rounded-xl border border-slate-800 self-start sm:self-auto">
                <a href="<?= BASE_URL ?>/historico.php?tab=logs"
                   class="px-4 py-2 rounded-lg text-xs font-bold transition flex items-center gap-2 <?= $tab === 'logs' ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-400 hover:text-white' ?>">
                    <i class="fa-solid fa-list-check"></i> Trilha de Auditoria
                </a>
                <a href="<?= BASE_URL ?>/historico.php?tab=partidas"
                   class="px-4 py-2 rounded-lg text-xs font-bold transition flex items-center gap-2 <?= $tab === 'partidas' ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-400 hover:text-white' ?>">
                    <i class="fa-solid fa-gamepad"></i> Partidas Jogadas
                </a>
            </div>
        </div>

        <?php if ($tab === 'logs'): ?>
            <!-- ABA: TRILHA DE AUDITORIA E LOGS -->
            <div class="bg-slate-900/80 border border-slate-800 rounded-2xl overflow-hidden shadow-2xl backdrop-blur-md">
                <!-- Barra de Filtros -->
                <div class="p-4 sm:p-5 border-b border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-database text-indigo-400"></i>
                        <span class="text-xs font-bold text-white uppercase tracking-wider">
                            Registros de Auditoria (<?= count($logs) ?> encontrados)
                        </span>
                    </div>

                    <form method="GET" action="<?= BASE_URL ?>/historico.php" class="flex items-center gap-2 w-full sm:w-auto">
                        <input type="hidden" name="tab" value="logs">
                        <select name="acao" onchange="this.form.submit()"
                                class="w-full sm:w-56 py-2 px-3 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white focus:outline-none focus:border-indigo-500">
                            <option value="">Todas as Ações</option>
                            <?php foreach ($acoesDisponiveis as $ac): ?>
                                <option value="<?= htmlspecialchars($ac) ?>" <?= $filtroAcao === $ac ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($ac) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (!empty($filtroAcao)): ?>
                            <a href="<?= BASE_URL ?>/historico.php?tab=logs" class="p-2 text-slate-400 hover:text-white text-xs" title="Limpar Filtro">
                                <i class="fa-solid fa-xmark"></i>
                            </a>
                        <?php endif; ?>
                    </form>
                </div>

                <?php if (empty($logs)): ?>
                    <div class="text-center py-16 text-slate-500">
                        <i class="fa-solid fa-folder-open text-3xl mb-2"></i>
                        <p class="text-sm">Nenhum registro de log encontrado para este filtro.</p>
                    </div>
                <?php else: ?>
                    <!-- Tabela Desktop -->
                    <div class="hidden md:block overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="bg-slate-950/60 text-slate-400 uppercase tracking-wider border-b border-slate-800">
                                    <th class="py-3 px-4 w-36">Data / Hora</th>
                                    <th class="py-3 px-4">Usuário</th>
                                    <th class="py-3 px-4">Ação</th>
                                    <th class="py-3 px-4">Detalhes</th>
                                    <th class="py-3 px-4">IP</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800/60 font-mono">
                                <?php foreach ($logs as $l): 
                                    $acao = $l['acao'];
                                    $badgeClass = 'bg-slate-800 text-slate-300';
                                    if (strpos($acao, 'SENHA') !== false || strpos($acao, 'EXCLU') !== false) {
                                        $badgeClass = 'bg-rose-950/80 text-rose-300 border border-rose-500/30';
                                    } elseif (strpos($acao, 'LOGIN') !== false || strpos($acao, 'CADASTRO') !== false) {
                                        $badgeClass = 'bg-emerald-950/80 text-emerald-300 border border-emerald-500/30';
                                    } elseif (strpos($acao, 'QUIZ') !== false) {
                                        $badgeClass = 'bg-indigo-950/80 text-indigo-300 border border-indigo-500/30';
                                    } elseif (strpos($acao, 'EDIT') !== false || strpos($acao, 'ATUALIZ') !== false) {
                                        $badgeClass = 'bg-amber-950/80 text-amber-300 border border-amber-500/30';
                                    }
                                ?>
                                    <tr class="hover:bg-slate-800/40 transition">
                                        <td class="py-3 px-4 text-slate-400 whitespace-nowrap">
                                            <?= date('d/m/Y H:i:s', strtotime($l['created_at'])) ?>
                                        </td>
                                        <td class="py-3 px-4 text-white font-sans font-semibold">
                                            <?= htmlspecialchars($l['usuario_nome'] ?? 'Visitante/Anônimo') ?>
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase <?= $badgeClass ?>">
                                                <?= htmlspecialchars($l['acao']) ?>
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-slate-300 font-sans max-w-md truncate" title="<?= htmlspecialchars($l['detalhes']) ?>">
                                            <?= htmlspecialchars($l['detalhes'] ?: '—') ?>
                                        </td>
                                        <td class="py-3 px-4 text-slate-500 text-[11px]">
                                            <?= htmlspecialchars($l['ip_address'] ?: '127.0.0.1') ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Cards Mobile (Mobile-First) -->
                    <div class="md:hidden divide-y divide-slate-800/60 p-4 space-y-4">
                        <?php foreach ($logs as $l): ?>
                            <div class="pt-3 first:pt-0">
                                <div class="flex items-center justify-between text-xs mb-1">
                                    <span class="font-bold text-white"><?= htmlspecialchars($l['usuario_nome'] ?? 'Anônimo') ?></span>
                                    <span class="text-[10px] text-slate-500"><?= date('d/m/Y H:i', strtotime($l['created_at'])) ?></span>
                                </div>
                                <div class="mb-2">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-800 text-indigo-300">
                                        <?= htmlspecialchars($l['acao']) ?>
                                    </span>
                                </div>
                                <p class="text-xs text-slate-300 leading-relaxed mb-1">
                                    <?= htmlspecialchars($l['detalhes'] ?: 'Sem detalhes') ?>
                                </p>
                                <span class="text-[10px] text-slate-500 font-mono">IP: <?= htmlspecialchars($l['ip_address'] ?: '127.0.0.1') ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

        <?php else: ?>
            <!-- ABA: PARTIDAS JOGADAS -->
            <div class="bg-slate-900/80 border border-slate-800 rounded-2xl overflow-hidden shadow-2xl backdrop-blur-md">
                <div class="p-4 sm:p-5 border-b border-slate-800 flex items-center justify-between">
                    <span class="text-xs font-bold text-white uppercase tracking-wider">
                        Histórico de Partidas Realizadas (<?= count($partidas) ?>)
                    </span>
                </div>

                <?php if (empty($partidas)): ?>
                    <div class="text-center py-16 text-slate-500">
                        <i class="fa-solid fa-gamepad text-3xl mb-2"></i>
                        <p class="text-sm">Nenhuma partida registrada até o momento.</p>
                        <a href="<?= BASE_URL ?>/arena.php" class="inline-block mt-3 px-4 py-2 bg-indigo-600 text-white rounded-xl text-xs font-bold">
                            Jogar Primeiro Quiz
                        </a>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="bg-slate-950/60 text-slate-400 uppercase tracking-wider border-b border-slate-800">
                                    <th class="py-3 px-4">Data</th>
                                    <?php if ($isAdmin): ?><th class="py-3 px-4">Jogador</th><?php endif; ?>
                                    <th class="py-3 px-4">Tema</th>
                                    <th class="py-3 px-4 text-center">Acertos</th>
                                    <th class="py-3 px-4 text-center">Tempo</th>
                                    <th class="py-3 px-4 text-right">Pontos</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800/60">
                                <?php foreach ($partidas as $p): ?>
                                    <tr class="hover:bg-slate-800/40 transition">
                                        <td class="py-3 px-4 text-slate-400 whitespace-nowrap">
                                            <?= date('d/m/Y H:i', strtotime($p['created_at'])) ?>
                                        </td>
                                        <?php if ($isAdmin): ?>
                                            <td class="py-3 px-4 font-bold text-white">
                                                <?= htmlspecialchars($p['usuario_nome']) ?>
                                            </td>
                                        <?php endif; ?>
                                        <td class="py-3 px-4 font-bold text-white">
                                            <i class="fa-solid <?= htmlspecialchars($p['categoria_icone'] ?? 'fa-gamepad') ?> text-indigo-400 mr-1.5"></i>
                                            <?= htmlspecialchars($p['categoria_nome']) ?>
                                        </td>
                                        <td class="py-3 px-4 text-center font-bold text-emerald-400">
                                            <?= $p['acertos'] ?> / <?= $p['total_perguntas'] ?>
                                        </td>
                                        <td class="py-3 px-4 text-center text-slate-400 font-mono">
                                            <?= $p['tempo_gasto_segundos'] ?>s
                                        </td>
                                        <td class="py-3 px-4 text-right font-black text-indigo-300">
                                            +<?= $p['pontuacao_total'] ?> XP
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
