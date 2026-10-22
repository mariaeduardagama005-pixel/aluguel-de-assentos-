<?php
/**
 * =====================================================================
 * RODAPÉ GLOBAL (FOOTER)
 * Inclusão dos Toasts, Modais e Scripts Globais
 * =====================================================================
 */
$basePath = isset($ehAdminArea) && $ehAdminArea ? '../' : './';
?>
    </main>

    <!-- RODAPÉ RESPONSIVO -->
    <footer class="bg-cinema-900 border-t border-slate-800/80 mt-16 text-slate-400 text-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                
                <!-- Coluna 1: Sobre -->
                <div class="space-y-3">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-red-600 flex items-center justify-center text-white">
                            <i class="fa-solid fa-film"></i>
                        </div>
                        <span class="font-extrabold text-lg text-white">CinePimenta</span>
                    </div>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Sistema moderno de aluguel de assentos para cinema com seleção interativa de poltronas, pagamento rápido e emissão imediata de ingressos.
                    </p>
                    <div class="flex items-center gap-3 pt-2 text-slate-400">
                        <span class="text-xs px-2.5 py-1 rounded bg-slate-800 border border-slate-700 text-emerald-400 font-mono">
                            <i class="fa-solid fa-database mr-1"></i> <?= isset($GLOBALS['usando_sqlite']) && $GLOBALS['usando_sqlite'] ? 'PDO SQLite Local' : 'PDO MySQL' ?>
                        </span>
                        <span class="text-xs px-2.5 py-1 rounded bg-slate-800 border border-slate-700 text-blue-400 font-mono">
                            <i class="fa-solid fa-shield mr-1"></i> BCRYPT
                        </span>
                    </div>
                </div>

                <!-- Coluna 2: Acesso Rápido -->
                <div>
                    <h4 class="text-white font-bold mb-3 text-xs uppercase tracking-wider">Acesso Rápido</h4>
                    <ul class="space-y-2 text-xs">
                        <li><a href="<?= $basePath ?>index.php" class="hover:text-red-400 transition-colors">Filmes em Cartaz</a></li>
                        <li><a href="<?= $basePath ?>index.php#horarios" class="hover:text-red-400 transition-colors">Horários e Sessões</a></li>
                        <li><a href="<?= $basePath ?>meus_ingressos.php" class="hover:text-red-400 transition-colors">Consultar Meus Ingressos</a></li>
                        <li><a href="<?= $basePath ?>login.php" class="hover:text-red-400 transition-colors">Área do Cliente</a></li>
                    </ul>
                </div>

                <!-- Coluna 3: Gerência & Auditoria -->
                <div>
                    <h4 class="text-white font-bold mb-3 text-xs uppercase tracking-wider">Administração</h4>
                    <ul class="space-y-2 text-xs">
                        <li><a href="<?= $basePath ?>admin/index.php" class="hover:text-red-400 transition-colors">Painel Administrativo</a></li>
                        <li><a href="<?= $basePath ?>admin/filmes.php" class="hover:text-red-400 transition-colors">Gerenciar Filmes</a></li>
                        <li><a href="<?= $basePath ?>admin/sessoes.php" class="hover:text-red-400 transition-colors">Gerenciar Sessões e Preços</a></li>
                        <li><a href="<?= $basePath ?>admin/salas.php" class="hover:text-red-400 transition-colors">Gerenciar Salas</a></li>
                        <li><a href="<?= $basePath ?>admin/logs.php" class="hover:text-red-400 transition-colors">Logs de Auditoria & Histórico</a></li>
                    </ul>
                </div>

                <!-- Coluna 4: Suporte e Regras -->
                <div>
                    <h4 class="text-white font-bold mb-3 text-xs uppercase tracking-wider">Informações</h4>
                    <p class="text-xs text-slate-400 mb-2">
                        Atendimento ao cliente:<br>
                        <strong class="text-slate-200">contato@cinepimenta.com.br</strong>
                    </p>
                    <p class="text-xs text-slate-400">
                        Horário de funcionamento:<br>
                        Segunda a Domingo: 13:00 às 23:30
                    </p>
                </div>

            </div>

            <div class="border-t border-slate-800/80 mt-8 pt-6 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-4">
                <p>&copy; <?= date('Y') ?> CinePimenta - Todos os direitos reservados. ETEC Esportes.</p>
                <div class="flex items-center gap-4">
                    <span class="hover:text-slate-300">Desenvolvido com PHP estruturado, PDO & Tailwind CSS</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- INCLUSÃO OBRIGATÓRIA DOS TOASTS E MODAIS -->
    <?php require_once __DIR__ . '/toast.php'; ?>
    <?php require_once __DIR__ . '/modal.php'; ?>

    <!-- Scripts do Header / Menu Mobile -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const menuBtn = document.getElementById('mobile-menu-btn');
            const menuDrawer = document.getElementById('mobile-menu-drawer');
            if (menuBtn && menuDrawer) {
                menuBtn.addEventListener('click', () => {
                    menuDrawer.classList.toggle('hidden');
                });
            }

            // Sobrescreve alert nativo para usar o modal customizado com acessibilidade
            window.alert = function(msg) {
                showModalAlert('Atenção', msg, 'info');
            };
        });
    </script>
</body>
</html>
