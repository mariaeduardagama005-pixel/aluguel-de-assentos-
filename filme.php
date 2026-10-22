<?php
/**
 * =====================================================================
 * ETAPA 2: DETALHES DO FILME & ESCOLHA DA SESSÃO
 * O cliente escolhe o filme que quer assistir e o sistema apresenta as 
 * sessões disponíveis com horário, sala e filme escolhido.
 * =====================================================================
 */

require_once __DIR__ . '/bd.php';

$filmeId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$filmeId) {
    definir_toast('error', 'Filme não especificado.');
    header("Location: index.php");
    exit;
}

// Busca dados do filme
$stmt = $pdo->prepare("SELECT * FROM filmes WHERE id = ?");
$stmt->execute([$filmeId]);
$filme = $stmt->fetch();

if (!$filme) {
    definir_toast('error', 'Filme não encontrado.');
    header("Location: index.php");
    exit;
}

// Busca sessões disponíveis para este filme
$stmtSessoes = $pdo->prepare("
    SELECT s.*, sa.nome AS sala_nome, sa.tipo AS sala_tipo, sa.capacidade AS sala_capacidade,
           (SELECT COUNT(*) FROM assentos_ocupados ao WHERE ao.sessao_id = s.id) AS ocupados
    FROM sessoes s
    JOIN salas sa ON s.sala_id = sa.id
    WHERE s.filme_id = ? AND s.status = 'ativa'
    ORDER BY s.data ASC, s.horario ASC
");
$stmtSessoes->execute([$filmeId]);
$sessoes = $stmtSessoes->fetchAll();

$paginaTitulo = htmlspecialchars($filme['titulo']) . ' - Sessões & Horários - CinePimenta';
$etapaCompraAtual = 2;

// Classificação indicativa badge
$classIndicativa = trim($filme['classificacao']);
$badgeClass = 'badge-class-12';
if ($classIndicativa === 'Livre') $badgeClass = 'badge-class-livre';
elseif ($classIndicativa === '10') $badgeClass = 'badge-class-10';
elseif ($classIndicativa === '14') $badgeClass = 'badge-class-14';
elseif ($classIndicativa === '16') $badgeClass = 'badge-class-16';
elseif ($classIndicativa === '18') $badgeClass = 'badge-class-18';

require_once __DIR__ . '/includes/header.php';
?>

<!-- BANNER HERO DO FILME -->
<div class="relative bg-cinema-900 border-b border-slate-800 overflow-hidden">
    <!-- Backdrop Blur Image -->
    <div class="absolute inset-0 opacity-20 filter blur-xl scale-110 pointer-events-none">
        <img src="<?= htmlspecialchars($filme['banner_url'] ?: $filme['cartaz_url']) ?>" alt="Backdrop" class="w-full h-full object-cover">
    </div>
    <div class="absolute inset-0 bg-gradient-to-t from-cinema-950 via-cinema-950/80 to-transparent"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 relative z-10">
        
        <!-- Botão Voltar -->
        <a href="index.php" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-400 hover:text-white transition-colors mb-6 group">
            <i class="fa-solid fa-arrow-left group-hover:-translate-x-1 transition-transform"></i>
            <span>Voltar para Lista de Filmes</span>
        </a>

        <div class="grid grid-cols-1 md:grid-cols-12 gap-8 items-start">
            
            <!-- Poster -->
            <div class="md:col-span-4 lg:col-span-3">
                <div class="rounded-3xl overflow-hidden shadow-2xl border border-slate-700/80 aspect-[2/3] max-w-[280px] mx-auto md:max-w-none bg-slate-900">
                    <img src="<?= htmlspecialchars($filme['cartaz_url']) ?>" alt="<?= htmlspecialchars($filme['titulo']) ?>" 
                         class="w-full h-full object-cover"
                         onerror="this.src='https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?w=600&auto=format&fit=crop&q=80'">
                </div>
            </div>

            <!-- Informações do Filme -->
            <div class="md:col-span-8 lg:col-span-9 space-y-4">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="px-2.5 py-1 rounded font-black text-xs uppercase shadow <?= $badgeClass ?>">
                        <?= htmlspecialchars($filme['classificacao']) ?>
                    </span>
                    <span class="px-2.5 py-1 rounded bg-cinema-800 border border-slate-700 text-xs font-semibold text-slate-300">
                        <i class="fa-regular fa-clock mr-1 text-slate-400"></i><?= $filme['duracao'] ?> minutos
                    </span>
                    <span class="px-2.5 py-1 rounded bg-red-600/20 text-red-400 border border-red-500/30 text-xs font-semibold uppercase">
                        <?= htmlspecialchars($filme['genero']) ?>
                    </span>
                    <?php if ($filme['status'] === 'em_cartaz'): ?>
                        <span class="px-2.5 py-1 rounded bg-emerald-500/20 text-emerald-400 text-xs font-bold uppercase">
                            Em Cartaz
                        </span>
                    <?php endif; ?>
                </div>

                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black text-white tracking-tight leading-tight">
                    <?= htmlspecialchars($filme['titulo']) ?>
                </h1>

                <?php if (!empty($filme['titulo_original'])): ?>
                    <p class="text-sm text-slate-400 italic -mt-2">
                        Título original: <?= htmlspecialchars($filme['titulo_original']) ?>
                    </p>
                <?php endif; ?>

                <div class="pt-2">
                    <h2 class="text-xs font-bold text-slate-300 uppercase tracking-widest mb-1">Sinopse</h2>
                    <p class="text-sm sm:text-base text-slate-300 leading-relaxed max-w-3xl">
                        <?= nl2br(htmlspecialchars($filme['sinopse'])) ?>
                    </p>
                </div>

                <!-- Botão de Trailer se houver -->
                <?php if (!empty($filme['trailer_url'])): ?>
                    <div class="pt-2">
                        <button type="button" onclick="openTrailerModal('<?= htmlspecialchars($filme['trailer_url']) ?>')" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-cinema-800 hover:bg-slate-700 text-slate-200 text-xs font-bold border border-slate-700 transition-all">
                            <i class="fa-brands fa-youtube text-red-500 text-base"></i>
                            <span>Assistir Trailer Oficial</span>
                        </button>
                    </div>
                <?php endif; ?>

            </div>

        </div>
    </div>
</div>

<!-- SEÇÃO DE SELEÇÃO DE SESSÃO -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="mb-8">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-red-600/20 text-red-400 text-xs font-bold uppercase mb-2">
            Etapa 2 de 5
        </div>
        <h2 class="text-2xl sm:text-3xl font-black text-white tracking-tight">
            Sessões Disponíveis para este Filme
        </h2>
        <p class="text-xs sm:text-sm text-slate-400 mt-1">
            Escolha um horário e sala abaixo para avançar e selecionar suas poltronas.
        </p>
    </div>

    <?php if (empty($sessoes)): ?>
        <div class="bg-cinema-900 border border-slate-800 rounded-3xl p-10 text-center max-w-md mx-auto">
            <div class="w-14 h-14 rounded-full bg-slate-800 flex items-center justify-center mx-auto text-slate-400 text-xl mb-3">
                <i class="fa-regular fa-calendar-xmark"></i>
            </div>
            <h3 class="text-base font-bold text-white mb-1">Nenhuma sessão disponível no momento</h3>
            <p class="text-xs text-slate-400 mb-4">Novos horários serão adicionados em breve pelo gerente do cinema.</p>
            <a href="index.php" class="px-4 py-2 bg-cinema-800 hover:bg-slate-700 text-white text-xs font-bold rounded-xl transition-all">
                Ver Outros Filmes
            </a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($sessoes as $sessao): 
                $lugaresLivres = max(0, $sessao['sala_capacidade'] - $sessao['ocupados']);
                $esgotado = ($lugaresLivres === 0);
            ?>
                <div class="bg-cinema-900 border border-slate-800 hover:border-red-500/50 rounded-2xl p-6 transition-all shadow-lg flex flex-col justify-between group">
                    <div>
                        <!-- Header do Card da Sessão -->
                        <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-800">
                            <div>
                                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">
                                    <i class="fa-solid fa-door-open text-slate-500 mr-1"></i><?= htmlspecialchars($sessao['sala_nome']) ?>
                                </span>
                                <span class="text-xs text-slate-500"><?= htmlspecialchars($sessao['sala_tipo']) ?></span>
                            </div>
                            <span class="px-2.5 py-1 rounded-lg bg-slate-800 border border-slate-700 text-xs font-bold text-slate-200 uppercase">
                                <?= htmlspecialchars($sessao['idioma']) ?>
                            </span>
                        </div>

                        <!-- Horário e Data -->
                        <div class="flex items-center gap-4 my-2">
                            <div class="w-14 h-14 rounded-2xl bg-cinema-800 border border-slate-700 flex flex-col items-center justify-center text-center">
                                <span class="text-[9px] font-bold uppercase tracking-wider text-slate-400">Hora</span>
                                <span class="text-lg font-black text-amber-400 leading-none mt-0.5">
                                    <?= formatar_hora($sessao['horario']) ?>
                                </span>
                            </div>
                            <div>
                                <div class="text-xs font-semibold text-slate-300">
                                    <i class="fa-regular fa-calendar mr-1 text-red-500"></i>
                                    Data: <?= formatar_data($sessao['data']) ?>
                                </div>
                                <div class="text-xs text-slate-400 mt-1">
                                    Formato: <strong class="text-slate-200"><?= htmlspecialchars($sessao['tipo_sessao']) ?></strong>
                                </div>
                            </div>
                        </div>

                        <!-- Capacidade e Lugares Livres -->
                        <div class="mt-4 pt-3 border-t border-slate-800/80 flex items-center justify-between text-xs">
                            <span class="text-slate-400">Disponibilidade:</span>
                            <span class="font-semibold <?= $esgotado ? 'text-rose-400' : 'text-emerald-400' ?>">
                                <i class="fa-solid fa-couch mr-1"></i>
                                <?= $lugaresLivres ?> de <?= $sessao['sala_capacidade'] ?> livres
                            </span>
                        </div>

                        <!-- Preço do Ingresso -->
                        <div class="mt-2 flex items-center justify-between text-xs">
                            <span class="text-slate-400">Preço por ingresso:</span>
                            <span class="text-base font-black text-white">
                                <?= formatar_moeda($sessao['preco_ingresso']) ?>
                            </span>
                        </div>
                    </div>

                    <!-- Botão de Ação -->
                    <div class="mt-6">
                        <?php if ($esgotado): ?>
                            <button disabled class="w-full py-3 px-4 rounded-xl bg-slate-800 text-slate-500 text-xs font-bold cursor-not-allowed text-center">
                                <i class="fa-solid fa-ban mr-1.5"></i> Sessão Esgotada
                            </button>
                        <?php else: ?>
                            <a href="assentos.php?sessao_id=<?= $sessao['id'] ?>" 
                               class="w-full py-3 px-4 rounded-xl bg-red-600 hover:bg-red-700 text-white text-xs font-bold text-center block transition-all shadow-lg shadow-red-950/40 group-hover:scale-[1.02]">
                                <i class="fa-solid fa-couch mr-1.5"></i> Selecionar Assento(s)
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<!-- Modal de Trailer (YouTube) -->
<div id="trailer-modal" class="fixed inset-0 z-50 hidden bg-black/90 backdrop-blur-md flex items-center justify-center p-4">
    <div class="relative w-full max-w-4xl bg-cinema-900 rounded-2xl overflow-hidden border border-slate-800 shadow-2xl">
        <div class="p-3 bg-cinema-950 flex items-center justify-between border-b border-slate-800">
            <span class="text-xs font-bold text-white flex items-center gap-2">
                <i class="fa-brands fa-youtube text-red-500"></i> Trailer do Filme
            </span>
            <button type="button" onclick="closeTrailerModal()" class="text-slate-400 hover:text-white p-1">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <div class="aspect-video w-full bg-black">
            <iframe id="trailer-iframe" class="w-full h-full" src="" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
        </div>
    </div>
</div>

<script>
function openTrailerModal(url) {
    let videoId = '';
    if (url.includes('v=')) {
        videoId = url.split('v=')[1].split('&')[0];
    } else if (url.includes('youtu.be/')) {
        videoId = url.split('youtu.be/')[1].split('?')[0];
    }
    
    if (videoId) {
        document.getElementById('trailer-iframe').src = `https://www.youtube.com/embed/${videoId}?autoplay=1`;
        document.getElementById('trailer-modal').classList.remove('hidden');
    } else {
        showModalAlert('Trailer', 'Trailer disponível no link: <a href="'+url+'" target="_blank" class="text-red-400 underline">'+url+'</a>', 'info');
    }
}

function closeTrailerModal() {
    const modal = document.getElementById('trailer-modal');
    const iframe = document.getElementById('trailer-iframe');
    iframe.src = '';
    modal.classList.add('hidden');
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
