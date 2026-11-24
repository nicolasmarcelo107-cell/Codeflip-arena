<?php
/**
 * Codeflip Arena - Componente de Modal Nativo Tailwind
 * Requisito: Tratar mensagens de caixa de mensagens nativas do navegador em um MODAL
 */
?>
<!-- Estrutura de Modal Global para Substituição de Alert/Confirm -->
<div id="arena-modal-root" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm hidden opacity-0 transition-opacity duration-200">
    <div id="arena-modal-box" class="max-w-md w-full bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-2xl transform scale-95 transition-transform duration-200">
        <div class="flex items-center gap-3 mb-4">
            <div id="arena-modal-icon-container" class="w-10 h-10 rounded-xl flex items-center justify-center text-xl">
                <i class="fa-solid fa-circle-info"></i>
            </div>
            <h3 id="arena-modal-title" class="text-lg font-bold text-white tracking-wide">Notificação</h3>
        </div>
        <p id="arena-modal-text" class="text-sm text-slate-300 leading-relaxed mb-6"></p>
        <div id="arena-modal-actions" class="flex items-center justify-end gap-3">
            <!-- Botões inseridos dinamicamente pelo ArenaModal JS -->
        </div>
    </div>
</div>
