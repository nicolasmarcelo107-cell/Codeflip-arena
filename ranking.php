<?php
/**
 * Codeflip Arena - Tabela de Classificação / Ranking Geral
 * Mobile-First com Tailwind CSS e ordenação por XP acumulado
 */

$pageTitle = 'Ranking dos Campeões';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/bd.php';
require_once __DIR__ . '/backend/helpers/auth_helper.php';

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

$pdo = getDB();
$currentUser = currentUser();
$jogadores = [];

try {
    $stmt = $pdo->query("
        SELECT id, nome, email, avatar, xp, nivel, partidas_jogadas, acertos_total
        FROM users
        ORDER BY xp DESC, acertos_total DESC
        LIMIT 50
    ");
    $jogadores = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Erro ao carregar ranking: " . $e->getMessage());
}
?>

<main class="flex-1 py-8 sm:py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-5xl mx-auto">
        <!-- Header do Ranking -->
        <div class="text-center max-w-xl mx-auto mb-10">
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-yellow-500/10 border border-yellow-500/20 text-yellow-400 text-xs font-semibold mb-3">
                <i class="fa-solid fa-crown"></i> Hall da Fama Oficial
            </div>
            <h1 class="text-3xl sm:text-4xl font-black text-white tracking-tight">Ranking da Arena</h1>
            <p class="text-sm text-slate-400 mt-2">
                Os jogadores mais rápidos e sábios que dominam os quizzes da Codeflip Arena.
            </p>
        </div>

        <!-- Pódio Top 3 (Mobile e Desktop) -->
        <?php if (count($jogadores) >= 3): 
            $top1 = $jogadores[0];
            $top2 = $jogadores[1];
            $top3 = $jogadores[2];
        ?>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-10 items-end">
                <!-- 2º Lugar -->
                <div class="order-2 sm:order-1 p-6 rounded-2xl bg-slate-900/60 border border-slate-700 text-center relative overflow-hidden backdrop-blur-md">
                    <div class="w-12 h-12 mx-auto rounded-xl bg-slate-800 border border-slate-600 flex items-center justify-center text-slate-300 text-xl font-black mb-3">
                        2º
                    </div>
                    <div class="w-16 h-16 mx-auto rounded-2xl bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-300 text-2xl mb-3">
                        <i class="fa-solid fa-<?= htmlspecialchars($top2['avatar'] ?? 'user-astronaut') ?>"></i>
                    </div>
                    <h3 class="font-bold text-white text-base truncate"><?= htmlspecialchars($top2['nome']) ?></h3>
                    <div class="text-xs text-indigo-400 font-bold mt-1">Nível <?= $top2['nivel'] ?></div>
                    <div class="text-lg font-black text-slate-200 mt-2"><?= number_format($top2['xp'], 0, ',', '.') ?> XP</div>
                </div>

                <!-- 1º Lugar (Destaque Maior) -->
                <div class="order-1 sm:order-2 p-8 rounded-3xl bg-gradient-to-b from-yellow-950/40 via-slate-900/90 to-slate-900 border-2 border-yellow-500/40 text-center relative overflow-hidden shadow-2xl shadow-yellow-500/10 -mt-2">
                    <div class="w-14 h-14 mx-auto rounded-2xl bg-gradient-to-tr from-yellow-400 to-amber-500 flex items-center justify-center text-slate-950 text-2xl font-black mb-3 shadow-lg shadow-yellow-500/30 animate-pulse">
                        <i class="fa-solid fa-crown"></i>
                    </div>
                    <div class="w-20 h-20 mx-auto rounded-3xl bg-slate-800 border-2 border-yellow-500/50 flex items-center justify-center text-yellow-400 text-3xl mb-3">
                        <i class="fa-solid fa-<?= htmlspecialchars($top1['avatar'] ?? 'crown') ?>"></i>
                    </div>
                    <h3 class="font-black text-white text-lg truncate"><?= htmlspecialchars($top1['nome']) ?></h3>
                    <div class="inline-block px-3 py-0.5 rounded-full bg-yellow-500/20 text-yellow-300 text-xs font-bold mt-1">
                        Campeão • Nível <?= $top1['nivel'] ?>
                    </div>
                    <div class="text-2xl font-black text-yellow-400 mt-3"><?= number_format($top1['xp'], 0, ',', '.') ?> XP</div>
                </div>

                <!-- 3º Lugar -->
                <div class="order-3 p-6 rounded-2xl bg-slate-900/60 border border-slate-700 text-center relative overflow-hidden backdrop-blur-md">
                    <div class="w-12 h-12 mx-auto rounded-xl bg-amber-950/40 border border-amber-700/50 flex items-center justify-center text-amber-500 text-xl font-black mb-3">
                        3º
                    </div>
                    <div class="w-16 h-16 mx-auto rounded-2xl bg-slate-800 border border-slate-700 flex items-center justify-center text-amber-500 text-2xl mb-3">
                        <i class="fa-solid fa-<?= htmlspecialchars($top3['avatar'] ?? 'user-astronaut') ?>"></i>
                    </div>
                    <h3 class="font-bold text-white text-base truncate"><?= htmlspecialchars($top3['nome']) ?></h3>
                    <div class="text-xs text-indigo-400 font-bold mt-1">Nível <?= $top3['nivel'] ?></div>
                    <div class="text-lg font-black text-slate-200 mt-2"><?= number_format($top3['xp'], 0, ',', '.') ?> XP</div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Tabela Completa do Ranking -->
        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl overflow-hidden shadow-2xl backdrop-blur-md">
            <div class="p-4 sm:p-5 border-b border-slate-800 flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Top Competidores</span>
                <span class="text-xs text-slate-500"><?= count($jogadores) ?> jogadores listados</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="bg-slate-950/60 text-slate-400 text-xs uppercase tracking-wider border-b border-slate-800">
                            <th class="py-3 px-4 text-center w-16">Pos</th>
                            <th class="py-3 px-4">Guerreiro</th>
                            <th class="py-3 px-4 text-center">Nível</th>
                            <th class="py-3 px-4 text-center hidden sm:table-cell">Partidas</th>
                            <th class="py-3 px-4 text-center hidden sm:table-cell">Acertos</th>
                            <th class="py-3 px-4 text-right">XP Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        <?php foreach ($jogadores as $idx => $j): 
                            $pos = $idx + 1;
                            $isMe = ($currentUser && $currentUser['id'] == $j['id']);
                        ?>
                            <tr class="hover:bg-slate-800/40 transition <?= $isMe ? 'bg-indigo-950/30 font-semibold' : '' ?>">
                                <td class="py-3.5 px-4 text-center font-bold">
                                    <?php if ($pos === 1): ?>
                                        <span class="text-yellow-400"><i class="fa-solid fa-medal"></i> 1</span>
                                    <?php elseif ($pos === 2): ?>
                                        <span class="text-slate-300"><i class="fa-solid fa-medal"></i> 2</span>
                                    <?php elseif ($pos === 3): ?>
                                        <span class="text-amber-600"><i class="fa-solid fa-medal"></i> 3</span>
                                    <?php else: ?>
                                        <span class="text-slate-500"><?= $pos ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-slate-800 flex items-center justify-center text-indigo-400 shrink-0">
                                            <i class="fa-solid fa-<?= htmlspecialchars($j['avatar'] ?? 'user-astronaut') ?>"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <span class="text-white font-bold block truncate">
                                                <?= htmlspecialchars($j['nome']) ?>
                                                <?php if ($isMe): ?>
                                                    <span class="ml-1.5 px-2 py-0.5 rounded text-[10px] bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">Você</span>
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-slate-800 text-indigo-400 border border-slate-700/60">
                                        Lvl <?= $j['nivel'] ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-center text-slate-400 hidden sm:table-cell">
                                    <?= $j['partidas_jogadas'] ?>
                                </td>
                                <td class="py-3.5 px-4 text-center text-slate-400 hidden sm:table-cell">
                                    <?= $j['acertos_total'] ?>
                                </td>
                                <td class="py-3.5 px-4 text-right font-black text-indigo-300">
                                    <?= number_format($j['xp'], 0, ',', '.') ?> pts
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
