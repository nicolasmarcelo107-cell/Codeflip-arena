<?php
/**
 * Codeflip Arena - Tela de Login
 * Mobile-First com Tailwind CSS e validação segura
 */

$pageTitle = 'Entrar na Arena';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/backend/helpers/auth_helper.php';

// Se já estiver logado, redireciona para a Arena
if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/arena.php');
    exit;
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
$csrfToken = generateCsrfToken();
?>

<main class="flex-1 flex items-center justify-center py-10 px-4 sm:px-6">
    <div class="w-full max-w-md">
        <!-- Card de Login -->
        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-6 sm:p-8 shadow-2xl backdrop-blur-md">
            <div class="text-center mb-8">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-gradient-to-tr from-indigo-600 to-purple-600 flex items-center justify-center text-white text-2xl mb-4 shadow-lg shadow-indigo-600/30">
                    <i class="fa-solid fa-gamepad"></i>
                </div>
                <h1 class="text-2xl font-black text-white tracking-tight">Acesse a Arena</h1>
                <p class="text-xs sm:text-sm text-slate-400 mt-1">Digite suas credenciais para entrar no jogo</p>
            </div>

            <form action="<?= BASE_URL ?>/backend/auth/login.php" method="POST" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

                <div>
                    <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">E-mail</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                            <i class="fa-solid fa-envelope"></i>
                        </div>
                        <input type="email" id="email" name="email" required placeholder="seu.email@exemplo.com"
                               class="w-full pl-10 pr-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-sm text-white placeholder-slate-600 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition">
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="senha" class="block text-xs font-bold uppercase tracking-wider text-slate-400">Senha</label>
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                            <i class="fa-solid fa-lock"></i>
                        </div>
                        <input type="password" id="senha" name="senha" required placeholder="••••••••"
                               class="w-full pl-10 pr-10 py-3 bg-slate-950 border border-slate-800 rounded-xl text-sm text-white placeholder-slate-600 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition">
                        <button type="button" onclick="togglePassVisibility('senha', this)" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-500 hover:text-slate-300">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="w-full py-3.5 px-4 rounded-xl font-black text-sm text-white bg-indigo-600 hover:bg-indigo-500 shadow-lg shadow-indigo-600/30 transition duration-150 flex items-center justify-center gap-2 mt-6">
                    <i class="fa-solid fa-right-to-bracket"></i> Entrar na Conta
                </button>
            </form>

            <!-- Dica de Contas de Teste -->
            <div class="mt-6 p-3.5 rounded-xl bg-slate-950/60 border border-slate-800/80 text-[11px] text-slate-400">
                <div class="font-bold text-slate-300 flex items-center gap-1.5 mb-1">
                    <i class="fa-solid fa-lightbulb text-amber-400"></i> Credenciais Rápidas de Demonstração:
                </div>
                <div class="flex flex-col gap-1">
                    <div><span class="text-indigo-400 font-mono">admin@codeflip.com</span> / senha: <span class="font-mono text-slate-300">admin123</span> (Admin)</div>
                    <div><span class="text-indigo-400 font-mono">jogador@codeflip.com</span> / senha: <span class="font-mono text-slate-300">jogador123</span> (Player)</div>
                </div>
            </div>

            <p class="text-center text-xs text-slate-400 mt-6">
                Ainda não tem guerreiro cadastrado? 
                <a href="<?= BASE_URL ?>/register.php" class="font-bold text-indigo-400 hover:text-indigo-300 transition">Criar conta grátis</a>
            </p>
        </div>
    </div>
</main>

<script>
function togglePassVisibility(id, btn) {
    const input = document.getElementById(id);
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
