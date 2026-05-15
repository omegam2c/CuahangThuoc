        </div> <!-- End auth-card -->
    </div> <!-- End auth-wrapper -->
    
    <!-- SweetAlert2 cho UI mượt mà -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            <?php if (!empty($success)): ?>
                Swal.fire({
                    icon: 'success',
                    title: 'Thành công',
                    text: '<?= addslashes(htmlspecialchars($success)) ?>',
                    timer: 2500,
                    showConfirmButton: false
                });
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <?php 
                    $errorMsg = '';
                    foreach ((array)$errors as $error) {
                        $errorMsg .= '• ' . addslashes(htmlspecialchars($error)) . '<br>';
                    }
                ?>
                Swal.fire({
                    icon: 'error',
                    title: 'Có lỗi xảy ra',
                    html: '<?= $errorMsg ?>',
                    confirmButtonText: 'Đóng'
                });
            <?php endif; ?>

            // Hiệu ứng mượt mà khi submit form
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function() {
                    const btn = this.querySelector('button[type="submit"]');
                    if (btn) {
                        btn.disabled = true;
                        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Đang xử lý...';
                    }
                });
            });
        });
    </script>
</body>
</html>
