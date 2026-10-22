<?php
/**
 * =====================================================================
 * ACTION: SALVAR SESSÃO & PREÇO DO INGRESSO
 * - Cadastrar e alterar sessões e preços
 * - Auditoria detalhada de alterações de valor
 * =====================================================================
 */

require_once __DIR__ . '/../bd.php';
require_once __DIR__ . '/../includes/auth.php';

exigir_gerente();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../admin/sessoes.php");
    exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$filmeId = filter_input(INPUT_POST, 'filme_id', FILTER_VALIDATE_INT);
$salaId = filter_input(INPUT_POST, 'sala_id', FILTER_VALIDATE_INT);
$data = trim($_POST['data'] ?? '');
$horario = trim($_POST['horario'] ?? '');
$idioma = trim($_POST['idioma'] ?? 'Dublado');
$tipoSessao = trim($_POST['tipo_sessao'] ?? '2D');
$precoIngresso = filter_input(INPUT_POST, 'preco_ingresso', FILTER_VALIDATE_FLOAT);
$status = trim($_POST['status'] ?? 'ativa');

if (!$filmeId || !$salaId || empty($data) || empty($horario) || !$precoIngresso) {
    definir_toast('error', 'Preencha todos os campos da sessão e o preço do ingresso.');
    header("Location: ../admin/sessoes.php");
    exit;
}

try {
    // Busca nome do filme e sala para auditoria
    $stmtF = $pdo->prepare("SELECT titulo FROM filmes WHERE id = ?");
    $stmtF->execute([$filmeId]);
    $filmeNome = $stmtF->fetchColumn() ?: "Filme #{$filmeId}";

    $stmtS = $pdo->prepare("SELECT nome FROM salas WHERE id = ?");
    $stmtS->execute([$salaId]);
    $salaNome = $stmtS->fetchColumn() ?: "Sala #{$salaId}";

    if ($id) {
        // Busca preço anterior para comparar na auditoria
        $stmtAntiga = $pdo->prepare("SELECT preco_ingresso, horario, data FROM sessoes WHERE id = ?");
        $stmtAntiga->execute([$id]);
        $sessaoAntiga = $stmtAntiga->fetch();
        $precoAntigo = $sessaoAntiga ? (float)$sessaoAntiga['preco_ingresso'] : $precoIngresso;

        $stmt = $pdo->prepare("
            UPDATE sessoes SET 
                filme_id = ?, sala_id = ?, data = ?, horario = ?,
                idioma = ?, tipo_sessao = ?, preco_ingresso = ?, status = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $filmeId, $salaId, $data, $horario,
            $idioma, $tipoSessao, $precoIngresso, $status, $id
        ]);

        $detalhesLog = sprintf(
            "Gerente alterou a Sessão #%d (%s na %s). Preço anterior: R$ %.2f -> Novo Preço: R$ %.2f. Horário: %s. Status: %s.",
            $id,
            $filmeNome,
            $salaNome,
            $precoAntigo,
            $precoIngresso,
            $horario,
            $status
        );
        registrar_auditoria($pdo, 'ALTERAR_SESSAO_PRECO', 'sessoes', $id, $detalhesLog);

        definir_toast('success', "Sessão #{$id} e preço do ingresso atualizados com sucesso!");
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO sessoes 
            (filme_id, sala_id, data, horario, idioma, tipo_sessao, preco_ingresso, status, criado_em)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, " . ($usando_sqlite ? "datetime('now', 'localtime')" : "NOW()") . ")
        ");
        $stmt->execute([
            $filmeId, $salaId, $data, $horario,
            $idioma, $tipoSessao, $precoIngresso, $status
        ]);
        $novoId = (int)$pdo->lastInsertId();

        $detalhesLog = sprintf(
            "Gerente cadastrou nova sessão para \"%s\" na \"%s\" para o dia %s às %s (%s, %s) com preço de R$ %.2f.",
            $filmeNome,
            $salaNome,
            formatar_data($data),
            $horario,
            $tipoSessao,
            $idioma,
            $precoIngresso
        );
        registrar_auditoria($pdo, 'CADASTRAR_SESSAO', 'sessoes', $novoId, $detalhesLog);

        definir_toast('success', "Nova sessão programada com sucesso!");
    }

} catch (Exception $e) {
    error_log("Erro ao salvar sessão: " . $e->getMessage());
    definir_toast('error', 'Erro ao salvar sessão: ' . $e->getMessage());
}

header("Location: ../admin/sessoes.php");
exit;
