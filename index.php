<?php
/**
 * =====================================================================
 * ETAPA 1: FILMES EM CARTAZ & HORÁRIOS DISPONÍVEIS
 * Exibe catálogo completo, filtros de gênero e horários em tempo real
 * =====================================================================
 */

require_once __DIR__ . '/bd.php';

$paginaTitulo = 'Filmes em Cartaz & Horários - CinePimenta';
$etapaCompraAtual = 1;

// Busca filmes em cartaz e sessões
$termoBusca = trim($_GET['busca'] ?? '');
$generoFiltro = trim($_GET['genero'] ?? '');

$sqlFilmes = "SELECT * FROM filmes WHERE status != 'encerrado'";
$params = [];

if ($termoBusca !== '') {
    $sqlFilmes .= " AND (titulo LIKE ? OR sinopse LIKE ?)";
    $params[] = "%{$termoBusca}%";
    $params[] = "%{$termoBusca}%";
}

if ($generoFiltro !== '') {
    $sqlFilmes .= " AND genero LIKE ?";
    $params[] = "%{$generoFiltro}%";
}

$sqlFilmes .= " ORDER BY (status = 'em_cartaz') DESC, id ASC";

$stmt = $pdo->prepare($sqlFilmes);
$stmt->execute($params);
$filmes = $stmt->fetchAll();

// Busca as sessões de hoje para exibição rápida nos cards
$stmtSessoes = $pdo->query("
    SELECT s.*, sa.nome AS sala_nome, sa.tipo AS sala_tipo, f.titulo AS filme_titulo
    FROM sessoes s
    JOIN salas sa ON s.sala_id = sa.id
    JOIN filmes f ON s.filme_id = f.id
    WHERE s.status = 'ativa'
    ORDER BY s.data ASC, s.horario ASC
");
$todasSessoes = $stmtSessoes->fetchAll();

// Mapeia sessões por filme_id
$sessoesPorFilme = [];
foreach ($todasSessoes as $s) {
    $sessoesPorFilme[$s['filme_id']][] = $s;
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- BANNER HERO DE DESTAQUE -->
<section class="relative bg-gradient-to-b from-cinema-900 via-cinema-950 to-cinema-950 pt-8 pb-14 border-b border-slate-800/60 overflow-hidden">
    <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#e50914_1px,transparent_1px)] [background-size:16px_16px] pointer-events-none"></div>
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
            
            <div class="lg:col-span-7 space-y-4 text-center lg:text-left">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-red-600/10 border border-red-500/30 text-red-400 text-xs font-semibold uppercase tracking-wider">
                    <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
                    Etapa 1: Escolha seu Filme e Horário
                </div>
                <h1 class="text-3xl sm:text-5xl font-black text-white tracking-tight leading-tight">
                    Escolha seu filme preferido e garanta os <span class="bg-clip-text text-transparent bg-gradient-to-r from-red-500 via-amber-400 to-red-500">melhores assentos</span>
                </h1>
                <p class="text-slate-300 text-sm sm:text-base max-w-xl mx-auto lg:mx-0 leading-relaxed">
                    Reserve seus ingressos com total conveniência: visualize o mapa da sala em tempo real, selecione sua poltrona numerada e pague via PIX ou Cartão com emissão instantânea do ingresso.
                </p>

                <!-- Barra de Pesquisa Rápida -->
                <form action="index.php" method="GET" class="pt-2 max-w-lg mx-auto lg:mx-0">
                    <div class="relative flex items-center">
                        <i class="fa-solid fa-magnifying-glass absolute left-4 text-slate-400"></i>
                        <input type="text" name="busca" value="<?= htmlspecialchars($termoBusca) ?>" placeholder="Buscar por título ou sinopse do filme..." 
                               class="w-full bg-cinema-800/90 text-white placeholder-slate-400 pl-11 pr-24 py-3 rounded-2xl border border-slate-700 focus:outline-none focus:border-red-500 focus:ring-2 focus:ring-red-500/20 text-sm transition-all shadow-inner">
                        <button type="submit" class="absolute right-2 px-4 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-bold rounded-xl transition-all shadow-md shadow-red-950/50">
                            Buscar
                        </button>
                    </div>
                </form>
            </div>

            <!-- Card Destaque / Bilheteria -->
            <div class="lg:col-span-5">
                <div class="bg-gradient-to-br from-cinema-800 to-cinema-900 border border-slate-700/80 rounded-3xl p-6 shadow-2xl relative">
                    <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-700/60">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-bolt text-amber-400"></i>
                            <span class="text-xs font-bold text-slate-200 uppercase tracking-wider">Como Funciona a Compra</span>
                        </div>
                        <span class="text-[11px] px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400 font-semibold">100% Online</span>
                    </div>

                    <div class="space-y-3.5 text-xs text-slate-300">
                        <div class="flex items-start gap-3">
                            <div class="w-6 h-6 rounded-lg bg-red-600/20 text-red-400 flex items-center justify-center font-bold shrink-0">1</div>
                            <div>
                                <strong class="text-white block">1. Selecione o Filme e Horário</strong>
                                Veja a sinopse e escolha a sessão ideal para você.
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="w-6 h-6 rounded-lg bg-amber-600/20 text-amber-400 flex items-center justify-center font-bold shrink-0">2</div>
                            <div>
                                <strong class="text-white block">2. Escolha sua Poltrona</strong>
                                Visualize o mapa da sala e marque os assentos desejados.
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="w-6 h-6 rounded-lg bg-emerald-600/20 text-emerald-400 flex items-center justify-center font-bold shrink-0">3</div>
                            <div>
                                <strong class="text-white block">3. Pague e Receba o Ticket</strong>
                                Pague via PIX ou Cartão e retire seu ingresso com QR Code.
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 pt-4 border-t border-slate-700/60 flex items-center justify-between text-xs text-slate-400">
                        <span><i class="fa-solid fa-shield-check text-emerald-400 mr-1"></i> Auditoria Segura</span>
                        <span><i class="fa-solid fa-qrcode text-cyan-400 mr-1"></i> Entrada sem Fila</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- SELEÇÃO DE GÊNEROS (FILTROS RÁPIDOS) -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-6 relative z-20">
    <div class="bg-cinema-900/90 backdrop-blur-md border border-slate-800 rounded-2xl p-2.5 shadow-xl flex items-center gap-2 overflow-x-auto no-scrollbar">
        <a href="index.php" class="px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-all <?= empty($generoFiltro) ? 'bg-red-600 text-white shadow-lg shadow-red-950/60' : 'bg-cinema-800 text-slate-300 hover:bg-slate-700 hover:text-white' ?>">
            Todos os Filmes
        </a>
        <a href="index.php?genero=Ficção" class="px-4 py-2 rounded-xl text-xs font-medium whitespace-nowrap transition-all <?= $generoFiltro === 'Ficção' ? 'bg-red-600 text-white shadow-lg' : 'bg-cinema-800 text-slate-300 hover:bg-slate-700 hover:text-white' ?>">
            Ficção Científica
        </a>
        <a href="index.php?genero=Ação" class="px-4 py-2 rounded-xl text-xs font-medium whitespace-nowrap transition-all <?= $generoFiltro === 'Ação' ? 'bg-red-600 text-white shadow-lg' : 'bg-cinema-800 text-slate-300 hover:bg-slate-700 hover:text-white' ?>">
            Ação
        </a>
        <a href="index.php?genero=Animação" class="px-4 py-2 rounded-xl text-xs font-medium whitespace-nowrap transition-all <?= $generoFiltro === 'Animação' ? 'bg-red-600 text-white shadow-lg' : 'bg-cinema-800 text-slate-300 hover:bg-slate-700 hover:text-white' ?>">
            Animação
        </a>
        <a href="index.php?genero=Suspense" class="px-4 py-2 rounded-xl text-xs font-medium whitespace-nowrap transition-all <?= $generoFiltro === 'Suspense' ? 'bg-red-600 text-white shadow-lg' : 'bg-cinema-800 text-slate-300 hover:bg-slate-700 hover:text-white' ?>">
            Suspense / Mistério
        </a>
        <a href="index.php?genero=Aventura" class="px-4 py-2 rounded-xl text-xs font-medium whitespace-nowrap transition-all <?= $generoFiltro === 'Aventura' ? 'bg-red-600 text-white shadow-lg' : 'bg-cinema-800 text-slate-300 hover:bg-slate-700 hover:text-white' ?>">
            Aventura
        </a>
    </div>
</section>

<!-- CATÁLOGO DE FILMES EM CARTAZ -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <h2 class="text-2xl sm:text-3xl font-black text-white tracking-tight flex items-center gap-3">
                <i class="fa-solid fa-clapperboard text-red-500"></i> Filmes Disponíveis
            </h2>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">
                <?= count($filmes) ?> filmes encontrados <?= !empty($termoBusca) ? "para a busca: \"<strong>".htmlspecialchars($termoBusca)."</strong>\"" : '' ?>
            </p>
        </div>

        <?php if (!empty($termoBusca) || !empty($generoFiltro)): ?>
            <a href="index.php" class="text-xs text-red-400 hover:text-red-300 font-semibold inline-flex items-center gap-1.5 self-start">
                <i class="fa-solid fa-xmark"></i> Limpar filtros
            </a>
        <?php endif; ?>
    </div>

    <?php if (empty($filmes)): ?>
        <div class="bg-cinema-900 border border-slate-800 rounded-3xl p-12 text-center max-w-lg mx-auto">
            <div class="w-16 h-16 rounded-full bg-slate-800 flex items-center justify-center mx-auto text-slate-500 text-2xl mb-4">
                <i class="fa-solid fa-film"></i>
            </div>
            <h3 class="text-lg font-bold text-white mb-2">Nenhum filme encontrado</h3>
            <p class="text-xs text-slate-400 mb-6">Tente buscar por outro termo ou remova os filtros aplicados.</p>
            <a href="index.php" class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white font-bold text-xs rounded-xl transition-all">
                Ver Todos os Filmes
            </a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
            <?php foreach ($filmes as $filme): 
                $sessoesFilme = $sessoesPorFilme[$filme['id']] ?? [];
                $classIndicativa = trim($filme['classificacao']);
                $badgeClass = 'badge-class-12';
                if ($classIndicativa === 'Livre') $badgeClass = 'badge-class-livre';
                elseif ($classIndicativa === '10') $badgeClass = 'badge-class-10';
                elseif ($classIndicativa === '14') $badgeClass = 'badge-class-14';
                elseif ($classIndicativa === '16') $badgeClass = 'badge-class-16';
                elseif ($classIndicativa === '18') $badgeClass = 'badge-class-18';
            ?>
                <div class="bg-cinema-900 border border-slate-800 hover:border-slate-700/80 rounded-3xl overflow-hidden shadow-xl hover:shadow-2xl transition-all duration-300 flex flex-col group">
                    
                    <!-- Imagem do Cartaz -->
                    <div class="relative aspect-[16/10] overflow-hidden bg-slate-950">
                        <img src="<?= htmlspecialchars($filme['cartaz_url']) ?>" alt="<?= htmlspecialchars($filme['titulo']) ?>" 
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                             loading="lazy"
                             onerror="this.src='https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?w=600&auto=format&fit=crop&q=80'">
                        
                        <div class="absolute inset-0 bg-gradient-to-t from-cinema-900 via-transparent to-black/40"></div>

                        <!-- Badge de Status -->
                        <div class="absolute top-3 left-3 flex items-center gap-2">
                            <?php if ($filme['status'] === 'em_cartaz'): ?>
                                <span class="px-2.5 py-1 rounded-lg bg-red-600 text-white font-black text-[10px] uppercase tracking-wider shadow-md">
                                    Em Cartaz
                                </span>
                            <?php else: ?>
                                <span class="px-2.5 py-1 rounded-lg bg-amber-500 text-slate-950 font-black text-[10px] uppercase tracking-wider shadow-md">
                                    Em Breve
                                </span>
                            <?php endif; ?>

                            <!-- Classificação -->
                            <span class="px-2 py-0.5 rounded font-black text-[10px] uppercase shadow-sm <?= $badgeClass ?>">
                                <?= htmlspecialchars($filme['classificacao']) ?>
                            </span>
                        </div>

                        <!-- Duração -->
                        <div class="absolute top-3 right-3 px-2 py-1 rounded-lg bg-black/70 backdrop-blur-md text-[11px] text-slate-200 font-semibold border border-white/10">
                            <i class="fa-regular fa-clock mr-1 text-slate-400"></i><?= $filme['duracao'] ?> min
                        </div>
                    </div>

                    <!-- Conteúdo do Card -->
                    <div class="p-5 flex-1 flex flex-col justify-between space-y-4">
                        <div>
                            <span class="text-[11px] font-semibold uppercase tracking-wider text-red-400 block mb-1">
                                <?= htmlspecialchars($filme['genero']) ?>
                            </span>
                            <h3 class="text-lg font-black text-white group-hover:text-red-400 transition-colors leading-snug">
                                <a href="filme.php?id=<?= $filme['id'] ?>">
                                    <?= htmlspecialchars($filme['titulo']) ?>
                                </a>
                            </h3>
                            <p class="text-xs text-slate-400 mt-2 line-clamp-3 leading-relaxed">
                                <?= htmlspecialchars($filme['sinopse']) ?>
                            </p>
                        </div>

                        <!-- Horários Disponíveis Rápido -->
                        <div class="pt-3 border-t border-slate-800">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-[11px] font-bold text-slate-300 uppercase tracking-wider">
                                    <i class="fa-regular fa-calendar-check text-red-500 mr-1"></i> Sessões Disponíveis
                                </span>
                                <span class="text-[10px] text-slate-500 font-medium">Hoje</span>
                            </div>

                            <?php if (!empty($sessoesFilme)): ?>
                                <div class="flex flex-wrap gap-2">
                                    <?php foreach ($sessoesFilme as $sess): ?>
                                        <a href="assentos.php?sessao_id=<?= $sess['id'] ?>" 
                                           class="px-2.5 py-1.5 rounded-xl bg-cinema-800 hover:bg-red-600 border border-slate-700 hover:border-red-500 text-white text-xs font-bold transition-all shadow-sm flex items-center gap-1.5 group/btn"
                                           title="Sala: <?= htmlspecialchars($sess['sala_nome']) ?> - <?= formatar_moeda($sess['preco_ingresso']) ?>">
                                            <i class="fa-solid fa-clock text-[10px] text-amber-400 group-hover/btn:text-white"></i>
                                            <?= formatar_hora($sess['horario']) ?>
                                            <span class="text-[9px] text-slate-400 group-hover/btn:text-white uppercase"><?= $sess['tipo_sessao'] ?></span>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-xs text-slate-500 italic py-1">
                                    Nenhuma sessão aberta no momento para este filme.
                                </p>
                            <?php endif; ?>
                        </div>

                        <!-- Botão de Ação -->
                        <div class="pt-2">
                            <a href="filme.php?id=<?= $filme['id'] ?>" 
                               class="w-full py-2.5 px-4 rounded-xl bg-cinema-800 hover:bg-slate-700 text-slate-100 hover:text-white font-bold text-xs transition-all flex items-center justify-center gap-2 border border-slate-700/80">
                                <span>Ver Detalhes & Todas as Sessões</span>
                                <i class="fa-solid fa-chevron-right text-[10px]"></i>
                            </a>
                        </div>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<!-- SEÇÃO HORÁRIOS GERAIS DE HOJE (#horarios) -->
<section id="horarios" class="bg-cinema-900/60 border-t border-slate-800/80 py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="text-center max-w-2xl mx-auto mb-12">
            <span class="text-xs font-bold uppercase tracking-widest text-amber-400 block mb-2">Grade de Programação</span>
            <h2 class="text-2xl sm:text-4xl font-black text-white tracking-tight">Horários das Sessões de Hoje</h2>
            <p class="text-slate-400 text-xs sm:text-sm mt-2">Escolha diretamente o horário desejado e vá direto para a seleção de assentos na sala.</p>
        </div>

        <?php if (empty($todasSessoes)): ?>
            <p class="text-center text-slate-400 text-sm">Nenhuma sessão programada para hoje.</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-800 text-xs font-bold text-slate-400 uppercase tracking-wider">
                            <th class="py-3 px-4">Filme</th>
                            <th class="py-3 px-4">Sala</th>
                            <th class="py-3 px-4">Tipo & Idioma</th>
                            <th class="py-3 px-4">Horário</th>
                            <th class="py-3 px-4">Preço</th>
                            <th class="py-3 px-4 text-right">Ação</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 text-sm">
                        <?php foreach ($todasSessoes as $sessao): ?>
                            <tr class="hover:bg-cinema-800/40 transition-colors">
                                <td class="py-4 px-4 font-bold text-white">
                                    <a href="filme.php?id=<?= $sessao['filme_id'] ?>" class="hover:text-red-400 transition-colors">
                                        <?= htmlspecialchars($sessao['filme_titulo']) ?>
                                    </a>
                                </td>
                                <td class="py-4 px-4 text-slate-300 text-xs">
                                    <i class="fa-solid fa-door-open mr-1 text-slate-500"></i><?= htmlspecialchars($sessao['sala_nome']) ?>
                                </td>
                                <td class="py-4 px-4 text-xs">
                                    <span class="px-2 py-0.5 rounded bg-slate-800 border border-slate-700 text-slate-200 font-semibold mr-1">
                                        <?= htmlspecialchars($sessao['tipo_sessao']) ?>
                                    </span>
                                    <span class="text-slate-400"><?= htmlspecialchars($sessao['idioma']) ?></span>
                                </td>
                                <td class="py-4 px-4">
                                    <span class="font-mono font-bold text-amber-400 bg-amber-500/10 px-2.5 py-1 rounded-lg border border-amber-500/20 text-xs">
                                        <?= formatar_hora($sessao['horario']) ?>
                                    </span>
                                </td>
                                <td class="py-4 px-4 font-bold text-emerald-400 text-xs sm:text-sm">
                                    <?= formatar_moeda($sessao['preco_ingresso']) ?>
                                </td>
                                <td class="py-4 px-4 text-right">
                                    <a href="assentos.php?sessao_id=<?= $sessao['id'] ?>" 
                                       class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold text-xs transition-all shadow-md shadow-red-950/40">
                                        <i class="fa-solid fa-couch"></i>
                                        <span>Escolher Assento</span>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
