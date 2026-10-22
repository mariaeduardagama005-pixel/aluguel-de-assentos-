<?php
/**
 * =====================================================================
 * COMPONENTE MODAL CUSTOMIZADO
 * Atende ao requisito: "sempre trate as mensagens de caixa de 
 * mensagens nativas do navegador em um MODAL"
 * Substitui completamente alert() e confirm() por modais Tailwind
 * =====================================================================
 */
?>

<!-- Container Principal do Modal -->
<div id="custom-modal-overlay" class="fixed inset-0 z-50 hidden bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4 transition-opacity duration-200">
    <div id="custom-modal-box" class="bg-slate-900 border border-slate-700/80 rounded-2xl shadow-2xl max-w-md w-full overflow-hidden transform scale-95 opacity-0 transition-all duration-200">
        
        <!-- Cabeçalho do Modal -->
        <div class="p-5 border-b border-slate-800 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div id="modal-icon-badge" class="w-10 h-10 rounded-xl flex items-center justify-center text-lg bg-red-600/20 text-red-400">
                    <i id="modal-icon" class="fa-solid fa-circle-question"></i>
                </div>
                <h3 id="modal-title" class="text-lg font-bold text-white tracking-wide">
                    Confirmação
                </h3>
            </div>
            <button type="button" onclick="closeCustomModal()" class="text-slate-400 hover:text-white transition-colors p-1.5 rounded-lg hover:bg-slate-800">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <!-- Conteúdo do Modal -->
        <div class="p-6">
            <p id="modal-message" class="text-slate-300 text-sm leading-relaxed">
                Mensagem padrão da caixa de diálogo.
            </p>
        </div>

        <!-- Rodapé / Ações -->
        <div id="modal-footer" class="p-4 bg-slate-950/50 border-t border-slate-800/80 flex items-center justify-end gap-3">
            <button type="button" id="modal-btn-cancel" onclick="closeCustomModal(false)" class="px-4 py-2 text-sm font-medium text-slate-300 hover:text-white bg-slate-800 hover:bg-slate-700 rounded-xl transition-all">
                Cancelar
            </button>
            <button type="button" id="modal-btn-confirm" onclick="confirmCustomModal()" class="px-5 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-xl transition-all shadow-lg shadow-red-900/30">
                Confirmar
            </button>
        </div>
    </div>
</div>

<script>
/**
 * Gerenciador JavaScript do Modal Customizado
 */
let modalConfirmCallback = null;
let modalCancelCallback = null;

function openCustomModal(options = {}) {
    const overlay = document.getElementById('custom-modal-overlay');
    const box = document.getElementById('custom-modal-box');
    const title = document.getElementById('modal-title');
    const message = document.getElementById('modal-message');
    const icon = document.getElementById('modal-icon');
    const badge = document.getElementById('modal-icon-badge');
    const btnCancel = document.getElementById('modal-btn-cancel');
    const btnConfirm = document.getElementById('modal-btn-confirm');

    title.innerText = options.title || 'Aviso do Sistema';
    message.innerHTML = options.message || '';
    
    // Configura callbacks
    modalConfirmCallback = options.onConfirm || null;
    modalCancelCallback = options.onCancel || null;

    // Tipo / Estilo do Modal
    const type = options.type || 'info'; // info, warning, danger, success
    if (type === 'danger') {
        badge.className = 'w-10 h-10 rounded-xl flex items-center justify-center text-lg bg-red-600/20 text-red-400';
        icon.className = 'fa-solid fa-triangle-exclamation';
        btnConfirm.className = 'px-5 py-2 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-xl transition-all shadow-lg shadow-red-900/30';
    } else if (type === 'success') {
        badge.className = 'w-10 h-10 rounded-xl flex items-center justify-center text-lg bg-emerald-600/20 text-emerald-400';
        icon.className = 'fa-solid fa-circle-check';
        btnConfirm.className = 'px-5 py-2 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition-all shadow-lg shadow-emerald-900/30';
    } else if (type === 'warning') {
        badge.className = 'w-10 h-10 rounded-xl flex items-center justify-center text-lg bg-amber-600/20 text-amber-400';
        icon.className = 'fa-solid fa-circle-exclamation';
        btnConfirm.className = 'px-5 py-2 text-sm font-semibold text-white bg-amber-600 hover:bg-amber-700 rounded-xl transition-all shadow-lg shadow-amber-900/30';
    } else {
        badge.className = 'w-10 h-10 rounded-xl flex items-center justify-center text-lg bg-blue-600/20 text-blue-400';
        icon.className = 'fa-solid fa-circle-info';
        btnConfirm.className = 'px-5 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition-all shadow-lg shadow-blue-900/30';
    }

    // Configura botões
    btnConfirm.innerText = options.confirmText || 'Confirmar';
    btnCancel.innerText = options.cancelText || 'Cancelar';

    if (options.isAlert) {
        btnCancel.classList.add('hidden');
        btnConfirm.innerText = options.confirmText || 'Entendido';
    } else {
        btnCancel.classList.remove('hidden');
    }

    // Exibe modal com animação
    overlay.classList.remove('hidden');
    requestAnimationFrame(() => {
        box.classList.remove('scale-95', 'opacity-0');
        box.classList.add('scale-100', 'opacity-100');
    });
}

function closeCustomModal(triggerCancel = true) {
    const overlay = document.getElementById('custom-modal-overlay');
    const box = document.getElementById('custom-modal-box');
    if (!overlay || !box) return;

    box.classList.remove('scale-100', 'opacity-100');
    box.classList.add('scale-95', 'opacity-0');

    setTimeout(() => {
        overlay.classList.add('hidden');
        if (triggerCancel && typeof modalCancelCallback === 'function') {
            modalCancelCallback();
        }
        modalConfirmCallback = null;
        modalCancelCallback = null;
    }, 150);
}

function confirmCustomModal() {
    if (typeof modalConfirmCallback === 'function') {
        modalConfirmCallback();
    }
    closeCustomModal(false);
}

/**
 * Funções de atalho para chamadas declarativas
 */
function showModalAlert(title, message, type = 'info', onOk = null) {
    openCustomModal({
        title,
        message,
        type,
        isAlert: true,
        confirmText: 'OK',
        onConfirm: onOk
    });
}

function showModalConfirm(title, message, onConfirm, onCancel = null, isDanger = false, confirmText = 'Confirmar') {
    openCustomModal({
        title,
        message,
        type: isDanger ? 'danger' : 'warning',
        isAlert: false,
        confirmText: confirmText,
        cancelText: 'Cancelar',
        onConfirm: onConfirm,
        onCancel: onCancel
    });
}

// Fechar ao pressionar ESC
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        const overlay = document.getElementById('custom-modal-overlay');
        if (overlay && !overlay.classList.contains('hidden')) {
            closeCustomModal(true);
        }
    }
});
</script>
