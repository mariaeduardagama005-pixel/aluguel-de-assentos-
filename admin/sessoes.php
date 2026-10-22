<?php
/**
 * =====================================================================
 * GESTÃO DE SESSÕES & PREÇO DOS INGRESSOS (ÁREA DO GERENTE)
 * "Ele consegue cadastrar e alterar filmes, sessões, salas e preço dos ingressos."
 * =====================================================================
 */

$ehAdminArea = true;
require_once __DIR__ . '/../bd.php';
require_once __DIR__ . '/../includes/auth.php';

exigir_gerente();

$paginaTitulo = 'Gerenciar Sessões e Preços - CinePimenta';

// Busca lista de filmes e salas ativas para os selects do formulário
$filmesLista = $pdo->query("SELECT id, titulo FROM filmes WHERE status != 'encerrado' ORDER BY titulo ASC")->fetchAll();
$salasLista = $pdo->query("SELECT id, nome, capacidade FROM salas WHERE status = 'ativa' ORDER BY id ASC")->fetchAll();

// Busca todas as sessões com dados de filmes e salas
$sessoes = $pdo->query("
    SELECT s.*, 
           f.titulo AS filme_titulo, f.cartaz_url,
           sa.nome AS sala_nome, sa.capacidade AS sala_capacidade,
           (SELECT COUNT(*) FROM assentos_ocupados ao WHERE ao.sessao_id = s.id) AS assentos_ocupados
    FROM sessoes s
    JOIN filmes f ON s.filme_id = f.id
    JOIN salas sa ON s.sala_id = sa.id
    ORDER BY s.data DESC, s.horario DESC
")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    
    <!-- TOPO & BOTÃO NOVA SESSÃO -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-800">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="index.php" class="hover:text-white">Painel</a>
                <span>&bull;</span>
                <span class="text-amber-400 font-bold">Sessões & Preços</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight flex items-center gap-3">
                <i class="fa-solid fa-clock text-amber-400"></i> Sessões & Preço dos Ingressos
            </h1>
            <p class="text-xs sm:text-sm text-slate-400">
                Agende horários, associe filmes a salas e configure o valor de bilheteria em tempo real.
            </p>
        </div>

        <button type="button" onclick="abrirModalSessao()" class="px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-black text-xs uppercase tracking-wider transition-all shadow-lg shadow-amber-950/50 flex items-center gap-2 self-start sm:self-auto">
            <i class="fa-solid fa-plus"></i>
            <span>Nova Sessão / Horário</span>
        </button>
    </div>

    <!-- NAVEGAÇÃO DE ABAS -->
    <div class="flex items-center gap-2 overflow-x-auto pb-2 border-b border-slate-800/80">
        <a href="index.php" class="px-4 py-2 rounded-xl bg-cinema-800 hover:bg-slate-700 text-slate-300 font-medium text-xs flex items-center gap-2">
            <i class="fa-solid fa-chart-line"></i> Visão Geral
        </a>
        <a href="filmes.php" class="px-4 py-2 rounded-xl bg-cinema-800 hover:bg-slate-700 text-slate-300 font-medium text-xs flex items-center gap-2">
            <i class="fa-solid fa-clapperboard text-red-400"></i> Filmes
        </a>
        <a href="sessoes.php" class="px-4 py-2 rounded-xl bg-red-600 text-white font-bold text-xs flex items-center gap-2 shadow-md">
            <i class="fa-solid fa-clock"></i> Sessões & Preços
        </a>
        <a href="salas.php" class="px-4 py-2 rounded-xl bg-cinema-800 hover:bg-slate-700 text-slate-300 font-medium text-xs flex items-center gap-2">
            <i class="fa-solid fa-door-open text-cyan-400"></i> Salas
        </a>
        <a href="ingressos.php" class="px-4 py-2 rounded-xl bg-cinema-800 hover:bg-slate-700 text-slate-300 font-medium text-xs flex items-center gap-2">
            <i class="fa-solid fa-ticket text-emerald-400"></i> Ingressos
        </a>
        <a href="logs.php" class="px-4 py-2 rounded-xl bg-cinema-800 hover:bg-slate-700 text-slate-300 font-medium text-xs flex items-center gap-2">
            <i class="fa-solid fa-clock-rotate-left text-purple-400"></i> Auditoria
        </a>
    </div>

    <!-- TABELA DE SESSÕES -->
    <div class="bg-cinema-900 border border-slate-800 rounded-3xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead>
                    <tr class="bg-cinema-950 border-b border-slate-800 text-slate-400 font-bold uppercase tracking-wider text-[11px]">
                        <th class="py-3 px-4">Filme</th>
                        <th class="py-3 px-4">Sala</th>
                        <th class="py-3 px-4">Data & Horário</th>
                        <th class="py-3 px-4">Formato / Idioma</th>
                        <th class="py-3 px-4">Preço do Ingresso</th>
                        <th class="py-3 px-4">Ocupação</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    <?php if (empty($sessoes)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-8 text-slate-500">Nenhuma sessão programada.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($sessoes as $s): ?>
                            <tr class="hover:bg-cinema-800/40 transition-colors">
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-2.5">
                                        <img src="<?= htmlspecialchars($s['cartaz_url']) ?>" alt="Capa" class="w-8 h-11 object-cover rounded shadow shrink-0">
                                        <strong class="text-white font-bold leading-tight"><?= htmlspecialchars($s['filme_titulo']) ?></strong>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-slate-300">
                                    <?= htmlspecialchars($s['sala_nome']) ?>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="text-white font-semibold"><?= formatar_data($s['data']) ?></div>
                                    <span class="font-mono text-amber-400 font-black text-xs"><?= formatar_hora($s['horario']) ?></span>
                                </td>
                                <td class="py-3.5 px-4 text-xs">
                                    <span class="px-2 py-0.5 rounded bg-slate-800 border border-slate-700 text-slate-200 font-semibold mr-1">
                                        <?= htmlspecialchars($s['tipo_sessao']) ?>
                                    </span>
                                    <span class="text-slate-400"><?= htmlspecialchars($s['idioma']) ?></span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="font-black text-emerald-400 text-sm">
                                        <?= formatar_moeda($s['preco_ingresso']) ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-xs">
                                    <span class="text-slate-300 font-semibold"><?= $s['assentos_ocupados'] ?> / <?= $s['sala_capacidade'] ?></span>
                                    <div class="w-20 bg-slate-800 rounded-full h-1.5 mt-1 overflow-hidden">
                                        <?php $perc = $s['sala_capacidade'] > 0 ? min(100, round(($s['assentos_ocupados'] / $s['sala_capacidade']) * 100)) : 0; ?>
                                        <div class="bg-red-500 h-1.5 rounded-full" style="width: <?= $perc ?>%"></div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase <?= $s['status'] === 'ativa' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-rose-500/20 text-rose-400' ?>">
                                        <?= htmlspecialchars($s['status']) ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right space-x-1">
                                    <button type="button" onclick="editarSessao(<?= htmlspecialchars(json_encode($s)) ?>)" class="p-2 rounded-lg bg-cinema-800 hover:bg-slate-700 text-slate-300 hover:text-white transition-colors" title="Editar Preço e Dados">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <button type="button" onclick="confirmarExclusaoSessao(<?= $s['id'] ?>, '<?= htmlspecialchars(addslashes($s['filme_titulo'])) ?>', '<?= formatar_hora($s['horario']) ?>')" class="p-2 rounded-lg bg-rose-950/40 hover:bg-rose-900 border border-rose-800/40 text-rose-400 hover:text-white transition-colors" title="Cancelar Sessão">
                                        <i class="fa-solid fa-trash"></i>
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

<!-- MODAL CADASTRAR / EDITAR SESSÃO & PREÇO -->
<div id="modal-form-sessao" class="fixed inset-0 z-50 hidden bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-cinema-900 border border-slate-700 rounded-3xl max-w-xl w-full p-6 sm:p-8 shadow-2xl relative">
        <div class="flex items-center justify-between pb-4 mb-6 border-b border-slate-800">
            <h3 id="modal-sessao-titulo" class="text-lg font-bold text-white">Cadastrar Sessão & Preço</h3>
            <button type="button" onclick="fecharModalSessao()" class="text-slate-400 hover:text-white p-1">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form action="../actions/salvar_sessao.php" method="POST" class="space-y-4">
            <input type="hidden" name="id" id="sessao-id" value="">

            <div>
                <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Filme *</label>
                <select name="filme_id" id="sessao-filme-id" required class="w-full bg-cinema-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-amber-500">
                    <option value="">Selecione um filme...</option>
                    <?php foreach ($filmesLista as $flm): ?>
                        <option value="<?= $flm['id'] ?>"><?= htmlspecialchars($flm['titulo']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Sala de Exibição *</label>
                <select name="sala_id" id="sessao-sala-id" required class="w-full bg-cinema-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-amber-500">
                    <option value="">Selecione uma sala...</option>
                    <?php foreach ($salasLista as $sl): ?>
                        <option value="<?= $sl['id'] ?>"><?= htmlspecialchars($sl['nome']) ?> (<?= $sl['capacidade'] ?> assentos)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Data da Sessão *</label>
                    <input type="date" name="data" id="sessao-data" required class="w-full bg-cinema-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-amber-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Horário de Início *</label>
                    <input type="time" name="horario" id="sessao-horario" required class="w-full bg-cinema-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-amber-500">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Idioma do Áudio *</label>
                    <select name="idioma" id="sessao-idioma" required class="w-full bg-cinema-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-amber-500">
                        <option value="Dublado">Dublado</option>
                        <option value="Legendado">Legendado</option>
                        <option value="Nacional">Nacional</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Formato / Projeção *</label>
                    <input type="text" name="tipo_sessao" id="sessao-tipo" required placeholder="Ex: IMAX 3D / 2D Standard" class="w-full bg-cinema-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-amber-500">
                </div>
            </div>

            <!-- REGRA DE NEGÓCIO: ALTERAÇÃO DE PREÇO DOS INGRESSOS PELO GERENTE -->
            <div class="p-4 bg-cinema-800/80 rounded-2xl border border-amber-500/40 space-y-2">
                <label class="block text-xs font-bold text-amber-300 uppercase tracking-wider">
                    <i class="fa-solid fa-tag mr-1"></i> Preço do Ingresso (R$) *
                </label>
                <div class="relative">
                    <span class="absolute left-4 top-2.5 text-slate-400 font-bold text-sm">R$</span>
                    <input type="number" step="0.50" min="5" max="200" name="preco_ingresso" id="sessao-preco" required placeholder="35.00" class="w-full bg-cinema-900 border border-slate-700 rounded-xl pl-12 pr-4 py-2.5 text-base font-black text-emerald-400 focus:outline-none focus:border-amber-500">
                </div>
                <p class="text-[10px] text-slate-400">Qualquer alteração de preço é salva imediatamente e registrada nos logs de auditoria.</p>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Status da Sessão *</label>
                <select name="status" id="sessao-status" class="w-full bg-cinema-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-amber-500">
                    <option value="ativa">Ativa (Aberta para compra de ingressos)</option>
                    <option value="cancelada">Cancelada</option>
                    <option value="encerrada">Encerrada</option>
                </select>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
                <button type="button" onclick="fecharModalSessao()" class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold">
                    Cancelar
                </button>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-black text-xs uppercase tracking-wider shadow-lg">
                    Salvar Sessão & Preço
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function abrirModalSessao() {
    document.getElementById('modal-sessao-titulo').innerText = 'Cadastrar Nova Sessão & Preço';
    document.getElementById('sessao-id').value = '';
    document.getElementById('sessao-filme-id').value = '';
    document.getElementById('sessao-sala-id').value = '';
    
    // Data de hoje como padrão
    const hoje = new Date().toISOString().split('T')[0];
    document.getElementById('sessao-data').value = hoje;
    document.getElementById('sessao-horario').value = '19:00';
    document.getElementById('sessao-idioma').value = 'Dublado';
    document.getElementById('sessao-tipo').value = '2D';
    document.getElementById('sessao-preco').value = '35.00';
    document.getElementById('sessao-status').value = 'ativa';
    document.getElementById('modal-form-sessao').classList.remove('hidden');
}

function editarSessao(s) {
    document.getElementById('modal-sessao-titulo').innerText = 'Editar Sessão #' + s.id;
    document.getElementById('sessao-id').value = s.id;
    document.getElementById('sessao-filme-id').value = s.filme_id;
    document.getElementById('sessao-sala-id').value = s.sala_id;
    document.getElementById('sessao-data').value = s.data;
    document.getElementById('sessao-horario').value = s.horario.substring(0, 5);
    document.getElementById('sessao-idioma').value = s.idioma;
    document.getElementById('sessao-tipo').value = s.tipo_sessao;
    document.getElementById('sessao-preco').value = parseFloat(s.preco_ingresso).toFixed(2);
    document.getElementById('sessao-status').value = s.status;
    document.getElementById('modal-form-sessao').classList.remove('hidden');
}

function fecharModalSessao() {
    document.getElementById('modal-form-sessao').classList.add('hidden');
}

function confirmarExclusaoSessao(id, filmeTitulo, horario) {
    showModalConfirm(
        'Cancelar / Excluir Sessão',
        `Deseja realmente excluir a sessão de <strong>"${filmeTitulo}"</strong> às <strong>${horario}</strong>?`,
        function() {
            window.location.href = `../actions/excluir_sessao.php?id=${id}`;
        },
        null,
        true,
        'Sim, Excluir Sessão'
    );
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
