<?php
/**
 * =====================================================================
 * ACTION: SALVAR FILME (CRIAR OU ATUALIZAR)
 * - Proteção por PDO
 * - Auditoria completa de alterações
 * =====================================================================
 */

require_once __DIR__ . '/../bd.php';
require_once __DIR__ . '/../includes/auth.php';

exigir_gerente();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../admin/filmes.php");
    exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$titulo = trim($_POST['titulo'] ?? '');
$tituloOriginal = trim($_POST['titulo_original'] ?? '');
$sinopse = trim($_POST['sinopse'] ?? '');
$genero = trim($_POST['genero'] ?? '');
$duracao = filter_input(INPUT_POST, 'duracao', FILTER_VALIDATE_INT);
$classificacao = trim($_POST['classificacao'] ?? '12');
$cartazUrl = trim($_POST['cartaz_url'] ?? '');
$bannerUrl = trim($_POST['banner_url'] ?? '');
$trailerUrl = trim($_POST['trailer_url'] ?? '');
$status = trim($_POST['status'] ?? 'em_cartaz');

if (empty($titulo) || empty($sinopse) || empty($genero) || !$duracao || empty($cartazUrl)) {
    definir_toast('error', 'Preencha todos os campos obrigatórios do filme.');
    header("Location: ../admin/filmes.php");
    exit;
}

try {
    if ($id) {
        // Atualização de Filme existente
        $stmt = $pdo->prepare("
            UPDATE filmes SET 
                titulo = ?, titulo_original = ?, sinopse = ?, genero = ?,
                duracao = ?, classificacao = ?, cartaz_url = ?, banner_url = ?,
                trailer_url = ?, status = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $titulo, $tituloOriginal, $sinopse, $genero,
            $duracao, $classificacao, $cartazUrl, $bannerUrl,
            $trailerUrl, $status, $id
        ]);

        registrar_auditoria(
            $pdo,
            'ALTERAR_FILME',
            'filmes',
            $id,
            "Gerente atualizou os dados do filme \"{$titulo}\" (ID: #{$id}, Status: {$status})."
        );

        definir_toast('success', "Filme \"{$titulo}\" atualizado com sucesso!");
    } else {
        // Cadastro de Novo Filme
        $stmt = $pdo->prepare("
            INSERT INTO filmes 
            (titulo, titulo_original, sinopse, genero, duracao, classificacao, cartaz_url, banner_url, trailer_url, status, criado_em)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, " . ($usando_sqlite ? "datetime('now', 'localtime')" : "NOW()") . ")
        ");
        $stmt->execute([
            $titulo, $tituloOriginal, $sinopse, $genero,
            $duracao, $classificacao, $cartazUrl, $bannerUrl,
            $trailerUrl, $status
        ]);
        $novoId = (int)$pdo->lastInsertId();

        registrar_auditoria(
            $pdo,
            'CADASTRAR_FILME',
            'filmes',
            $novoId,
            "Gerente cadastrou um novo filme: \"{$titulo}\" (Gênero: {$genero}, Duração: {$duracao}min)."
        );

        definir_toast('success', "Filme \"{$titulo}\" cadastrado com sucesso!");
    }

} catch (Exception $e) {
    error_log("Erro ao salvar filme: " . $e->getMessage());
    definir_toast('error', 'Erro ao salvar filme: ' . $e->getMessage());
}

header("Location: ../admin/filmes.php");
exit;
