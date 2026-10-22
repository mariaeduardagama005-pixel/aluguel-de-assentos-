<?php
/**
 * =====================================================================
 * PÁGINA: MEUS INGRESSOS & CONSULTA DE TICKETS
 * Permite ao cliente consultar seus ingressos comprados por conta ou CPF
 * =====================================================================
 */

require_once __DIR__ . '/bd.php';

$paginaTitulo = 'Meus Ingressos - CinePimenta';

$buscaCpf = trim($_GET['cpf'] ?? '');
$ingressos = [];

if (esta_logado()) {
    $stmt = $pdo->prepare("
        SELECT i.*, 
               s.data AS sessao_data, s.horario AS sessao_horario,
               f.titulo AS filme_titulo, f.cartaz_url,
               sa.nome AS sala_nome
        FROM ingressos i
        JOIN sessoes s ON i.sessao_id = s.id
        JOIN filmes f ON s.filme_id = f.id
        JOIN salas sa ON s.sala_id = sa.id
        WHERE i.usuario_id = ? OR i.email_cliente = ?
        ORDER BY i.criado_em DESC
    ");
    $stmt->execute([$_SESSION['usuario_id'], $_SESSION['usuario_email'] ?? '']);
    $ingressos = $stmt->fetchAll();
} elseif (!empty($buscaCpf)) {
    $stmt = $pdo->prepare("
        SELECT i.*, 
               s.data AS sessao_data, s.horario AS sessao_horario,
               f.titulo AS filme_titulo, f.cartaz_url,
               sa.nome AS sala_nome
        FROM ingressos i
        JOIN sessoes s ON i.sessao_id = s.id
        JOIN filmes f ON s.filme_id = f.id
        JOIN salas sa ON s.sala_id = sa.id
        WHERE i.cpf_cliente LIKE ? OR i.codigo = ?
        ORDER BY i.criado_em DESC
    ");
    $stmt->execute(["%{$buscaCpf}%", $buscaCpf]);
    $ingressos = $stmt->fetchAll();
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    
    <div class="max-w-3xl mx-auto text-center mb-10">
        <div class="w-14 h-14 rounded-2xl bg-cyan-600/20 text-cyan-400 flex items-center justify-center mx-auto text-2xl mb-3">
            <i class="fa-solid fa-ticket"></i>
        </div>
        <h1 class="text-3xl font-black text-white tracking-tight">Meus Ingressos</h1>
        <p class="text-xs sm:text-sm text-slate-400 mt-1">
            Consulte aqui o histórico das suas compras, assentos reservados e imprima seus tickets a qualquer momento.
        </p>

        <!-- Formulário de Consulta por CPF / Código se não logado ou busca manual -->
        <form action="meus_ingressos.php" method="GET" class="mt-6 max-w-md mx-auto flex gap-2">
            <input type="text" name="cpf" value="<?= htmlspecialchars($buscaCpf) ?>" placeholder="Digite seu CPF ou Código do Ingresso..." 
                   class="flex-1 bg-cinema-800 border border-slate-700 rounded-xl px-4 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-red-500">
            <button type="submit" class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white font-bold text-xs rounded-xl transition-all shadow-md">
                Buscar
            </button>
        </form>
    </div>

    <?php if (empty($ingressos)): ?>
        <div class="bg-cinema-900 border border-slate-800 rounded-3xl p-10 text-center max-w-md mx-auto">
            <div class="w-14 h-14 rounded-full bg-slate-800 flex items-center justify-center mx-auto text-slate-500 text-xl mb-3">
                <i class="fa-solid fa-receipt"></i>
            </div>
            <h3 class="text-base font-bold text-white mb-1">Nenhum ingresso encontrado</h3>
            <p class="text-xs text-slate-400 mb-6">
                <?= !empty($buscaCpf) ? 'Não encontramos compras associadas ao CPF ou Código informado.' : 'Faça login para ver suas compras ou consulte pelo CPF acima.' ?>
            </p>
            <a href="index.php" class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white text-xs font-bold rounded-xl transition-all">
                Ver Filmes em Cartaz
            </a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($ingressos as $ing): ?>
                <div class="bg-cinema-900 border border-slate-800 rounded-2xl p-5 shadow-xl hover:border-slate-700 transition-all flex flex-col justify-between space-y-4">
                    
                    <div class="space-y-3">
                        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                            <span class="font-mono text-xs font-bold text-amber-400">
                                <?= htmlspecialchars($ing['codigo']) ?>
                            </span>
                            <span class="px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400 text-[10px] font-bold uppercase">
                                <?= htmlspecialchars($ing['status_pagamento']) ?>
                            </span>
                        </div>

                        <div class="flex gap-3 items-center">
                            <img src="<?= htmlspecialchars($ing['cartaz_url']) ?>" alt="Capa" class="w-12 h-16 object-cover rounded-lg border border-slate-700 shrink-0">
                            <div>
                                <h3 class="text-sm font-bold text-white line-clamp-1 leading-snug">
                                    <?= htmlspecialchars($ing['filme_titulo']) ?>
                                </h3>
                                <div class="text-xs text-slate-400 mt-0.5">
                                    <?= htmlspecialchars($ing['sala_nome']) ?>
                                </div>
                                <div class="text-xs text-slate-300 font-semibold mt-1">
                                    <?= formatar_data($ing['sessao_data']) ?> às <?= formatar_hora($ing['sessao_horario']) ?>
                                </div>
                            </div>
                        </div>

                        <div class="p-2.5 bg-cinema-800/60 rounded-xl border border-slate-700/60 text-xs">
                            <span class="text-slate-400 block mb-1">Assentos Reservados:</span>
                            <div class="flex flex-wrap gap-1">
                                <?php foreach (explode(',', $ing['assentos_selecionados']) as $ass): ?>
                                    <span class="px-2 py-0.5 rounded bg-red-600/20 text-red-300 font-bold text-[11px] border border-red-500/30">
                                        <?= trim($ass) ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="flex items-center justify-between text-xs text-slate-400 pt-1">
                            <span>Total pago:</span>
                            <span class="font-bold text-emerald-400 text-sm"><?= formatar_moeda($ing['valor_total']) ?></span>
                        </div>
                    </div>

                    <a href="ticket.php?codigo=<?= urlencode($ing['codigo']) ?>" 
                       class="w-full py-2.5 px-4 rounded-xl bg-cinema-800 hover:bg-red-600 text-slate-200 hover:text-white font-bold text-xs text-center transition-all border border-slate-700 flex items-center justify-center gap-2">
                        <i class="fa-solid fa-qrcode"></i>
                        <span>Visualizar / Imprimir Ticket</span>
                    </a>

                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
