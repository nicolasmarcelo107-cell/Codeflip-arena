## Objetivos
- Desenvolver a **Codeflip Arena**, uma plataforma web interativa, moderna e responsiva de quizzes educativos e competitivos de diversos temas (Programação & Web, Banco de Dados, Cultura Geek, Ciência & Tecnologia, Cinema, Séries e Conhecimentos Gerais).
- Proporcionar aos usuários uma experiência imersiva de aprendizado e diversão com pontuações em tempo real, streaks de combo, cronômetro dinâmico por questão, ganho de experiência (XP), progressão de nível de jogador e ranking dos campeões.
- Disponibilizar aos administradores ferramentas completas para gerenciar categorias, banco de perguntas/respostas, usuários e fiscalização de conformidade via auditoria.

### Stack Tecnológico
- Backend: PHP estruturado com sessões nativas
- Banco de dados: MySQL (PDO para segurança) com compatibilidade SQLite para testes rápidos
- Frontend: HTML5, PHP, CSS, Tailwind CSS

#### Regras de negócio (CORE)
- **Criptografia de Senhas**: Tratar senhas de usuários com hash bcrypt nativo (`PASSWORD_BCRYPT`).
- **Auditoria e Rastreabilidade Completa**: O sistema deve ter uma página de históricos e manter sempre os logs de qualquer alteração feita por qualquer usuário, para auditorias futuras (registrando ID do usuário, nome, tipo de ação, detalhes, IP e data/hora).
- **Mecânica de Jogo (Gameplay)**:
  - Partidas com limite de tempo por questão (20 segundos).
  - Bônus de pontuação para respostas rápidas e multiplicador por sequência de acertos consecutivos (streaks).
  - Sistema de XP e cálculo progressivo de níveis para os jogadores.
  - Revisão pós-partida com gabarito e explicação detalhada de cada questão.
- **Controle de Acesso Baseado em Cargos (RBAC)**:
  - Usuários Comuns (`user`): jogam quizzes, visualizam seus históricos, sobem de nível e personalizam seu perfil.
  - Administradores (`admin`): controlam categorias, perguntas, usuários e visualizam a trilha integral de logs do sistema.

##### Regras Globais
- Use sempre PDO para conexão e queries no MySQL para evitar SQL Injections
- Mantenha o código limpo e comente apenas lógicas complexas.
- Separe os arquivos de forma lógica: um arquivo para conexão com a base (bd.php) e scripts de backend isolados e views em HTML5/PHP, nunca faça o sistema como um monolito, deixe sempre separados todas as regras para facilitar os futuros upgrades.
- Estilize as telas em Tailwind de forma responsiva priorizando o Mobile-First.
- Retorne sempre as mensagens de erros de forma claras na interface para o usuário (TOAST)
- Sempre trate as mensagens de caixa de mensagens nativas do navegador em um MODAL
