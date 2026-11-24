<?php
/**
 * Codeflip Arena - Gestão de Usuários e Permissões (Administração)
 * Mobile-First com Tailwind CSS, alteração de cargos e exclusão tratada em Modal
 */

$pageTitle = 'Gerenciar Usuários';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../bd.php';
require_once __DIR__ . '/../backend/helpers/auth_helper.php';

requireAdmin();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

$pdo = getDB();
$currentAdmin = currentUser();
$csrfToken = generateCsrfToken();
$users = [];

try {
    $stmt = $pdo->query("SELECT * FROM users ORDER BY id ASC");
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Erro ao carregar usuários: " . $e->getMessage());
}
?>

<main class="flex-1 py-8 sm:py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-6xl mx-auto">
        <!-- Breadcrumbs & Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
            <div>
                <a href="<?= BASE_URL ?>/admin/dashboard.php" class="text-xs font-bold text-slate-400 hover:text-white transition flex items-center gap-1.5 mb-2">
                    <i class="fa-solid fa-arrow-left"></i> Painel Geral
                </a>
                <h1 class="text-3xl font-black text-white tracking-tight">Gerenciamento de Competidores</h1>
                <p class="text-sm text-slate-400 mt-1">Supervisione os participantes da Arena, níveis e níveis de acesso.</p>
            </div>
        </div>

        <!-- Tabela de Usuários -->
        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl overflow-hidden shadow-2xl backdrop-blur-md">
            <div class="p-4 sm:p-5 border-b border-slate-800 flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Usuários Registrados (<?= count($users) ?>)</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-950/60 text-slate-400 uppercase tracking-wider border-b border-slate-800">
                            <th class="py-3 px-4 w-14 text-center">ID</th>
                            <th class="py-3 px-4">Usuário</th>
                            <th class="py-3 px-4 text-center">Cargo / Role</th>
                            <th class="py-3 px-4 text-center">Nível</th>
                            <th class="py-3 px-4 text-center">XP</th>
                            <th class="py-3 px-4 text-center">Registro</th>
                            <th class="py-3 px-4 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        <?php foreach ($users as $u): 
                            $isSelf = ($u['id'] == $currentAdmin['id']);
                        ?>
                            <tr class="hover:bg-slate-800/40 transition <?= $isSelf ? 'bg-purple-950/20' : '' ?>">
                                <td class="py-3.5 px-4 text-center font-mono text-slate-500">
                                    #<?= $u['id'] ?>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-slate-800 flex items-center justify-center text-indigo-400 text-sm shrink-0">
                                            <i class="fa-solid fa-<?= htmlspecialchars($u['avatar'] ?? 'user-astronaut') ?>"></i>
                                        </div>
                                        <div>
                                            <span class="font-bold text-white text-sm block">
                                                <?= htmlspecialchars($u['nome']) ?>
                                                <?php if ($isSelf): ?>
                                                    <span class="ml-1 text-[10px] text-purple-400 font-normal">(Você)</span>
                                                <?php endif; ?>
                                            </span>
                                            <span class="text-[11px] text-slate-500 font-mono"><?= htmlspecialchars($u['email']) ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <?php if ($isSelf): ?>
                                        <span class="px-2.5 py-1 rounded-full text-xs font-bold uppercase bg-purple-950 text-purple-300 border border-purple-500/40">
                                            ADMIN
                                        </span>
                                    <?php else: ?>
                                        <form action="<?= BASE_URL ?>/backend/admin/user_action.php" method="POST" class="inline-block">
                                            <input type="hidden" name="action" value="change_role">
                                            <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                            <select name="role" onchange="this.form.submit()"
                                                    class="py-1 px-2.5 rounded-lg text-xs font-bold uppercase bg-slate-950 border border-slate-800 <?= $u['role'] === 'admin' ? 'text-purple-300' : 'text-slate-300' ?> focus:outline-none focus:border-indigo-500">
                                                <option value="user" <?= $u['role'] === 'user' ? 'selected' : '' ?>>User</option>
                                                <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                            </select>
                                        </form>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4 text-center font-bold text-slate-300">
                                    Lvl <?= $u['nivel'] ?>
                                </td>
                                <td class="py-3.5 px-4 text-center font-bold text-indigo-400">
                                    <?= $u['xp'] ?>
                                </td>
                                <td class="py-3.5 px-4 text-center text-slate-500 text-[11px]">
                                    <?= date('d/m/Y', strtotime($u['created_at'])) ?>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <?php if (!$isSelf): ?>
                                        <!-- Exclusão tratada em Modal pelo app.js data-confirm -->
                                        <a href="<?= BASE_URL ?>/backend/admin/user_action.php?action=delete&id=<?= $u['id'] ?>&token=<?= $csrfToken ?>"
                                           data-confirm="Deseja realmente remover o usuário <?= htmlspecialchars($u['nome']) ?> da Arena?"
                                           data-title="Excluir Usuário"
                                           class="p-1.5 rounded-lg text-slate-400 hover:text-rose-400 hover:bg-rose-950/40 transition" title="Excluir Competidor">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-slate-600 text-[11px] italic">Protegido</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
