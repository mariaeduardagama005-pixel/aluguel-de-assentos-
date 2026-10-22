<?php
/**
 * =====================================================================
 * ETAPA 3: SELEÇÃO INTERATIVA DE ASSENTOS DE CINEMA
 * Mapa da sala em tempo real, seleção múltipla de poltronas e cálculo dinâmico
 * =====================================================================
 */

require_once __DIR__ . '/bd.php';

$sessaoId = filter_input(INPUT_GET, 'sessao_id', FILTER_VALIDATE_INT);

if (!$sessaoId) {
    definir_toast('error', 'Sessão inválida ou não especificada.');
    header("Location: index.php");
    exit;
}

// Busca dados completos da sessão, sala e filme
$stmt = $pdo->prepare("
    SELECT s.*, 
           f.id AS filme_id, f.titulo AS filme_titulo, f.cartaz_url, f.classificacao, f.duracao, f.genero,
           sa.id AS sala_id, sa.nome AS sala_nome, sa.tipo AS sala_tipo, sa.fileiras, sa.colunas, sa.capacidade
    FROM sessoes s
    JOIN filmes f ON s.filme_id = f.id
    JOIN salas sa ON s.sala_id = sa.id
    WHERE s.id = ? AND s.status = 'ativa'
");
$stmt->execute([$sessaoId]);
$sessao = $stmt->fetch();

if (!$sessao) {
    definir_toast('error', 'Sessão não encontrada ou inativa.');
    header("Location: index.php");
    exit;
}

// Busca os assentos já ocupados nesta sessão
$stmtOcupados = $pdo->prepare("SELECT codigo_assento FROM assentos_ocupados WHERE sessao_id = ?");
$stmtOcupados->execute([$sessaoId]);
$assentosOcupadosLista = $stmtOcupados->fetchAll(PDO::FETCH_COLUMN);
$assentosOcupadosSet = array_flip($assentosOcupadosLista);

$paginaTitulo = 'Escolha seus Assentos - ' . htmlspecialchars($sessao['filme_titulo']) . ' - CinePimenta';
$etapaCompraAtual = 3;

require_once __DIR__ . '/includes/header.php';
?>

<!-- RESUMO DA SESSÃO SELECIONADA -->
<section class="bg-cinema-900 border-b border-slate-800 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            
            <div class="flex items-center gap-4">
                <a href="filme.php?id=<?= $sessao['filme_id'] ?>" class="text-slate-400 hover:text-white p-2 rounded-xl bg-cinema-800 border border-slate-700 transition-colors" title="Voltar">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                
                <img src="<?= htmlspecialchars($sessao['cartaz_url']) ?>" alt="Poster" class="w-12 h-16 object-cover rounded-lg shadow border border-slate-700 shrink-0">
                
                <div>
                    <span class="text-[11px] font-bold text-red-400 uppercase tracking-wider block">Etapa 3 de 5: Seleção de Poltronas</span>
                    <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight leading-tight">
                        <?= htmlspecialchars($sessao['filme_titulo']) ?>
                    </h1>
                    <div class="flex flex-wrap items-center gap-2 mt-1 text-xs text-slate-400">
                        <span><i class="fa-solid fa-door-open text-slate-500 mr-1"></i><?= htmlspecialchars($sessao['sala_nome']) ?></span>
                        <span>&bull;</span>
                        <span><i class="fa-regular fa-calendar mr-1 text-slate-500"></i><?= formatar_data($sessao['data']) ?></span>
                        <span>&bull;</span>
                        <span class="font-bold text-amber-400"><i class="fa-solid fa-clock mr-1"></i><?= formatar_hora($sessao['horario']) ?></span>
                        <span>&bull;</span>
                        <span class="px-1.5 py-0.5 rounded bg-slate-800 text-slate-300 font-semibold uppercase text-[10px]"><?= htmlspecialchars($sessao['idioma']) ?></span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4 self-end md:self-auto bg-cinema-800/80 px-4 py-2.5 rounded-2xl border border-slate-700">
                <span class="text-xs text-slate-400">Preço unitário:</span>
                <span class="text-lg font-black text-emerald-400"><?= formatar_moeda($sessao['preco_ingresso']) ?></span>
            </div>

        </div>
    </div>
</section>

<!-- ÁREA INTERATIVA DO MAPA DA SALA -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    
    <!-- LEGENDA DE ASSENTOS -->
    <div class="flex flex-wrap items-center justify-center gap-4 sm:gap-8 mb-10 text-xs font-semibold text-slate-300 bg-cinema-900/60 border border-slate-800/80 py-3.5 px-6 rounded-2xl max-w-2xl mx-auto">
        <div class="flex items-center gap-2">
            <div class="w-6 h-6 rounded-lg seat-available border border-slate-600"></div>
            <span>Disponível</span>
        </div>
        <div class="flex items-center gap-2">
            <div class="w-6 h-6 rounded-lg seat-selected"></div>
            <span>Selecionado</span>
        </div>
        <div class="flex items-center gap-2">
            <div class="w-6 h-6 rounded-lg seat-occupied"></div>
            <span>Ocupado</span>
        </div>
        <div class="flex items-center gap-2">
            <div class="w-6 h-6 rounded-lg border-2 border-amber-400/80 bg-cinema-800 flex items-center justify-center text-[10px] text-amber-400">
                <i class="fa-solid fa-wheelchair"></i>
            </div>
            <span>Acessível</span>
        </div>
    </div>

    <!-- TELA DO CINEMA -->
    <div class="max-w-3xl mx-auto text-center mb-8">
        <div class="cinema-screen">
            <span><i class="fa-solid fa-film mr-2"></i>TELA DO CINEMA</span>
        </div>
        <p class="text-[11px] text-slate-500 uppercase tracking-widest font-bold -mt-8 mb-8">Frente da Sala</p>
    </div>

    <!-- GRADE DE POLTRONAS (MAPA) -->
    <div class="overflow-x-auto pb-8">
        <div class="inline-block min-w-full text-center">
            <div class="inline-flex flex-col gap-3 p-6 bg-cinema-900/40 border border-slate-800/80 rounded-3xl shadow-inner">
                <?php
                    $numFileiras = (int)$sessao['fileiras'];
                    $numColunas = (int)$sessao['colunas'];
                    $letras = range('A', 'Z');

                    for ($f = 0; $f < $numFileiras; $f++):
                        $letraFileira = $letras[$f];
                        $ehFileiraAcessivel = ($f === 0); // Primeira fileira com acessibilidade
                ?>
                    <div class="flex items-center justify-center gap-2 sm:gap-3">
                        <!-- Letra da Fileira (Esquerda) -->
                        <span class="w-6 text-xs font-black text-slate-400 select-none">
                            <?= $letraFileira ?>
                        </span>

                        <!-- Assentos da Fileira -->
                        <div class="flex items-center gap-1.5 sm:gap-2">
                            <?php 
                                for ($c = 1; $c <= $numColunas; $c++):
                                    $codigoAssento = $letraFileira . $c;
                                    $estaOcupado = isset($assentosOcupadosSet[$codigoAssento]);
                                    
                                    // Adiciona corredor no meio da sala
                                    $ehCorredor = ($c === (int)ceil($numColunas / 2));
                            ?>
                                <button type="button" 
                                        class="seat <?= $estaOcupado ? 'seat-occupied' : 'seat-available' ?> <?= $ehFileiraAcessivel && !$estaOcupado ? 'border-amber-400/50' : '' ?>"
                                        data-seat="<?= $codigoAssento ?>"
                                        data-occupied="<?= $estaOcupado ? '1' : '0' ?>"
                                        <?= $estaOcupado ? 'disabled title="Assento ocupado"' : 'title="Poltrona '.$codigoAssento.'"' ?>>
                                    <?php if ($ehFileiraAcessivel && !$estaOcupado): ?>
                                        <i class="fa-solid fa-wheelchair text-[9px] text-amber-400/80"></i>
                                    <?php else: ?>
                                        <?= $c ?>
                                    <?php endif; ?>
                                </button>

                                <?php if ($ehCorredor): ?>
                                    <div class="w-3 sm:w-6"></div>
                                <?php endif; ?>

                            <?php endfor; ?>
                        </div>

                        <!-- Letra da Fileira (Direita) -->
                        <span class="w-6 text-xs font-black text-slate-400 select-none">
                            <?= $letraFileira ?>
                        </span>
                    </div>
                <?php endfor; ?>
            </div>
        </div>
    </div>

</section>

<!-- BARRA FIXA INFERIOR DE RESUMO & AVANÇO (Checkout Bar) -->
<div class="sticky bottom-0 z-30 bg-cinema-900/95 backdrop-blur-md border-t border-slate-800 p-4 shadow-2xl">
    <div class="max-w-7xl mx-auto flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        
        <!-- Detalhes dos Assentos Selecionados -->
        <div class="flex items-center gap-4">
            <div class="w-11 h-11 rounded-2xl bg-red-600/20 text-red-400 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-couch"></i>
            </div>
            <div>
                <div class="text-xs text-slate-400">Assento(s) selecionado(s):</div>
                <div id="display-selected-seats" class="text-sm sm:text-base font-black text-white">
                    <span class="text-slate-500 font-normal italic">Nenhum assento selecionado ainda</span>
                </div>
            </div>
        </div>

        <!-- Cálculo do Valor e Botão Avançar -->
        <div class="flex items-center justify-between sm:justify-end gap-6 border-t sm:border-t-0 pt-2 sm:pt-0 border-slate-800">
            <div class="text-right">
                <div class="text-xs text-slate-400"><span id="count-selected">0</span> ingresso(s)</div>
                <div id="total-price" class="text-xl sm:text-2xl font-black text-emerald-400 leading-none">
                    R$ 0,00
                </div>
            </div>

            <button type="button" id="btn-prosseguir" onclick="prosseguirPagamento()" 
                    class="px-6 py-3 rounded-2xl bg-red-600 hover:bg-red-700 text-white font-extrabold text-sm transition-all shadow-lg shadow-red-950/50 flex items-center gap-2 group">
                <span>Continuar para Pagamento</span>
                <i class="fa-solid fa-arrow-right text-xs group-hover:translate-x-1 transition-transform"></i>
            </button>
        </div>

    </div>
</div>

<script>
const precoUnitario = <?= (float)$sessao['preco_ingresso'] ?>;
const sessaoId = <?= (int)$sessao['id'] ?>;
let selectedSeats = [];

document.addEventListener('DOMContentLoaded', () => {
    const seatButtons = document.querySelectorAll('.seat[data-occupied="0"]');

    seatButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const seatCode = btn.getAttribute('data-seat');
            
            if (selectedSeats.includes(seatCode)) {
                // Desmarcar
                selectedSeats = selectedSeats.filter(s => s !== seatCode);
                btn.classList.remove('seat-selected');
                btn.classList.add('seat-available');
            } else {
                // Marcar
                if (selectedSeats.length >= 8) {
                    showToast('warning', 'Limite máximo de 8 ingressos por compra atingido.');
                    return;
                }
                selectedSeats.push(seatCode);
                btn.classList.remove('seat-available');
                btn.classList.add('seat-selected');
            }

            atualizarResumo();
        });
    });
});

function atualizarResumo() {
    const displaySeats = document.getElementById('display-selected-seats');
    const displayCount = document.getElementById('count-selected');
    const displayTotal = document.getElementById('total-price');

    displayCount.innerText = selectedSeats.length;

    if (selectedSeats.length === 0) {
        displaySeats.innerHTML = '<span class="text-slate-500 font-normal italic">Nenhum assento selecionado ainda</span>';
        displayTotal.innerText = 'R$ 0,00';
    } else {
        selectedSeats.sort();
        displaySeats.innerHTML = selectedSeats.map(s => 
            `<span class="inline-block px-2 py-0.5 rounded bg-red-600/30 border border-red-500/50 text-red-300 text-xs font-bold mr-1">${s}</span>`
        ).join('');
        
        const total = selectedSeats.length * precoUnitario;
        displayTotal.innerText = 'R$ ' + total.toFixed(2).replace('.', ',');
    }
}

function prosseguirPagamento() {
    if (selectedSeats.length === 0) {
        showModalAlert(
            'Selecione uma Poltrona',
            'Por favor, clique sobre uma ou mais poltronas disponíveis no mapa da sala antes de prosseguir com a compra.',
            'warning'
        );
        return;
    }

    const assentosParam = encodeURIComponent(selectedSeats.join(','));
    window.location.href = `pagamento.php?sessao_id=${sessaoId}&assentos=${assentosParam}`;
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
