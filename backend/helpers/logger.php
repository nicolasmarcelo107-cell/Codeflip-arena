<?php
/**
 * Codeflip Arena - Módulo de Auditoria e Logs de Alterações
 * Requisito: Manter sempre os logs de qualquer alteração feita por qualquer usuário para auditorias futuras.
 */

require_once __DIR__ . '/../../bd.php';

/**
 * Registra um evento de auditoria no banco de dados
 *
 * @param int|null $usuario_id ID do usuário (ou null se anônimo)
 * @param string|null $usuario_nome Nome ou identificador do usuário
 * @param string $acao Identificador da ação (ex: 'CADASTRO', 'LOGIN', 'SENHA_ALTERADA', 'QUIZ_JOGADO')
 * @param string $detalhes Informações complementares ou payload resumido da ação
 * @return bool
 */
function logAction($usuario_id, $usuario_nome, $acao, $detalhes = '') {
    $pdo = getDB();
    if (!$pdo) {
        return false;
    }

    // Identificação de IP e User Agent
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    if (strpos($ip, ',') !== false) {
        $ip = trim(explode(',', $ip)[0]);
    }
    $ip = substr($ip, 0, 45);
    $user_agent = substr($_SERVER['HTTP_USER_AGENT'] ?? 'Desconhecido', 0, 255);

    try {
        $sql = "INSERT INTO logs (usuario_id, usuario_nome, acao, detalhes, ip_address, user_agent, created_at)
                VALUES (:usuario_id, :usuario_nome, :acao, :detalhes, :ip_address, :user_agent, :created_at)";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            ':usuario_id'    => $usuario_id,
            ':usuario_nome'  => $usuario_nome,
            ':acao'          => strtoupper($acao),
            ':detalhes'      => $detalhes,
            ':ip_address'    => $ip,
            ':user_agent'    => $user_agent,
            ':created_at'    => date('Y-m-d H:i:s')
        ]);
    } catch (PDOException $e) {
        error_log("Falha ao registrar log de auditoria: " . $e->getMessage());
        return false;
    }
}
