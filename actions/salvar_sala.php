<?php
/**
 * =====================================================================
 * ACTION: SALVAR SALA (CRIAR OU ATUALIZAR)
 * Auditoria automática e cálculo de capacidade
 * =====================================================================
 */

require_once __DIR__ . '/../bd.php';
require_once __DIR__ . '/../includes/auth.php';

exigir_gerente();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../admin/salas.php");
    exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$nome = trim($_POST['nome'] ?? '');
$tipo = trim($_POST['tipo'] ?? '2D Padrão');
$fileiras = filter_input(INPUT_POST, 'fileiras', FILTER_VALIDATE_INT) ?: 6;
$colunas = filter_input(INPUT_POST, 'colunas', FILTER_VALIDATE_INT) ?: 10;
$status = trim($_POST['status'] ?? 'ativa');

if (empty($nome) || empty($tipo)) {
    definir_toast('error', 'Nome e tipo da sala são obrigatórios.');
    header("Location: ../admin/salas.php");
    exit;
}

$capacidade = $fileiras * $colunas;

try {
    if ($id) {
        $stmt = $pdo->prepare("
            UPDATE salas SET 
                nome = ?, tipo = ?, fileiras = ?, colunas = ?, capacidade = ?, status = ?
            WHERE id = ?
        ");
        $stmt->execute([$nome, $tipo, $fileiras, $colunas, $capacidade, $status, $id]);

        registrar_auditoria(
            $pdo,
            'ALTERAR_SALA',
            'salas',
            $id,
            "Gerente atualizou a sala \"{$nome}\" (Capacidade: {$capacidade} lugares, Status: {$status})."
        );

        definir_toast('success', "Sala \"{$nome}\" atualizada com sucesso!");
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO salas (nome, tipo, fileiras, colunas, capacidade, status, criado_em)
            VALUES (?, ?, ?, ?, ?, ?, " . ($usando_sqlite ? "datetime('now', 'localtime')" : "NOW()") . ")
        ");
        $stmt->execute([$nome, $tipo, $fileiras, $colunas, $capacidade, $status]);
        $novoId = (int)$pdo->lastInsertId();

        registrar_auditoria(
            $pdo,
            'CADASTRAR_SALA',
            'salas',
            $novoId,
            "Gerente cadastrou a nova sala \"{$nome}\" com capacidade para {$capacidade} lugares ({$fileiras}x{$colunas})."
        );

        definir_toast('success', "Sala \"{$nome}\" cadastrada com sucesso!");
    }
} catch (Exception $e) {
    error_log("Erro ao salvar sala: " . $e->getMessage());
    definir_toast('error', 'Erro ao salvar sala: ' . $e->getMessage());
}

header("Location: ../admin/salas.php");
exit;
