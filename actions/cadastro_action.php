<?php
/**
 * =====================================================================
 * ACTION: CADASTRO DE NOVO CLIENTE
 * - Criptografia de senha com BCRYPT
 * - Auditoria de novo usuário
 * =====================================================================
 */

require_once __DIR__ . '/../bd.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../cadastro.php");
    exit;
}

$nome = trim($_POST['nome'] ?? '');
$email = trim($_POST['email'] ?? '');
$telefone = trim($_POST['telefone'] ?? '');
$cpf = trim($_POST['cpf'] ?? '');
$senha = trim($_POST['senha'] ?? '');
$senhaConfirm = trim($_POST['senha_confirm'] ?? '');

if (empty($nome) || empty($email) || empty($cpf) || empty($senha)) {
    definir_toast('error', 'Preencha todos os campos obrigatórios.');
    header("Location: ../cadastro.php");
    exit;
}

if ($senha !== $senhaConfirm) {
    definir_toast('error', 'As senhas informadas não coincidem.');
    header("Location: ../cadastro.php");
    exit;
}

if (strlen($senha) < 6) {
    definir_toast('error', 'A senha deve conter no mínimo 6 caracteres.');
    header("Location: ../cadastro.php");
    exit;
}

// Verifica se e-mail já existe
$stmtCheck = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
$stmtCheck->execute([$email]);
if ($stmtCheck->fetch()) {
    definir_toast('error', 'Este e-mail já está cadastrado no sistema.');
    header("Location: ../cadastro.php");
    exit;
}

// HASH SEGURO COM BCRYPT (Requisito CORE)
$senhaHash = password_hash($senha, PASSWORD_BCRYPT);

try {
    $stmt = $pdo->prepare("
        INSERT INTO usuarios (nome, email, senha, tipo, telefone, cpf, criado_em)
        VALUES (?, ?, ?, 'cliente', ?, ?, " . ($usando_sqlite ? "datetime('now', 'localtime')" : "NOW()") . ")
    ");
    $stmt->execute([$nome, $email, $senhaHash, $telefone, $cpf]);
    $novoId = (int)$pdo->lastInsertId();

    // Auto login
    $_SESSION['usuario_id'] = $novoId;
    $_SESSION['usuario_nome'] = $nome;
    $_SESSION['usuario_email'] = $email;
    $_SESSION['usuario_tipo'] = 'cliente';

    // Registro de Auditoria (CORE)
    registrar_auditoria(
        $pdo, 
        'CADASTRO_CLIENTE', 
        'usuarios', 
        $novoId, 
        "Novo cliente cadastrado: {$nome} (Email: {$email}, CPF: {$cpf})."
    );

    definir_toast('success', "Conta criada com sucesso! Seja bem-vindo, {$nome}.");
    header("Location: ../index.php");
    exit;

} catch (Exception $e) {
    error_log("Erro no cadastro: " . $e->getMessage());
    definir_toast('error', 'Erro ao cadastrar conta. Tente novamente.');
    header("Location: ../cadastro.php");
    exit;
}
