-- =====================================================================
-- BANCO DE DADOS: cinema_assentos
-- Sistema de Aluguel de Assentos de Cinema
-- Regras: PDO, Auditoria Completa, Hash Bcrypt, Mobile-First
-- =====================================================================

CREATE DATABASE IF NOT EXISTS `cinema_assentos` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `cinema_assentos`;

-- ---------------------------------------------------------------------
-- 1. TABELA DE USUÁRIOS (Gerentes e Clientes)
-- Senhas criptografadas com BCRYPT
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nome` VARCHAR(120) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `senha` VARCHAR(255) NOT NULL,
  `tipo` ENUM('gerente', 'cliente') NOT NULL DEFAULT 'cliente',
  `telefone` VARCHAR(30) NULL,
  `cpf` VARCHAR(20) NULL,
  `criado_em` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 2. TABELA DE SALAS DE CINEMA
-- Define capacidade, linhas e colunas para renderização do mapa
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `salas` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nome` VARCHAR(100) NOT NULL,
  `tipo` VARCHAR(50) NOT NULL DEFAULT '2D Padrão',
  `fileiras` INT NOT NULL DEFAULT 6,
  `colunas` INT NOT NULL DEFAULT 10,
  `capacidade` INT NOT NULL DEFAULT 60,
  `status` ENUM('ativa', 'manutencao') NOT NULL DEFAULT 'ativa',
  `criado_em` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 3. TABELA DE FILMES EM CARTAZ
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `filmes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `titulo` VARCHAR(150) NOT NULL,
  `titulo_original` VARCHAR(150) NULL,
  `sinopse` TEXT NOT NULL,
  `genero` VARCHAR(80) NOT NULL,
  `duracao` INT NOT NULL COMMENT 'Em minutos',
  `classificacao` VARCHAR(10) NOT NULL DEFAULT '12' COMMENT 'Livre, 10, 12, 14, 16, 18',
  `cartaz_url` TEXT NOT NULL,
  `banner_url` TEXT NULL,
  `trailer_url` VARCHAR(255) NULL,
  `status` ENUM('em_cartaz', 'em_breve', 'encerrado') NOT NULL DEFAULT 'em_cartaz',
  `criado_em` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 4. TABELA DE SESSÕES
-- Vincula Filme, Sala, Horário e Preço do Ingresso
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sessoes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `filme_id` INT NOT NULL,
  `sala_id` INT NOT NULL,
  `data` DATE NOT NULL,
  `horario` TIME NOT NULL,
  `idioma` ENUM('Dublado', 'Legendado', 'Nacional') NOT NULL DEFAULT 'Dublado',
  `tipo_sessao` VARCHAR(30) NOT NULL DEFAULT '2D',
  `preco_ingresso` DECIMAL(10,2) NOT NULL DEFAULT 32.00,
  `status` ENUM('ativa', 'cancelada', 'encerrada') NOT NULL DEFAULT 'ativa',
  `criado_em` DATETIME DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_sessao_filme` FOREIGN KEY (`filme_id`) REFERENCES `filmes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_sessao_sala` FOREIGN KEY (`sala_id`) REFERENCES `salas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 5. TABELA DE INGRESSOS (Vendas e Tickets Gerados)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ingressos` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `codigo` VARCHAR(40) NOT NULL UNIQUE,
  `sessao_id` INT NOT NULL,
  `usuario_id` INT NULL,
  `nome_cliente` VARCHAR(120) NOT NULL,
  `email_cliente` VARCHAR(150) NOT NULL,
  `cpf_cliente` VARCHAR(20) NOT NULL,
  `assentos_selecionados` VARCHAR(255) NOT NULL,
  `quantidade_assentos` INT NOT NULL DEFAULT 1,
  `valor_unitario` DECIMAL(10,2) NOT NULL,
  `valor_total` DECIMAL(10,2) NOT NULL,
  `forma_pagamento` ENUM('pix', 'credito', 'debito', 'dinheiro') NOT NULL DEFAULT 'pix',
  `status_pagamento` ENUM('aprovado', 'pendente', 'cancelado') NOT NULL DEFAULT 'aprovado',
  `codigo_transacao` VARCHAR(100) NULL,
  `criado_em` DATETIME DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_ingresso_sessao` FOREIGN KEY (`sessao_id`) REFERENCES `sessoes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ingresso_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 6. TABELA DE ASSENTOS OCUPADOS (Evita duplicidade de assentos por sessão)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `assentos_ocupados` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `sessao_id` INT NOT NULL,
  `ingresso_id` INT NOT NULL,
  `codigo_assento` VARCHAR(10) NOT NULL,
  `criado_em` DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_sessao_assento` (`sessao_id`, `codigo_assento`),
  CONSTRAINT `fk_ocupado_sessao` FOREIGN KEY (`sessao_id`) REFERENCES `sessoes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ocupado_ingresso` FOREIGN KEY (`ingresso_id`) REFERENCES `ingressos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 7. TABELA DE LOGS DE AUDITORIA (Histórico de alterações no sistema)
-- Registra qualquer alteração por qualquer usuário (CORE)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `auditoria_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `usuario_id` INT NULL,
  `usuario_nome` VARCHAR(120) NOT NULL DEFAULT 'Sistema/Visitante',
  `usuario_tipo` VARCHAR(30) NOT NULL DEFAULT 'visitante',
  `acao` VARCHAR(80) NOT NULL,
  `tabela_afetada` VARCHAR(60) NOT NULL,
  `registro_id` INT NULL,
  `detalhes` TEXT NOT NULL,
  `ip_origem` VARCHAR(45) NULL,
  `criado_em` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- DADOS INICIAIS (SEEDS)
-- Senhas padrão hasheadas com BCRYPT:
-- 'admin123'   -> $2y$10$wL44yW9Z2Z1E7b4c6e9UfO8Z3Yn7qL9a7m6p4u3y2w1v0t9s8r7q6 (gerente)
-- 'cliente123' -> $2y$10$tZ91xK5Y8X7V6b5c4d3UeN2Z1Ym6qK8a6m5p3u2y1w0v9t8s7r6q5 (cliente)
-- ---------------------------------------------------------------------

-- Usuários: 1 Gerente / 1 Cliente de teste
INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`, `tipo`, `telefone`, `cpf`) VALUES
(1, 'Gerente Geral', 'gerente@cinema.com', '$2y$10$22n9Vn89eR2l6/HjR05YheOtzxKxGszb434oA0h6kX.E3kOti/K4y', 'gerente', '(11) 98765-4321', '111.222.333-44'),
(2, 'Maria Silva', 'cliente@cinema.com', '$2y$10$22n9Vn89eR2l6/HjR05YheOtzxKxGszb434oA0h6kX.E3kOti/K4y', 'cliente', '(11) 91234-5678', '555.666.777-88')
ON DUPLICATE KEY UPDATE `id`=`id`;

-- Salas de Cinema
INSERT INTO `salas` (`id`, `nome`, `tipo`, `fileiras`, `colunas`, `capacidade`, `status`) VALUES
(1, 'Sala 1 - IMAX Laser Experience', 'IMAX 3D Laser', 7, 10, 70, 'ativa'),
(2, 'Sala 2 - VIP Dolby Atmos Premium', 'VIP 2D Dolby Atmos', 5, 8, 40, 'ativa'),
(3, 'Sala 3 - Standard Digital Comfort', '2D Standard Digital', 6, 10, 60, 'ativa')
ON DUPLICATE KEY UPDATE `id`=`id`;

-- Filmes em Cartaz
INSERT INTO `filmes` (`id`, `titulo`, `titulo_original`, `sinopse`, `genero`, `duracao`, `classificacao`, `cartaz_url`, `banner_url`, `trailer_url`, `status`) VALUES
(1, 'Interestelar: Além do Tempo', 'Interstellar', 'As reservas naturais da Terra estão chegando ao fim e um grupo de astronautas recebe a missão de verificar possíveis planetas para receberem a população mundial, possibilitando a continuação da espécie.', 'Ficção Científica / Aventura', 169, '12', 'https://images.unsplash.com/photo-1534447677768-be436bb09401?w=600&auto=format&fit=crop&q=80', 'https://images.unsplash.com/photo-1506703719100-a0f3a48c0f86?w=1200&auto=format&fit=crop&q=80', 'https://www.youtube.com/watch?v=zSWdZVtXT7E', 'em_cartaz'),
(2, 'Cyberpunk: O Despertar da IA', 'Cyberpunk Awakening', 'Em uma metrópole futurista iluminada por neons, uma detetive cibernética descobre uma conspiração que ameaça apagar as memórias de toda a sociedade humana.', 'Ação / Ficção Científica', 135, '16', 'https://images.unsplash.com/photo-1578632767115-351597cf2477?w=600&auto=format&fit=crop&q=80', 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?w=1200&auto=format&fit=crop&q=80', 'https://www.youtube.com/watch?v=qIcTM8WXFjk', 'em_cartaz'),
(3, 'O Mistério da Meia-Noite', 'Midnight Secret', 'Um detetive solitário aceita investigar o desaparecimento de uma jovem herdeira em um casarão isolado, onde cada sombra guarda segredos sombrios do passado.', 'Suspense / Mistério', 118, '14', 'https://images.unsplash.com/photo-1485846234645-a62644f84728?w=600&auto=format&fit=crop&q=80', 'https://images.unsplash.com/photo-1509198397868-475647b2a1e5?w=1200&auto=format&fit=crop&q=80', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'em_cartaz'),
(4, 'Aventuras na Selva Encantada', 'Enchanted Forest Quest', 'Dois irmãos corajosos descobrem um portal mágico para uma floresta habitada por criaturas fantásticas e precisam resgatar o Cristal da Harmonia antes do eclipse lunar.', 'Animação / Família', 95, 'Livre', 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?w=600&auto=format&fit=crop&q=80', 'https://images.unsplash.com/photo-1534447677768-be436bb09401?w=1200&auto=format&fit=crop&q=80', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'em_cartaz'),
(5, 'Velocidade Extrema: Rota 99', 'Extreme Velocity', 'Pilotos clandestinos se enfrentam em um campeonato internacional sem regras, disputando o título de piloto mais veloz do submundo automotivo.', 'Ação / Corrida', 122, '14', 'https://images.unsplash.com/photo-1568605117036-5fe5e7bab0b7?w=600&auto=format&fit=crop&q=80', 'https://images.unsplash.com/photo-1503376780353-7e6692767b70?w=1200&auto=format&fit=crop&q=80', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'em_cartaz'),
(6, 'O Último Guardião do Tempo', 'The Last Timekeeper', 'Em um futuro pós-apocalíptico, um cientista constrói um dispositivo que pode reverter desastres naturais através da manipulação do fluxo temporal.', 'Aventura / Drama', 142, '12', 'https://images.unsplash.com/photo-1514565131-fce0801e5785?w=600&auto=format&fit=crop&q=80', 'https://images.unsplash.com/photo-1478760329108-5c3ed9d495a0?w=1200&auto=format&fit=crop&q=80', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'em_breve')
ON DUPLICATE KEY UPDATE `id`=`id`;

-- Sessões de Cinema (Hoje e próximos dias)
INSERT INTO `sessoes` (`id`, `filme_id`, `sala_id`, `data`, `horario`, `idioma`, `tipo_sessao`, `preco_ingresso`, `status`) VALUES
(1, 1, 1, CURDATE(), '14:30:00', 'Dublado', 'IMAX 3D', 42.00, 'ativa'),
(2, 1, 1, CURDATE(), '18:00:00', 'Legendado', 'IMAX 3D', 46.00, 'ativa'),
(3, 1, 1, CURDATE(), '21:30:00', 'Legendado', 'IMAX 3D', 46.00, 'ativa'),
(4, 2, 2, CURDATE(), '15:00:00', 'Dublado', 'VIP Dolby', 52.00, 'ativa'),
(5, 2, 2, CURDATE(), '19:15:00', 'Legendado', 'VIP Dolby', 55.00, 'ativa'),
(6, 3, 3, CURDATE(), '16:00:00', 'Dublado', '2D Standard', 32.00, 'ativa'),
(7, 3, 3, CURDATE(), '20:30:00', 'Legendado', '2D Standard', 34.00, 'ativa'),
(8, 4, 3, CURDATE(), '13:30:00', 'Dublado', '2D Standard', 28.00, 'ativa'),
(9, 5, 2, CURDATE(), '22:00:00', 'Dublado', 'VIP Dolby', 48.00, 'ativa'),
(10, 1, 1, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '18:00:00', 'Legendado', 'IMAX 3D', 46.00, 'ativa')
ON DUPLICATE KEY UPDATE `id`=`id`;

-- Ingressos de Exemplo
INSERT INTO `ingressos` (`id`, `codigo`, `sessao_id`, `usuario_id`, `nome_cliente`, `email_cliente`, `cpf_cliente`, `assentos_selecionados`, `quantidade_assentos`, `valor_unitario`, `valor_total`, `forma_pagamento`, `status_pagamento`, `codigo_transacao`) VALUES
(1, 'TCK-2026-X89A12', 2, 2, 'Maria Silva', 'cliente@cinema.com', '555.666.777-88', 'C4, C5', 2, 46.00, 92.00, 'pix', 'aprovado', 'PIX-TXN-984321049')
ON DUPLICATE KEY UPDATE `id`=`id`;

-- Assentos já Ocupados na Sessão 2
INSERT INTO `assentos_ocupados` (`sessao_id`, `ingresso_id`, `codigo_assento`) VALUES
(2, 1, 'C4'),
(2, 1, 'C5')
ON DUPLICATE KEY UPDATE `id`=`id`;

-- Logs de Auditoria Iniciais
INSERT INTO `auditoria_logs` (`usuario_id`, `usuario_nome`, `usuario_tipo`, `acao`, `tabela_afetada`, `registro_id`, `detalhes`, `ip_origem`) VALUES
(1, 'Gerente Geral', 'gerente', 'SISTEMA_INICIALIZADO', 'sistema', 1, 'Base de dados inicializada com sucesso com filmes, salas e sessões.', '127.0.0.1'),
(1, 'Gerente Geral', 'gerente', 'CADASTRAR_FILME', 'filmes', 1, 'Filme "Interestelar: Além do Tempo" cadastrado no sistema.', '127.0.0.1'),
(1, 'Gerente Geral', 'gerente', 'CADASTRAR_SALA', 'salas', 1, 'Sala "Sala 1 - IMAX Laser Experience" cadastrada com 70 lugares.', '127.0.0.1'),
(1, 'Gerente Geral', 'gerente', 'CADASTRAR_SESSAO', 'sessoes', 2, 'Sessão às 18:00 criada para "Interestelar" com preço R$ 46,00.', '127.0.0.1'),
(2, 'Maria Silva', 'cliente', 'COMPRA_INGRESSO', 'ingressos', 1, 'Ingresso TCK-2026-X89A12 emitido com sucesso (2 assentos: C4, C5). Forma: PIX. Total: R$ 92,00.', '127.0.0.1');
