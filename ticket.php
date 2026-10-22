<?php
/**
 * =====================================================================
 * ETAPA 5: EMISSÃO E VISUALIZAÇÃO DO TICKET DE CINEMA
 * "logo depois ele paga e recebe o ticket até a final, registra o resultado e posições"
 * =====================================================================
 */

require_once __DIR__ . '/bd.php';

$codigo = trim($_GET['codigo'] ?? '');

if (empty($codigo)) {
    definir_toast('error', 'Código do ingresso não informado.');
    header("Location: index.php");
    exit;
}

// Busca os dados do ingresso completo
$stmt = $pdo->prepare("
    SELECT i.*, 
           s.data AS sessao_data, s.horario AS sessao_horario, s.idioma AS sessao_idioma, s.tipo_sessao,
           f.id AS filme_id, f.titulo AS filme_titulo, f.cartaz_url, f.classificacao, f.duracao, f.genero,
           sa.nome AS sala_nome, sa.tipo AS sala_tipo
    FROM ingressos i
    JOIN sessoes s ON i.sessao_id = s.id
    JOIN filmes f ON s.filme_id = f.id
    JOIN salas sa ON s.sala_id = sa.id
    WHERE i.codigo = ?
");
$stmt->execute([$codigo]);
$ticket = $stmt->fetch();

if (!$ticket) {
    definir_toast('error', 'Ingresso não encontrado no sistema.');
    header("Location: index.php");
    exit;
}

$paginaTitulo = 'Ticket ' . htmlspecialchars($ticket['codigo']) . ' - CinePimenta';
$etapaCompraAtual = 5;

// URL de geração do QR Code para validação na entrada
$qrData = urlencode("CINEPIMENTA-TICKET:" . $ticket['codigo'] . "|SESSAO:" . $ticket['sessao_id'] . "|ASSENTOS:" . $ticket['assentos_selecionados']);
$qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=160x160&data=" . $qrData;

require_once __DIR__ . '/includes/header.php';
?>

<!-- ÁREA DO INGRESSO (Otimizada para Mobile e Impressão) -->
<section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    
    <!-- Mensagem de Sucesso no Topo -->
    <div class="text-center mb-8">
        <div class="w-16 h-16 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center mx-auto text-3xl mb-3 shadow-lg shadow-emerald-950/50">
            <i class="fa-solid fa-circle-check"></i>
        </div>
        <span class="inline-block px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-bold uppercase tracking-wider mb-2">
            Etapa 5 Concluída com Sucesso
        </span>
        <h1 class="text-2xl sm:text-4xl font-black text-white tracking-tight">
            Pagamento Aprovado!
        </h1>
        <p class="text-xs sm:text-sm text-slate-400 mt-1">
            Seu ingresso está confirmado. Apresente o QR Code na entrada da sala ou imprima este ticket.
        </p>
    </div>

    <!-- CARTÃO DO TICKET (Estilo Ingresso de Cinema Perfurado) -->
    <div id="area-ticket" class="cinema-ticket max-w-2xl mx-auto border border-slate-700 bg-gradient-to-b from-slate-900 to-cinema-900 shadow-2xl">
        
        <!-- Topo Dourado do Ingresso -->
        <div class="bg-gradient-to-r from-red-700 via-red-600 to-amber-600 p-6 text-white flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-black/30 flex items-center justify-center text-white text-xl">
                    <i class="fa-solid fa-film"></i>
                </div>
                <div>
                    <span class="text-[10px] font-black uppercase tracking-widest text-amber-200 block">Ingresso Digital Oficial</span>
                    <span class="text-xl font-black tracking-tight">CinePimenta</span>
                </div>
            </div>
            <div class="text-right">
                <span class="text-[10px] uppercase font-bold text-slate-200 block">Código da Reserva</span>
                <span class="font-mono font-black text-sm tracking-wider text-amber-300"><?= htmlspecialchars($ticket['codigo']) ?></span>
            </div>
        </div>

        <!-- Corpo Principal do Ticket -->
        <div class="p-6 sm:p-8 space-y-6">
            
            <!-- Dados do Filme e Sessão -->
            <div class="flex flex-col sm:flex-row gap-6 items-start">
                <img src="<?= htmlspecialchars($ticket['cartaz_url']) ?>" alt="Filme" class="w-24 h-32 object-cover rounded-xl border border-slate-700 shadow-lg shrink-0 mx-auto sm:mx-0">
                
                <div class="space-y-2 text-center sm:text-left flex-1">
                    <div class="inline-flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded bg-slate-800 border border-slate-700 text-xs font-bold text-slate-300 uppercase">
                            <?= htmlspecialchars($ticket['classificacao']) ?>
                        </span>
                        <span class="text-xs text-red-400 font-semibold uppercase">
                            <?= htmlspecialchars($ticket['genero']) ?>
                        </span>
                    </div>

                    <h2 class="text-2xl font-black text-white leading-tight">
                        <?= htmlspecialchars($ticket['filme_titulo']) ?>
                    </h2>

                    <div class="grid grid-cols-2 gap-3 pt-2 text-xs text-slate-300">
                        <div>
                            <span class="text-slate-500 block text-[10px] uppercase">Sala</span>
                            <strong class="text-white"><?= htmlspecialchars($ticket['sala_nome']) ?></strong>
                        </div>
                        <div>
                            <span class="text-slate-500 block text-[10px] uppercase">Formato</span>
                            <strong class="text-white"><?= htmlspecialchars($ticket['tipo_sessao']) ?> (<?= htmlspecialchars($ticket['sessao_idioma']) ?>)</strong>
                        </div>
                        <div>
                            <span class="text-slate-500 block text-[10px] uppercase">Data</span>
                            <strong class="text-white"><?= formatar_data($ticket['sessao_data']) ?></strong>
                        </div>
                        <div>
                            <span class="text-slate-500 block text-[10px] uppercase">Horário de Início</span>
                            <strong class="text-amber-400 font-bold"><?= formatar_hora($ticket['sessao_horario']) ?></strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- LINHA PERFURADA DO INGRESSO COM PICOTE -->
            <div class="ticket-divider">
                <div class="ticket-notch-left"></div>
                <div class="ticket-notch-right"></div>
            </div>

            <!-- POLTRONAS & QR CODE -->
            <div class="grid grid-cols-1 sm:grid-cols-12 gap-6 items-center">
                
                <div class="sm:col-span-7 space-y-4 text-center sm:text-left">
                    <div>
                        <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider block mb-1">
                            Assento(s) Reservado(s)
                        </span>
                        <div class="flex flex-wrap gap-2 justify-center sm:justify-start">
                            <?php 
                                $assentosLista = explode(',', $ticket['assentos_selecionados']);
                                foreach ($assentosLista as $ass):
                            ?>
                                <span class="px-3.5 py-1.5 rounded-xl bg-red-600 text-white font-black text-sm tracking-wide shadow-md shadow-red-950/60">
                                    <i class="fa-solid fa-couch mr-1 text-xs"></i><?= trim($ass) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="space-y-1 text-xs text-slate-400 pt-2">
                        <div>Titular: <strong class="text-white"><?= htmlspecialchars($ticket['nome_cliente']) ?></strong></div>
                        <div>CPF: <span class="font-mono text-slate-300"><?= htmlspecialchars($ticket['cpf_cliente']) ?></span></div>
                        <div>Forma de Pagamento: <strong class="uppercase text-emerald-400"><?= htmlspecialchars($ticket['forma_pagamento']) ?></strong></div>
                        <div>Valor Pago: <strong class="text-white text-sm"><?= formatar_moeda($ticket['valor_total']) ?></strong> (<?= $ticket['quantidade_assentos'] ?>x <?= formatar_moeda($ticket['valor_unitario']) ?>)</div>
                    </div>
                </div>

                <!-- QR Code de Validação -->
                <div class="sm:col-span-5 flex flex-col items-center justify-center p-3 bg-white rounded-2xl shadow-inner border border-slate-200">
                    <img src="<?= $qrUrl ?>" alt="QR Code Ticket" class="w-32 h-32 object-contain" 
                         onerror="this.src='https://api.qrserver.com/v1/create-qr-code/?size=160x160&data=CINEPIMENTA-<?= urlencode($ticket['codigo']) ?>'">
                    <span class="text-[9px] font-mono text-slate-800 font-bold mt-2 uppercase tracking-wider">
                        Validar na Entrada
                    </span>
                </div>

            </div>

        </div>

        <!-- Rodapé do Ticket -->
        <div class="p-4 bg-slate-950/70 border-t border-slate-800 flex items-center justify-between text-xs text-slate-500">
            <span>Transação: <?= htmlspecialchars($ticket['codigo_transacao'] ?? 'TXN-OK') ?></span>
            <span class="text-emerald-400 font-semibold"><i class="fa-solid fa-circle-check mr-1"></i> Aprovado</span>
        </div>

    </div>

    <!-- BOTÕES DE AÇÃO -->
    <div class="max-w-2xl mx-auto mt-8 flex flex-col sm:flex-row items-center justify-center gap-4">
        <button type="button" onclick="window.print()" class="w-full sm:w-auto px-6 py-3 rounded-2xl bg-cinema-800 hover:bg-slate-700 text-white font-bold text-xs uppercase tracking-wider transition-all border border-slate-700 flex items-center justify-center gap-2">
            <i class="fa-solid fa-print"></i>
            <span>Imprimir / Salvar PDF</span>
        </button>

        <a href="meus_ingressos.php" class="w-full sm:w-auto px-6 py-3 rounded-2xl bg-cinema-800 hover:bg-slate-700 text-slate-200 font-bold text-xs uppercase tracking-wider transition-all border border-slate-700 flex items-center justify-center gap-2">
            <i class="fa-solid fa-ticket text-cyan-400"></i>
            <span>Meus Ingressos</span>
        </a>

        <a href="index.php" class="w-full sm:w-auto px-6 py-3 rounded-2xl bg-red-600 hover:bg-red-700 text-white font-black text-xs uppercase tracking-wider transition-all shadow-lg shadow-red-950/40 flex items-center justify-center gap-2">
            <i class="fa-solid fa-film"></i>
            <span>Comprar Mais Ingressos</span>
        </a>
    </div>

</section>

<!-- ESTILOS ESPECÍFICOS PARA IMPRESSÃO LIMPA -->
<style>
@media print {
    body {
        background: #ffffff !important;
        color: #000000 !important;
    }
    header, footer, .sticky, button, a {
        display: none !important;
    }
    #area-ticket {
        box-shadow: none !important;
        border: 2px solid #000000 !important;
        background: #ffffff !important;
        color: #000000 !important;
        margin: 0 auto !important;
    }
    #area-ticket * {
        color: #000000 !important;
    }
    .ticket-divider {
        border-top: 2px dashed #000000 !important;
    }
    .ticket-notch-left, .ticket-notch-right {
        display: none !important;
    }
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
