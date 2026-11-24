<?php
/**
 * Codeflip Arena - Perfil do Jogador
 * Mobile-First com Tailwind CSS, cálculo de progresso de nível e edição com hash bcrypt
 */

$pageTitle = 'Meu Perfil de Guerreiro';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/bd.php';
require_once __DIR__ . '/backend/helpers/auth_helper.php';

requireAuth();

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

$pdo = getDB();
$sessionUser = currentUser();

// Busca dados mais recentes do usuário no banco
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute([':id' => $sessionUser['id']]);
$user = $stmt->fetch();

$progresso = calculateLevelProgress($user['xp']);
$taxaAcerto = ($user['partidas_jogadas'] > 0 && $user['acertos_total'] > 0)
    ? min(100, round(($user['acertos_total'] / ($user['partidas_jogadas'] * 5)) * 100))
    : 0;

$csrfToken = generateCsrfToken();
$avatarList = [
    'user-astronaut' => 'Astronauta',
    'user-ninja'     => 'Ninja',
    'user-secret'    => 'Agente',
    'ghost'          => 'Fantasma',
    'dragon'         => 'Dragão',
    'fire'           => 'Fogo',
    'bolt'           => 'Relâmpago',
    'gamepad'        => 'Gamer',
    'robot'          => 'Cyborg',
    'brain'          => 'Mente Mestra',
    'meteor'         => 'Meteoro',
    'crown'          => 'Rei da Arena'
];
?>

<main class="flex-1 py-8 sm:py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">
        <!-- Card de Apresentação do Perfil -->
        <div class="p-6 sm:p-8 rounded-3xl bg-slate-900/90 border border-slate-800 shadow-2xl mb-8 backdrop-blur-md relative overflow-hidden">
            <div class="absolute top-0 right-0 w-64 h-64 bg-indigo-600/10 rounded-full blur-3xl pointer-events-none"></div>

            <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6 relative z-10 text-center sm:text-left">
                <!-- Avatar do Jogador -->
                <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-3xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-purple-600 border-2 border-indigo-400/30 flex items-center justify-center text-white text-4xl shadow-2xl shadow-indigo-500/30 shrink-0">
                    <i class="fa-solid fa-<?= htmlspecialchars($user['avatar'] ?? 'user-astronaut') ?>"></i>
                </div>

                <div class="flex-1 min-w-0">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-2">
                        <div>
                            <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight truncate">
                                <?= htmlspecialchars($user['nome']) ?>
                            </h1>
                            <p class="text-xs text-slate-400"><?= htmlspecialchars($user['email']) ?></p>
                        </div>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 self-center sm:self-start">
                            <i class="fa-solid fa-shield text-indigo-400"></i> Cargo: <?= strtoupper($user['role']) ?>
                        </span>
                    </div>

                    <!-- Barra de Progresso do Nível -->
                    <div class="mt-4 p-4 rounded-2xl bg-slate-950/80 border border-slate-800/80">
                        <div class="flex items-center justify-between text-xs font-bold mb-2">
                            <span class="text-indigo-400 uppercase tracking-wider">
                                <i class="fa-solid fa-bolt mr-1"></i> Nível <?= $progresso['level'] ?>
                            </span>
                            <span class="text-slate-400">
                                <?= $user['xp'] ?> XP Total (<?= $progresso['percentage'] ?>% para o Lvl <?= $progresso['level'] + 1 ?>)
                            </span>
                        </div>
                        <div class="w-full bg-slate-900 h-3 rounded-full overflow-hidden p-0.5 border border-slate-800">
                            <div class="h-full bg-gradient-to-r from-indigo-500 to-purple-500 rounded-full transition-all duration-500"
                                 style="width: <?= $progresso['percentage'] ?>%;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estatísticas Gerais em Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mb-8">
            <div class="p-4 sm:p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 text-center">
                <div class="text-2xl sm:text-3xl font-black text-indigo-400"><?= $user['partidas_jogadas'] ?></div>
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mt-1">Partidas</div>
            </div>
            <div class="p-4 sm:p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 text-center">
                <div class="text-2xl sm:text-3xl font-black text-emerald-400"><?= $user['acertos_total'] ?></div>
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mt-1">Acertos</div>
            </div>
            <div class="p-4 sm:p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 text-center">
                <div class="text-2xl sm:text-3xl font-black text-purple-400"><?= $taxaAcerto ?>%</div>
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mt-1">Precisão Média</div>
            </div>
            <div class="p-4 sm:p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 text-center">
                <div class="text-2xl sm:text-3xl font-black text-amber-400"><?= $progresso['level'] ?></div>
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mt-1">Nível Atual</div>
            </div>
        </div>

        <!-- Conquistas & Medalhas da Arena -->
        <div class="p-6 rounded-2xl bg-slate-900/60 border border-slate-800/80 mb-8">
            <h2 class="text-lg font-black text-white tracking-tight mb-4 flex items-center gap-2">
                <i class="fa-solid fa-medal text-amber-400"></i> Medalhas & Conquistas
            </h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
                <div class="p-3.5 rounded-xl bg-slate-950 border <?= $user['partidas_jogadas'] >= 1 ? 'border-amber-500/40' : 'border-slate-800 opacity-50' ?>">
                    <div class="text-2xl mb-1 text-amber-400"><i class="fa-solid fa-flag-checkered"></i></div>
                    <div class="text-xs font-bold text-white">Primeiro Passo</div>
                    <div class="text-[10px] text-slate-500">Jogou 1 quiz</div>
                </div>
                <div class="p-3.5 rounded-xl bg-slate-950 border <?= $user['acertos_total'] >= 10 ? 'border-emerald-500/40' : 'border-slate-800 opacity-50' ?>">
                    <div class="text-2xl mb-1 text-emerald-400"><i class="fa-solid fa-brain"></i></div>
                    <div class="text-xs font-bold text-white">Sábio Aprendiz</div>
                    <div class="text-[10px] text-slate-500">10 acertos totais</div>
                </div>
                <div class="p-3.5 rounded-xl bg-slate-950 border <?= $progresso['level'] >= 3 ? 'border-indigo-500/40' : 'border-slate-800 opacity-50' ?>">
                    <div class="text-2xl mb-1 text-indigo-400"><i class="fa-solid fa-fire"></i></div>
                    <div class="text-xs font-bold text-white">Guerreiro Nível 3</div>
                    <div class="text-[10px] text-slate-500">Alcançou nível 3</div>
                </div>
                <div class="p-3.5 rounded-xl bg-slate-950 border <?= $user['xp'] >= 500 ? 'border-purple-500/40' : 'border-slate-800 opacity-50' ?>">
                    <div class="text-2xl mb-1 text-purple-400"><i class="fa-solid fa-crown"></i></div>
                    <div class="text-xs font-bold text-white">Elite da Arena</div>
                    <div class="text-[10px] text-slate-500">500+ XP acumulados</div>
                </div>
            </div>
        </div>

        <!-- Formulário de Edição de Perfil e Senha (Bcrypt) -->
        <div class="p-6 sm:p-8 rounded-2xl bg-slate-900/80 border border-slate-800 shadow-xl">
            <h2 class="text-xl font-black text-white tracking-tight mb-2 flex items-center gap-2">
                <i class="fa-solid fa-user-pen text-indigo-400"></i> Editar Informações de Perfil
            </h2>
            <p class="text-xs text-slate-400 mb-6">
                Altere seu nome de exibição, avatar gamer ou atualize sua senha com hash Bcrypt para manter sua conta protegida.
            </p>

            <form action="<?= BASE_URL ?>/backend/profile/update.php" method="POST" class="space-y-6">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

                <!-- Nome de Exibição -->
                <div>
                    <label for="nome" class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Nome de Guerreiro</label>
                    <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($user['nome']) ?>" required minlength="3"
                           class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-sm text-white focus:outline-none focus:border-indigo-500 transition">
                </div>

                <!-- Seletor de Avatar Gamer -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Selecione seu Avatar</label>
                    <div class="grid grid-cols-4 sm:grid-cols-6 gap-3">
                        <?php foreach ($avatarList as $iconClass => $iconLabel): ?>
                            <label class="cursor-pointer">
                                <input type="radio" name="avatar" value="<?= $iconClass ?>" class="sr-only peer" <?= ($user['avatar'] === $iconClass) ? 'checked' : '' ?>>
                                <div class="p-3 rounded-2xl bg-slate-950 border border-slate-800 peer-checked:border-indigo-500 peer-checked:bg-indigo-950/40 peer-checked:text-indigo-400 text-slate-400 text-center transition hover:border-slate-700">
                                    <i class="fa-solid fa-<?= $iconClass ?> text-2xl mb-1 block"></i>
                                    <span class="text-[10px] font-bold block truncate"><?= $iconLabel ?></span>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Divisor: Alteração de Senha -->
                <div class="pt-6 border-t border-slate-800">
                    <h3 class="text-sm font-bold text-white mb-1 flex items-center gap-1.5">
                        <i class="fa-solid fa-lock text-amber-400"></i> Alterar Senha (Opcional)
                    </h3>
                    <p class="text-xs text-slate-500 mb-4">Preencha apenas se desejar substituir sua senha atual.</p>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label for="senha_atual" class="block text-[11px] font-bold uppercase text-slate-400 mb-1">Senha Atual</label>
                            <input type="password" id="senha_atual" name="senha_atual" placeholder="••••••••"
                                   class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white focus:outline-none focus:border-indigo-500">
                        </div>
                        <div>
                            <label for="nova_senha" class="block text-[11px] font-bold uppercase text-slate-400 mb-1">Nova Senha</label>
                            <input type="password" id="nova_senha" name="nova_senha" minlength="6" placeholder="Mínimo 6 dígitos"
                                   class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white focus:outline-none focus:border-indigo-500">
                        </div>
                        <div>
                            <label for="confirmar_nova_senha" class="block text-[11px] font-bold uppercase text-slate-400 mb-1">Confirmar Nova Senha</label>
                            <input type="password" id="confirmar_nova_senha" name="confirmar_nova_senha" minlength="6" placeholder="••••••••"
                                   class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white focus:outline-none focus:border-indigo-500">
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4">
                    <button type="submit" class="w-full sm:w-auto px-6 py-3 rounded-xl font-bold text-sm text-white bg-indigo-600 hover:bg-indigo-500 shadow-lg shadow-indigo-600/30 transition">
                        <i class="fa-solid fa-floppy-disk mr-1.5"></i> Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
