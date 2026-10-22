<?php
/**
 * =====================================================================
 * ACTION: EXCLUIR SALA
 * =====================================================================
 */

require_once __DIR__ . '/../bd.php';
require_once __DIR__ . '/../includes/auth.php';

exigir_gerente();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    definir_toast('error', 'ID da sala inválido.');
    header("Location: ../admin/salas.php");
    exit;
}

try {
    $stmtFind = $pdo->prepare("SELECT nome FROM salas WHERE id = ?");
    $stmtFind->execute([$id]);
    $sala = $stmtFind->fetch();

    if ($sala) {
        $stmt = $pdo->prepare("DELETE FROM salas WHERE id = ?");
        $stmt->execute([$id]);

        registrar_auditoria(
            $pdo,
            'EXCLUIR_SALA',
            'salas',
            $id,
            "Gerente removeu a sala \"{$sala['nome']}\" (ID: #{$id}) do sistema."
        );

        definir_toast('success', "Sala \"{$sala['nome']}\" excluída com sucesso.");
    }
} catch (Exception $e) {
    error_log("Erro ao excluir sala: " . $e->getMessage());
    definir_toast('error', 'Não foi possível excluir esta sala pois há sessões vinculadas a ela.');
}

header("Location: ../admin/salas.php");
exit;
