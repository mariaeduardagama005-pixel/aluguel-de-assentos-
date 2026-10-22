<?php
/**
 * =====================================================================
 * CABEÇALHO GLOBAL (HEADER)
 * Mobile-First, Tailwind CSS CDN, FontAwesome, Design Cinematográfico
 * =====================================================================
 */

if (!isset($paginaTitulo)) {
    $paginaTitulo = 'CinePimenta - Aluguel de Assentos de Cinema';
}
$basePath = isset($ehAdminArea) && $ehAdminArea ? '../' : './';
$adminPath = isset($ehAdminArea) && $ehAdminArea ? './' : 'admin/';
$usuarioLogado = obter_usuario_logado();
?>
<!DOCTYPE html>
<html lang="pt-BR" class="dark scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($paginaTitulo) ?></title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        cinema: {
                            950: '#070a12',
                            900: '#0c111d',
                            800: '#151d30',
                            700: '#1f2b45',
                            accent: '#e50914',
                            gold: '#f59e0b',
                            neon: '#06b6d4'
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    
    <!-- FontAwesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Google Fonts Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- CSS Customizado -->
    <link rel="stylesheet" href="<?= $basePath ?>assets/css/style.css">
</head>
<body class="bg-cinema-950 text-slate-100 min-h-screen flex flex-col font-sans antialiased selection:bg-red-600 selection:text-white">

    <!-- BARRA DE NAVEGAÇÃO RESPONSIVA (Mobile-First) -->
    <header class="sticky top-0 z-40 bg-cinema-900/90 backdrop-blur-md border-b border-slate-800/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 sm:h-20">
                
                <!-- Logo Marca -->
                <a href="<?= $basePath ?>index.php" class="flex items-center gap-3 group">
                    <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-gradient-to-tr from-red-600 to-amber-500 flex items-center justify-center shadow-lg shadow-red-950/60 group-hover:scale-105 transition-transform">
                        <i class="fa-solid fa-film text-white text-lg sm:text-xl"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-1.5">
                            <span class="font-black text-xl sm:text-2xl tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-white via-slate-100 to-slate-400">Cine</span>
                            <span class="font-black text-xl sm:text-2xl text-red-500">Pimenta</span>
                        </div>
                        <span class="text-[10px] sm:text-xs font-semibold uppercase tracking-widest text-slate-400 block -mt-1">Aluguel de Assentos</span>
                    </div>
                </a>

                <!-- Navegação Desktop -->
                <nav class="hidden md:flex items-center gap-6 text-sm font-medium">
                    <a href="<?= $basePath ?>index.php" class="hover:text-red-400 transition-colors flex items-center gap-2">
                        <i class="fa-solid fa-clapperboard text-xs text-red-500"></i> Filmes em Cartaz
                    </a>
                    <a href="<?= $basePath ?>index.php#horarios" class="hover:text-red-400 transition-colors flex items-center gap-2">
                        <i class="fa-solid fa-clock text-xs text-amber-400"></i> Horários de Hoje
                    </a>
                    <a href="<?= $basePath ?>meus_ingressos.php" class="hover:text-red-400 transition-colors flex items-center gap-2">
                        <i class="fa-solid fa-ticket text-xs text-cyan-400"></i> Meus Ingressos
                    </a>

                    <?php if (eh_gerente()): ?>
                        <a href="<?= $adminPath ?>index.php" class="px-3 py-1.5 rounded-lg bg-red-600/20 border border-red-500/40 text-red-400 hover:bg-red-600 hover:text-white transition-all flex items-center gap-2">
                            <i class="fa-solid fa-shield-halved"></i> Painel do Gerente
                        </a>
                    <?php endif; ?>
                </nav>

                <!-- Área de Usuário / Ações Desktop -->
                <div class="hidden md:flex items-center gap-4">
                    <?php if ($usuarioLogado): ?>
                        <div class="flex items-center gap-3 bg-cinema-800/80 px-3.5 py-1.5 rounded-xl border border-slate-700/60">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-slate-700 to-slate-600 flex items-center justify-center font-bold text-xs text-white">
                                <?= strtoupper(substr($usuarioLogado['nome'], 0, 1)) ?>
                            </div>
                            <div class="text-left">
                                <span class="text-xs font-semibold block text-white leading-tight max-w-[120px] truncate"><?= htmlspecialchars($usuarioLogado['nome']) ?></span>
                                <span class="text-[10px] text-slate-400 uppercase tracking-wider"><?= htmlspecialchars($usuarioLogado['tipo']) ?></span>
                            </div>
                            <a href="<?= $basePath ?>logout.php" title="Encerrar Sessão" class="text-slate-400 hover:text-rose-400 ml-1 transition-colors p-1">
                                <i class="fa-solid fa-arrow-right-from-bracket"></i>
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="flex items-center gap-2">
                            <a href="<?= $basePath ?>login.php" class="text-xs sm:text-sm font-semibold text-slate-300 hover:text-white px-3 py-2 rounded-lg transition-colors">
                                Entrar
                            </a>
                            <a href="<?= $basePath ?>cadastro.php" class="text-xs sm:text-sm font-semibold bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-xl transition-all shadow-md shadow-red-950/40">
                                Criar Conta
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Botão Menu Mobile -->
                <div class="flex md:hidden items-center gap-2">
                    <?php if ($usuarioLogado): ?>
                        <a href="<?= $basePath ?>logout.php" class="text-slate-400 hover:text-rose-400 p-2" title="Sair">
                            <i class="fa-solid fa-arrow-right-from-bracket"></i>
                        </a>
                    <?php endif; ?>
                    <button type="button" id="mobile-menu-btn" class="p-2 rounded-xl text-slate-400 hover:text-white hover:bg-cinema-800 focus:outline-none" aria-label="Abrir Menu">
                        <i class="fa-solid fa-bars text-xl"></i>
                    </button>
                </div>

            </div>
        </div>

        <!-- Gaveta Mobile Dropdown -->
        <div id="mobile-menu-drawer" class="md:hidden hidden bg-cinema-900 border-b border-slate-800 px-4 pt-3 pb-5 space-y-2.5">
            <?php if ($usuarioLogado): ?>
                <div class="p-3 bg-cinema-800 rounded-xl mb-3 flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-red-600/30 text-red-400 flex items-center justify-center font-bold text-sm">
                        <?= strtoupper(substr($usuarioLogado['nome'], 0, 1)) ?>
                    </div>
                    <div>
                        <div class="text-sm font-bold text-white"><?= htmlspecialchars($usuarioLogado['nome']) ?></div>
                        <div class="text-xs text-slate-400"><?= htmlspecialchars($usuarioLogado['email']) ?> (<?= htmlspecialchars($usuarioLogado['tipo']) ?>)</div>
                    </div>
                </div>
            <?php endif; ?>

            <a href="<?= $basePath ?>index.php" class="block px-3 py-2 rounded-lg text-slate-200 hover:bg-cinema-800 hover:text-red-400 text-sm font-medium">
                <i class="fa-solid fa-clapperboard mr-2 text-red-500"></i> Filmes em Cartaz
            </a>
            <a href="<?= $basePath ?>index.php#horarios" class="block px-3 py-2 rounded-lg text-slate-200 hover:bg-cinema-800 hover:text-amber-400 text-sm font-medium">
                <i class="fa-solid fa-clock mr-2 text-amber-400"></i> Horários de Hoje
            </a>
            <a href="<?= $basePath ?>meus_ingressos.php" class="block px-3 py-2 rounded-lg text-slate-200 hover:bg-cinema-800 hover:text-cyan-400 text-sm font-medium">
                <i class="fa-solid fa-ticket mr-2 text-cyan-400"></i> Meus Ingressos
            </a>

            <?php if (eh_gerente()): ?>
                <a href="<?= $adminPath ?>index.php" class="block px-3 py-2 rounded-lg bg-red-600/20 text-red-400 font-bold text-sm">
                    <i class="fa-solid fa-shield-halved mr-2"></i> Painel do Gerente
                </a>
                <a href="<?= $adminPath ?>logs.php" class="block px-3 py-2 rounded-lg text-slate-300 hover:bg-cinema-800 text-sm">
                    <i class="fa-solid fa-clock-rotate-left mr-2 text-purple-400"></i> Auditoria & Histórico
                </a>
            <?php endif; ?>

            <div class="pt-3 border-t border-slate-800">
                <?php if ($usuarioLogado): ?>
                    <a href="<?= $basePath ?>logout.php" class="block text-center w-full px-4 py-2 text-sm font-medium text-rose-400 bg-rose-950/40 rounded-xl hover:bg-rose-950">
                        <i class="fa-solid fa-arrow-right-from-bracket mr-2"></i> Encerrar Sessão
                    </a>
                <?php else: ?>
                    <div class="grid grid-cols-2 gap-2">
                        <a href="<?= $basePath ?>login.php" class="text-center px-4 py-2 text-sm font-medium text-slate-200 bg-cinema-800 rounded-xl">
                            Entrar
                        </a>
                        <a href="<?= $basePath ?>cadastro.php" class="text-center px-4 py-2 text-sm font-semibold text-white bg-red-600 rounded-xl">
                            Cadastrar
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Indicador de Etapas (Stepper) se estiver no fluxo de compra -->
    <?php if (isset($etapaCompraAtual) && $etapaCompraAtual > 0): ?>
        <section class="bg-cinema-900/50 border-b border-slate-800/80 py-3.5">
            <div class="max-w-4xl mx-auto px-4">
                <div class="flex items-center justify-between text-xs sm:text-sm">
                    <?php
                        $etapas = [
                            1 => ['num' => 1, 'nome' => 'Filme & Horário', 'icone' => 'fa-film'],
                            2 => ['num' => 2, 'nome' => 'Sessão', 'icone' => 'fa-clock'],
                            3 => ['num' => 3, 'nome' => 'Assentos', 'icone' => 'fa-couch'],
                            4 => ['num' => 4, 'nome' => 'Pagamento', 'icone' => 'fa-credit-card'],
                            5 => ['num' => 5, 'nome' => 'Ticket', 'icone' => 'fa-ticket'],
                        ];
                        foreach ($etapas as $num => $et):
                            $ativo = ($etapaCompraAtual === $num);
                            $concluido = ($etapaCompraAtual > $num);
                    ?>
                        <div class="flex items-center gap-1.5 sm:gap-2 <?= $ativo ? 'text-red-400 font-bold' : ($concluido ? 'text-emerald-400' : 'text-slate-500') ?>">
                            <div class="w-6 h-6 sm:w-7 sm:h-7 rounded-full flex items-center justify-center text-xs <?= $ativo ? 'bg-red-600 text-white shadow-lg shadow-red-900/40 ring-2 ring-red-400/30' : ($concluido ? 'bg-emerald-600 text-white' : 'bg-slate-800 text-slate-400') ?>">
                                <?php if ($concluido): ?>
                                    <i class="fa-solid fa-check"></i>
                                <?php else: ?>
                                    <?= $num ?>
                                <?php endif; ?>
                            </div>
                            <span class="hidden sm:inline"><?= $et['nome'] ?></span>
                        </div>
                        <?php if ($num < 5): ?>
                            <div class="flex-1 h-0.5 mx-1 sm:mx-2 <?= $concluido ? 'bg-emerald-600/60' : 'bg-slate-800' ?>"></div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- Conteúdo Principal -->
    <main class="flex-1">
