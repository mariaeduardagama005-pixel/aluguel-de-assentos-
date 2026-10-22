<?php
/**
 * =====================================================================
 * ACTION: PROCESSAR PAGAMENTO & EMISSÃO DO TICKET
 * - Transação atômica (PDO Transaction)
 * - Verificação de concorrência de poltronas
 * - Registro de Auditoria no banco (Requisito CORE)
 * =====================================================================
 */

require_once __DIR__ . '/../bd.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    definir_toast('error', 'Método inválido.');
    header("Location: ../index.php");
    exit;
}

$sessaoId = filter_input(INPUT_POST, 'sessao_id', FILTER_VALIDATE_INT);
$assentosRaw = trim($_POST['assentos'] ?? '');
$formaPagamento = trim($_POST['forma_pagamento'] ?? 'pix');
$nomeCliente = trim($_POST['nome_cliente'] ?? '');
$emailCliente = trim($_POST['email_cliente'] ?? '');
$cpfCliente = trim($_POST['cpf_cliente'] ?? '');
$telefoneCliente = trim($_POST['telefone_cliente'] ?? '');

// Validações básicas
if (!$sessaoId || empty($assentosRaw) || empty($nomeCliente) || empty($emailCliente) || empty($cpfCliente)) {
    definir_toast('error', 'Preencha todos os campos obrigatórios para emissão do ingresso.');
    header("Location: ../pagamento.php?sessao_id=" . $sessaoId . "&assentos=" . urlencode($assentosRaw));
    exit;
}

$assentosArray = array_values(array_unique(array_filter(array_map('trim', explode(',', $assentosRaw)))));
if (empty($assentosArray)) {
    definir_toast('error', 'Nenhum assento válido selecionado.');
    header("Location: ../index.php");
    exit;
}

// Inicia transação no PDO para garantir consistência
try {
    $pdo->beginTransaction();

    // 1. Busca os detalhes da sessão
    $stmtSessao = $pdo->prepare("SELECT * FROM sessoes WHERE id = ? AND status = 'ativa'");
    $stmtSessao->execute([$sessaoId]);
    $sessao = $stmtSessao->fetch();

    if (!$sessao) {
        $pdo->rollBack();
        definir_toast('error', 'A sessão selecionada não está mais disponível.');
        header("Location: ../index.php");
        exit;
    }

    // 2. Trava e verifica se algum dos assentos já foi ocupado
    $placeholders = implode(',', array_fill(0, count($assentosArray), '?'));
    $stmtOcupados = $pdo->prepare("
        SELECT codigo_assento FROM assentos_ocupados 
        WHERE sessao_id = ? AND codigo_assento IN ($placeholders)
    ");
    $stmtOcupados->execute(array_merge([$sessaoId], $assentosArray));
    $conflitos = $stmtOcupados->fetchAll(PDO::FETCH_COLUMN);

    if (!empty($conflitos)) {
        $pdo->rollBack();
        definir_toast('error', 'Os seguintes assentos foram reservados por outro cliente: ' . implode(', ', $conflitos) . '. Por favor, escolha outros lugares.');
        header("Location: ../assentos.php?sessao_id=" . $sessaoId);
        exit;
    }

    // 3. Cálculos de valor
    $qtdAssentos = count($assentosArray);
    $precoUnitario = (float)$sessao['preco_ingresso'];
    $valorTotal = $qtdAssentos * $precoUnitario;
    $codigoTicket = 'TCK-' . date('Y') . '-' . strtoupper(bin2hex(random_bytes(4)));
    $codigoTransacao = strtoupper($formaPagamento) . '-TXN-' . rand(1000000, 9999999);
    $usuarioId = esta_logado() ? $_SESSION['usuario_id'] : null;

    // 4. Insere o ingresso no banco
    $stmtIngresso = $pdo->prepare("
        INSERT INTO ingressos 
        (codigo, sessao_id, usuario_id, nome_cliente, email_cliente, cpf_cliente, assentos_selecionados, quantidade_assentos, valor_unitario, valor_total, forma_pagamento, status_pagamento, codigo_transacao, criado_em)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'aprovado', ?, " . ($usando_sqlite ? "datetime('now', 'localtime')" : "NOW()") . ")
    ");
    $stmtIngresso->execute([
        $codigoTicket,
        $sessaoId,
        $usuarioId,
        $nomeCliente,
        $emailCliente,
        $cpfCliente,
        implode(', ', $assentosArray),
        $qtdAssentos,
        $precoUnitario,
        $valorTotal,
        $formaPagamento,
        $codigoTransacao
    ]);
    $ingressoId = (int)$pdo->lastInsertId();

    // 5. Insere os assentos na tabela assentos_ocupados
    $stmtOcupar = $pdo->prepare("
        INSERT INTO assentos_ocupados (sessao_id, ingresso_id, codigo_assento, criado_em)
        VALUES (?, ?, ?, " . ($usando_sqlite ? "datetime('now', 'localtime')" : "NOW()") . ")
    ");
    foreach ($assentosArray as $assento) {
        $stmtOcupar->execute([$sessaoId, $ingressoId, $assento]);
    }

    // 6. REGISTRO OBRIGATÓRIO DE AUDITORIA (CORE)
    $detalhesLog = sprintf(
        "Ingresso %s emitido com sucesso para %s (CPF: %s). %d assento(s) [%s] na Sessão #%d. Total: R$ %.2f. Forma: %s.",
        $codigoTicket,
        $nomeCliente,
        $cpfCliente,
        $qtdAssentos,
        implode(', ', $assentosArray),
        $sessaoId,
        $valorTotal,
        strtoupper($formaPagamento)
    );
    registrar_auditoria($pdo, 'COMPRA_INGRESSO', 'ingressos', $ingressoId, $detalhesLog);

    // Efetiva a transação
    $pdo->commit();

    definir_toast('success', 'Pagamento confirmado! Seu ingresso foi gerado com sucesso.');
    header("Location: ../ticket.php?codigo=" . urlencode($codigoTicket));
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Erro no processamento de pagamento: " . $e->getMessage());
    definir_toast('error', 'Ocorreu um erro ao processar seu pagamento. Tente novamente.');
    header("Location: ../pagamento.php?sessao_id=" . $sessaoId . "&assentos=" . urlencode($assentosRaw));
    exit;
}
