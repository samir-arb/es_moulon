    </div> <!-- content-area -->
</div> <!-- main-content -->
</div> <!-- dashboard-container -->

<script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleBtn = document.getElementById('menu-toggle');
            const sidebar = document.getElementById('sidebar');

            if (!toggleBtn || !sidebar) {
                console.warn('Bouton ou sidebar introuvable');
                return;
            }

            toggleBtn.addEventListener('click', function(e) {
                e.preventDefault();
                sidebar.classList.toggle('collapsed');
                console.log('toggle sidebar:', sidebar.className);
            });
        });
    </script>
</body>
</html>
