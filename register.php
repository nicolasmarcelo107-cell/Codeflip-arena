<?php
/**
 * Codeflip Arena - Tela de Cadastro de Usuário
 * Mobile-First com Tailwind CSS e validação com Hash Bcrypt
 */

$pageTitle = 'Criar Nova Conta';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/backend/helpers/auth_helper.php';

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
        <!-- Card de Cadastro -->
        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-6 sm:p-8 shadow-2xl backdrop-blur-md">
            <div class="text-center mb-8">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-gradient-to-tr from-purple-600 to-indigo-600 flex items-center justify-center text-white text-2xl mb-4 shadow-lg shadow-purple-600/30">
                    <i class="fa-solid fa-user-plus"></i>
                </div>
                <h1 class="text-2xl font-black text-white tracking-tight">Junte-se à Arena</h1>
                <p class="text-xs sm:text-sm text-slate-400 mt-1">Crie seu perfil e comece a pontuar</p>
            </div>

            <form action="<?= BASE_URL ?>/backend/auth/register.php" method="POST" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

                <div>
                    <label for="nome" class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Nome de Guerreiro / Nick</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                            <i class="fa-solid fa-user"></i>
                        </div>
                        <input type="text" id="nome" name="nome" required minlength="3" placeholder="Ex: PlayerMaster"
                               class="w-full pl-10 pr-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-sm text-white placeholder-slate-600 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition">
                    </div>
                </div>

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
                    <label for="senha" class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Senha (mínimo 6 dígitos)</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                            <i class="fa-solid fa-lock"></i>
                        </div>
                        <input type="password" id="senha" name="senha" required minlength="6" placeholder="••••••••"
                               class="w-full pl-10 pr-10 py-3 bg-slate-950 border border-slate-800 rounded-xl text-sm text-white placeholder-slate-600 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition">
                        <button type="button" onclick="togglePassVisibility('senha', this)" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-500 hover:text-slate-300">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div>
                    <label for="confirmar_senha" class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Confirmar Senha</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                            <i class="fa-solid fa-shield"></i>
                        </div>
                        <input type="password" id="confirmar_senha" name="confirmar_senha" required minlength="6" placeholder="••••••••"
                               class="w-full pl-10 pr-10 py-3 bg-slate-950 border border-slate-800 rounded-xl text-sm text-white placeholder-slate-600 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition">
                        <button type="button" onclick="togglePassVisibility('confirmar_senha', this)" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-500 hover:text-slate-300">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="text-[11px] text-slate-500 leading-normal">
                    <i class="fa-solid fa-shield-check text-indigo-400 mr-1"></i> Suas senhas são criptografadas com o algoritmo nativo Bcrypt para máxima segurança.
                </div>

                <button type="submit" class="w-full py-3.5 px-4 rounded-xl font-black text-sm text-white bg-indigo-600 hover:bg-indigo-500 shadow-lg shadow-indigo-600/30 transition duration-150 flex items-center justify-center gap-2 mt-4">
                    <i class="fa-solid fa-user-check"></i> Cadastrar e Entrar na Arena
                </button>
            </form>

            <p class="text-center text-xs text-slate-400 mt-6">
                Já possui conta? 
                <a href="<?= BASE_URL ?>/login.php" class="font-bold text-indigo-400 hover:text-indigo-300 transition">Fazer login</a>
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
