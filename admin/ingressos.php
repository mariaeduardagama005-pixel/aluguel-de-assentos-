<?php
/**
 * =====================================================================
 * GESTÃO DE INGRESSOS VENDIDOS & BILHETERIA (ÁREA DO GERENTE)
 * Lista todas as reservas, assentos e status de pagamento
 * =====================================================================
 */

$ehAdminArea = true;
require_once __DIR__ . '/../bd.php';
require_once __DIR__ . '/../includes/auth.php';

exigir_gerente();

$paginaTitulo = 'Ingressos Vendidos - CinePimenta';

$busca = trim($_GET['busca'] ?? '');

$sql = "
    SELECT i.*, 
           s.data AS sessao_data, s.horario AS sessao_horario, s.tipo_sessao,
           f.titulo AS filme_titulo, f.cartaz_url,
           sa.nome AS sala_nome
    FROM ingressos i
    JOIN sessoes s ON i.sessao_id = s.id
    JOIN filmes f ON s.filme_id = f.id
    JOIN salas sa ON s.sala_id = sa.id
    WHERE 1=1
";
$params = [];

if (!empty($busca)) {
    $sql .= " AND (i.codigo LIKE ? OR i.nome_cliente LIKE ? OR i.cpf_cliente LIKE ? OR f.titulo LIKE ?)";
    $params[] = "%{$busca}%";
    $params[] = "%{$busca}%";
    $params[] = "%{$busca}%";
    $params[] = "%{$busca}%";
}

$sql .= " ORDER BY i.criado_em DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$ingressos = $stmt->fetchAll();

$totalArrecadado = 0;
foreach ($ingressos as $ing) {
    if ($ing['status_pagamento'] === 'aprovado') {
        $totalArrecadado += (float)$ing['valor_total'];
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    
    <!-- CABEÇALHO -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-800">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="index.php" class="hover:text-white">Painel</a>
                <span>&bull;</span>
                <span class="text-emerald-400 font-bold">Ingressos</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight flex items-center gap-3">
                <i class="fa-solid fa-ticket text-emerald-400"></i> Ingressos & Bilheteria
            </h1>
            <p class="text-xs sm:text-sm text-slate-400">
                Visualize todas as vendas realizadas, clientes, poltronas ocupadas e validação de QR Code.
            </p>
        </div>

        <div class="bg-cinema-900 border border-slate-800 px-4 py-2.5 rounded-2xl flex items-center gap-3">
            <span class="text-xs text-slate-400">Total desta listagem:</span>
            <span class="text-lg font-black text-emerald-400"><?= formatar_moeda($totalArrecadado) ?></span>
        </div>
    </div>

    <!-- NAVEGAÇÃO DE ABAS -->
    <div class="flex items-center gap-2 overflow-x-auto pb-2 border-b border-slate-800/80">
        <a href="index.php" class="px-4 py-2 rounded-xl bg-cinema-800 hover:bg-slate-700 text-slate-300 font-medium text-xs flex items-center gap-2">
            <i class="fa-solid fa-chart-line"></i> Visão Geral
        </a>
        <a href="filmes.php" class="px-4 py-2 rounded-xl bg-cinema-800 hover:bg-slate-700 text-slate-300 font-medium text-xs flex items-center gap-2">
            <i class="fa-solid fa-clapperboard text-red-400"></i> Filmes
        </a>
        <a href="sessoes.php" class="px-4 py-2 rounded-xl bg-cinema-800 hover:bg-slate-700 text-slate-300 font-medium text-xs flex items-center gap-2">
            <i class="fa-solid fa-clock text-amber-400"></i> Sessões & Preços
        </a>
        <a href="salas.php" class="px-4 py-2 rounded-xl bg-cinema-800 hover:bg-slate-700 text-slate-300 font-medium text-xs flex items-center gap-2">
            <i class="fa-solid fa-door-open text-cyan-400"></i> Salas
        </a>
        <a href="ingressos.php" class="px-4 py-2 rounded-xl bg-red-600 text-white font-bold text-xs flex items-center gap-2 shadow-md">
            <i class="fa-solid fa-ticket"></i> Ingressos
        </a>
        <a href="logs.php" class="px-4 py-2 rounded-xl bg-cinema-800 hover:bg-slate-700 text-slate-300 font-medium text-xs flex items-center gap-2">
            <i class="fa-solid fa-clock-rotate-left text-purple-400"></i> Auditoria
        </a>
    </div>

    <!-- BARRA DE BUSCA -->
    <div class="bg-cinema-900 border border-slate-800 rounded-2xl p-4 shadow">
        <form action="ingressos.php" method="GET" class="flex gap-2">
            <input type="text" name="busca" value="<?= htmlspecialchars($busca) ?>" placeholder="Buscar por código, cliente, CPF ou filme..." 
                   class="flex-1 bg-cinema-800 border border-slate-700 rounded-xl px-4 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500">
            <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl transition-all shadow">
                Buscar
            </button>
            <?php if (!empty($busca)): ?>
                <a href="ingressos.php" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold rounded-xl flex items-center">
                    Limpar
                </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- TABELA DE INGRESSOS -->
    <div class="bg-cinema-900 border border-slate-800 rounded-3xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-cinema-950 border-b border-slate-800 text-slate-400 font-bold uppercase tracking-wider text-[10px]">
                        <th class="py-3 px-4">Código & Data</th>
                        <th class="py-3 px-4">Cliente / CPF</th>
                        <th class="py-3 px-4">Filme & Sala</th>
                        <th class="py-3 px-4">Horário</th>
                        <th class="py-3 px-4">Poltronas</th>
                        <th class="py-3 px-4">Valor Total</th>
                        <th class="py-3 px-4">Pagamento</th>
                        <th class="py-3 px-4 text-right">Ticket</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    <?php if (empty($ingressos)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-8 text-slate-500">Nenhum ingresso encontrado.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($ingressos as $i): ?>
                            <tr class="hover:bg-cinema-800/40 transition-colors">
                                <td class="py-3 px-4 font-mono font-bold text-amber-400 whitespace-nowrap">
                                    <?= htmlspecialchars($i['codigo']) ?>
                                    <span class="block font-normal text-[10px] text-slate-500 mt-0.5">
                                        <?= date('d/m/Y H:i', strtotime($i['criado_em'])) ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <strong class="text-white block"><?= htmlspecialchars($i['nome_cliente']) ?></strong>
                                    <span class="text-[10px] text-slate-400 font-mono"><?= htmlspecialchars($i['cpf_cliente']) ?></span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="font-bold text-slate-200 block"><?= htmlspecialchars($i['filme_titulo']) ?></span>
                                    <span class="text-[10px] text-slate-500"><?= htmlspecialchars($i['sala_nome']) ?></span>
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap text-slate-300">
                                    <?= formatar_data($i['sessao_data']) ?> às <span class="font-bold text-amber-400"><?= formatar_hora($i['sessao_horario']) ?></span>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex flex-wrap gap-1 max-w-[140px]">
                                        <?php foreach (explode(',', $i['assentos_selecionados']) as $ass): ?>
                                            <span class="px-1.5 py-0.5 rounded bg-red-600/20 text-red-300 font-bold text-[10px] border border-red-500/30">
                                                <?= trim($ass) ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                </td>
                                <td class="py-3 px-4 font-black text-emerald-400 whitespace-nowrap">
                                    <?= formatar_moeda($i['valor_total']) ?>
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded uppercase font-bold text-[10px] bg-slate-800 border border-slate-700 text-slate-300">
                                        <?= htmlspecialchars($i['forma_pagamento']) ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <a href="../ticket.php?codigo=<?= urlencode($i['codigo']) ?>" target="_blank" class="p-2 rounded-lg bg-cinema-800 hover:bg-red-600 text-slate-200 hover:text-white transition-all inline-block" title="Visualizar Ticket">
                                        <i class="fa-solid fa-qrcode"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
