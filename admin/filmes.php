<?php
/**
 * =====================================================================
 * GESTÃO DE FILMES (ÁREA DO GERENTE)
 * Cadastrar, alterar e excluir filmes em cartaz e em breve
 * =====================================================================
 */

$ehAdminArea = true;
require_once __DIR__ . '/../bd.php';
require_once __DIR__ . '/../includes/auth.php';

exigir_gerente();

$paginaTitulo = 'Gerenciar Filmes - CinePimenta';

$filmes = $pdo->query("SELECT * FROM filmes ORDER BY id DESC")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    
    <!-- TOPO & BOTÃO NOVO FILME -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-800">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="index.php" class="hover:text-white">Painel</a>
                <span>&bull;</span>
                <span class="text-red-400 font-bold">Filmes</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight flex items-center gap-3">
                <i class="fa-solid fa-clapperboard text-red-500"></i> Gestão de Filmes
            </h1>
            <p class="text-xs sm:text-sm text-slate-400">
                Cadastre novos títulos, atualize sinopses, capas e altere status de exibição.
            </p>
        </div>

        <button type="button" onclick="abrirModalFilme()" class="px-5 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold text-xs uppercase tracking-wider transition-all shadow-lg shadow-red-950/50 flex items-center gap-2 self-start sm:self-auto">
            <i class="fa-solid fa-plus"></i>
            <span>Cadastrar Novo Filme</span>
        </button>
    </div>

    <!-- NAVEGAÇÃO RÁPIDA DE ABAS -->
    <div class="flex items-center gap-2 overflow-x-auto pb-2 border-b border-slate-800/80">
        <a href="index.php" class="px-4 py-2 rounded-xl bg-cinema-800 hover:bg-slate-700 text-slate-300 font-medium text-xs flex items-center gap-2">
            <i class="fa-solid fa-chart-line"></i> Visão Geral
        </a>
        <a href="filmes.php" class="px-4 py-2 rounded-xl bg-red-600 text-white font-bold text-xs flex items-center gap-2 shadow-md">
            <i class="fa-solid fa-clapperboard"></i> Filmes
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
        <a href="logs.php" class="px-4 py-2 rounded-xl bg-cinema-800 hover:bg-slate-700 text-slate-300 font-medium text-xs flex items-center gap-2">
            <i class="fa-solid fa-clock-rotate-left text-purple-400"></i> Auditoria
        </a>
    </div>

    <!-- TABELA DE FILMES -->
    <div class="bg-cinema-900 border border-slate-800 rounded-3xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead>
                    <tr class="bg-cinema-950 border-b border-slate-800 text-slate-400 font-bold uppercase tracking-wider text-[11px]">
                        <th class="py-3 px-4">Cartaz & Título</th>
                        <th class="py-3 px-4">Gênero</th>
                        <th class="py-3 px-4">Duração</th>
                        <th class="py-3 px-4">Classificação</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    <?php if (empty($filmes)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-8 text-slate-500">Nenhum filme cadastrado.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($filmes as $f): ?>
                            <tr class="hover:bg-cinema-800/40 transition-colors">
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-3">
                                        <img src="<?= htmlspecialchars($f['cartaz_url']) ?>" alt="Capa" class="w-10 h-14 object-cover rounded-lg border border-slate-700 shrink-0">
                                        <div>
                                            <strong class="text-white block font-bold leading-snug"><?= htmlspecialchars($f['titulo']) ?></strong>
                                            <?php if ($f['titulo_original']): ?>
                                                <span class="text-slate-400 text-[11px] italic"><?= htmlspecialchars($f['titulo_original']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-slate-300">
                                    <?= htmlspecialchars($f['genero']) ?>
                                </td>
                                <td class="py-3 px-4 text-slate-300">
                                    <?= $f['duracao'] ?> min
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded font-black text-[10px] uppercase bg-slate-800 border border-slate-700 text-slate-200">
                                        <?= htmlspecialchars($f['classificacao']) ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <?php if ($f['status'] === 'em_cartaz'): ?>
                                        <span class="px-2.5 py-1 rounded-full bg-emerald-500/20 text-emerald-400 text-[10px] font-bold uppercase">
                                            Em Cartaz
                                        </span>
                                    <?php elseif ($f['status'] === 'em_breve'): ?>
                                        <span class="px-2.5 py-1 rounded-full bg-amber-500/20 text-amber-400 text-[10px] font-bold uppercase">
                                            Em Breve
                                        </span>
                                    <?php else: ?>
                                        <span class="px-2.5 py-1 rounded-full bg-slate-700 text-slate-400 text-[10px] font-bold uppercase">
                                            Encerrado
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-4 text-right space-x-1">
                                    <button type="button" 
                                            onclick="editarFilme(<?= htmlspecialchars(json_encode($f)) ?>)" 
                                            class="p-2 rounded-lg bg-cinema-800 hover:bg-slate-700 text-slate-300 hover:text-white transition-colors" title="Editar Filme">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <button type="button" 
                                            onclick="confirmarExclusaoFilme(<?= $f['id'] ?>, '<?= htmlspecialchars(addslashes($f['titulo'])) ?>')" 
                                            class="p-2 rounded-lg bg-rose-950/40 hover:bg-rose-900 border border-rose-800/40 text-rose-400 hover:text-white transition-colors" title="Excluir Filme">
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

<!-- MODAL DE CADASTRO / EDIÇÃO DE FILME -->
<div id="modal-form-filme" class="fixed inset-0 z-50 hidden bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-cinema-900 border border-slate-700 rounded-3xl max-w-2xl w-full p-6 sm:p-8 shadow-2xl my-8 relative">
        
        <div class="flex items-center justify-between pb-4 mb-6 border-b border-slate-800">
            <h3 id="modal-filme-titulo" class="text-xl font-bold text-white">Cadastrar Filme</h3>
            <button type="button" onclick="fecharModalFilme()" class="text-slate-400 hover:text-white p-1">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form action="../actions/salvar_filme.php" method="POST" class="space-y-4">
            <input type="hidden" name="id" id="filme-id" value="">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Título do Filme *</label>
                    <input type="text" name="titulo" id="filme-titulo" required class="w-full bg-cinema-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-red-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Título Original</label>
                    <input type="text" name="titulo_original" id="filme-titulo-orig" class="w-full bg-cinema-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-red-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Gênero *</label>
                    <input type="text" name="genero" id="filme-genero" required placeholder="Ex: Ação / Ficção" class="w-full bg-cinema-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-red-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Duração (Minutos) *</label>
                    <input type="number" name="duracao" id="filme-duracao" required min="1" placeholder="Ex: 140" class="w-full bg-cinema-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-red-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Classificação Indicativa *</label>
                    <select name="classificacao" id="filme-classificacao" required class="w-full bg-cinema-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-red-500">
                        <option value="Livre">Livre para todos os públicos</option>
                        <option value="10">Não recomendado para menores de 10 anos</option>
                        <option value="12" selected>Não recomendado para menores de 12 anos</option>
                        <option value="14">Não recomendado para menores de 14 anos</option>
                        <option value="16">Não recomendado para menores de 16 anos</option>
                        <option value="18">Não recomendado para menores de 18 anos</option>
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1">URL do Cartaz (Imagem Vertical) *</label>
                    <input type="url" name="cartaz_url" id="filme-cartaz-url" required placeholder="https://..." class="w-full bg-cinema-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-red-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1">URL do Banner (Horizontal)</label>
                    <input type="url" name="banner_url" id="filme-banner-url" placeholder="https://..." class="w-full bg-cinema-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-red-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1">URL do Trailer (YouTube)</label>
                    <input type="url" name="trailer_url" id="filme-trailer-url" placeholder="https://www.youtube.com/watch?v=..." class="w-full bg-cinema-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-red-500">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Status de Exibição *</label>
                    <select name="status" id="filme-status" required class="w-full bg-cinema-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-red-500">
                        <option value="em_cartaz">Em Cartaz (Disponível para venda de ingressos)</option>
                        <option value="em_breve">Em Breve (Divulgação prévia)</option>
                        <option value="encerrado">Encerrado (Oculto no catálogo)</option>
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Sinopse Completa *</label>
                    <textarea name="sinopse" id="filme-sinopse" rows="4" required class="w-full bg-cinema-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-red-500"></textarea>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
                <button type="button" onclick="fecharModalFilme()" class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold">
                    Cancelar
                </button>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white text-xs font-black uppercase tracking-wider shadow-lg">
                    Salvar Filme
                </button>
            </div>
        </form>

    </div>
</div>

<script>
function abrirModalFilme() {
    document.getElementById('modal-filme-titulo').innerText = 'Cadastrar Novo Filme';
    document.getElementById('filme-id').value = '';
    document.getElementById('filme-titulo').value = '';
    document.getElementById('filme-titulo-orig').value = '';
    document.getElementById('filme-genero').value = '';
    document.getElementById('filme-duracao').value = '';
    document.getElementById('filme-classificacao').value = '12';
    document.getElementById('filme-cartaz-url').value = '';
    document.getElementById('filme-banner-url').value = '';
    document.getElementById('filme-trailer-url').value = '';
    document.getElementById('filme-status').value = 'em_cartaz';
    document.getElementById('filme-sinopse').value = '';
    document.getElementById('modal-form-filme').classList.remove('hidden');
}

function editarFilme(f) {
    document.getElementById('modal-filme-titulo').innerText = 'Editar Filme: ' + f.titulo;
    document.getElementById('filme-id').value = f.id;
    document.getElementById('filme-titulo').value = f.titulo;
    document.getElementById('filme-titulo-orig').value = f.titulo_original || '';
    document.getElementById('filme-genero').value = f.genero;
    document.getElementById('filme-duracao').value = f.duracao;
    document.getElementById('filme-classificacao').value = f.classificacao;
    document.getElementById('filme-cartaz-url').value = f.cartaz_url;
    document.getElementById('filme-banner-url').value = f.banner_url || '';
    document.getElementById('filme-trailer-url').value = f.trailer_url || '';
    document.getElementById('filme-status').value = f.status;
    document.getElementById('filme-sinopse').value = f.sinopse;
    document.getElementById('modal-form-filme').classList.remove('hidden');
}

function fecharModalFilme() {
    document.getElementById('modal-form-filme').classList.add('hidden');
}

function confirmarExclusaoFilme(id, titulo) {
    showModalConfirm(
        'Excluir Filme',
        `Tem certeza que deseja excluir o filme <strong>"${titulo}"</strong>?<br><span class="text-xs text-rose-400">Aviso: Todas as sessões associadas a este filme também serão removidas do sistema.</span>`,
        function() {
            window.location.href = `../actions/excluir_filme.php?id=${id}`;
        },
        null,
        true,
        'Sim, Excluir Filme'
    );
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
