<?php
/**
 * =====================================================================
 * PÁGINA DE CADASTRO DE CLIENTE
 * Senhas tratadas com Hash BCRYPT nativo
 * =====================================================================
 */

require_once __DIR__ . '/bd.php';

if (esta_logado()) {
    header("Location: index.php");
    exit;
}

$paginaTitulo = 'Criar Nova Conta - CinePimenta';
require_once __DIR__ . '/includes/header.php';
?>

<section class="max-w-lg mx-auto px-4 py-12">
    <div class="bg-cinema-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-2xl">
        
        <div class="text-center mb-8">
            <div class="w-12 h-12 rounded-2xl bg-red-600/20 text-red-400 flex items-center justify-center mx-auto text-xl mb-3">
                <i class="fa-solid fa-user-plus"></i>
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight">Criar Conta</h1>
            <p class="text-xs text-slate-400 mt-1">Cadastre-se para reservar assentos com rapidez e agilidade.</p>
        </div>

        <form action="actions/cadastro_action.php" method="POST" class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Nome Completo *</label>
                <input type="text" name="nome" required placeholder="Ex: Lucas Henrique Souza" 
                       class="w-full bg-cinema-800 border border-slate-700 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-red-500 focus:ring-2 focus:ring-red-500/20">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">E-mail *</label>
                    <input type="email" name="email" required placeholder="lucas@exemplo.com" 
                           class="w-full bg-cinema-800 border border-slate-700 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-red-500 focus:ring-2 focus:ring-red-500/20">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Telefone</label>
                    <input type="text" name="telefone" placeholder="(11) 98765-4321" 
                           class="w-full bg-cinema-800 border border-slate-700 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-red-500 focus:ring-2 focus:ring-red-500/20">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">CPF *</label>
                <input type="text" name="cpf" required placeholder="000.000.000-00" 
                       class="w-full bg-cinema-800 border border-slate-700 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-red-500 focus:ring-2 focus:ring-red-500/20">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Senha *</label>
                    <input type="password" name="senha" required minlength="6" placeholder="Mínimo 6 dígitos" 
                           class="w-full bg-cinema-800 border border-slate-700 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-red-500 focus:ring-2 focus:ring-red-500/20">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Confirmar Senha *</label>
                    <input type="password" name="senha_confirm" required minlength="6" placeholder="Repita a senha" 
                           class="w-full bg-cinema-800 border border-slate-700 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-red-500 focus:ring-2 focus:ring-red-500/20">
                </div>
            </div>

            <button type="submit" class="w-full py-3.5 px-4 rounded-xl bg-red-600 hover:bg-red-700 text-white font-black text-sm uppercase tracking-wider transition-all shadow-lg shadow-red-950/50 mt-4">
                Finalizar Cadastro
            </button>
        </form>

        <div class="mt-6 pt-6 border-t border-slate-800 text-center">
            <p class="text-xs text-slate-400">
                Já possui uma conta? 
                <a href="login.php" class="text-red-400 hover:text-red-300 font-bold underline ml-1">Fazer Login</a>
            </p>
        </div>

    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
