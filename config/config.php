<?php
/**
 * Codeflip Arena - Configurações Gerais da Aplicação
 */

// Inicialização segura de sessão nativa
if (session_status() === PHP_SESSION_NONE) {
    // Configurações de cookie de sessão para segurança
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    session_start();
}

// Configurações gerais
define('APP_NAME', 'Codeflip Arena');
define('APP_VERSION', '1.0.0');
define('APP_DESCRIPTION', 'Aplicativo de quiz de diversos temas para diversão e aprendizado');

// Definição de URLs e Caminhos
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';

define('ROOT_PATH', dirname(__DIR__));

// Calcula o subdiretório relativo ao DOCUMENT_ROOT (compatível com XAMPP, WAMP e php -S)
$docRoot = isset($_SERVER['DOCUMENT_ROOT']) ? str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']) ?: $_SERVER['DOCUMENT_ROOT']) : '';
$appRoot = str_replace('\\', '/', ROOT_PATH);
$subPath = '';

if (!empty($docRoot) && strpos($appRoot, $docRoot) === 0) {
    $subPath = substr($appRoot, strlen($docRoot));
}
$subPath = '/' . trim($subPath, '/');
$subPath = ($subPath === '/') ? '' : $subPath;

// Normaliza base URL sem barra final
define('BASE_URL', rtrim($protocol . $host . $subPath, '/'));

// Timezone padrão
date_default_timezone_set('America/Sao_Paulo');
