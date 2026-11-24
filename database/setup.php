<?php
/**
 * Codeflip Arena - Instalador e Inicializador da Base de Dados
 * Executável via CLI: php database/setup.php
 * Ou acessível via Navegador com interface amigável.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/bd.php';
require_once __DIR__ . '/../backend/helpers/logger.php';

$pdo = getDB();
$is_cli = (php_sapi_name() === 'cli');
$messages = [];

function out($msg) {
    global $messages, $is_cli;
    $messages[] = $msg;
    if ($is_cli) {
        echo $msg . PHP_EOL;
    }
}

try {
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    out(">> Inicializando banco de dados com driver: " . strtoupper($driver));

    if ($driver === 'sqlite') {
        // Esquema para SQLite
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                nome VARCHAR(100) NOT NULL,
                email VARCHAR(191) NOT NULL UNIQUE,
                senha VARCHAR(255) NOT NULL,
                role VARCHAR(20) DEFAULT 'user',
                avatar VARCHAR(50) DEFAULT 'user-astronaut',
                xp INTEGER DEFAULT 0,
                nivel INTEGER DEFAULT 1,
                partidas_jogadas INTEGER DEFAULT 0,
                acertos_total INTEGER DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS categorias (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                nome VARCHAR(100) NOT NULL,
                slug VARCHAR(100) NOT NULL UNIQUE,
                descricao TEXT,
                icone VARCHAR(60) DEFAULT 'fa-gamepad',
                cor VARCHAR(60) DEFAULT 'from-blue-600 to-indigo-700',
                ativo INTEGER DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS perguntas (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                categoria_id INTEGER NOT NULL,
                pergunta TEXT NOT NULL,
                dificuldade VARCHAR(20) DEFAULT 'medio',
                pontos INTEGER DEFAULT 10,
                explicacao TEXT,
                ativo INTEGER DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE CASCADE
            );

            CREATE TABLE IF NOT EXISTS opcoes (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                pergunta_id INTEGER NOT NULL,
                texto TEXT NOT NULL,
                is_correta INTEGER DEFAULT 0,
                FOREIGN KEY (pergunta_id) REFERENCES perguntas(id) ON DELETE CASCADE
            );

            CREATE TABLE IF NOT EXISTS partidas (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                usuario_id INTEGER NOT NULL,
                categoria_id INTEGER NOT NULL,
                pontuacao_total INTEGER DEFAULT 0,
                acertos INTEGER DEFAULT 0,
                total_perguntas INTEGER DEFAULT 0,
                tempo_gasto_segundos INTEGER DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (usuario_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE CASCADE
            );

            CREATE TABLE IF NOT EXISTS logs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                usuario_id INTEGER NULL,
                usuario_nome VARCHAR(100) NULL,
                acao VARCHAR(100) NOT NULL,
                detalhes TEXT,
                ip_address VARCHAR(45) DEFAULT NULL,
                user_agent VARCHAR(255) DEFAULT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );
        ");
    } else {
        // Esquema para MySQL
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `users` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `nome` VARCHAR(100) NOT NULL,
                `email` VARCHAR(191) NOT NULL UNIQUE,
                `senha` VARCHAR(255) NOT NULL,
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

            CREATE TABLE IF NOT EXISTS `opcoes` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `pergunta_id` INT NOT NULL,
                `texto` TEXT NOT NULL,
                `is_correta` TINYINT(1) DEFAULT 0,
                FOREIGN KEY (`pergunta_id`) REFERENCES `perguntas`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
        ");
    }
    out("[OK] Tabelas verificadas/criadas com sucesso.");

    // Seed de Usuários (com hash bcrypt obrigatório)
    $stmtUser = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
    
    // 1. Admin
    $stmtUser->execute([':email' => 'admin@codeflip.com']);
    if ($stmtUser->fetchColumn() == 0) {
        $senhaAdminHash = password_hash('admin123', PASSWORD_BCRYPT);
        $insertAdmin = $pdo->prepare("
            INSERT INTO users (nome, email, senha, role, avatar, xp, nivel)
            VALUES (:nome, :email, :senha, 'admin', 'user-shield', 500, 4)
        ");
        $insertAdmin->execute([
            ':nome'  => 'Administrador Codeflip',
            ':email' => 'admin@codeflip.com',
            ':senha' => $senhaAdminHash
        ]);
        out("[OK] Usuário Admin criado (admin@codeflip.com / admin123)");
    }

    // 2. Jogador Padrão
    $stmtUser->execute([':email' => 'jogador@codeflip.com']);
    if ($stmtUser->fetchColumn() == 0) {
        $senhaUserHash = password_hash('jogador123', PASSWORD_BCRYPT);
        $insertUser = $pdo->prepare("
            INSERT INTO users (nome, email, senha, role, avatar, xp, nivel, partidas_jogadas, acertos_total)
            VALUES (:nome, :email, :senha, 'user', 'user-astronaut', 250, 3, 5, 22)
        ");
        $insertUser->execute([
            ':nome'  => 'Player Um',
            ':email' => 'jogador@codeflip.com',
            ':senha' => $senhaUserHash
        ]);
        out("[OK] Usuário Jogador criado (jogador@codeflip.com / jogador123)");
    }

    // Seed de Categorias
    $categoriasSeed = [
        [
            'nome' => 'Programação & Web',
            'slug' => 'programacao-web',
            'descricao' => 'Desafie seus conhecimentos em PHP, JavaScript, Python, CSS e arquitetura de software.',
            'icone' => 'fa-code',
            'cor' => 'from-blue-600 via-indigo-600 to-purple-600'
        ],
        [
            'nome' => 'Banco de Dados & SQL',
            'slug' => 'banco-de-dados',
            'descricao' => 'Queries, modelagem relacional, chaves, índices, PDO e comandos essenciais do MySQL.',
            'icone' => 'fa-database',
            'cor' => 'from-emerald-600 via-teal-600 to-cyan-600'
        ],
        [
            'nome' => 'Cultura Geek & Games',
            'slug' => 'geek-games',
            'descricao' => 'Mundo dos games, franquias históricas, consoles, easter eggs e mitologia dos videogames.',
            'icone' => 'fa-gamepad',
            'cor' => 'from-purple-600 via-pink-600 to-rose-600'
        ],
        [
            'nome' => 'Ciência & Tecnologia',
            'slug' => 'ciencia-tecnologia',
            'descricao' => 'Inteligência Artificial, computação quântica, exploração espacial e inovações.',
            'icone' => 'fa-atom',
            'cor' => 'from-amber-500 via-orange-600 to-red-600'
        ],
        [
            'nome' => 'Cinema, Séries & Animes',
            'slug' => 'cinema-series',
            'descricao' => 'Obras cinematográficas, curiosidades sobre diretores, clássicos do streaming e animes lendários.',
            'icone' => 'fa-film',
            'cor' => 'from-rose-600 via-red-600 to-orange-500'
        ],
        [
            'nome' => 'Conhecimentos Gerais',
            'slug' => 'conhecimentos-gerais',
            'descricao' => 'Geografia global, momentos históricos, invenções humanas e fatos surpreendentes.',
            'icone' => 'fa-globe-americas',
            'cor' => 'from-teal-500 via-cyan-600 to-blue-600'
        ]
    ];

    $stmtCheckCat = $pdo->prepare("SELECT id FROM categorias WHERE slug = :slug");
    $stmtInsertCat = $pdo->prepare("
        INSERT INTO categorias (nome, slug, descricao, icone, cor, ativo)
        VALUES (:nome, :slug, :descricao, :icone, :cor, 1)
    ");

    $catIds = [];
    foreach ($categoriasSeed as $cat) {
        $stmtCheckCat->execute([':slug' => $cat['slug']]);
        $existingId = $stmtCheckCat->fetchColumn();
        if (!$existingId) {
            $stmtInsertCat->execute([
                ':nome'      => $cat['nome'],
                ':slug'      => $cat['slug'],
                ':descricao' => $cat['descricao'],
                ':icone'     => $cat['icone'],
                ':cor'       => $cat['cor']
            ]);
            $catIds[$cat['slug']] = $pdo->lastInsertId();
            out("[OK] Categoria adicionada: {$cat['nome']}");
        } else {
            $catIds[$cat['slug']] = $existingId;
        }
    }

    // Seed de Perguntas e Opções
    $perguntasSeed = [
        // Categoria: Programação & Web
        [
            'cat_slug' => 'programacao-web',
            'pergunta' => 'Qual função nativa do PHP é a mais recomendada para armazenar senhas de forma segura utilizando o algoritmo Bcrypt?',
            'dificuldade' => 'facil',
            'pontos' => 10,
            'explicacao' => 'A função password_hash($senha, PASSWORD_BCRYPT) gera automaticamente um hash com salt seguro e fator de custo.',
            'opcoes' => [
                ['texto' => 'password_hash($senha, PASSWORD_BCRYPT)', 'correta' => 1],
                ['texto' => 'md5($senha)', 'correta' => 0],
                ['texto' => 'sha1(base64_encode($senha))', 'correta' => 0],
                ['texto' => 'crypt_blowfish($senha)', 'correta' => 0]
            ]
        ],
        [
            'cat_slug' => 'programacao-web',
            'pergunta' => 'Por que o uso de PDO com Prepared Statements previne vulnerabilidades de SQL Injection?',
            'dificuldade' => 'medio',
            'pontos' => 15,
            'explicacao' => 'Prepared statements separam os comandos SQL dos dados enviados, garantindo que valores de entrada nunca sejam executados como código SQL.',
            'opcoes' => [
                ['texto' => 'Separa a sintaxe SQL dos parâmetros enviados pelo usuário', 'correta' => 1],
                ['texto' => 'Criptografa todas as tabelas do MySQL em tempo real', 'correta' => 0],
                ['texto' => 'Remove automaticamente tags HTML das strings recebidas', 'correta' => 0],
                ['texto' => 'Impede conexões não autorizadas ao Apache', 'correta' => 0]
            ]
        ],
        [
            'cat_slug' => 'programacao-web',
            'pergunta' => 'No Tailwind CSS, qual classe utilitária é utilizada para centralizar um container flexível vertical e horizontalmente?',
            'dificuldade' => 'facil',
            'pontos' => 10,
            'explicacao' => 'items-center alinha no eixo transversal (vertical) e justify-center no eixo principal (horizontal).',
            'opcoes' => [
                ['texto' => 'flex items-center justify-center', 'correta' => 1],
                ['texto' => 'flex align-middle text-center', 'correta' => 0],
                ['texto' => 'grid place-middle center-all', 'correta' => 0],
                ['texto' => 'display-flex center-content-box', 'correta' => 0]
            ]
        ],
        [
            'cat_slug' => 'programacao-web',
            'pergunta' => 'Qual das seguintes superglobais do PHP é mantida no lado do servidor entre diferentes requisições HTTP do mesmo cliente?',
            'dificuldade' => 'facil',
            'pontos' => 10,
            'explicacao' => '$_SESSION armazena dados persistidos no servidor através de session_start().',
            'opcoes' => [
                ['texto' => '$_SESSION', 'correta' => 1],
                ['texto' => '$_POST', 'correta' => 0],
                ['texto' => '$_SERVER', 'correta' => 0],
                ['texto' => '$_REQUEST', 'correta' => 0]
            ]
        ],
        [
            'cat_slug' => 'programacao-web',
            'pergunta' => 'Em JavaScript moderno, qual método de array retorna um novo array contendo apenas os elementos que atendem a uma determinada condição?',
            'dificuldade' => 'facil',
            'pontos' => 10,
            'explicacao' => 'O método filter() cria uma cópia rasa de uma parte de um determinado array, filtrada apenas para os elementos que passarem no teste.',
            'opcoes' => [
                ['texto' => 'filter()', 'correta' => 1],
                ['texto' => 'map()', 'correta' => 0],
                ['texto' => 'reduce()', 'correta' => 0],
                ['texto' => 'forEach()', 'correta' => 0]
            ]
        ],

        // Categoria: Banco de Dados & SQL
        [
            'cat_slug' => 'banco-de-dados',
            'pergunta' => 'Qual tipo de JOIN no SQL retorna todos os registros da tabela da esquerda e os correspondentes da direita?',
            'dificuldade' => 'medio',
            'pontos' => 15,
            'explicacao' => 'LEFT JOIN (ou LEFT OUTER JOIN) preserva todas as linhas da tabela esquerda mesmo sem correspondência na direita.',
            'opcoes' => [
                ['texto' => 'LEFT JOIN', 'correta' => 1],
                ['texto' => 'INNER JOIN', 'correta' => 0],
                ['texto' => 'RIGHT JOIN', 'correta' => 0],
                ['texto' => 'CROSS JOIN', 'correta' => 0]
            ]
        ],
        [
            'cat_slug' => 'banco-de-dados',
            'pergunta' => 'Qual cláusula SQL é utilizada para filtrar resultados de agregações feitas com GROUP BY?',
            'dificuldade' => 'medio',
            'pontos' => 15,
            'explicacao' => 'WHERE filtra registros individuais antes da agregação; HAVING filtra após a agregação.',
            'opcoes' => [
                ['texto' => 'HAVING', 'correta' => 1],
                ['texto' => 'WHERE', 'correta' => 0],
                ['texto' => 'ORDER BY', 'correta' => 0],
                ['texto' => 'FILTER BY', 'correta' => 0]
            ]
        ],
        [
            'cat_slug' => 'banco-de-dados',
            'pergunta' => 'Qual propriedade do padrão ACID garante que, caso ocorra uma falha durante uma transação, nenhuma de suas operações parciais será gravada?',
            'dificuldade' => 'dificil',
            'pontos' => 20,
            'explicacao' => 'A Atomicidade garante que uma transação seja executada como uma unidade única e indivisível (tudo ou nada).',
            'opcoes' => [
                ['texto' => 'Atomicidade', 'correta' => 1],
                ['texto' => 'Consistência', 'correta' => 0],
                ['texto' => 'Isolamento', 'correta' => 0],
                ['texto' => 'Durabilidade', 'correta' => 0]
            ]
        ],

        // Categoria: Cultura Geek & Games
        [
            'cat_slug' => 'geek-games',
            'pergunta' => 'Em qual jogo clássico de 1981 o icônico personagem Mario fez sua primeira aparição pública (inicialmente conhecido como Jumpman)?',
            'dificuldade' => 'facil',
            'pontos' => 10,
            'explicacao' => 'Mario estreou como Jumpman no jogo de arcade Donkey Kong em 1981, criado por Shigeru Miyamoto.',
            'opcoes' => [
                ['texto' => 'Donkey Kong', 'correta' => 1],
                ['texto' => 'Super Mario Bros', 'correta' => 0],
                ['texto' => 'Mario Kart 64', 'correta' => 0],
                ['texto' => 'Pac-Man', 'correta' => 0]
            ]
        ],
        [
            'cat_slug' => 'geek-games',
            'pergunta' => 'Qual é o nome da espada lendária empunhada por Cloud Strife em Final Fantasy VII?',
            'dificuldade' => 'medio',
            'pontos' => 15,
            'explicacao' => 'A enorme espada empunhada por Cloud se chama Buster Sword (Espada Destruidora).',
            'opcoes' => [
                ['texto' => 'Buster Sword', 'correta' => 1],
                ['texto' => 'Masamune', 'correta' => 0],
                ['texto' => 'Gunblade', 'correta' => 0],
                ['texto' => 'Master Sword', 'correta' => 0]
            ]
        ],
        [
            'cat_slug' => 'geek-games',
            'pergunta' => 'No universo de The Legend of Zelda, quais são os três poderes divinos representados pelos triângulos da Triforce?',
            'dificuldade' => 'medio',
            'pontos' => 15,
            'explicacao' => 'A Triforce é composta por Poder (Ganon), Sabedoria (Zelda) e Coragem (Link).',
            'opcoes' => [
                ['texto' => 'Poder, Sabedoria e Coragem', 'correta' => 1],
                ['texto' => 'Força, Destreza e Inteligência', 'correta' => 0],
                ['texto' => 'Honra, Glória e Destino', 'correta' => 0],
                ['texto' => 'Fogo, Luz e Escuridão', 'correta' => 0]
            ]
        ],

        // Categoria: Ciência & Tecnologia
        [
            'cat_slug' => 'ciencia-tecnologia',
            'pergunta' => 'Quem é historicamente reconhecida como a primeira pessoa a escrever um algoritmo computacional processável por máquina?',
            'dificuldade' => 'facil',
            'pontos' => 10,
            'explicacao' => 'Ada Lovelace trabalhou na Máquina Analítica de Charles Babbage e publicou o primeiro algoritmo em 1843.',
            'opcoes' => [
                ['texto' => 'Ada Lovelace', 'correta' => 1],
                ['texto' => 'Grace Hopper', 'correta' => 0],
                ['texto' => 'Alan Turing', 'correta' => 0],
                ['texto' => 'Margaret Hamilton', 'correta' => 0]
            ]
        ],
        [
            'cat_slug' => 'ciencia-tecnologia',
            'pergunta' => 'Qual é a unidade fundamental de informação na computação quântica?',
            'dificuldade' => 'medio',
            'pontos' => 15,
            'explicacao' => 'O Qubit (quantum bit) pode representar estados 0, 1 ou uma superposição simultânea de ambos.',
            'opcoes' => [
                ['texto' => 'Qubit', 'correta' => 1],
                ['texto' => 'Byte', 'correta' => 0],
                ['texto' => 'Quark', 'correta' => 0],
                ['texto' => 'Fóton lógico', 'correta' => 0]
            ]
        ],

        // Categoria: Cinema, Séries & Animes
        [
            'cat_slug' => 'cinema-series',
            'pergunta' => 'No filme The Matrix (1999), qual pílula Neo deve escolher para permanecer na ilusão e esquecer tudo?',
            'dificuldade' => 'facil',
            'pontos' => 10,
            'explicacao' => 'Morpheus explica que com a pílula azul você acorda em sua cama e acredita no que quiser; com a vermelha vê a verdade.',
            'opcoes' => [
                ['texto' => 'A pílula azul', 'correta' => 1],
                ['texto' => 'A pílula vermelha', 'correta' => 0],
                ['texto' => 'A pílula verde', 'correta' => 0],
                ['texto' => 'A pílula amarela', 'correta' => 0]
            ]
        ],
        [
            'cat_slug' => 'cinema-series',
            'pergunta' => 'Em Fullmetal Alchemist, qual é a Primeira Regra Fundamental da Alquimia?',
            'dificuldade' => 'facil',
            'pontos' => 10,
            'explicacao' => 'A Lei da Troca Equivalente: a humanidade não pode obter nada sem antes dar algo em troca com o mesmo valor.',
            'opcoes' => [
                ['texto' => 'A Lei da Troca Equivalente', 'correta' => 1],
                ['texto' => 'A Proibição da Transmutação Humana', 'correta' => 0],
                ['texto' => 'O Dogma da Pedra Filosofal', 'correta' => 0],
                ['texto' => 'A Conservação da Energia Cinética', 'correta' => 0]
            ]
        ],

        // Categoria: Conhecimentos Gerais
        [
            'cat_slug' => 'conhecimentos-gerais',
            'pergunta' => 'Qual é a montanha mais alta do planeta Terra considerando a medição acima do nível do mar?',
            'dificuldade' => 'facil',
            'pontos' => 10,
            'explicacao' => 'O Monte Everest atinge 8.848 metros de altitude na Cordilheira do Himalaia.',
            'opcoes' => [
                ['texto' => 'Monte Everest', 'correta' => 1],
                ['texto' => 'K2 (Godwin-Austen)', 'correta' => 0],
                ['texto' => 'Monte Kilimanjaro', 'correta' => 0],
                ['texto' => 'Aconcágua', 'correta' => 0]
            ]
        ],
        [
            'cat_slug' => 'conhecimentos-gerais',
            'pergunta' => 'Qual elemento químico possui o número atômico 1 e é o elemento mais abundante em todo o Universo observável?',
            'dificuldade' => 'facil',
            'pontos' => 10,
            'explicacao' => 'O Hidrogênio (H) possui 1 próton e compõe cerca de 75% da massa bariônica do cosmos.',
            'opcoes' => [
                ['texto' => 'Hidrogênio', 'correta' => 1],
                ['texto' => 'Hélio', 'correta' => 0],
                ['texto' => 'Oxigênio', 'correta' => 0],
                ['texto' => 'Carbono', 'correta' => 0]
            ]
        ]
    ];

    $stmtCheckQ = $pdo->prepare("SELECT id FROM perguntas WHERE pergunta = :pergunta");
    $stmtInsertQ = $pdo->prepare("
        INSERT INTO perguntas (categoria_id, pergunta, dificuldade, pontos, explicacao, ativo)
        VALUES (:categoria_id, :pergunta, :dificuldade, :pontos, :explicacao, 1)
    ");
    $stmtInsertOp = $pdo->prepare("
        INSERT INTO opcoes (pergunta_id, texto, is_correta)
        VALUES (:pergunta_id, :texto, :is_correta)
    ");

    $countQ = 0;
    foreach ($perguntasSeed as $q) {
        if (!isset($catIds[$q['cat_slug']])) continue;
        $catId = $catIds[$q['cat_slug']];

        $stmtCheckQ->execute([':pergunta' => $q['pergunta']]);
        $existingQId = $stmtCheckQ->fetchColumn();

        if (!$existingQId) {
            $stmtInsertQ->execute([
                ':categoria_id' => $catId,
                ':pergunta'     => $q['pergunta'],
                ':dificuldade'  => $q['dificuldade'],
                ':pontos'       => $q['pontos'],
                ':explicacao'   => $q['explicacao']
            ]);
            $newQId = $pdo->lastInsertId();

            foreach ($q['opcoes'] as $opt) {
                $stmtInsertOp->execute([
                    ':pergunta_id' => $newQId,
                    ':texto'       => $opt['texto'],
                    ':is_correta'  => $opt['correta']
                ]);
            }
            $countQ++;
        }
    }
    out("[OK] {$countQ} novas perguntas cadastradas com suas alternativas.");

    // Registro do log de instalação (auditoria)
    logAction(1, 'Administrador Codeflip', 'SISTEMA_INSTALADO', 'Base de dados e tabelas inicializadas com sucesso.');
    out("[OK] Auditoria inicial gravada na tabela de logs.");
    out(">> Instalação e verificação concluídas com sucesso!");

} catch (Exception $e) {
    out("[ERRO CRÍTICO] " . $e->getMessage());
}

if (!$is_cli):
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalação - Codeflip Arena</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-xl w-full bg-slate-900 border border-slate-800 rounded-2xl p-6 sm:p-8 shadow-2xl">
        <div class="flex items-center gap-4 mb-6">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-tr from-indigo-500 to-purple-600 flex items-center justify-center text-white text-2xl shadow-lg">
                <i class="fa-solid fa-gamepad"></i>
            </div>
            <div>
                <h1 class="text-2xl font-black tracking-tight text-white">Codeflip Arena</h1>
                <p class="text-sm text-slate-400">Instalação e Verificação do Banco de Dados</p>
            </div>
        </div>

        <div class="space-y-2 mb-6 max-h-72 overflow-y-auto font-mono text-xs bg-slate-950 p-4 rounded-xl border border-slate-800/80">
            <?php foreach ($messages as $msg): ?>
                <div class="<?= strpos($msg, '[ERRO') !== false ? 'text-rose-400' : (strpos($msg, '[OK]') !== false ? 'text-emerald-400' : 'text-indigo-300') ?>">
                    <?= htmlspecialchars($msg) ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="bg-indigo-950/40 border border-indigo-500/20 rounded-xl p-4 mb-6 text-sm">
            <p class="text-indigo-200 font-semibold mb-1"><i class="fa-solid fa-key mr-2"></i>Contas Padrão de Acesso:</p>
            <p class="text-slate-300"><strong>Admin:</strong> admin@codeflip.com | <strong>Senha:</strong> admin123</p>
            <p class="text-slate-300"><strong>Jogador:</strong> jogador@codeflip.com | <strong>Senha:</strong> jogador123</p>
        </div>

        <a href="<?= BASE_URL ?>/index.php" class="w-full inline-flex items-center justify-center gap-2 py-3.5 px-6 rounded-xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white shadow-lg shadow-indigo-500/25 transition">
            <i class="fa-solid fa-arrow-right"></i> Entrar na Arena
        </a>
    </div>
</body>
</html>
<?php endif; ?>
