# CinePimenta - Aluguel de Assentos de Cinema 🍿🎬

Sistema completo e responsivo de reserva e aluguel de poltronas de cinema desenvolvido estritamente de acordo com as especificações do arquivo [`requisitos.md`](requisitos.md).

---

## 🎯 Objetivos Atendidos

O sistema funciona em fluxo contínuo de **5 etapas**:
1. **Etapa 1 (`index.php`)**: Catálogo completo dos filmes em cartaz e horários do dia, com filtros por gênero, pesquisa e status.
2. **Etapa 2 (`filme.php`)**: Apresentação detalhada do filme selecionado com sinopse, classificação indicativa, trailer oficial e seleção da sessão/sala desejada.
3. **Etapa 3 (`assentos.php`)**: Mapa interativo da sala de cinema em tempo real (tela curva luminosa, fileiras de A até F, assentos livres, ocupados, VIP e acessíveis para cadeirantes, com cálculo dinâmico de valor total).
4. **Etapa 4 (`pagamento.php`)**: Identificação do cliente e escolha da forma de pagamento (PIX Instantâneo com QR Code dinâmico, Cartão de Crédito com parcelamento, Cartão de Débito e Reserva na Bilheteria).
5. **Etapa 5 (`ticket.php`)**: Confirmação imediata e emissão do **Ticket / Ingresso de Cinema Realista** contendo picote digital, código de autenticação único, dados completos da sessão e QR Code para validação na entrada.

---

## 🛠️ Stack Tecnológico

- **Backend**: PHP estruturado com sessões nativas (`session_start()`).
- **Banco de Dados**: MySQL com **PDO** (Prepared Statements em todas as consultas para prevenção total contra SQL Injections) e fallback automático local para demonstração offline.
- **Frontend**: HTML5 semântico, PHP, CSS e **Tailwind CSS** (via CDN com visual Dark Cinema moderno e Mobile-First).
- **Ícones**: FontAwesome 6 Pro CDN.

---

## 🔒 Regras de Negócio Implementadas (CORE)

- **Criptografia Bcrypt**: Todas as senhas de usuários e gerentes são tratadas com `password_hash($senha, PASSWORD_BCRYPT)` e verificadas via `password_verify()`.
- **Histórico & Auditoria Global (`/admin/logs.php`)**: Toda e qualquer alteração realizada no sistema (cadastro/edição de filmes, exclusões, alterações de preço dos ingressos, criação de sessões, compras de ingressos, logins e logouts) é registrada automaticamente na tabela `auditoria_logs` com usuário responsável, ação, tabela afetada, detalhes antes/depois, data/hora e IP de origem.
- **Painel Administrativo do Gerente (`/admin/`)**:
  - Cadastro, alteração e exclusão de Filmes (`/admin/filmes.php`).
  - Cadastro e alteração de Sessões e Preço dos Ingressos (`/admin/sessoes.php`).
  - Cadastro e alteração de Salas e Capacidade de Lugares (`/admin/salas.php`).
  - Gestão de Ingressos Vendidos e Faturamento (`/admin/ingressos.php`).
  - Página de Histórico & Auditoria (`/admin/logs.php`).
- **Prevenção contra Diálogos Nativos**: O sistema **não utiliza** `alert()`, `confirm()` ou `prompt()` nativos do navegador. Todas as confirmações de exclusão e alertas utilizam um **Modal Tailwind** moderno com acessibilidade e suporte à tecla `ESC`.
- **Notificações Toast**: Mensagens de sucesso, erro e alertas são exibidas na interface através de componentes flutuantes **Toast** com auto-fechamento e animação suave.

---

## 👤 Credenciais Padrão para Testes

O sistema já vem populado com dados iniciais (SEEDS) e botões de preenchimento automático na tela de login:

| Perfil | E-mail | Senha | Acesso |
| :--- | :--- | :--- | :--- |
| **Gerente (Admin)** | `gerente@cinema.com` | `admin123` | Acesso total ao painel administrativo |
| **Cliente** | `cliente@cinema.com` | `cliente123` | Compra de ingressos e consulta de tickets |

---

## 🚀 Como Executar o Projeto

### Opção 1: Execução Rápida com 1 Clique (Recomendado)
Basta dar um duplo-clique no arquivo:
```cmd
iniciar_servidor.bat
```
O script iniciará o servidor PHP embutido e abrirá automaticamente o navegador em `http://localhost:8000`.

### Opção 2: Linha de Comando (CLI)
No terminal do Windows (PowerShell ou Prompt de Comando), na pasta do projeto:
```bash
php -S localhost:8000
```
Em seguida, acesse no navegador: `http://localhost:8000`

### Opção 3: XAMPP / WampServer / Laragon
1. Copie esta pasta para o diretório `htdocs` (ex: `C:\xampp\htdocs\pimentinhas`).
2. Abra o phpMyAdmin e importe o arquivo [`database.sql`](database.sql) (o arquivo [`bd.php`](bd.php) também cria o banco e tabelas automaticamente caso não existam).
3. Acesse no navegador: `http://localhost/pimentinhas/`

---

## 📂 Estrutura de Arquivos Organizada

```
pimentinhas/
├── bd.php                       # Conexão PDO, auto-setup e função registrar_auditoria()
├── database.sql                 # Script SQL com criação das tabelas e dados iniciais
├── iniciar_servidor.bat         # Inicializador rápido para Windows
├── index.php                    # Etapa 1: Filmes em cartaz e horários
├── filme.php                    # Etapa 2: Detalhes do filme e escolha da sessão
├── assentos.php                 # Etapa 3: Seleção interativa de poltronas
├── pagamento.php                # Etapa 4: Dados do comprador e pagamento
├── ticket.php                   # Etapa 5: Emissão e impressão do ingresso
├── meus_ingressos.php           # Histórico de ingressos comprados pelo cliente
├── login.php                    # Autenticação de clientes e gerentes
├── cadastro.php                 # Cadastro de clientes com hash bcrypt
├── logout.php                   # Encerramento de sessão nativa
│
├── includes/
│   ├── header.php               # Cabeçalho responsivo, menu mobile e stepper
│   ├── footer.php               # Rodapé e inclusão de modais/toasts
│   ├── toast.php                # Componente de notificações Toast
│   ├── modal.php                # Modais que substituem alert() e confirm()
│   └── auth.php                 # Proteção de rotas para clientes e gerentes
│
├── actions/
│   ├── login_action.php         # Processamento de login
│   ├── cadastro_action.php      # Processamento de cadastro
│   ├── processar_pagamento.php  # Transação de pagamento e emissão do ticket
│   ├── salvar_filme.php         # Cadastro/edição de filme com auditoria
│   ├── excluir_filme.php        # Exclusão de filme com auditoria
│   ├── salvar_sala.php          # Cadastro/edição de sala com auditoria
│   ├── excluir_sala.php         # Exclusão de sala com auditoria
│   ├── salvar_sessao.php        # Cadastro/edição de sessão e preço com auditoria
│   └── excluir_sessao.php       # Cancelamento de sessão com auditoria
│
├── admin/
│   ├── index.php                # Dashboard do gerente com métricas e faturamento
│   ├── filmes.php               # Gestão de filmes em cartaz e em breve
│   ├── salas.php                # Gestão de salas e capacidade
│   ├── sessoes.php              # Gestão de sessões e preços dos ingressos
│   ├── ingressos.php            # Relatório de vendas e bilheteria
│   └── logs.php                 # Histórico completo de auditoria do sistema
│
├── assets/
│   └── css/style.css            # Estilos personalizados (tela curva, poltronas, ticket)
│
├── README.md                    # Este documento
└── requisitos.md                # Requisitos oficiais do projeto
```

---
Projeto desenvolvido para a disciplina técnica da **ETEC Esportes**.
