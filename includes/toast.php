<?php
/**
 * =====================================================================
 * COMPONENTE TOAST DE NOTIFICAÇÕES
 * Atende ao requisito: "Retorne sempre as mensagens de erros de 
 * forma claras na interface para o usuário (TOAST)"
 * =====================================================================
 */
$toastAtual = obter_toast();
?>

<!-- Container Flutuante para Toasts -->
<div id="toast-container" class="fixed top-5 right-5 z-50 flex flex-col gap-3 max-w-sm w-full pointer-events-none">
    <?php if ($toastAtual): ?>
        <?php
            $tipo = $toastAtual['tipo'] ?? 'info';
            $msg = $toastAtual['mensagem'] ?? '';
            $bg = 'bg-slate-800 border-slate-700 text-slate-100';
            $icon = 'fa-info-circle text-blue-400';
            
            if ($tipo === 'success') {
                $bg = 'bg-emerald-950/95 border-emerald-600/50 text-emerald-100 shadow-emerald-950/50';
                $icon = 'fa-circle-check text-emerald-400';
            } elseif ($tipo === 'error' || $tipo === 'danger') {
                $bg = 'bg-rose-950/95 border-rose-600/50 text-rose-100 shadow-rose-950/50';
                $icon = 'fa-triangle-exclamation text-rose-400';
            } elseif ($tipo === 'warning') {
                $bg = 'bg-amber-950/95 border-amber-600/50 text-amber-100 shadow-amber-950/50';
                $icon = 'fa-circle-exclamation text-amber-400';
            }
        ?>
        <div class="toast-item pointer-events-auto flex items-start gap-3 p-4 rounded-xl border shadow-2xl backdrop-blur-md transition-all duration-300 transform translate-y-0 opacity-100 <?= $bg ?>"
             data-auto-dismiss="4500">
            <div class="text-xl pt-0.5">
                <i class="fa-solid <?= $icon ?>"></i>
            </div>
            <div class="flex-1 text-sm font-medium leading-relaxed">
                <?= htmlspecialchars($msg) ?>
            </div>
            <button type="button" onclick="this.closest('.toast-item').remove()" class="text-slate-400 hover:text-white transition-colors p-1">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    <?php endif; ?>
</div>

<script>
/**
 * Função Global para exibir Toast via JavaScript
 * Uso: showToast('success', 'Operação realizada com sucesso!')
 */
function showToast(tipo, mensagem) {
    const container = document.getElementById('toast-container');
    if (!container) return;

    let bg = 'bg-slate-900 border-slate-700 text-slate-100';
    let icon = 'fa-circle-info text-blue-400';

    if (tipo === 'success') {
        bg = 'bg-emerald-950/95 border-emerald-600/50 text-emerald-100 shadow-emerald-950/50';
        icon = 'fa-circle-check text-emerald-400';
    } else if (tipo === 'error' || tipo === 'danger') {
        bg = 'bg-rose-950/95 border-rose-600/50 text-rose-100 shadow-rose-950/50';
        icon = 'fa-triangle-exclamation text-rose-400';
    } else if (tipo === 'warning') {
        bg = 'bg-amber-950/95 border-amber-600/50 text-amber-100 shadow-amber-950/50';
        icon = 'fa-circle-exclamation text-amber-400';
    }

    const toast = document.createElement('div');
    toast.className = `toast-item pointer-events-auto flex items-start gap-3 p-4 rounded-xl border shadow-2xl backdrop-blur-md transition-all duration-300 transform translate-x-4 opacity-0 ${bg}`;
    toast.innerHTML = `
        <div class="text-xl pt-0.5"><i class="fa-solid ${icon}"></i></div>
        <div class="flex-1 text-sm font-medium leading-relaxed">${mensagem}</div>
        <button type="button" class="text-slate-400 hover:text-white transition-colors p-1" onclick="this.closest('.toast-item').remove()">
            <i class="fa-solid fa-xmark"></i>
        </button>
    `;

    container.appendChild(toast);

    // Animação de entrada
    requestAnimationFrame(() => {
        toast.classList.remove('translate-x-4', 'opacity-0');
        toast.classList.add('translate-x-0', 'opacity-100');
    });

    // Auto dismiss
    setTimeout(() => {
        toast.classList.add('opacity-0', 'translate-x-4');
        setTimeout(() => toast.remove(), 300);
    }, 4500);
}

// Inicia auto dismiss para toasts pré-renderizados pelo PHP
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.toast-item[data-auto-dismiss]').forEach(el => {
        const time = parseInt(el.getAttribute('data-auto-dismiss') || '4500', 10);
        setTimeout(() => {
            el.classList.add('opacity-0', 'translate-x-4');
            setTimeout(() => el.remove(), 300);
        }, time);
    });
});
</script>
