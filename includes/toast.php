<?php
/**
 * Codeflip Arena - Renderizador de Mensagens Flash Toast
 * Requisito: Retorne sempre as mensagens de erros de forma claras na interface para o usuário (TOAST)
 */

require_once __DIR__ . '/../backend/helpers/toast_helper.php';

$pendingToast = getToast();
if ($pendingToast):
?>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        showToast(
            <?= json_encode($pendingToast['type']) ?>,
            <?= json_encode($pendingToast['message']) ?>,
            <?= json_encode($pendingToast['title']) ?>
        );
    });
</script>
<?php endif; ?>
