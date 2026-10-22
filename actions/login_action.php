<?php
/**
 * =====================================================================
 * ACTION: AUTENTICAÇÃO DE USUÁRIO (LOGIN)
 * Validação com BCRYPT e Sessão Nativa
 * =====================================================================
 */

require_once __DIR__ . '/../bd.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../login.php");
    exit;
}

$email = trim($_POST['email'] ?? '');
$senha = trim($_POST['senha'] ?? '');

if (empty($email) || empty($senha)) {
    definir_toast('error', 'Preencha seu e-mail e senha.');
    header("Location: ../login.php");
    exit;
}

// Busca usuário por e-mail com PDO seguro
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
$stmt->execute([$email]);
$usuario = $stmt->fetch();

// Validação com BCRYPT (password_verify)
if ($usuario && password_verify($senha, $usuario['senha'])) {
    
    // Inicia sessão nativa com dados do usuário
    $_SESSION['usuario_id'] = (int)$usuario['id'];
    $_SESSION['usuario_nome'] = $usuario['nome'];
    $_SESSION['usuario_email'] = $usuario['email'];
    $_SESSION['usuario_tipo'] = $usuario['tipo']; // 'gerente' ou 'cliente'

    // Registro de Auditoria (CORE)
    registrar_auditoria(
        $pdo, 
        'LOGIN_USUARIO', 
        'usuarios', 
        (int)$usuario['id'], 
        "Usuário {$usuario['nome']} ({$usuario['tipo']}) efetuou login no sistema."
    );

    definir_toast('success', "Bem-vindo de volta, {$usuario['nome']}!");

    if ($usuario['tipo'] === 'gerente') {
        header("Location: ../admin/index.php");
    } else {
        header("Location: ../index.php");
    }
    exit;
}

// Caso falhe a autenticação
definir_toast('error', 'E-mail ou senha incorretos.');
header("Location: ../login.php");
exit;
