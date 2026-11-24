<?php
/**
 * Codeflip Arena - Gerenciador de Mensagens Toast
 * Requisito: Retorne sempre as mensagens de erros de forma claras na interface para o usuário (TOAST)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Define uma notificação Toast na sessão
 *
 * @param string $tipo 'error' | 'success' | 'warning' | 'info'
 * @param string $mensagem Texto da notificação
 * @param string $titulo Título opcional
 */
function setToast($tipo, $mensagem, $titulo = '') {
    $tipoValido = in_array($tipo, ['error', 'success', 'warning', 'info']) ? $tipo : 'info';
    
    if (empty($titulo)) {
        switch ($tipoValido) {
            case 'error':
                $titulo = 'Atenção!';
                break;
            case 'success':
                $titulo = 'Sucesso!';
                break;
            case 'warning':
                $titulo = 'Aviso';
                break;
            case 'info':
            default:
                $titulo = 'Informação';
                break;
        }
    }

    $_SESSION['toast'] = [
        'type'     => $tipoValido,
        'message'  => $mensagem,
        'title'    => $titulo,
        'id'       => uniqid('toast_')
    ];
}

/**
 * Recupera e consome o Toast pendente na sessão
 * @return array|null
 */
function getToast() {
    if (isset($_SESSION['toast'])) {
        $toast = $_SESSION['toast'];
        unset($_SESSION['toast']);
        return $toast;
    }
    return null;
}
