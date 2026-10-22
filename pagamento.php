<?php
/**
 * =====================================================================
 * ETAPA 4: IDENTIFICAÇÃO DO CLIENTE & FORMA DE PAGAMENTO
 * O cliente escolhe seu assento e a forma de pagamento (PIX, Cartão, Débito)
 * =====================================================================
 */

require_once __DIR__ . '/bd.php';

$sessaoId = filter_input(INPUT_GET, 'sessao_id', FILTER_VALIDATE_INT);
$assentosRaw = trim($_GET['assentos'] ?? '');

if (!$sessaoId || empty($assentosRaw)) {
    definir_toast('error', 'Sessão ou assentos não especificados.');
    header("Location: index.php");
    exit;
}

$assentosArray = array_filter(array_map('trim', explode(',', $assentosRaw)));
if (empty($assentosArray)) {
    definir_toast('error', 'Selecione ao menos um assento.');
    header("Location: index.php");
    exit;
}

// Busca detalhes da sessão e filme
$stmt = $pdo->prepare("
    SELECT s.*, 
           f.id AS filme_id, f.titulo AS filme_titulo, f.cartaz_url, f.classificacao, f.duracao, f.genero,
           sa.id AS sala_id, sa.nome AS sala_nome, sa.tipo AS sala_tipo
    FROM sessoes s
    JOIN filmes f ON s.filme_id = f.id
    JOIN salas sa ON s.sala_id = sa.id
    WHERE s.id = ? AND s.status = 'ativa'
");
$stmt->execute([$sessaoId]);
$sessao = $stmt->fetch();

if (!$sessao) {
    definir_toast('error', 'Sessão indisponível.');
    header("Location: index.php");
    exit;
}

// Verifica se algum dos assentos selecionados já foi ocupado
$placeholders = implode(',', array_fill(0, count($assentosArray), '?'));
$stmtVerifica = $pdo->prepare("
    SELECT codigo_assento FROM assentos_ocupados 
    WHERE sessao_id = ? AND codigo_assento IN ($placeholders)
");
$paramsVerifica = array_merge([$sessaoId], $assentosArray);
$stmtVerifica->execute($paramsVerifica);
$conflitos = $stmtVerifica->fetchAll(PDO::FETCH_COLUMN);

if (!empty($conflitos)) {
    definir_toast('error', 'Infelizmente o(s) assento(s) ' . implode(', ', $conflitos) . ' acabou(aram) de ser ocupado(s). Por favor selecione outro.');
    header("Location: assentos.php?sessao_id=" . $sessaoId);
    exit;
}

$qtdAssentos = count($assentosArray);
$precoUnitario = (float)$sessao['preco_ingresso'];
$valorTotal = $qtdAssentos * $precoUnitario;

// Usuário atual para preenchimento automático
$usuario = null;
if (esta_logado()) {
    $stmtUser = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmtUser->execute([$_SESSION['usuario_id']]);
    $usuario = $stmtUser->fetch();
}

$paginaTitulo = 'Pagamento - CinePimenta';
$etapaCompraAtual = 4;

require_once __DIR__ . '/includes/header.php';
?>

<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    
    <div class="mb-8">
        <a href="assentos.php?sessao_id=<?= $sessaoId ?>" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-400 hover:text-white mb-3 group">
            <i class="fa-solid fa-arrow-left group-hover:-translate-x-1 transition-transform"></i>
            <span>Alterar Seleção de Poltronas</span>
        </a>
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-red-600/20 text-red-400 text-xs font-bold uppercase mb-2">
            Etapa 4 de 5: Finalização da Compra
        </div>
        <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">
            Identificação & Pagamento
        </h1>
        <p class="text-xs sm:text-sm text-slate-400 mt-1">
            Preencha seus dados para emissão do ticket e escolha sua forma de pagamento favorita.
        </p>
    </div>

    <form id="form-pagamento" action="actions/processar_pagamento.php" method="POST">
        <input type="hidden" name="sessao_id" value="<?= $sessaoId ?>">
        <input type="hidden" name="assentos" value="<?= htmlspecialchars(implode(',', $assentosArray)) ?>">
        <input type="hidden" name="forma_pagamento" id="input-forma-pagamento" value="pix">

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- COLUNA ESQUERDA: IDENTIFICAÇÃO E PAGAMENTO -->
            <div class="lg:col-span-7 space-y-8">
                
                <!-- DADOS DO CLIENTE -->
                <div class="bg-cinema-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xl">
                    <div class="flex items-center gap-3 pb-4 mb-6 border-b border-slate-800">
                        <div class="w-9 h-9 rounded-xl bg-red-600/20 text-red-400 flex items-center justify-center">
                            <i class="fa-solid fa-user-check"></i>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-white">Dados do Comprador</h2>
                            <p class="text-xs text-slate-400">O ingresso digital será emitido com estes dados.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Nome Completo *</label>
                            <input type="text" name="nome_cliente" required 
                                   value="<?= htmlspecialchars($usuario['nome'] ?? '') ?>"
                                   placeholder="Ex: Carlos Eduardo de Oliveira" 
                                   class="w-full bg-cinema-800 border border-slate-700 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-red-500 focus:ring-2 focus:ring-red-500/20">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">E-mail *</label>
                            <input type="email" name="email_cliente" required 
                                   value="<?= htmlspecialchars($usuario['email'] ?? '') ?>"
                                   placeholder="seu.email@exemplo.com" 
                                   class="w-full bg-cinema-800 border border-slate-700 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-red-500 focus:ring-2 focus:ring-red-500/20">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">CPF *</label>
                            <input type="text" name="cpf_cliente" required 
                                   value="<?= htmlspecialchars($usuario['cpf'] ?? '') ?>"
                                   placeholder="000.000.000-00" 
                                   class="w-full bg-cinema-800 border border-slate-700 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-red-500 focus:ring-2 focus:ring-red-500/20">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Telefone / WhatsApp</label>
                            <input type="text" name="telefone_cliente" 
                                   value="<?= htmlspecialchars($usuario['telefone'] ?? '') ?>"
                                   placeholder="(11) 98765-4321" 
                                   class="w-full bg-cinema-800 border border-slate-700 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-red-500 focus:ring-2 focus:ring-red-500/20">
                        </div>
                    </div>
                </div>

                <!-- FORMAS DE PAGAMENTO -->
                <div class="bg-cinema-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xl">
                    <div class="flex items-center gap-3 pb-4 mb-6 border-b border-slate-800">
                        <div class="w-9 h-9 rounded-xl bg-emerald-600/20 text-emerald-400 flex items-center justify-center">
                            <i class="fa-solid fa-credit-card"></i>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-white">Escolha a Forma de Pagamento</h2>
                            <p class="text-xs text-slate-400">Ambiente 100% seguro com criptografia de ponta a ponta.</p>
                        </div>
                    </div>

                    <!-- ABAS DE SELEÇÃO DE PAGAMENTO -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mb-6">
                        <button type="button" onclick="selecionarMetodo('pix')" id="tab-pix" 
                                class="metodo-tab p-3 rounded-2xl border text-center transition-all bg-cinema-800 border-red-500 text-white font-bold text-xs shadow-md">
                            <i class="fa-brands fa-pix text-emerald-400 text-xl block mb-1"></i>
                            <span>PIX Instantâneo</span>
                        </button>

                        <button type="button" onclick="selecionarMetodo('credito')" id="tab-credito" 
                                class="metodo-tab p-3 rounded-2xl border text-center transition-all bg-cinema-800/40 border-slate-700 text-slate-400 hover:text-white font-medium text-xs">
                            <i class="fa-solid fa-credit-card text-blue-400 text-xl block mb-1"></i>
                            <span>Cartão Crédito</span>
                        </button>

                        <button type="button" onclick="selecionarMetodo('debito')" id="tab-debito" 
                                class="metodo-tab p-3 rounded-2xl border text-center transition-all bg-cinema-800/40 border-slate-700 text-slate-400 hover:text-white font-medium text-xs">
                            <i class="fa-solid fa-wallet text-amber-400 text-xl block mb-1"></i>
                            <span>Cartão Débito</span>
                        </button>

                        <button type="button" onclick="selecionarMetodo('dinheiro')" id="tab-dinheiro" 
                                class="metodo-tab p-3 rounded-2xl border text-center transition-all bg-cinema-800/40 border-slate-700 text-slate-400 hover:text-white font-medium text-xs">
                            <i class="fa-solid fa-money-bill-wave text-emerald-500 text-xl block mb-1"></i>
                            <span>Bilheteria</span>
                        </button>
                    </div>

                    <!-- CONTEÚDO 1: PIX -->
                    <div id="conteudo-pix" class="conteudo-metodo bg-cinema-800/60 border border-slate-700/80 rounded-2xl p-6 text-center space-y-4">
                        <div class="w-12 h-12 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center mx-auto text-2xl">
                            <i class="fa-brands fa-pix"></i>
                        </div>
                        <h3 class="text-sm font-bold text-white">Pagamento Instantâneo via PIX</h3>
                        <p class="text-xs text-slate-400 max-w-sm mx-auto leading-relaxed">
                            Ao clicar em Confirmar Compra, o QR Code dinâmico será gerado e a confirmação é imediata sem taxas adicionais.
                        </p>
                        <div class="p-3 bg-cinema-900 border border-slate-800 rounded-xl text-xs text-slate-300 font-mono inline-block">
                            <i class="fa-solid fa-bolt text-amber-400 mr-1.5"></i> Liberação imediata dos assentos selecionados
                        </div>
                    </div>

                    <!-- CONTEÚDO 2: CARTÃO DE CRÉDITO -->
                    <div id="conteudo-credito" class="conteudo-metodo hidden bg-cinema-800/60 border border-slate-700/80 rounded-2xl p-6 space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-slate-300 mb-1">Número do Cartão</label>
                                <input type="text" placeholder="0000 0000 0000 0000" maxlength="19" class="w-full bg-cinema-900 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-red-500">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-slate-300 mb-1">Nome no Cartão</label>
                                <input type="text" placeholder="Nome como impresso no cartão" class="w-full bg-cinema-900 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-red-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-300 mb-1">Validade (MM/AA)</label>
                                <input type="text" placeholder="12/28" maxlength="5" class="w-full bg-cinema-900 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-red-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-300 mb-1">CVV (3 dígitos)</label>
                                <input type="password" placeholder="123" maxlength="4" class="w-full bg-cinema-900 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-red-500">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-slate-300 mb-1">Parcelamento</label>
                                <select class="w-full bg-cinema-900 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-red-500">
                                    <option value="1">1x de <?= formatar_moeda($valorTotal) ?> (sem juros)</option>
                                    <option value="2">2x de <?= formatar_moeda($valorTotal / 2) ?> (sem juros)</option>
                                    <option value="3">3x de <?= formatar_moeda($valorTotal / 3) ?> (sem juros)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- CONTEÚDO 3: CARTÃO DE DÉBITO -->
                    <div id="conteudo-debito" class="conteudo-metodo hidden bg-cinema-800/60 border border-slate-700/80 rounded-2xl p-6 text-center space-y-3">
                        <i class="fa-solid fa-shield-halved text-amber-400 text-3xl"></i>
                        <h4 class="text-sm font-bold text-white">Débito Online / Banco</h4>
                        <p class="text-xs text-slate-400">Você será direcionado para autenticação de 2 fatores do seu banco.</p>
                    </div>

                    <!-- CONTEÚDO 4: BILHETERIA -->
                    <div id="conteudo-dinheiro" class="conteudo-metodo hidden bg-cinema-800/60 border border-slate-700/80 rounded-2xl p-6 text-center space-y-3">
                        <i class="fa-solid fa-ticket text-emerald-400 text-3xl"></i>
                        <h4 class="text-sm font-bold text-white">Reserva para Pagamento na Bilheteria</h4>
                        <p class="text-xs text-slate-400">Seus assentos ficarão reservados até 20 minutos antes do início da sessão.</p>
                    </div>

                </div>

            </div>

            <!-- COLUNA DIREITA: RESUMO DO PEDIDO -->
            <div class="lg:col-span-5">
                <div class="bg-cinema-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-2xl sticky top-24 space-y-6">
                    
                    <div class="flex items-center justify-between pb-4 border-b border-slate-800">
                        <h3 class="text-base font-bold text-white flex items-center gap-2">
                            <i class="fa-solid fa-receipt text-red-500"></i> Resumo do Ingresso
                        </h3>
                        <span class="text-xs text-slate-400"><?= $qtdAssentos ?> assento(s)</span>
                    </div>

                    <!-- Filme e Sessão -->
                    <div class="flex gap-4 items-center">
                        <img src="<?= htmlspecialchars($sessao['cartaz_url']) ?>" alt="Capa" class="w-16 h-22 object-cover rounded-xl border border-slate-700 shadow shrink-0">
                        <div class="space-y-1">
                            <h4 class="text-base font-black text-white leading-snug">
                                <?= htmlspecialchars($sessao['filme_titulo']) ?>
                            </h4>
                            <div class="text-xs text-slate-400">
                                <?= htmlspecialchars($sessao['sala_nome']) ?> (<?= htmlspecialchars($sessao['tipo_sessao']) ?>)
                            </div>
                            <div class="text-xs font-semibold text-amber-400">
                                <?= formatar_data($sessao['data']) ?> às <?= formatar_hora($sessao['horario']) ?>
                            </div>
                        </div>
                    </div>

                    <!-- Poltronas Selecionadas -->
                    <div class="p-3.5 bg-cinema-800/80 rounded-2xl border border-slate-700/60 space-y-2">
                        <span class="text-xs text-slate-400 block font-semibold">Poltronas reservadas:</span>
                        <div class="flex flex-wrap gap-1.5">
                            <?php foreach ($assentosArray as $ass): ?>
                                <span class="px-2.5 py-1 rounded-lg bg-red-600/20 border border-red-500/40 text-red-300 text-xs font-black">
                                    <i class="fa-solid fa-couch mr-1 text-[10px]"></i><?= $ass ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Discriminação de Valores -->
                    <div class="space-y-2.5 text-xs text-slate-300 border-t border-slate-800 pt-4">
                        <div class="flex justify-between">
                            <span>Ingresso padrão (<?= $qtdAssentos ?>x <?= formatar_moeda($precoUnitario) ?>)</span>
                            <span class="font-bold text-white"><?= formatar_moeda($valorTotal) ?></span>
                        </div>
                        <div class="flex justify-between text-slate-400">
                            <span>Taxa de serviço</span>
                            <span class="text-emerald-400 font-bold">Grátis (R$ 0,00)</span>
                        </div>
                        <div class="flex justify-between items-center text-sm sm:text-base font-black text-white pt-2 border-t border-slate-800">
                            <span>Total a Pagar</span>
                            <span class="text-2xl text-emerald-400 font-black"><?= formatar_moeda($valorTotal) ?></span>
                        </div>
                    </div>

                    <!-- Botão de Confirmação -->
                    <button type="submit" class="w-full py-4 px-6 rounded-2xl bg-red-600 hover:bg-red-700 text-white font-black text-sm uppercase tracking-wider transition-all shadow-xl shadow-red-950/60 flex items-center justify-center gap-2 group">
                        <i class="fa-solid fa-lock text-xs"></i>
                        <span>Pagar e Receber Ticket</span>
                        <i class="fa-solid fa-arrow-right text-xs group-hover:translate-x-1 transition-transform"></i>
                    </button>

                    <div class="text-[11px] text-slate-500 text-center space-y-1">
                        <p><i class="fa-solid fa-shield-check text-emerald-500 mr-1"></i> Auditoria de transação ativa</p>
                        <p>O ticket gerado contém QR Code para validação na entrada da sala.</p>
                    </div>

                </div>
            </div>

        </div>
    </form>
</section>

<script>
function selecionarMetodo(metodo) {
    document.getElementById('input-forma-pagamento').value = metodo;

    // Remove destaque de todas as abas
    document.querySelectorAll('.metodo-tab').forEach(tab => {
        tab.className = 'metodo-tab p-3 rounded-2xl border text-center transition-all bg-cinema-800/40 border-slate-700 text-slate-400 hover:text-white font-medium text-xs';
    });

    // Oculta todos os conteúdos
    document.querySelectorAll('.conteudo-metodo').forEach(c => c.classList.add('hidden'));

    // Ativa a aba e o conteúdo selecionado
    const tabAtiva = document.getElementById(`tab-${metodo}`);
    const conteudoAtivo = document.getElementById(`conteudo-${metodo}`);

    if (tabAtiva) {
        tabAtiva.className = 'metodo-tab p-3 rounded-2xl border text-center transition-all bg-cinema-800 border-red-500 text-white font-bold text-xs shadow-md';
    }
    if (conteudoAtivo) {
        conteudoAtivo.classList.remove('hidden');
    }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
