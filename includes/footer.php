<?php
/**
 * Codeflip Arena - Rodapé Padrão
 */
?>
    <footer class="mt-auto border-t border-slate-900 bg-slate-950/60 py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="font-semibold text-slate-400"><?= APP_NAME ?> v<?= APP_VERSION ?></span>
                <span>• Quiz Arena Multiplayer & Solo</span>
            </div>
            <div class="flex items-center gap-6">
                <a href="<?= BASE_URL ?>/arena.php" class="hover:text-slate-300 transition">Arena</a>
                <a href="<?= BASE_URL ?>/ranking.php" class="hover:text-slate-300 transition">Ranking</a>
                <a href="<?= BASE_URL ?>/historico.php" class="hover:text-slate-300 transition">Auditoria & Logs</a>
                <a href="https://github.com/nicolasmarcelo107-cell/Codeflip-arena" target="_blank" class="hover:text-indigo-400 transition flex items-center gap-1.5">
                    <i class="fa-brands fa-github text-sm"></i> Repositório
                </a>
            </div>
            <div>
                &copy; <?= date('Y') ?> Codeflip Arena. Desenvolvido com PHP & Tailwind CSS.
            </div>
        </div>
    </footer>

    <!-- Inclusão do Modal e Scripts Globais -->
    <?php require_once __DIR__ . '/modal.php'; ?>
    <script src="<?= BASE_URL ?>/assets/js/app.js"></script>
    <?php require_once __DIR__ . '/toast.php'; ?>
</body>
</html>
