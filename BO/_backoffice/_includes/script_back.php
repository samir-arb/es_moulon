    </div> <!-- content-area -->
    </div> <!-- main-content -->
    </div> <!-- dashboard-container -->

    <script>
        //script admin.php
        // Petit log pour vérifier que le fichier est bien inclus
        console.log('[script_back] chargé');

        // ----- Sidebar toggle -----
        document.addEventListener('DOMContentLoaded', function() {
            const toggleBtn = document.getElementById('menu-toggle');
            const sidebar = document.getElementById('sidebar');

            if (!toggleBtn || !sidebar) {
                console.warn('[script_back] Bouton (#menu-toggle) ou sidebar (#sidebar) introuvable sur cette page.');
                return;
            }

            toggleBtn.addEventListener('click', function(e) {
                e.preventDefault();
                sidebar.classList.toggle('collapsed');
                console.log('[script_back] toggle sidebar:', sidebar.className);
            });
        });
    </script>

    </body>

    </html>