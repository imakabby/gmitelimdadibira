<?php
// includes/admin-footer.php
?>
            </div><!-- end .admin-content -->
        </div><!-- end .admin-main -->
    </div><!-- end .admin-container -->
    
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- SweetAlert2 Library -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        // Highlight current page in sidebar
        document.addEventListener('DOMContentLoaded', function() {
            const currentPath = window.location.pathname;
            const sidebarLinks = document.querySelectorAll('.admin-sidebar a');
            
            sidebarLinks.forEach(link => {
                if (currentPath === link.getAttribute('href') || 
                    (currentPath.includes(link.getAttribute('href')) && link.getAttribute('href') !== '/admin/dashboard.php')) {
                    link.classList.add('active');
                }
            });

            // Alert dismiss functionality
            const alertDismissButtons = document.querySelectorAll('.alert-dismiss');
            alertDismissButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const alert = this.closest('.alert');
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-20px)';
                    setTimeout(() => {
                        alert.style.display = 'none';
                    }, 300);
                });
            });
            
            // Responsif tabel untuk mobile
            const responsiveTables = document.querySelectorAll('.table-responsive');
            if (responsiveTables) {
                responsiveTables.forEach(table => {
                    if (window.innerWidth < 768) {
                        table.style.overflowX = 'auto';
                    }
                });
            }
            
            // Tambahkan class untuk form pada mobile
            const formGroups = document.querySelectorAll('.form-group.row');
            if (formGroups && window.innerWidth < 768) {
                formGroups.forEach(group => {
                    group.classList.add('mb-3');
                    const label = group.querySelector('label');
                    if (label) {
                        label.style.marginBottom = '8px';
                    }
                });
            }
            
            // Media Manager - Copy URL
            const copyUrlBtns = document.querySelectorAll('.copy-url-btn');
            if (copyUrlBtns) {
                copyUrlBtns.forEach(btn => {
                    btn.addEventListener('click', function() {
                        const url = this.getAttribute('data-url');
                        navigator.clipboard.writeText(url).then(function() {
                            const originalText = btn.innerHTML;
                            btn.innerHTML = '<i class="fas fa-check"></i><span class="d-none d-md-inline"> Tersalin!</span>';
                            setTimeout(() => {
                                btn.innerHTML = originalText;
                            }, 2000);
                        });
                    });
                });
            }
        });
    </script>
</body>
</html>