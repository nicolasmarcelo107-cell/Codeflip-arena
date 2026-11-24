# Codeflip Arena 🎮⚡

Aplicativo web completo de quiz temático para diversão, aprendizado e competição saudável, desenvolvido em **PHP Estruturado**, **MySQL (PDO)** e **Tailwind CSS (Mobile-First)**.

---

## 🚀 Funcionalidades Principais

1. **Arena de Quizzes Multitemática**:
   - Categorias: Programação & Web, Banco de Dados & SQL, Cultura Geek & Games, Ciência & Tecnologia, Cinema, Séries & Animes e Conhecimentos Gerais.
   - Modo Batalha Mista (Roleta com perguntas aleatórias de todos os temas).
   - Cronômetro regressivo com aceleração de cor (verde -> amarelo -> vermelho).
   - Sistema de Combo / Streak multiplicador de pontuação.
   - Efeitos sonoros via Web Audio API.

2. **Segurança & Boas Práticas**:
   - Senhas criptografadas com `PASSWORD_BCRYPT`.
   - Conexão e queries 100% preparadas via **PDO** (prevenção total contra SQL Injection).
   - Proteção de sessões nativas com `session.cookie_httponly` e renovação periódica de ID.
   - Tokens de proteção CSRF em todos os formulários.

3. **Auditoria e Logs de Alterações (Requisito CORE)**:
   - Página dedicada (`historico.php`) com histórico de partidas e trilha de auditoria completa.
   - Registro permanente de qualquer alteração feita por qualquer usuário (cadastros, logins, atualizações de perfil, trocas de senha, partidas jogadas, criação, edição e exclusão de categorias e questões).
   - Filtros por tipo de ação, exibição de IP de origem e data/hora.

4. **Experiência do Usuário (UI/UX Mobile-First)**:
   - Design moderno no estilo gamer/neon com Tailwind CSS.
   - Notificações claras e elegantes em formato **TOAST** para todos os erros e alertas do sistema.
   - Substituição total de caixas nativas do navegador (`alert`/`confirm`) por **Modais Tailwind** responsivos com backdrop blur.

5. **Gamificação**:
   - Cálculo progressivo de XP e níveis (`calculateLevel` e `calculateLevelProgress`).
   - Tabela de classificação / Ranking geral com pódio para o Top 3.
   - Medalhas e conquistas desbloqueáveis no perfil do competidor.

6. **Painel Administrativo Completo**:
   - Dashboard com métricas globais e logs em tempo real.
   - CRUD completo de Categorias com ícones e slugs.
   - CRUD completo de Perguntas com 4 alternativas e gabarito.
   - Gestão de Usuários (promoção a administrador ou exclusão com confirmação em Modal).

---

## 📂 Estrutura de Arquivos

```
CODE FLIP/
├── bd.php                      # Ponto de conexão principal com o banco (PDO)
├── index.php                   # Página inicial / Landing Page
├── login.php                   # Tela de login
├── register.php                # Tela de cadastro com validação de senha
├── arena.php                   # Catálogo e seleção de categorias de quiz
├── play.php                    # Interface de gameplay interativo
├── resultado.php               # Resumo de pontuação, XP e revisão de questões
├── ranking.php                 # Tabela de classificação dos campeões
├── historico.php               # Página de históricos e auditoria (Logs do sistema)
├── perfil.php                  # Perfil do competidor com edição e estatísticas
├── config/
│   ├── config.php              # Configurações globais e inicialização de sessão
│   └── bd.php                  # Configuração do PDO (MySQL com fallback SQLite)
├── database/
│   ├── schema.sql              # Script DDL do banco de dados MySQL
│   └── setup.php               # Instalador e seeder automático (CLI ou navegador)
├── backend/
│   ├── auth/                   # Scripts isolados de autenticação
│   ├── profile/                # Scripts isolados de atualização cadastral
│   ├── quiz/                   # Motor de início, validação e término de partidas
│   ├── admin/                  # Ações restritas de gerenciamento
│   └── helpers/                # Módulos de auditoria (logger), toasts e auth
├── includes/
│   ├── header.php              # Cabeçalho HTML com CDN do Tailwind e fontes
│   ├── navbar.php              # Barra de navegação responsiva Mobile-First
│   ├── footer.php              # Rodapé da aplicação e inclusão de scripts
│   ├── toast.php               # Renderizador de mensagens flash
│   └── modal.php               # Estrutura do Modal Tailwind
├── assets/
│   ├── css/custom.css          # Animações e regras complementares
│   └── js/
│       ├── app.js              # Toasts, áudio e substituto de alert/confirm em modal
│       └── quiz.js             # Motor de jogo, cronômetro e feedback de acerto/erro
└── requisitos.md               # Especificação de requisitos do projeto
```

---

## 🛠️ Como Executar Localmente

### Pré-requisitos
- PHP 8.0 ou superior (com extensões `pdo_mysql` ou `pdo_sqlite` ativas).
- Servidor MySQL (opcional caso use MySQL; o projeto inclui suporte nativo com fallback automático para SQLite em desenvolvimento local).

### Passo a Passo

1. **Inicializar a Base de Dados**:
   Abra o terminal no diretório do projeto e execute:
   ```bash
   php database/setup.php
   ```
   *(Ou acesse pelo navegador a URL `http://localhost:8000/database/setup.php`)*

2. **Iniciar o Servidor Embutido do PHP**:
   ```bash
   php -S localhost:8000
   ```

3. **Acessar a Aplicação**:
   Abra seu navegador em: [http://localhost:8000](http://localhost:8000)

---

## 🔑 Credenciais Padrão de Demonstração

| Perfil | E-mail | Senha | Privilégios |
| :--- | :--- | :--- | :--- |
| **Administrador** | `admin@codeflip.com` | `admin123` | Acesso total ao painel, CRUDs e logs |
| **Jogador Padrão** | `jogador@codeflip.com` | `jogador123` | Jogo na arena, ranking e perfil |

*As senhas de demonstração já foram semeadas com hash `PASSWORD_BCRYPT` seguro.*

---

## 🛡️ Conformidade com os Requisitos

- [x] **Hash Bcrypt**: Implementado em cadastros, verificações de login e atualizações de senha.
- [x] **Página de Históricos & Auditoria**: Rastreamento sistemático de todas as modificações por usuário.
- [x] **PDO Exclusivo**: Prevenção ativa contra SQL Injections em 100% das consultas.
- [x] **Arquitetura Modular**: Separação clara entre `bd.php`, scripts de regras no `backend/` e `views/`.
- [x] **Mobile-First com Tailwind CSS**: Interface totalmente responsiva otimizada para smartphones e desktops.
- [x] **Notificações em TOAST**: Mensagens claras de sucesso, erro e advertência.
- [x] **Modais Interativos**: Substituição total de caixas de diálogo nativas do navegador.
