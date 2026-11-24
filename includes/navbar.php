<?php
/**
 * Codeflip Arena - Barra de Navegação Responsiva (Mobile-First)
 */

$currentPage = basename($_SERVER['PHP_SELF'] ?? '');
$user = currentUser();
$csrfToken = generateCsrfToken();
?>
<nav class="sticky top-0 z-40 bg-slate-950/80 backdrop-blur-md border-b border-slate-800/80">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 sm:h-20">
            <!-- Logo & Marca -->
            <a href="<?= BASE_URL ?>/index.php" class="flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-purple-600 flex items-center justify-center text-white text-xl shadow-lg shadow-indigo-500/25 group-hover:scale-105 transition-transform duration-300">
                    <i class="fa-solid fa-gamepad"></i>
                </div>
                <div class="flex flex-col">
                    <span class="text-xl font-black tracking-tight text-white group-hover:text-indigo-400 transition">CODEFLIP</span>
                    <span class="text-[10px] font-bold tracking-widest text-indigo-400 -mt-1 uppercase">ARENA</span>
                </div>
            </a>

            <!-- Links Desktop -->
            <div class="hidden md:flex items-center gap-1">
                <a href="<?= BASE_URL ?>/index.php" class="px-3.5 py-2 rounded-xl text-sm font-semibold transition <?= $currentPage === 'index.php' ? 'text-white bg-slate-800/80' : 'text-slate-400 hover:text-white hover:bg-slate-900' ?>">
                    <i class="fa-solid fa-house mr-1.5 opacity-70"></i> Início
                </a>
                <a href="<?= BASE_URL ?>/arena.php" class="px-3.5 py-2 rounded-xl text-sm font-semibold transition <?= $currentPage === 'arena.php' ? 'text-white bg-slate-800/80' : 'text-slate-400 hover:text-white hover:bg-slate-900' ?>">
                    <i class="fa-solid fa-fire mr-1.5 opacity-70 text-amber-400"></i> Arena
                </a>
                <a href="<?= BASE_URL ?>/ranking.php" class="px-3.5 py-2 rounded-xl text-sm font-semibold transition <?= $currentPage === 'ranking.php' ? 'text-white bg-slate-800/80' : 'text-slate-400 hover:text-white hover:bg-slate-900' ?>">
                    <i class="fa-solid fa-trophy mr-1.5 opacity-70 text-yellow-400"></i> Ranking
                </a>
                <a href="<?= BASE_URL ?>/historico.php" class="px-3.5 py-2 rounded-xl text-sm font-semibold transition <?= $currentPage === 'historico.php' ? 'text-white bg-slate-800/80' : 'text-slate-400 hover:text-white hover:bg-slate-900' ?>">
                    <i class="fa-solid fa-clock-rotate-left mr-1.5 opacity-70 text-indigo-400"></i> Histórico & Auditoria
                </a>
                <?php if (isAdmin()): ?>
                    <a href="<?= BASE_URL ?>/admin/dashboard.php" class="px-3.5 py-2 rounded-xl text-sm font-semibold text-purple-400 hover:text-purple-300 hover:bg-purple-950/40 transition border border-purple-500/20">
                        <i class="fa-solid fa-shield-halved mr-1.5"></i> Admin
                    </a>
                <?php endif; ?>
            </div>

            <!-- Área de Usuário / Login (Desktop) -->
            <div class="hidden md:flex items-center gap-3">
                <?php if ($user): ?>
                    <a href="<?= BASE_URL ?>/perfil.php" class="flex items-center gap-3 p-1.5 pr-4 rounded-xl bg-slate-900/90 hover:bg-slate-800 border border-slate-800 transition group">
                        <div class="w-8 h-8 rounded-lg bg-indigo-600/30 border border-indigo-500/40 flex items-center justify-center text-indigo-400">
                            <i class="fa-solid fa-<?= htmlspecialchars($user['avatar'] ?? 'user-astronaut') ?>"></i>
                        </div>
                        <div class="flex flex-col text-left">
                            <span class="text-xs font-bold text-white group-hover:text-indigo-300 transition leading-tight"><?= htmlspecialchars($user['nome']) ?></span>
                            <span class="text-[10px] text-slate-400">Nível <?= $user['nivel'] ?? 1 ?> • <?= $user['xp'] ?? 0 ?> XP</span>
                        </div>
                    </a>
                    <a href="<?= BASE_URL ?>/backend/auth/logout.php" data-confirm="Deseja realmente sair da sua conta?" data-title="Encerrar Sessão" class="w-9 h-9 rounded-xl bg-slate-900 hover:bg-rose-950/60 text-slate-400 hover:text-rose-400 border border-slate-800 hover:border-rose-500/30 flex items-center justify-center transition" title="Sair da Conta">
                        <i class="fa-solid fa-right-from-bracket"></i>
                    </a>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>/login.php" class="px-4 py-2 text-sm font-semibold text-slate-300 hover:text-white transition">
                        Entrar
                    </a>
                    <a href="<?= BASE_URL ?>/register.php" class="px-4 py-2 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-500 rounded-xl shadow-lg shadow-indigo-600/25 transition">
                        Criar Conta
                    </a>
                <?php endif; ?>
            </div>

            <!-- Botão Menu Mobile -->
            <div class="flex md:hidden items-center gap-2">
                <?php if ($user): ?>
                    <a href="<?= BASE_URL ?>/perfil.php" class="w-9 h-9 rounded-xl bg-slate-900 border border-slate-800 flex items-center justify-center text-indigo-400">
                        <i class="fa-solid fa-<?= htmlspecialchars($user['avatar'] ?? 'user-astronaut') ?>"></i>
                    </a>
                <?php endif; ?>
                <button type="button" id="mobile-menu-toggle" class="w-10 h-10 rounded-xl bg-slate-900 border border-slate-800 text-slate-400 hover:text-white flex items-center justify-center transition focus:outline-none">
                    <i class="fa-solid fa-bars text-lg"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Drawer Mobile -->
    <div id="mobile-menu-drawer" class="hidden md:hidden border-t border-slate-800 bg-slate-950 px-4 pt-3 pb-6 space-y-2">
        <a href="<?= BASE_URL ?>/index.php" class="block px-3 py-2.5 rounded-xl text-base font-semibold <?= $currentPage === 'index.php' ? 'text-white bg-slate-800' : 'text-slate-400' ?>">
            <i class="fa-solid fa-house mr-2.5"></i> Início
        </a>
        <a href="<?= BASE_URL ?>/arena.php" class="block px-3 py-2.5 rounded-xl text-base font-semibold <?= $currentPage === 'arena.php' ? 'text-white bg-slate-800' : 'text-slate-400' ?>">
            <i class="fa-solid fa-fire mr-2.5 text-amber-400"></i> Arena de Quizzes
        </a>
        <a href="<?= BASE_URL ?>/ranking.php" class="block px-3 py-2.5 rounded-xl text-base font-semibold <?= $currentPage === 'ranking.php' ? 'text-white bg-slate-800' : 'text-slate-400' ?>">
            <i class="fa-solid fa-trophy mr-2.5 text-yellow-400"></i> Ranking Geral
        </a>
        <a href="<?= BASE_URL ?>/historico.php" class="block px-3 py-2.5 rounded-xl text-base font-semibold <?= $currentPage === 'historico.php' ? 'text-white bg-slate-800' : 'text-slate-400' ?>">
            <i class="fa-solid fa-clock-rotate-left mr-2.5 text-indigo-400"></i> Histórico & Logs
        </a>
        <?php if (isAdmin()): ?>
            <a href="<?= BASE_URL ?>/admin/dashboard.php" class="block px-3 py-2.5 rounded-xl text-base font-semibold text-purple-400 bg-purple-950/20 border border-purple-500/20">
                <i class="fa-solid fa-shield-halved mr-2.5"></i> Painel Administrativo
            </a>
        <?php endif; ?>

        <div class="pt-4 border-t border-slate-800/80">
            <?php if ($user): ?>
                <div class="flex items-center justify-between px-2 mb-3">
                    <div class="flex flex-col">
                        <span class="text-sm font-bold text-white"><?= htmlspecialchars($user['nome']) ?></span>
                        <span class="text-xs text-slate-400">Nível <?= $user['nivel'] ?? 1 ?> • <?= $user['xp'] ?? 0 ?> XP</span>
                    </div>
                    <a href="<?= BASE_URL ?>/backend/auth/logout.php" data-confirm="Deseja realmente sair da sua conta?" data-title="Encerrar Sessão" class="text-xs font-semibold text-rose-400 hover:text-rose-300">
                        <i class="fa-solid fa-right-from-bracket mr-1"></i> Sair
                    </a>
                </div>
                <a href="<?= BASE_URL ?>/perfil.php" class="w-full inline-flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl text-sm font-bold bg-slate-800 hover:bg-slate-700 text-white transition">
                    <i class="fa-solid fa-user-gear"></i> Ver Meu Perfil
                </a>
            <?php else: ?>
                <div class="grid grid-cols-2 gap-3">
                    <a href="<?= BASE_URL ?>/login.php" class="w-full text-center py-2.5 px-4 rounded-xl text-sm font-semibold bg-slate-900 border border-slate-800 text-white">
                        Entrar
                    </a>
                    <a href="<?= BASE_URL ?>/register.php" class="w-full text-center py-2.5 px-4 rounded-xl text-sm font-bold bg-indigo-600 text-white">
                        Cadastrar
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</nav>
