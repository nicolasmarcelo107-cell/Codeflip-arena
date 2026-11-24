<?php
/**
 * Codeflip Arena - Conexão com o Banco de Dados via PDO
 * Regra: Uso exclusivo de PDO com prepared statements para mitigar SQL Injection.
 */

require_once __DIR__ . '/config.php';

// Configurações do Banco de Dados MySQL
$db_host = getenv('DB_HOST') ?: '127.0.0.1';
$db_port = getenv('DB_PORT') ?: '3306';
$db_name = getenv('DB_NAME') ?: 'codeflip_arena';
$db_user = getenv('DB_USER') ?: 'root';
$db_pass = getenv('DB_PASS') ?: '';
$db_driver = getenv('DB_DRIVER') ?: 'mysql'; // 'mysql' ou 'sqlite'

$pdo = null;

try {
    if ($db_driver === 'sqlite') {
        // Modo SQLite explícito
        $sqlite_file = ROOT_PATH . '/database/codeflip_arena.sqlite';
        $pdo = new PDO("sqlite:" . $sqlite_file);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->exec("PRAGMA foreign_keys = ON;");
    } else {
        // Verifica rapidamente se a porta do MySQL está aberta antes de travar no timeout do Windows
        $mysql_online = false;
        $connection_test = @fsockopen($db_host, (int)$db_port, $errno, $errstr, 0.3);
        if (is_resource($connection_test)) {
            $mysql_online = true;
            fclose($connection_test);
        }

        if ($mysql_online) {
            $dsn = "mysql:host={$db_host};port={$db_port};dbname={$db_name};charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_TIMEOUT            => 1,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ];
            $pdo = new PDO($dsn, $db_user, $db_pass, $options);
        } else {
            // MySQL offline: ativa fallback SQLite para desenvolvimento e testes imediatos
            throw new PDOException("Servidor MySQL não está ativo na porta {$db_port}. Ativando modo local SQLite.");
        }
    }
} catch (PDOException $e) {
    // Caso o MySQL não esteja rodando ou a base não exista ainda,
    // verifica se é possível usar o fallback SQLite para execução imediata
    $sqlite_dir = ROOT_PATH . '/database';
    if (!is_dir($sqlite_dir)) {
        mkdir($sqlite_dir, 0777, true);
    }
    $sqlite_file = $sqlite_dir . '/codeflip_arena.sqlite';
    
    try {
        $pdo = new PDO("sqlite:" . $sqlite_file);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->exec("PRAGMA foreign_keys = ON;");
        
        // Define flag global indicando modo fallback
        $GLOBALS['DB_MODE'] = 'sqlite';
    } catch (PDOException $sqliteEx) {
        die("Erro crítico de conexão com o banco de dados: " . htmlspecialchars($e->getMessage()));
    }
}

/**
 * Função utilitária para obter a instância ativa do PDO
 * @return PDO
 */
function getDB() {
    global $pdo;
    return $pdo;
}
