<?php
/**
 * =====================================================================
 * PAINEL ADMINISTRATIVO: DASHBOARD DO GERENTE
 * Métricas em tempo real, resumo de faturamento e atalhos rápidos
 * =====================================================================
 */

$ehAdminArea = true;
require_once __DIR__ . '/../bd.php';
require_once __DIR__ . '/../includes/auth.php';

// Proteção da área: apenas gerentes podem acessar
exigir_gerente();

$paginaTitulo = 'Painel do Gerente - CinePimenta';

// 1. Estatísticas do Dashboard
$totalFilmes = $pdo->query("SELECT COUNT(*) FROM filmes WHERE status != 'encerrado'")->fetchColumn();
$totalSessoes = $pdo->query("SELECT COUNT(*) FROM sessoes WHERE status = 'ativa'")->fetchColumn();
$totalIngressos = $pdo->query("SELECT COUNT(*) FROM ingressos WHERE status_pagamento = 'aprovado'")->fetchColumn();
$faturamentoTotal = $pdo->query("SELECT SUM(valor_total) FROM ingressos WHERE status_pagamento = 'aprovado'")->fetchColumn() ?: 0.00;

// 2. Vendas Recentes
$stmtVendas = $pdo->query("
    SELECT i.*, f.titulo AS filme_titulo, s.data AS sessao_data, s.horario AS sessao_horario, sa.nome AS sala_nome
    FROM ingressos i
    JOIN sessoes s ON i.sessao_id = s.id
    JOIN filmes f ON s.filme_id = f.id
    JOIN salas sa ON s.sala_id = sa.id
    ORDER BY i.criado_em DESC
    LIMIT 6
");
$vendasRecentes = $stmtVendas->fetchAll();

// 3. Últimos Logs de Auditoria
$stmtLogs = $pdo->query("
    SELECT * FROM auditoria_logs 
    ORDER BY criado_em DESC 
    LIMIT 6
");
$logsRecentes = $stmtLogs->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
    
    <!-- BARRA SUPERIOR DO ADMIN -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-6 border-b border-slate-800">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-red-600/20 text-red-400 text-xs font-bold uppercase mb-1">
                <i class="fa-solid fa-shield-halved"></i> Área Restrita do Gerente
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">
                Painel Administrativo do Cinema
            </h1>
            <p class="text-xs sm:text-sm text-slate-400">
                Gerencie catálogo de filmes, salas, sessões, preços dos ingressos e consulte o histórico de auditoria.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="../index.php" target="_blank" class="px-4 py-2 rounded-xl bg-cinema-800 hover:bg-slate-700 text-slate-200 text-xs font-bold transition-all border border-slate-700 flex items-center gap-2">
                <i class="fa-solid fa-arrow-up-right-from-square"></i> Ver Site do Cliente
            </a>
            <a href="logs.php" class="px-4 py-2 rounded-xl bg-purple-600/20 hover:bg-purple-600 text-purple-300 hover:text-white border border-purple-500/40 text-xs font-bold transition-all flex items-center gap-2">
                <i class="fa-solid fa-clock-rotate-left"></i> Histórico & Auditoria
            </a>
        </div>
    </div>

    <!-- ABAS DE NAVEGAÇÃO DA GERÊNCIA -->
    <div class="flex items-center gap-2 overflow-x-auto pb-2 border-b border-slate-800/80">
        <a href="index.php" class="px-4 py-2 rounded-xl bg-red-600 text-white font-bold text-xs flex items-center gap-2 shadow-md">
            <i class="fa-solid fa-chart-line"></i> Visão Geral
        </a>
        <a href="filmes.php" class="px-4 py-2 rounded-xl bg-cinema-800 hover:bg-slate-700 text-slate-300 hover:text-white font-medium text-xs flex items-center gap-2 transition-all">
            <i class="fa-solid fa-clapperboard text-red-400"></i> Filmes
        </a>
        <a href="sessoes.php" class="px-4 py-2 rounded-xl bg-cinema-800 hover:bg-slate-700 text-slate-300 hover:text-white font-medium text-xs flex items-center gap-2 transition-all">
            <i class="fa-solid fa-clock text-amber-400"></i> Sessões & Preços
        </a>
        <a href="salas.php" class="px-4 py-2 rounded-xl bg-cinema-800 hover:bg-slate-700 text-slate-300 hover:text-white font-medium text-xs flex items-center gap-2 transition-all">
            <i class="fa-solid fa-door-open text-cyan-400"></i> Salas
        </a>
        <a href="ingressos.php" class="px-4 py-2 rounded-xl bg-cinema-800 hover:bg-slate-700 text-slate-300 hover:text-white font-medium text-xs flex items-center gap-2 transition-all">
            <i class="fa-solid fa-ticket text-emerald-400"></i> Ingressos Vendidos
        </a>
        <a href="logs.php" class="px-4 py-2 rounded-xl bg-cinema-800 hover:bg-slate-700 text-slate-300 hover:text-white font-medium text-xs flex items-center gap-2 transition-all">
            <i class="fa-solid fa-clock-rotate-left text-purple-400"></i> Auditoria
        </a>
    </div>

    <!-- CARDS DE MÉTRICAS -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        
        <div class="bg-cinema-900 border border-slate-800 rounded-3xl p-5 shadow-xl flex items-center justify-between">
            <div>
                <span class="text-xs text-slate-400 font-bold uppercase tracking-wider block">Filmes Ativos</span>
                <span class="text-3xl font-black text-white mt-1 block"><?= $totalFilmes ?></span>
                <a href="filmes.php" class="text-[11px] text-red-400 hover:underline mt-1 inline-block">Gerenciar filmes &rarr;</a>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-red-600/20 text-red-400 flex items-center justify-center text-xl">
                <i class="fa-solid fa-film"></i>
            </div>
        </div>

        <div class="bg-cinema-900 border border-slate-800 rounded-3xl p-5 shadow-xl flex items-center justify-between">
            <div>
                <span class="text-xs text-slate-400 font-bold uppercase tracking-wider block">Sessões Programadas</span>
                <span class="text-3xl font-black text-white mt-1 block"><?= $totalSessoes ?></span>
                <a href="sessoes.php" class="text-[11px] text-amber-400 hover:underline mt-1 inline-block">Ajustar horários &rarr;</a>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-600/20 text-amber-400 flex items-center justify-center text-xl">
                <i class="fa-solid fa-clock"></i>
            </div>
        </div>

        <div class="bg-cinema-900 border border-slate-800 rounded-3xl p-5 shadow-xl flex items-center justify-between">
            <div>
                <span class="text-xs text-slate-400 font-bold uppercase tracking-wider block">Ingressos Vendidos</span>
                <span class="text-3xl font-black text-white mt-1 block"><?= $totalIngressos ?></span>
                <a href="ingressos.php" class="text-[11px] text-emerald-400 hover:underline mt-1 inline-block">Ver bilheteria &rarr;</a>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-600/20 text-emerald-400 flex items-center justify-center text-xl">
                <i class="fa-solid fa-ticket"></i>
            </div>
        </div>

        <div class="bg-cinema-900 border border-slate-800 rounded-3xl p-5 shadow-xl flex items-center justify-between">
            <div>
                <span class="text-xs text-slate-400 font-bold uppercase tracking-wider block">Faturamento Total</span>
                <span class="text-2xl sm:text-3xl font-black text-emerald-400 mt-1 block leading-tight"><?= formatar_moeda($faturamentoTotal) ?></span>
                <span class="text-[10px] text-slate-500 uppercase tracking-wider mt-1 block">Receita confirmada</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-600/20 text-emerald-400 flex items-center justify-center text-xl">
                <i class="fa-solid fa-dollar-sign"></i>
            </div>
        </div>

    </div>

    <!-- SEÇÃO: VENDAS RECENTES & AUDITORIA RECENTE -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- Vendas Recentes (7 Colunas) -->
        <div class="lg:col-span-7 bg-cinema-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    <i class="fa-solid fa-receipt text-red-500"></i> Últimas Vendas de Ingressos
                </h3>
                <a href="ingressos.php" class="text-xs text-red-400 hover:text-red-300 font-bold">Ver todos &rarr;</a>
            </div>

            <?php if (empty($vendasRecentes)): ?>
                <p class="text-xs text-slate-500 text-center py-6">Nenhum ingresso vendido ainda.</p>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="text-slate-400 border-b border-slate-800">
                                <th class="py-2.5">Código</th>
                                <th class="py-2.5">Filme & Sala</th>
                                <th class="py-2.5">Poltronas</th>
                                <th class="py-2.5">Total</th>
                                <th class="py-2.5 text-right">Ação</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60">
                            <?php foreach ($vendasRecentes as $venda): ?>
                                <tr class="hover:bg-cinema-800/40">
                                    <td class="py-3 font-mono font-bold text-amber-400">
                                        <?= htmlspecialchars($venda['codigo']) ?>
                                    </td>
                                    <td class="py-3">
                                        <strong class="text-white block"><?= htmlspecialchars($venda['filme_titulo']) ?></strong>
                                        <span class="text-slate-400 text-[10px]"><?= htmlspecialchars($venda['sala_nome']) ?></span>
                                    </td>
                                    <td class="py-3">
                                        <span class="px-2 py-0.5 rounded bg-red-600/20 text-red-300 font-bold">
                                            <?= htmlspecialchars($venda['assentos_selecionados']) ?>
                                        </span>
                                    </td>
                                    <td class="py-3 font-bold text-emerald-400">
                                        <?= formatar_moeda($venda['valor_total']) ?>
                                    </td>
                                    <td class="py-3 text-right">
                                        <a href="../ticket.php?codigo=<?= urlencode($venda['codigo']) ?>" target="_blank" class="text-xs text-slate-300 hover:text-white p-1.5 rounded-lg bg-cinema-800 hover:bg-slate-700">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Logs de Auditoria Recentes (5 Colunas) -->
        <div class="lg:col-span-5 bg-cinema-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left text-purple-400"></i> Histórico de Auditoria
                </h3>
                <a href="logs.php" class="text-xs text-purple-400 hover:text-purple-300 font-bold">Ver todos &rarr;</a>
            </div>

            <?php if (empty($logsRecentes)): ?>
                <p class="text-xs text-slate-500 text-center py-6">Nenhum registro de log encontrado.</p>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($logsRecentes as $log): ?>
                        <div class="p-3 bg-cinema-800/60 rounded-xl border border-slate-700/60 text-xs space-y-1">
                            <div class="flex items-center justify-between">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-purple-900/40 text-purple-300 border border-purple-700/40">
                                    <?= htmlspecialchars($log['acao']) ?>
                                </span>
                                <span class="text-[10px] text-slate-400">
                                    <?= date('d/m H:i', strtotime($log['criado_em'])) ?>
                                </span>
                            </div>
                            <p class="text-slate-300 line-clamp-2 leading-relaxed">
                                <?= htmlspecialchars($log['detalhes']) ?>
                            </p>
                            <div class="text-[10px] text-slate-500">
                                Por: <strong class="text-slate-400"><?= htmlspecialchars($log['usuario_nome']) ?></strong> (<?= htmlspecialchars($log['usuario_tipo']) ?>)
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
