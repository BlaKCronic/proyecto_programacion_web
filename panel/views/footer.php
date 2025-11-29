</div>
            </div>

            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; Amazon Lite <?= date('Y') ?></span>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <a class="scroll-to-top rounded" href="#page-top">
        <i class="bi bi-arrow-up"></i>
    </a>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
    <script src="../../js/utils/validaciones.js"></script>
    <script src="../../js/panel/admin.js"></script>
    <script>
    (function() {
        var btn = document.getElementById('sidebarToggle');
        var btnTop = document.getElementById('sidebarToggleTop');
        var sidebar = document.querySelector('.sidebar');

        function toggleSidebar() {
            document.body.classList.toggle('sidebar-toggled');
            if(sidebar) sidebar.classList.toggle('toggled');
        }

        if(btn) btn.addEventListener('click', toggleSidebar);
        if(btnTop) btnTop.addEventListener('click', toggleSidebar);
    })();
    </script>
</body>
</html>