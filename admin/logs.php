<?php
/**
 * =====================================================================
 * PÁGINA DE HISTÓRICOS & AUDITORIA COMPLETA (REQUISITO CORE)
 * "O sistema deve ter uma página de históricos e manter sempre os logs 
 * de qualquer alteração feita por qualquer usuário, para auditorias futuras."
 * =====================================================================
 */

$ehAdminArea = true;
require_once __DIR__ . '/../bd.php';
require_once __DIR__ . '/../includes/auth.php';

exigir_gerente();

$paginaTitulo = 'Histórico e Auditoria do Sistema - CinePimenta';

// Filtros
$acaoFiltro = trim($_GET['acao'] ?? '');
$buscaFiltro = trim($_GET['busca'] ?? '');
$tabelaFiltro = trim($_GET['tabela'] ?? '');

$sql = "SELECT * FROM auditoria_logs WHERE 1=1";
$params = [];

if (!empty($acaoFiltro)) {
    $sql .= " AND acao = ?";
    $params[] = $acaoFiltro;
}

if (!empty($tabelaFiltro)) {
    $sql .= " AND tabela_afetada = ?";
    $params[] = $tabelaFiltro;
}

if (!empty($buscaFiltro)) {
    $sql .= " AND (detalhes LIKE ? OR usuario_nome LIKE ? OR ip_origem LIKE ?)";
    $params[] = "%{$buscaFiltro}%";
    $params[] = "%{$buscaFiltro}%";
    $params[] = "%{$buscaFiltro}%";
}

$sql .= " ORDER BY criado_em DESC LIMIT 150";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll();

// Métricas de auditoria
$totalLogs = $pdo->query("SELECT COUNT(*) FROM auditoria_logs")->fetchColumn();
$totalCompras = $pdo->query("SELECT COUNT(*) FROM auditoria_logs WHERE acao LIKE '%INGRESSO%' OR acao LIKE '%COMPRA%'")->fetchColumn();
$totalAlteracoes = $pdo->query("SELECT COUNT(*) FROM auditoria_logs WHERE acao LIKE '%ALTERAR%' OR acao LIKE '%PRECO%'")->fetchColumn();
$totalExclusoes = $pdo->query("SELECT COUNT(*) FROM auditoria_logs WHERE acao LIKE '%EXCLUIR%'")->fetchColumn();

// Lista de ações distintas para o dropdown de filtro
$acoesDistintas = $pdo->query("SELECT DISTINCT acao FROM auditoria_logs ORDER BY acao ASC")->fetchAll(PDO::FETCH_COLUMN);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    
    <!-- CABEÇALHO -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-800">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="index.php" class="hover:text-white">Painel</a>
                <span>&bull;</span>
                <span class="text-purple-400 font-bold">Histórico & Auditoria</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight flex items-center gap-3">
                <i class="fa-solid fa-clock-rotate-left text-purple-400"></i> Auditoria & Histórico de Alterações
            </h1>
            <p class="text-xs sm:text-sm text-slate-400">
                Registro imutável de todas as ações de usuários, alterações de preços, compras e cadastros.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" onclick="window.print()" class="px-4 py-2 rounded-xl bg-cinema-800 hover:bg-slate-700 text-slate-200 text-xs font-bold transition-all border border-slate-700 flex items-center gap-2">
                <i class="fa-solid fa-print"></i> Imprimir Relatório
            </button>
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
        <a href="ingressos.php" class="px-4 py-2 rounded-xl bg-cinema-800 hover:bg-slate-700 text-slate-300 font-medium text-xs flex items-center gap-2">
            <i class="fa-solid fa-ticket text-emerald-400"></i> Ingressos
        </a>
        <a href="logs.php" class="px-4 py-2 rounded-xl bg-red-600 text-white font-bold text-xs flex items-center gap-2 shadow-md">
            <i class="fa-solid fa-clock-rotate-left"></i> Auditoria
        </a>
    </div>

    <!-- CARDS DE RESUMO DE LOGS -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-cinema-900 border border-slate-800 rounded-2xl p-4 shadow">
            <span class="text-[10px] uppercase font-bold text-slate-400 block">Total de Eventos</span>
            <span class="text-2xl font-black text-white mt-1 block"><?= $totalLogs ?></span>
        </div>
        <div class="bg-cinema-900 border border-slate-800 rounded-2xl p-4 shadow">
            <span class="text-[10px] uppercase font-bold text-emerald-400 block">Ingressos Vendidos</span>
            <span class="text-2xl font-black text-emerald-400 mt-1 block"><?= $totalCompras ?></span>
        </div>
        <div class="bg-cinema-900 border border-slate-800 rounded-2xl p-4 shadow">
            <span class="text-[10px] uppercase font-bold text-amber-400 block">Alterações de Preço/Dados</span>
            <span class="text-2xl font-black text-amber-400 mt-1 block"><?= $totalAlteracoes ?></span>
        </div>
        <div class="bg-cinema-900 border border-slate-800 rounded-2xl p-4 shadow">
            <span class="text-[10px] uppercase font-bold text-rose-400 block">Exclusões / Cancelamentos</span>
            <span class="text-2xl font-black text-rose-400 mt-1 block"><?= $totalExclusoes ?></span>
        </div>
    </div>

    <!-- FORMULÁRIO DE FILTROS -->
    <div class="bg-cinema-900 border border-slate-800 rounded-2xl p-4 shadow-xl">
        <form action="logs.php" method="GET" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
            
            <div class="sm:col-span-5">
                <label class="block text-[11px] font-bold text-slate-400 uppercase mb-1">Buscar por Palavra-Chave ou Usuário</label>
                <input type="text" name="busca" value="<?= htmlspecialchars($buscaFiltro) ?>" placeholder="Ex: Maria Silva, Preço, TCK-2026..." 
                       class="w-full bg-cinema-800 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-purple-500">
            </div>

            <div class="sm:col-span-3">
                <label class="block text-[11px] font-bold text-slate-400 uppercase mb-1">Filtrar por Ação</label>
                <select name="acao" class="w-full bg-cinema-800 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-purple-500">
                    <option value="">Todas as Ações</option>
                    <?php foreach ($acoesDistintas as $ac): ?>
                        <option value="<?= htmlspecialchars($ac) ?>" <?= $acaoFiltro === $ac ? 'selected' : '' ?>>
                            <?= htmlspecialchars($ac) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="sm:col-span-2">
                <label class="block text-[11px] font-bold text-slate-400 uppercase mb-1">Tabela Afetada</label>
                <select name="tabela" class="w-full bg-cinema-800 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-purple-500">
                    <option value="">Todas</option>
                    <option value="filmes" <?= $tabelaFiltro === 'filmes' ? 'selected' : '' ?>>filmes</option>
                    <option value="sessoes" <?= $tabelaFiltro === 'sessoes' ? 'selected' : '' ?>>sessoes</option>
                    <option value="salas" <?= $tabelaFiltro === 'salas' ? 'selected' : '' ?>>salas</option>
                    <option value="ingressos" <?= $tabelaFiltro === 'ingressos' ? 'selected' : '' ?>>ingressos</option>
                    <option value="usuarios" <?= $tabelaFiltro === 'usuarios' ? 'selected' : '' ?>>usuarios</option>
                </select>
            </div>

            <div class="sm:col-span-2 flex gap-2">
                <button type="submit" class="flex-1 py-2 px-3 bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs rounded-xl transition-all shadow">
                    Filtrar
                </button>
                <?php if (!empty($acaoFiltro) || !empty($buscaFiltro) || !empty($tabelaFiltro)): ?>
                    <a href="logs.php" class="p-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-xs flex items-center justify-center" title="Limpar Filtros">
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                <?php endif; ?>
            </div>

        </form>
    </div>

    <!-- TABELA COMPLETA DE AUDITORIA -->
    <div class="bg-cinema-900 border border-slate-800 rounded-3xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-cinema-950 border-b border-slate-800 text-slate-400 font-bold uppercase tracking-wider text-[10px]">
                        <th class="py-3 px-4">Data & Hora</th>
                        <th class="py-3 px-4">Usuário</th>
                        <th class="py-3 px-4">Ação</th>
                        <th class="py-3 px-4">Tabela</th>
                        <th class="py-3 px-4">Detalhes da Alteração</th>
                        <th class="py-3 px-4">IP Origem</th>
                        <th class="py-3 px-4 text-right">Ação</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-8 text-slate-500">Nenhum log encontrado para os critérios selecionados.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $l): 
                            $acao = $l['acao'];
                            $badgeColor = 'bg-slate-800 text-slate-300 border-slate-700';

                            if (strpos($acao, 'COMPRA') !== false || strpos($acao, 'CADASTRAR') !== false) {
                                $badgeColor = 'bg-emerald-950/60 text-emerald-400 border-emerald-700/50';
                            } elseif (strpos($acao, 'ALTERAR') !== false || strpos($acao, 'PRECO') !== false) {
                                $badgeColor = 'bg-amber-950/60 text-amber-400 border-amber-700/50';
                            } elseif (strpos($acao, 'EXCLUIR') !== false || strpos($acao, 'CANCELAR') !== false) {
                                $badgeColor = 'bg-rose-950/60 text-rose-400 border-rose-700/50';
                            } elseif (strpos($acao, 'LOGIN') !== false) {
                                $badgeColor = 'bg-blue-950/60 text-blue-400 border-blue-700/50';
                            }
                        ?>
                            <tr class="hover:bg-cinema-800/40 transition-colors">
                                <td class="py-3 px-4 font-mono text-slate-300 whitespace-nowrap">
                                    <i class="fa-regular fa-clock text-slate-500 mr-1"></i>
                                    <?= date('d/m/Y H:i:s', strtotime($l['criado_em'])) ?>
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <strong class="text-white block"><?= htmlspecialchars($l['usuario_nome']) ?></strong>
                                    <span class="text-[10px] text-slate-500 uppercase"><?= htmlspecialchars($l['usuario_tipo']) ?></span>
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded border text-[10px] font-bold uppercase tracking-wider <?= $badgeColor ?>">
                                        <?= htmlspecialchars($l['acao']) ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4 font-mono text-slate-400 text-[11px]">
                                    <?= htmlspecialchars($l['tabela_afetada']) ?>
                                    <?php if ($l['registro_id']): ?>
                                        <span class="text-slate-500">#<?= $l['registro_id'] ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-4 max-w-md text-slate-300 leading-relaxed">
                                    <?= htmlspecialchars($l['detalhes']) ?>
                                </td>
                                <td class="py-3 px-4 font-mono text-[10px] text-slate-500">
                                    <?= htmlspecialchars($l['ip_origem'] ?: '127.0.0.1') ?>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <button type="button" 
                                            onclick="verDetalhesLog(<?= htmlspecialchars(json_encode($l)) ?>)" 
                                            class="p-1.5 rounded-lg bg-cinema-800 hover:bg-slate-700 text-slate-300 hover:text-white" title="Ver Detalhes">
                                        <i class="fa-solid fa-circle-info"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
function verDetalhesLog(log) {
    const conteudo = `
        <div class="space-y-3 text-xs text-left">
            <div><strong class="text-white">ID do Registro:</strong> #${log.id}</div>
            <div><strong class="text-white">Data e Hora:</strong> ${log.criado_em}</div>
            <div><strong class="text-white">Usuário Responsável:</strong> ${log.usuario_nome} (${log.usuario_tipo})</div>
            <div><strong class="text-white">Ação:</strong> <span class="px-2 py-0.5 rounded bg-purple-900/40 text-purple-300 font-bold font-mono">${log.acao}</span></div>
            <div><strong class="text-white">Tabela / Registro:</strong> ${log.tabela_afetada} (ID: ${log.registro_id || 'N/A'})</div>
            <div><strong class="text-white">IP de Origem:</strong> ${log.ip_origem || '127.0.0.1'}</div>
            <div class="p-3 bg-cinema-950 border border-slate-800 rounded-xl mt-2">
                <strong class="text-amber-400 block mb-1">Descrição Completa da Operação:</strong>
                <p class="text-slate-200 leading-relaxed">${log.detalhes}</p>
            </div>
        </div>
    `;

    showModalAlert('Detalhes do Registro de Auditoria', conteudo, 'info');
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
