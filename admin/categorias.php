<?php
/**
 * Codeflip Arena - Gestão de Categorias (Administração)
 * Mobile-First com Tailwind CSS, formulário de adição e exclusão com confirmação em Modal
 */

$pageTitle = 'Gerenciar Categorias';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../bd.php';
require_once __DIR__ . '/../backend/helpers/auth_helper.php';

requireAdmin();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

$pdo = getDB();
$categorias = [];
$csrfToken = generateCsrfToken();

try {
    $stmt = $pdo->query("
        SELECT c.*, COUNT(p.id) as total_perguntas
        FROM categorias c
        LEFT JOIN perguntas p ON c.id = p.categoria_id
        GROUP BY c.id
        ORDER BY c.id DESC
    ");
    $categorias = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Erro ao carregar categorias: " . $e->getMessage());
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
                <h1 class="text-3xl font-black text-white tracking-tight">Gerenciamento de Categorias</h1>
                <p class="text-sm text-slate-400 mt-1">Crie, edite ou remova temas para a Arena de Quizzes.</p>
            </div>

            <button type="button" onclick="document.getElementById('form-nova-categoria').classList.toggle('hidden')"
                    class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold bg-indigo-600 hover:bg-indigo-500 text-white shadow-lg shadow-indigo-600/25 transition">
                <i class="fa-solid fa-plus"></i> Nova Categoria
            </button>
        </div>

        <!-- Formulário de Nova Categoria (Toggle) -->
        <div id="form-nova-categoria" class="hidden mb-8 p-6 rounded-2xl bg-slate-900 border border-slate-800 shadow-2xl">
            <h2 class="text-lg font-black text-white mb-4 flex items-center gap-2">
                <i class="fa-solid fa-folder-plus text-indigo-400"></i> Cadastrar Nova Categoria
            </h2>

            <form action="<?= BASE_URL ?>/backend/admin/category_action.php" method="POST" class="space-y-4">
                <input type="hidden" name="action" value="create">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-400 mb-1">Nome da Categoria</label>
                        <input type="text" name="nome" required placeholder="Ex: Inteligência Artificial"
                               class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-sm text-white focus:outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-400 mb-1">Ícone FontAwesome</label>
                        <input type="text" name="icone" value="fa-gamepad" required placeholder="Ex: fa-robot, fa-code, fa-brain"
                               class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-sm text-white focus:outline-none focus:border-indigo-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1">Descrição</label>
                    <textarea name="descricao" rows="2" placeholder="Breve resumo sobre os tópicos abordados nesta categoria..."
                              class="w-full px-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-sm text-white focus:outline-none focus:border-indigo-500"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('form-nova-categoria').classList.add('hidden')"
                            class="px-4 py-2 rounded-xl text-xs font-bold text-slate-400 hover:text-white bg-slate-800">
                        Cancelar
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 shadow-md">
                        Salvar Categoria
                    </button>
                </div>
            </form>
        </div>

        <!-- Tabela de Categorias Existentes -->
        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl overflow-hidden shadow-2xl backdrop-blur-md">
            <div class="p-4 sm:p-5 border-b border-slate-800 flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Categorias Cadastradas (<?= count($categorias) ?>)</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-950/60 text-slate-400 uppercase tracking-wider border-b border-slate-800">
                            <th class="py-3 px-4 w-14 text-center">ID</th>
                            <th class="py-3 px-4">Nome & Slug</th>
                            <th class="py-3 px-4 text-center">Ícone</th>
                            <th class="py-3 px-4 text-center">Perguntas</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        <?php foreach ($categorias as $cat): ?>
                            <tr class="hover:bg-slate-800/40 transition">
                                <td class="py-3.5 px-4 text-center font-mono text-slate-500">
                                    #<?= $cat['id'] ?>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-white text-sm"><?= htmlspecialchars($cat['nome']) ?></div>
                                    <div class="text-[11px] text-slate-500 font-mono"><?= htmlspecialchars($cat['slug']) ?></div>
                                </td>
                                <td class="py-3.5 px-4 text-center text-indigo-400 text-base">
                                    <i class="fa-solid <?= htmlspecialchars($cat['icone']) ?>"></i>
                                </td>
                                <td class="py-3.5 px-4 text-center font-bold text-slate-300">
                                    <?= $cat['total_perguntas'] ?>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase <?= $cat['ativo'] ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-slate-800 text-slate-400' ?>">
                                        <?= $cat['ativo'] ? 'Ativo' : 'Inativo' ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right space-x-2">
                                    <a href="<?= BASE_URL ?>/admin/perguntas.php?categoria=<?= $cat['id'] ?>" class="p-1.5 rounded-lg text-slate-400 hover:text-indigo-300 hover:bg-slate-800" title="Ver Perguntas">
                                        <i class="fa-solid fa-list"></i>
                                    </a>
                                    <!-- Ação de Excluir tratada em Modal pelo app.js data-confirm -->
                                    <a href="<?= BASE_URL ?>/backend/admin/category_action.php?action=delete&id=<?= $cat['id'] ?>&token=<?= $csrfToken ?>"
                                       data-confirm="Deseja excluir a categoria '<?= htmlspecialchars($cat['nome']) ?>'? Todas as perguntas vinculadas serão deletadas!"
                                       data-title="Excluir Categoria"
                                       class="p-1.5 rounded-lg text-slate-400 hover:text-rose-400 hover:bg-rose-950/40" title="Excluir Categoria">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </a>
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
