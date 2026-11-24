<?php
/**
 * Codeflip Arena - Gestão de Perguntas e Respostas (Administração)
 * Mobile-First com Tailwind CSS, cadastro de 4 alternativas e exclusão tratada em Modal
 */

$pageTitle = 'Banco de Perguntas';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../bd.php';
require_once __DIR__ . '/../backend/helpers/auth_helper.php';

requireAdmin();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

$pdo = getDB();
$filtroCat = filter_input(INPUT_GET, 'categoria', FILTER_VALIDATE_INT);
$csrfToken = generateCsrfToken();

$categorias = [];
$perguntas = [];

try {
    $categorias = $pdo->query("SELECT id, nome FROM categorias ORDER BY nome ASC")->fetchAll();

    if ($filtroCat) {
        $stmtP = $pdo->prepare("
            SELECT p.*, c.nome as categoria_nome
            FROM perguntas p
            JOIN categorias c ON p.categoria_id = c.id
            WHERE p.categoria_id = :cat_id
            ORDER BY p.id DESC
        ");
        $stmtP->execute([':cat_id' => $filtroCat]);
    } else {
        $stmtP = $pdo->query("
            SELECT p.*, c.nome as categoria_nome
            FROM perguntas p
            JOIN categorias c ON p.categoria_id = c.id
            ORDER BY p.id DESC
        ");
    }
    $perguntas = $stmtP->fetchAll();

    // Carrega opções de cada pergunta
    $stmtOptions = $pdo->prepare("SELECT id, texto, is_correta FROM opcoes WHERE pergunta_id = :p_id");
    foreach ($perguntas as &$q) {
        $stmtOptions->execute([':p_id' => $q['id']]);
        $q['opcoes'] = $stmtOptions->fetchAll();
    }
    unset($q);

} catch (PDOException $e) {
    error_log("Erro ao carregar perguntas: " . $e->getMessage());
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
                <h1 class="text-3xl font-black text-white tracking-tight">Banco de Perguntas</h1>
                <p class="text-sm text-slate-400 mt-1">Crie questões com múltiplas escolhas e explicações educativas.</p>
            </div>

            <button type="button" onclick="document.getElementById('form-nova-pergunta').classList.toggle('hidden')"
                    class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-500 text-white shadow-lg shadow-emerald-600/25 transition">
                <i class="fa-solid fa-plus"></i> Nova Pergunta
            </button>
        </div>

        <!-- Formulário de Nova Pergunta (Toggle) -->
        <div id="form-nova-pergunta" class="hidden mb-8 p-6 rounded-2xl bg-slate-900 border border-slate-800 shadow-2xl">
            <h2 class="text-lg font-black text-white mb-4 flex items-center gap-2">
                <i class="fa-solid fa-circle-question text-emerald-400"></i> Criar Nova Questão
            </h2>

            <form action="<?= BASE_URL ?>/backend/admin/question_action.php" method="POST" class="space-y-4">
                <input type="hidden" name="action" value="create">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-400 mb-1">Categoria</label>
                        <select name="categoria_id" required class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-sm text-white focus:outline-none focus:border-indigo-500">
                            <option value="">Selecione uma categoria...</option>
                            <?php foreach ($categorias as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= $filtroCat == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-400 mb-1">Dificuldade</label>
                        <select name="dificuldade" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-sm text-white focus:outline-none focus:border-indigo-500">
                            <option value="facil">Fácil</option>
                            <option value="medio" selected>Médio</option>
                            <option value="dificil">Difícil</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-400 mb-1">Pontos Base</label>
                        <input type="number" name="pontos" value="10" min="5" max="100" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-sm text-white focus:outline-none focus:border-indigo-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1">Enunciado da Pergunta</label>
                    <textarea name="pergunta" rows="3" required placeholder="Digite a pergunta clara e objetiva..."
                              class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-sm text-white focus:outline-none focus:border-indigo-500"></textarea>
                </div>

                <!-- 4 Alternativas com Seleção da Correta -->
                <div class="pt-2">
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-2">Alternativas (Marque o círculo da resposta correta):</label>
                    <div class="space-y-2.5">
                        <?php for ($i = 0; $i < 4; $i++): ?>
                            <div class="flex items-center gap-3 bg-slate-950 p-2.5 rounded-xl border border-slate-800">
                                <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-slate-400 pl-2">
                                    <input type="radio" name="correta_index" value="<?= $i ?>" <?= $i === 0 ? 'checked' : '' ?>
                                           class="w-4 h-4 text-emerald-500 focus:ring-emerald-500 bg-slate-900 border-slate-700">
                                    <span><?= chr(65 + $i) ?></span>
                                </label>
                                <input type="text" name="opcoes[]" required placeholder="Texto da alternativa <?= chr(65 + $i) ?>"
                                       class="flex-1 bg-transparent border-0 text-sm text-white focus:ring-0 focus:outline-none placeholder-slate-600">
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1">Explicação / Resumo Educativo (Opcional)</label>
                    <input type="text" name="explicacao" placeholder="Ex: Essa função foi introduzida no PHP 5.5 e é recomendada por..."
                           class="w-full px-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white focus:outline-none focus:border-indigo-500">
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('form-nova-pergunta').classList.add('hidden')"
                            class="px-4 py-2 rounded-xl text-xs font-bold text-slate-400 hover:text-white bg-slate-800">
                        Cancelar
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 shadow-md">
                        Salvar Pergunta
                    </button>
                </div>
            </form>
        </div>

        <!-- Filtro por Categoria -->
        <div class="flex items-center justify-between mb-4">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Perguntas Existentes (<?= count($perguntas) ?>)</span>

            <form method="GET" class="flex items-center gap-2">
                <select name="categoria" onchange="this.form.submit()"
                        class="py-1.5 px-3 bg-slate-900 border border-slate-800 rounded-xl text-xs text-slate-300 focus:outline-none focus:border-indigo-500">
                    <option value="">Todas as Categorias</option>
                    <?php foreach ($categorias as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $filtroCat == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>

        <!-- Lista de Perguntas Cadastradas -->
        <div class="space-y-4">
            <?php if (empty($perguntas)): ?>
                <div class="text-center py-16 bg-slate-900/40 rounded-2xl border border-slate-800 text-slate-500">
                    <i class="fa-solid fa-folder-open text-3xl mb-2"></i>
                    <p class="text-sm">Nenhuma pergunta encontrada com o filtro atual.</p>
                </div>
            <?php else: ?>
                <?php foreach ($perguntas as $q): ?>
                    <div class="p-5 sm:p-6 rounded-2xl bg-slate-900/80 border border-slate-800 shadow-xl backdrop-blur-md">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3">
                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                                    <?= htmlspecialchars($q['categoria_nome']) ?>
                                </span>
                                <span class="px-2 py-0.5 rounded text-[10px] uppercase font-bold bg-slate-800 text-slate-300">
                                    <?= htmlspecialchars($q['dificuldade']) ?> • <?= $q['pontos'] ?> pts
                                </span>
                            </div>

                            <!-- Botão Deletar com Confirmação em Modal -->
                            <a href="<?= BASE_URL ?>/backend/admin/question_action.php?action=delete&id=<?= $q['id'] ?>&token=<?= $csrfToken ?>"
                               data-confirm="Deseja realmente apagar esta pergunta do banco de dados?"
                               data-title="Excluir Pergunta"
                               class="text-xs font-bold text-rose-400 hover:text-rose-300 p-1 rounded transition self-end sm:self-auto">
                                <i class="fa-solid fa-trash-can mr-1"></i> Excluir
                            </a>
                        </div>

                        <h3 class="text-sm sm:text-base font-bold text-white mb-4">
                            <?= htmlspecialchars($q['pergunta']) ?>
                        </h3>

                        <!-- Opções -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                            <?php foreach ($q['opcoes'] as $opt): ?>
                                <div class="p-2.5 rounded-xl border flex items-center justify-between <?= $opt['is_correta'] ? 'bg-emerald-950/60 border-emerald-500/40 text-emerald-300 font-semibold' : 'bg-slate-950 border-slate-800 text-slate-400' ?>">
                                    <span><?= htmlspecialchars($opt['texto']) ?></span>
                                    <?php if ($opt['is_correta']): ?>
                                        <i class="fa-solid fa-circle-check text-emerald-400 ml-2"></i>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <?php if (!empty($q['explicacao'])): ?>
                            <div class="mt-3 pt-3 border-t border-slate-800/80 text-[11px] text-slate-400 italic">
                                <strong>Explicação:</strong> <?= htmlspecialchars($q['explicacao']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
