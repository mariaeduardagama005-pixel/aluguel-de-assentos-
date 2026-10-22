<?php
/**
 * =====================================================================
 * CONEXÃO COM O BANCO DE DADOS (PDO) & HELPERS GLOBAIS
 * =====================================================================
 * Atende às Regras Globais do Projeto:
 * - Uso obrigatório de PDO contra SQL Injection
 * - Registro centralizado de auditoria em tabela própria (auditoria_logs)
 * - Hash seguro de senhas com BCRYPT
 * - Mensagens de Toast integradas à sessão nativa
 * =====================================================================
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configurações do Banco de Dados MySQL
$db_host = 'localhost';
$db_port = '3306';
$db_name = 'cinema_assentos';
$db_user = 'root';
$db_pass = '';

$pdo = null;
$usando_sqlite = false;

try {
    // 1. Tenta conexão direta com o MySQL
    $dsn = "mysql:host={$db_host};port={$db_port};dbname={$db_name};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (PDOException $e) {
    // Se o banco não existe no MySQL, tenta conectar sem dbname e criar automaticamente
    try {
        $dsnSemBanco = "mysql:host={$db_host};port={$db_port};charset=utf8mb4";
        $pdoTemp = new PDO($dsnSemBanco, $db_user, $db_pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        $pdoTemp->exec("CREATE DATABASE IF NOT EXISTS `{$db_name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
        $pdoTemp = null;

        // Reconecta apontando para o banco criado
        $pdo = new PDO($dsn, $db_user, $db_pass, $options);
        inicializar_estrutura_banco($pdo, 'mysql');
    } catch (Exception $exMysql) {
        // Fallback seguro: se o MySQL não estiver ativo (ex: teste rápido local sem XAMPP ligado),
        // utiliza SQLite local via PDO para que o projeto funcione em qualquer computador sem travar.
        try {
            $sqlitePath = __DIR__ . '/cinema.sqlite';
            $pdo = new PDO("sqlite:" . $sqlitePath, null, null, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            $usando_sqlite = true;
            inicializar_estrutura_banco($pdo, 'sqlite');
        } catch (Exception $exSqlite) {
            die("Erro crítico de conexão com o banco de dados: " . htmlspecialchars($exSqlite->getMessage()));
        }
    }
}

// Garante que as tabelas existam caso seja primeira execução
if (!$usando_sqlite) {
    verificar_tabelas_mysql($pdo);
}

/**
 * Cria a estrutura inicial de tabelas e seeds caso não existam
 */
function verificar_tabelas_mysql(PDO $pdo) {
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE 'filmes'");
        if ($stmt->rowCount() === 0) {
            inicializar_estrutura_banco($pdo, 'mysql');
        }
    } catch (Exception $e) {
        // Ignora ou registra erro silencioso
    }
}

/**
 * Inicializador da estrutura de tabelas e dados de teste
 */
function inicializar_estrutura_banco(PDO $pdo, string $driver = 'mysql') {
    $sqlFile = __DIR__ . '/database.sql';
    if ($driver === 'mysql' && file_exists($sqlFile)) {
        $sql = file_get_contents($sqlFile);
        $pdo->exec($sql);
        return;
    }

    // Estrutura para SQLite (compatibilidade offline com PDO)
    if ($driver === 'sqlite') {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS usuarios (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                nome TEXT NOT NULL,
                email TEXT NOT NULL UNIQUE,
                senha TEXT NOT NULL,
                tipo TEXT NOT NULL DEFAULT 'cliente',
                telefone TEXT,
                cpf TEXT,
                criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
            );
            CREATE TABLE IF NOT EXISTS salas (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                nome TEXT NOT NULL,
                tipo TEXT NOT NULL DEFAULT '2D Padrão',
                fileiras INTEGER NOT NULL DEFAULT 6,
                colunas INTEGER NOT NULL DEFAULT 10,
                capacidade INTEGER NOT NULL DEFAULT 60,
                status TEXT NOT NULL DEFAULT 'ativa',
                criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
            );
            CREATE TABLE IF NOT EXISTS filmes (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                titulo TEXT NOT NULL,
                titulo_original TEXT,
                sinopse TEXT NOT NULL,
                genero TEXT NOT NULL,
                duracao INTEGER NOT NULL,
                classificacao TEXT NOT NULL DEFAULT '12',
                cartaz_url TEXT NOT NULL,
                banner_url TEXT,
                trailer_url TEXT,
                status TEXT NOT NULL DEFAULT 'em_cartaz',
                criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
            );
            CREATE TABLE IF NOT EXISTS sessoes (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                filme_id INTEGER NOT NULL,
                sala_id INTEGER NOT NULL,
                data DATE NOT NULL,
                horario TIME NOT NULL,
                idioma TEXT NOT NULL DEFAULT 'Dublado',
                tipo_sessao TEXT NOT NULL DEFAULT '2D',
                preco_ingresso REAL NOT NULL DEFAULT 32.00,
                status TEXT NOT NULL DEFAULT 'ativa',
                criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
            );
            CREATE TABLE IF NOT EXISTS ingressos (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                codigo TEXT NOT NULL UNIQUE,
                sessao_id INTEGER NOT NULL,
                usuario_id INTEGER,
                nome_cliente TEXT NOT NULL,
                email_cliente TEXT NOT NULL,
                cpf_cliente TEXT NOT NULL,
                assentos_selecionados TEXT NOT NULL,
                quantidade_assentos INTEGER NOT NULL DEFAULT 1,
                valor_unitario REAL NOT NULL,
                valor_total REAL NOT NULL,
                forma_pagamento TEXT NOT NULL DEFAULT 'pix',
                status_pagamento TEXT NOT NULL DEFAULT 'aprovado',
                codigo_transacao TEXT,
                criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
            );
            CREATE TABLE IF NOT EXISTS assentos_ocupados (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                sessao_id INTEGER NOT NULL,
                ingresso_id INTEGER NOT NULL,
                codigo_assento TEXT NOT NULL,
                criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
                UNIQUE(sessao_id, codigo_assento)
            );
            CREATE TABLE IF NOT EXISTS auditoria_logs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                usuario_id INTEGER,
                usuario_nome TEXT NOT NULL DEFAULT 'Visitante',
                usuario_tipo TEXT NOT NULL DEFAULT 'visitante',
                acao TEXT NOT NULL,
                tabela_afetada TEXT NOT NULL,
                registro_id INTEGER,
                detalhes TEXT NOT NULL,
                ip_origem TEXT,
                criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
            );
        ");

        // Insere seeds se tabela usuarios estiver vazia
        $chk = $pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
        if ($chk == 0) {
            $senhaPadrao = password_hash('admin123', PASSWORD_BCRYPT);
            $senhaCli = password_hash('cliente123', PASSWORD_BCRYPT);
            $pdo->exec("
                INSERT INTO usuarios (id, nome, email, senha, tipo, telefone, cpf) VALUES
                (1, 'Gerente Geral', 'gerente@cinema.com', '{$senhaPadrao}', 'gerente', '(11) 98765-4321', '111.222.333-44'),
                (2, 'Maria Silva', 'cliente@cinema.com', '{$senhaCli}', 'cliente', '(11) 91234-5678', '555.666.777-88');

                INSERT INTO salas (id, nome, tipo, fileiras, colunas, capacidade, status) VALUES
                (1, 'Sala 1 - IMAX Laser Experience', 'IMAX 3D Laser', 7, 10, 70, 'ativa'),
                (2, 'Sala 2 - VIP Dolby Atmos Premium', 'VIP 2D Dolby Atmos', 5, 8, 40, 'ativa'),
                (3, 'Sala 3 - Standard Digital Comfort', '2D Standard Digital', 6, 10, 60, 'ativa');

                INSERT INTO filmes (id, titulo, titulo_original, sinopse, genero, duracao, classificacao, cartaz_url, banner_url, trailer_url, status) VALUES
                (1, 'Interestelar: Além do Tempo', 'Interstellar', 'As reservas naturais da Terra estão chegando ao fim e um grupo de astronautas recebe a missão de verificar possíveis planetas para acolher a humanidade.', 'Ficção Científica / Aventura', 169, '12', 'https://images.unsplash.com/photo-1534447677768-be436bb09401?w=600&auto=format&fit=crop&q=80', 'https://images.unsplash.com/photo-1506703719100-a0f3a48c0f86?w=1200&auto=format&fit=crop&q=80', 'https://www.youtube.com/watch?v=zSWdZVtXT7E', 'em_cartaz'),
                (2, 'Cyberpunk: O Despertar da IA', 'Cyberpunk Awakening', 'Em uma metrópole futurista iluminada por neons, uma detetive cibernética descobre uma conspiração que ameaça apagar as memórias de toda a sociedade humana.', 'Ação / Ficção Científica', 135, '16', 'https://images.unsplash.com/photo-1578632767115-351597cf2477?w=600&auto=format&fit=crop&q=80', 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?w=1200&auto=format&fit=crop&q=80', 'https://www.youtube.com/watch?v=qIcTM8WXFjk', 'em_cartaz'),
                (3, 'O Mistério da Meia-Noite', 'Midnight Secret', 'Um detetive solitário aceita investigar o desaparecimento de uma jovem herdeira em um casarão isolado cheio de segredos.', 'Suspense / Mistério', 118, '14', 'https://images.unsplash.com/photo-1485846234645-a62644f84728?w=600&auto=format&fit=crop&q=80', 'https://images.unsplash.com/photo-1509198397868-475647b2a1e5?w=1200&auto=format&fit=crop&q=80', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'em_cartaz'),
                (4, 'Aventuras na Selva Encantada', 'Enchanted Forest Quest', 'Dois irmãos corajosos descobrem um portal mágico para uma floresta mágica e precisam resgatar o Cristal da Harmonia.', 'Animação / Família', 95, 'Livre', 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?w=600&auto=format&fit=crop&q=80', 'https://images.unsplash.com/photo-1534447677768-be436bb09401?w=1200&auto=format&fit=crop&q=80', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'em_cartaz'),
                (5, 'Velocidade Extrema: Rota 99', 'Extreme Velocity', 'Pilotos clandestinos se enfrentam em um campeonato internacional sem regras pelo submundo automotivo.', 'Ação / Corrida', 122, '14', 'https://images.unsplash.com/photo-1568605117036-5fe5e7bab0b7?w=600&auto=format&fit=crop&q=80', 'https://images.unsplash.com/photo-1503376780353-7e6692767b70?w=1200&auto=format&fit=crop&q=80', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'em_cartaz'),
                (6, 'O Último Guardião do Tempo', 'The Last Timekeeper', 'Em um futuro pós-apocalíptico, um cientista constrói um dispositivo capaz de manipular o fluxo temporal.', 'Aventura / Drama', 142, '12', 'https://images.unsplash.com/photo-1514565131-fce0801e5785?w=600&auto=format&fit=crop&q=80', 'https://images.unsplash.com/photo-1478760329108-5c3ed9d495a0?w=1200&auto=format&fit=crop&q=80', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'em_breve');

                INSERT INTO sessoes (id, filme_id, sala_id, data, horario, idioma, tipo_sessao, preco_ingresso, status) VALUES
                (1, 1, 1, date('now'), '14:30:00', 'Dublado', 'IMAX 3D', 42.00, 'ativa'),
                (2, 1, 1, date('now'), '18:00:00', 'Legendado', 'IMAX 3D', 46.00, 'ativa'),
                (3, 1, 1, date('now'), '21:30:00', 'Legendado', 'IMAX 3D', 46.00, 'ativa'),
                (4, 2, 2, date('now'), '15:00:00', 'Dublado', 'VIP Dolby', 52.00, 'ativa'),
                (5, 2, 2, date('now'), '19:15:00', 'Legendado', 'VIP Dolby', 55.00, 'ativa'),
                (6, 3, 3, date('now'), '16:00:00', 'Dublado', '2D Standard', 32.00, 'ativa'),
                (7, 3, 3, date('now'), '20:30:00', 'Legendado', '2D Standard', 34.00, 'ativa'),
                (8, 4, 3, date('now'), '13:30:00', 'Dublado', '2D Standard', 28.00, 'ativa'),
                (9, 5, 2, date('now'), '22:00:00', 'Dublado', 'VIP Dolby', 48.00, 'ativa');

                INSERT INTO auditoria_logs (usuario_id, usuario_nome, usuario_tipo, acao, tabela_afetada, registro_id, detalhes, ip_origem) VALUES
                (1, 'Gerente Geral', 'gerente', 'INICIALIZACAO_SISTEMA', 'sistema', 1, 'Banco de dados inicializado com sucesso.', '127.0.0.1');
            ");
        }
    }
}

/**
 * =====================================================================
 * REGRA DE NEGÓCIO CORE: AUDITORIA E LOGS
 * Mantém sempre os logs de qualquer alteração feita por qualquer usuário
 * =====================================================================
 */
function registrar_auditoria(PDO $pdo, string $acao, string $tabela, ?int $registro_id, string $detalhes): bool {
    try {
        $usuario_id = $_SESSION['usuario_id'] ?? null;
        $usuario_nome = $_SESSION['usuario_nome'] ?? 'Visitante';
        $usuario_tipo = $_SESSION['usuario_tipo'] ?? 'visitante';
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        $stmt = $pdo->prepare("
            INSERT INTO auditoria_logs 
            (usuario_id, usuario_nome, usuario_tipo, acao, tabela_afetada, registro_id, detalhes, ip_origem, criado_em)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, datetime('now', 'localtime'))
        ");
        
        // Em MySQL CURRENT_TIMESTAMP funciona automaticamente se ajustado
        global $usando_sqlite;
        if (!$usando_sqlite) {
            $stmt = $pdo->prepare("
                INSERT INTO auditoria_logs 
                (usuario_id, usuario_nome, usuario_tipo, acao, tabela_afetada, registro_id, detalhes, ip_origem, criado_em)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
        }

        return $stmt->execute([
            $usuario_id,
            $usuario_nome,
            $usuario_tipo,
            strtoupper($acao),
            $tabela,
            $registro_id,
            $detalhes,
            $ip
        ]);
    } catch (Exception $e) {
        // Auditoria não pode interromper a transação se falhar, mas loga
        error_log("Falha ao registrar auditoria: " . $e->getMessage());
        return false;
    }
}

/**
 * =====================================================================
 * SISTEMA DE MENSAGENS TOAST NA SESSÃO
 * =====================================================================
 */
function definir_toast(string $tipo, string $mensagem) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['toast'] = [
        'tipo' => $tipo, // success, error, warning, info
        'mensagem' => $mensagem
    ];
}

function obter_toast(): ?array {
    if (isset($_SESSION['toast'])) {
        $toast = $_SESSION['toast'];
        unset($_SESSION['toast']);
        return $toast;
    }
    return null;
}

/**
 * =====================================================================
 * UTILITÁRIOS GLOBAIS DE FORMATAÇÃO E SANITIZAÇÃO
 * =====================================================================
 */
function sanitizar($dado): string {
    return htmlspecialchars(trim((string)$dado), ENT_QUOTES, 'UTF-8');
}

function formatar_moeda($valor): string {
    return 'R$ ' . number_format((float)$valor, 2, ',', '.');
}

function formatar_data($data): string {
    if (!$data) return '';
    $timestamp = strtotime($data);
    return date('d/m/Y', $timestamp);
}

function formatar_hora($hora): string {
    if (!$hora) return '';
    return substr($hora, 0, 5);
}

function eh_gerente(): bool {
    return isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] === 'gerente';
}

function esta_logado(): bool {
    return isset($_SESSION['usuario_id']);
}

function obter_usuario_logado(): ?array {
    if (!esta_logado()) return null;
    return [
        'id' => $_SESSION['usuario_id'],
        'nome' => $_SESSION['usuario_nome'] ?? '',
        'email' => $_SESSION['usuario_email'] ?? '',
        'tipo' => $_SESSION['usuario_tipo'] ?? 'cliente'
    ];
}
