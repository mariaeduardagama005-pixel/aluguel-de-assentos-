<?php
/**
 * =====================================================================
 * ACTION: EXCLUIR / CANCELAR SESSÃO
 * =====================================================================
 */

require_once __DIR__ . '/../bd.php';
require_once __DIR__ . '/../includes/auth.php';

exigir_gerente();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    definir_toast('error', 'ID da sessão inválido.');
    header("Location: ../admin/sessoes.php");
    exit;
}

try {
    $stmtFind = $pdo->prepare("
        SELECT s.id, f.titulo, s.horario 
        FROM sessoes s
        JOIN filmes f ON s.filme_id = f.id
        WHERE s.id = ?
    ");
    $stmtFind->execute([$id]);
    $sessao = $stmtFind->fetch();

    if ($sessao) {
        $stmt = $pdo->prepare("DELETE FROM sessoes WHERE id = ?");
        $stmt->execute([$id]);

        registrar_auditoria(
            $pdo,
            'EXCLUIR_SESSAO',
            'sessoes',
            $id,
            "Gerente cancelou e excluiu a sessão #{$id} do filme \"{$sessao['titulo']}\" às {$sessao['horario']}."
        );

        definir_toast('success', "Sessão #{$id} excluída com sucesso.");
    }
} catch (Exception $e) {
    error_log("Erro ao excluir sessão: " . $e->getMessage());
    definir_toast('error', 'Não foi possível excluir esta sessão. Verifique se há ingressos já vendidos para ela.');
}

header("Location: ../admin/sessoes.php");
exit;
