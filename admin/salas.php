<?php
/**
 * =====================================================================
 * GESTÃO DE SALAS (ÁREA DO GERENTE)
 * Cadastrar, alterar e configurar dimensões das salas de cinema
 * =====================================================================
 */

$ehAdminArea = true;
require_once __DIR__ . '/../bd.php';
require_once __DIR__ . '/../includes/auth.php';

exigir_gerente();

$paginaTitulo = 'Gerenciar Salas - CinePimenta';

$salas = $pdo->query("SELECT * FROM salas ORDER BY id ASC")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    
    <!-- TOPO & BOTÃO NOVA SALA -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-800">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="index.php" class="hover:text-white">Painel</a>
                <span>&bull;</span>
                <span class="text-cyan-400 font-bold">Salas</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight flex items-center gap-3">
                <i class="fa-solid fa-door-open text-cyan-400"></i> Gestão de Salas
            </h1>
            <p class="text-xs sm:text-sm text-slate-400">
                Cadastre e personalize salas, tecnologia de projeção, fileiras e quantidade de poltronas.
            </p>
        </div>

        <button type="button" onclick="abrirModalSala()" class="px-5 py-2.5 rounded-xl bg-cyan-600 hover:bg-cyan-700 text-white font-bold text-xs uppercase tracking-wider transition-all shadow-lg shadow-cyan-950/50 flex items-center gap-2 self-start sm:self-auto">
            <i class="fa-solid fa-plus"></i>
            <span>Cadastrar Nova Sala</span>
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
        <a href="sessoes.php" class="px-4 py-2 rounded-xl bg-cinema-800 hover:bg-slate-700 text-slate-300 font-medium text-xs flex items-center gap-2">
            <i class="fa-solid fa-clock text-amber-400"></i> Sessões & Preços
        </a>
        <a href="salas.php" class="px-4 py-2 rounded-xl bg-red-600 text-white font-bold text-xs flex items-center gap-2 shadow-md">
            <i class="fa-solid fa-door-open"></i> Salas
        </a>
        <a href="ingressos.php" class="px-4 py-2 rounded-xl bg-cinema-800 hover:bg-slate-700 text-slate-300 font-medium text-xs flex items-center gap-2">
            <i class="fa-solid fa-ticket text-emerald-400"></i> Ingressos
        </a>
        <a href="logs.php" class="px-4 py-2 rounded-xl bg-cinema-800 hover:bg-slate-700 text-slate-300 font-medium text-xs flex items-center gap-2">
            <i class="fa-solid fa-clock-rotate-left text-purple-400"></i> Auditoria
        </a>
    </div>

    <!-- LISTA DE CARDS DE SALAS -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <?php foreach ($salas as $s): ?>
            <div class="bg-cinema-900 border border-slate-800 rounded-3xl p-6 shadow-xl flex flex-col justify-between space-y-4 hover:border-slate-700 transition-all">
                <div>
                    <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-800">
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase <?= $s['status'] === 'ativa' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-rose-500/20 text-rose-400' ?>">
                            <?= htmlspecialchars($s['status']) ?>
                        </span>
                        <span class="text-xs text-slate-500 font-mono">ID #<?= $s['id'] ?></span>
                    </div>

                    <h3 class="text-lg font-bold text-white mb-1"><?= htmlspecialchars($s['nome']) ?></h3>
                    <p class="text-xs text-cyan-400 font-semibold mb-4"><?= htmlspecialchars($s['tipo']) ?></p>

                    <div class="grid grid-cols-3 gap-2 bg-cinema-800/60 p-3 rounded-2xl border border-slate-700/60 text-center">
                        <div>
                            <span class="text-[10px] uppercase text-slate-400 block font-bold">Fileiras</span>
                            <strong class="text-white text-sm"><?= $s['fileiras'] ?> (A - <?= chr(64 + (int)$s['fileiras']) ?>)</strong>
                        </div>
                        <div>
                            <span class="text-[10px] uppercase text-slate-400 block font-bold">Colunas</span>
                            <strong class="text-white text-sm"><?= $s['colunas'] ?></strong>
                        </div>
                        <div>
                            <span class="text-[10px] uppercase text-slate-400 block font-bold">Capacidade</span>
                            <strong class="text-emerald-400 text-sm"><?= $s['capacidade'] ?> lug.</strong>
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-800 flex items-center justify-end gap-2">
                    <button type="button" onclick="editarSala(<?= htmlspecialchars(json_encode($s)) ?>)" class="px-3 py-1.5 rounded-xl bg-cinema-800 hover:bg-slate-700 text-slate-200 text-xs font-bold transition-all border border-slate-700">
                        <i class="fa-solid fa-pen-to-square mr-1"></i> Editar
                    </button>
                    <button type="button" onclick="confirmarExclusaoSala(<?= $s['id'] ?>, '<?= htmlspecialchars(addslashes($s['nome'])) ?>')" class="px-3 py-1.5 rounded-xl bg-rose-950/40 hover:bg-rose-900 border border-rose-800/40 text-rose-400 hover:text-white text-xs font-bold transition-all">
                        <i class="fa-solid fa-trash mr-1"></i> Excluir
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

</div>

<!-- MODAL CADASTRAR / EDITAR SALA -->
<div id="modal-form-sala" class="fixed inset-0 z-50 hidden bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-cinema-900 border border-slate-700 rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl relative">
        <div class="flex items-center justify-between pb-4 mb-6 border-b border-slate-800">
            <h3 id="modal-sala-titulo" class="text-lg font-bold text-white">Cadastrar Sala</h3>
            <button type="button" onclick="fecharModalSala()" class="text-slate-400 hover:text-white p-1">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form action="../actions/salvar_sala.php" method="POST" class="space-y-4">
            <input type="hidden" name="id" id="sala-id" value="">

            <div>
                <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Nome da Sala *</label>
                <input type="text" name="nome" id="sala-nome" required placeholder="Ex: Sala 4 - ScreenX 4DX" class="w-full bg-cinema-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-cyan-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Tipo de Projeção / Tecnologia *</label>
                <input type="text" name="tipo" id="sala-tipo" required placeholder="Ex: IMAX 3D Laser / Dolby Atmos" class="w-full bg-cinema-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-cyan-500">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Qtd. Fileiras (Linhas) *</label>
                    <input type="number" name="fileiras" id="sala-fileiras" required min="3" max="15" value="6" class="w-full bg-cinema-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-cyan-500">
                    <span class="text-[10px] text-slate-500">Ex: 6 gera fileiras A até F</span>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Qtd. Colunas (Assentos/linha) *</label>
                    <input type="number" name="colunas" id="sala-colunas" required min="4" max="20" value="10" class="w-full bg-cinema-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-cyan-500">
                    <span class="text-[10px] text-slate-500">Ex: 10 gera poltronas de 1 a 10</span>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Status da Sala *</label>
                <select name="status" id="sala-status" class="w-full bg-cinema-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-cyan-500">
                    <option value="ativa">Ativa (Disponível para agendamento de sessões)</option>
                    <option value="manutencao">Em Manutenção (Bloqueada temporariamente)</option>
                </select>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
                <button type="button" onclick="fecharModalSala()" class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold">
                    Cancelar
                </button>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-cyan-600 hover:bg-cyan-700 text-white text-xs font-bold uppercase tracking-wider shadow-lg">
                    Salvar Sala
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function abrirModalSala() {
    document.getElementById('modal-sala-titulo').innerText = 'Cadastrar Nova Sala';
    document.getElementById('sala-id').value = '';
    document.getElementById('sala-nome').value = '';
    document.getElementById('sala-tipo').value = '2D Padrão';
    document.getElementById('sala-fileiras').value = 6;
    document.getElementById('sala-colunas').value = 10;
    document.getElementById('sala-status').value = 'ativa';
    document.getElementById('modal-form-sala').classList.remove('hidden');
}

function editarSala(s) {
    document.getElementById('modal-sala-titulo').innerText = 'Editar Sala: ' + s.nome;
    document.getElementById('sala-id').value = s.id;
    document.getElementById('sala-nome').value = s.nome;
    document.getElementById('sala-tipo').value = s.tipo;
    document.getElementById('sala-fileiras').value = s.fileiras;
    document.getElementById('sala-colunas').value = s.colunas;
    document.getElementById('sala-status').value = s.status;
    document.getElementById('modal-form-sala').classList.remove('hidden');
}

function fecharModalSala() {
    document.getElementById('modal-form-sala').classList.add('hidden');
}

function confirmarExclusaoSala(id, nome) {
    showModalConfirm(
        'Excluir Sala',
        `Deseja realmente remover a <strong>"${nome}"</strong>?<br><span class="text-xs text-rose-400">Aviso: Sessões vinculadas a esta sala também serão afetadas.</span>`,
        function() {
            window.location.href = `../actions/excluir_sala.php?id=${id}`;
        },
        null,
        true,
        'Sim, Excluir Sala'
    );
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
