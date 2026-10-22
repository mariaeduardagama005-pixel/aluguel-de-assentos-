<?php
/**
 * =====================================================================
 * PÁGINA DE LOGIN (CLIENTES E GERENTES)
 * Hash BCRYPT & Sessões Nativas (Requisito CORE)
 * =====================================================================
 */

require_once __DIR__ . '/bd.php';

if (esta_logado()) {
    if (eh_gerente()) {
        header("Location: admin/index.php");
    } else {
        header("Location: index.php");
    }
    exit;
}

$paginaTitulo = 'Entrar na Conta - CinePimenta';
require_once __DIR__ . '/includes/header.php';
?>

<section class="max-w-md mx-auto px-4 py-16">
    <div class="bg-cinema-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-2xl">
        
        <div class="text-center mb-8">
            <div class="w-12 h-12 rounded-2xl bg-red-600/20 text-red-400 flex items-center justify-center mx-auto text-xl mb-3">
                <i class="fa-solid fa-user-lock"></i>
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight">Acessar Conta</h1>
            <p class="text-xs text-slate-400 mt-1">Entre para acompanhar seus ingressos e histórico de reservas.</p>
        </div>

        <!-- Atalhos de Demonstração Rápida (Ótimo para Avaliação ETEC) -->
        <div class="mb-6 p-3 bg-cinema-800/80 border border-slate-700/80 rounded-2xl">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-2 text-center">
                <i class="fa-solid fa-key text-amber-400 mr-1"></i> Contas de Demonstração (Clique para Preencher)
            </span>
            <div class="grid grid-cols-2 gap-2">
                <button type="button" onclick="preencherLogin('gerente@cinema.com', 'admin123')" class="px-2.5 py-1.5 rounded-xl bg-red-600/20 hover:bg-red-600 border border-red-500/40 text-red-300 hover:text-white text-[11px] font-bold transition-all">
                    Gerente (Admin)
                </button>
                <button type="button" onclick="preencherLogin('cliente@cinema.com', 'cliente123')" class="px-2.5 py-1.5 rounded-xl bg-cyan-600/20 hover:bg-cyan-600 border border-cyan-500/40 text-cyan-300 hover:text-white text-[11px] font-bold transition-all">
                    Cliente Teste
                </button>
            </div>
        </div>

        <form action="actions/login_action.php" method="POST" class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">E-mail</label>
                <div class="relative">
                    <i class="fa-solid fa-envelope absolute left-4 top-3.5 text-slate-500 text-sm"></i>
                    <input type="email" id="login-email" name="email" required placeholder="seu.email@exemplo.com" 
                           class="w-full bg-cinema-800 border border-slate-700 rounded-xl pl-11 pr-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-red-500 focus:ring-2 focus:ring-red-500/20">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Senha</label>
                <div class="relative">
                    <i class="fa-solid fa-lock absolute left-4 top-3.5 text-slate-500 text-sm"></i>
                    <input type="password" id="login-senha" name="senha" required placeholder="••••••••" 
                           class="w-full bg-cinema-800 border border-slate-700 rounded-xl pl-11 pr-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-red-500 focus:ring-2 focus:ring-red-500/20">
                </div>
            </div>

            <button type="submit" class="w-full py-3.5 px-4 rounded-xl bg-red-600 hover:bg-red-700 text-white font-black text-sm uppercase tracking-wider transition-all shadow-lg shadow-red-950/50 mt-2">
                Entrar no Sistema
            </button>
        </form>

        <div class="mt-6 pt-6 border-t border-slate-800 text-center">
            <p class="text-xs text-slate-400">
                Ainda não possui cadastro? 
                <a href="cadastro.php" class="text-red-400 hover:text-red-300 font-bold underline ml-1">Criar Conta Grátis</a>
            </p>
        </div>

    </div>
</section>

<script>
function preencherLogin(email, senha) {
    document.getElementById('login-email').value = email;
    document.getElementById('login-senha').value = senha;
    showToast('info', `Credenciais preenchidas: ${email}`);
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
