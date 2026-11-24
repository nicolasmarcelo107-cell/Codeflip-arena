-- ========================================================
-- Codeflip Arena - Script de Criação do Banco de Dados
-- MySQL / MariaDB (Compatível com PDO)
-- ========================================================

CREATE DATABASE IF NOT EXISTS `codeflip_arena` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `codeflip_arena`;

-- 1. Tabela de Usuários
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nome` VARCHAR(100) NOT NULL,
    `email` VARCHAR(191) NOT NULL UNIQUE,
    `senha` VARCHAR(255) NOT NULL COMMENT 'Hash seguro gerado com PASSWORD_BCRYPT',
    `role` ENUM('user', 'admin') DEFAULT 'user',
    `avatar` VARCHAR(50) DEFAULT 'user-astronaut',
    `xp` INT DEFAULT 0,
    `nivel` INT DEFAULT 1,
    `partidas_jogadas` INT DEFAULT 0,
    `acertos_total` INT DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_users_email` (`email`),
    INDEX `idx_users_xp` (`xp` DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tabela de Categorias
CREATE TABLE IF NOT EXISTS `categorias` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nome` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `descricao` TEXT,
    `icone` VARCHAR(60) DEFAULT 'fa-gamepad',
    `cor` VARCHAR(60) DEFAULT 'from-blue-600 to-indigo-700',
    `ativo` TINYINT(1) DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Tabela de Perguntas
CREATE TABLE IF NOT EXISTS `perguntas` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `categoria_id` INT NOT NULL,
    `pergunta` TEXT NOT NULL,
    `dificuldade` ENUM('facil', 'medio', 'dificil') DEFAULT 'medio',
    `pontos` INT DEFAULT 10,
    `explicacao` TEXT,
    `ativo` TINYINT(1) DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`categoria_id`) REFERENCES `categorias`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Tabela de Opções de Resposta
CREATE TABLE IF NOT EXISTS `opcoes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `pergunta_id` INT NOT NULL,
    `texto` TEXT NOT NULL,
    `is_correta` TINYINT(1) DEFAULT 0,
    FOREIGN KEY (`pergunta_id`) REFERENCES `perguntas`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Tabela de Histórico de Partidas
CREATE TABLE IF NOT EXISTS `partidas` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `usuario_id` INT NOT NULL,
    `categoria_id` INT NOT NULL,
    `pontuacao_total` INT DEFAULT 0,
    `acertos` INT DEFAULT 0,
    `total_perguntas` INT DEFAULT 0,
    `tempo_gasto_segundos` INT DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`usuario_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`categoria_id`) REFERENCES `categorias`(`id`) ON DELETE CASCADE,
    INDEX `idx_partidas_usuario` (`usuario_id`),
    INDEX `idx_partidas_pontos` (`pontuacao_total` DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Tabela de Auditoria e Logs do Sistema (Requisito CORE)
CREATE TABLE IF NOT EXISTS `logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `usuario_id` INT NULL,
    `usuario_nome` VARCHAR(100) NULL,
    `acao` VARCHAR(100) NOT NULL,
    `detalhes` TEXT,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `user_agent` VARCHAR(255) DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_logs_acao` (`acao`),
    INDEX `idx_logs_usuario` (`usuario_id`),
    INDEX `idx_logs_created` (`created_at` DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
