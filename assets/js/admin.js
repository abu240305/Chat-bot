document.addEventListener('DOMContentLoaded', function () {
    var container = document.querySelector('.admin-container');
    var hamburger = document.querySelector('.hamburger');
    var backdrop = document.querySelector('.sidebar-backdrop');

    if (container && hamburger) {
        function openSidebar() { container.classList.add('sidebar-open'); }
        function closeSidebar() { container.classList.remove('sidebar-open'); }

        hamburger.addEventListener('click', openSidebar);

        if (backdrop) {
            backdrop.addEventListener('click', closeSidebar);
        }

        container.querySelectorAll('.sidebar-menu a, .logout-btn').forEach(function (link) {
            link.addEventListener('click', closeSidebar);
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeSidebar();
        });
    }

    /* -----------------------------------------------------------------
       Toast notification (auto-hide 3 detik)
       ----------------------------------------------------------------- */
    function showToast(message, type) {
        var icon = type === 'error' ? '✕' : '✓';
        var toast = document.createElement('div');
        toast.className = 'toast-notif ' + (type === 'error' ? 'error' : 'success');
        toast.innerHTML = '<span class="toast-icon">' + icon + '</span><span>' + message + '</span>';
        document.body.appendChild(toast);

        requestAnimationFrame(function () {
            toast.classList.add('show');
        });

        setTimeout(function () {
            toast.classList.remove('show');
            setTimeout(function () { toast.remove(); }, 350);
        }, 3000);
    }

    /* Tampilkan pesan sukses/error server sebagai toast lalu hilangkan bloknya */
    document.querySelectorAll('.msg-success, .msg-error').forEach(function (msg) {
        var text = msg.textContent.trim();
        var type = msg.classList.contains('msg-error') ? 'error' : 'success';
        if (text) showToast(text, type);
        msg.remove();
    });

    /* -----------------------------------------------------------------
       Indikator scroll tabel (panah kanan bila bisa discroll)
       ----------------------------------------------------------------- */
    document.querySelectorAll('.table-wrap').forEach(function (wrap) {
        function checkScroll() {
            wrap.classList.toggle('can-scroll', wrap.scrollWidth > wrap.clientWidth);
        }
        checkScroll();
        wrap.addEventListener('scroll', checkScroll);
        window.addEventListener('resize', checkScroll);
    });

    /* -----------------------------------------------------------------
       Truncate -> Lihat Selengkapnya (accordion)
       ----------------------------------------------------------------- */
    document.querySelectorAll('.toggle-more').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var cell = this.closest('.cell-main');
            if (!cell) return;
            var text = cell.querySelector('.cell-truncate');
            if (!text) return;
            var expanded = text.classList.toggle('expanded');
            this.textContent = expanded ? 'Tutup' : 'Lihat Selengkapnya';
        });
    });

    /* -----------------------------------------------------------------
       Modal: tandai dirty saat ada input (guard di closeModal halaman)
       ----------------------------------------------------------------- */
    document.querySelectorAll('.modal-overlay').forEach(function (modal) {
        modal.querySelectorAll('input, textarea, select').forEach(function (el) {
            el.addEventListener('input', function () {
                modal.classList.add('dirty');
                var row = el.closest('.form-row');
                if (row) row.classList.remove('is-invalid');
            });
        });
    });

    /* Validasi inline form Q&A (sebelum submit) */
    var qaForm = document.querySelector('#qaModal form');
    if (qaForm) {
        qaForm.addEventListener('submit', function (e) {
            var ok = true;
            qaForm.querySelectorAll('textarea[required]').forEach(function (t) {
                var row = t.closest('.form-row');
                if (t.value.trim() === '') {
                    if (row) row.classList.add('is-invalid');
                    ok = false;
                } else if (row) {
                    row.classList.remove('is-invalid');
                }
            });
            if (!ok) {
                e.preventDefault();
                showToast('Pertanyaan dan jawaban wajib diisi.', 'error');
            }
        });
    }

    /* -----------------------------------------------------------------
       Drag & drop + preview file upload
       ----------------------------------------------------------------- */
    var uploadModal = document.getElementById('uploadModal');
    if (uploadModal) {
        var box = uploadModal.querySelector('.upload-box');
        var fileInput = uploadModal.querySelector('input[type="file"]');
        var preview = uploadModal.querySelector('.file-preview');

        function formatSize(bytes) {
            if (bytes >= 1048576) return (bytes / 1048576).toFixed(2) + ' MB';
            return Math.max(1, Math.round(bytes / 1024)) + ' KB';
        }

        function showPreview(file) {
            if (!file) return;
            var nameEl = preview.querySelector('.fp-name');
            var sizeEl = preview.querySelector('.fp-size');
            if (nameEl) nameEl.textContent = file.name;
            if (sizeEl) sizeEl.textContent = formatSize(file.size);
            preview.classList.add('show');
        }

        if (box && fileInput && preview) {
            ['dragenter', 'dragover'].forEach(function (ev) {
                box.addEventListener(ev, function (e) {
                    e.preventDefault();
                    box.classList.add('dragover');
                });
            });

            ['dragleave', 'drop'].forEach(function (ev) {
                box.addEventListener(ev, function (e) {
                    e.preventDefault();
                    box.classList.remove('dragover');
                });
            });

            box.addEventListener('drop', function (e) {
                var files = e.dataTransfer.files;
                if (files && files.length) {
                    fileInput.files = files;
                    showPreview(files[0]);
                }
            });

            fileInput.addEventListener('change', function () {
                if (this.files && this.files.length) {
                    showPreview(this.files[0]);
                } else {
                    preview.classList.remove('show');
                }
            });

            var removeBtn = preview.querySelector('.fp-remove');
            if (removeBtn) {
                removeBtn.addEventListener('click', function () {
                    fileInput.value = '';
                    preview.classList.remove('show');
                    uploadModal.classList.remove('dirty');
                });
            }
        }
    }
});