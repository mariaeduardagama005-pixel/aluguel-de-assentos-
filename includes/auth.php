<?php
/**
 * =====================================================================
 * MIDDLEWARE DE AUTENTICAÇÃO E PERMISSÕES
 * =====================================================================
 */

require_once __DIR__ . '/../bd.php';

function exigir_login(string $redirect = 'login.php') {
    if (!esta_logado()) {
        definir_toast('warning', 'Faça login para continuar com esta operação.');
        header("Location: " . $redirect);
        exit;
    }
}

function exigir_gerente(string $redirect = '../login.php') {
    if (!esta_logado()) {
        definir_toast('error', 'Acesso restrito. Faça login como gerente.');
        header("Location: " . $redirect);
        exit;
    }
    if (!eh_gerente()) {
        definir_toast('error', 'Acesso negado. Esta área é restrita a gerentes.');
        header("Location: ../index.php");
        exit;
    }
}
