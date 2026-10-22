<?php
/**
 * =====================================================================
 * LOGOUT: ENCERRAMENTO DE SESSÃO
 * =====================================================================
 */

require_once __DIR__ . '/bd.php';

if (esta_logado()) {
    $nome = $_SESSION['usuario_nome'] ?? 'Usuário';
    registrar_auditoria($pdo, 'LOGOUT_USUARIO', 'usuarios', (int)$_SESSION['usuario_id'], "Usuário {$nome} encerrou a sessão.");
    
    // Limpa todas as variáveis de sessão
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}

// Inicia nova sessão limpa para exibir toast de despedida
session_start();
definir_toast('info', 'Você saiu da sua conta.');
header("Location: index.php");
exit;
