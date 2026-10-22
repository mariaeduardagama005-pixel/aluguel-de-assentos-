<?php
/**
 * =====================================================================
 * ACTION: EXCLUIR FILME
 * - Proteção PDO
 * - Auditoria
 * =====================================================================
 */

require_once __DIR__ . '/../bd.php';
require_once __DIR__ . '/../includes/auth.php';

exigir_gerente();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    definir_toast('error', 'ID do filme inválido.');
    header("Location: ../admin/filmes.php");
    exit;
}

try {
    $stmtFind = $pdo->prepare("SELECT titulo FROM filmes WHERE id = ?");
    $stmtFind->execute([$id]);
    $filme = $stmtFind->fetch();

    if ($filme) {
        $stmt = $pdo->prepare("DELETE FROM filmes WHERE id = ?");
        $stmt->execute([$id]);

        registrar_auditoria(
            $pdo,
            'EXCLUIR_FILME',
            'filmes',
            $id,
            "Gerente removeu o filme \"{$filme['titulo']}\" e suas sessões associadas do sistema."
        );

        definir_toast('success', "Filme \"{$filme['titulo']}\" removido com sucesso.");
    } else {
        definir_toast('error', 'Filme não encontrado.');
    }
} catch (Exception $e) {
    error_log("Erro ao excluir filme: " . $e->getMessage());
    definir_toast('error', 'Não foi possível excluir o filme. Verifique se existem ingressos emitidos.');
}

header("Location: ../admin/filmes.php");
exit;
