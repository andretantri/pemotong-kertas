/**
 * DashboardKit - Custom JavaScript
 * HMI Pemotong Kertas
 */

// Sidebar Toggle for Mobile
document.addEventListener('DOMContentLoaded', function () {
    // Mobile sidebar toggle
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.sidebar');

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function () {
            sidebar.classList.toggle('show');
        });
    }

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function (e) {
        if (window.innerWidth <= 768) {
            if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                sidebar.classList.remove('show');
            }
        }
    });

    // Auto refresh handler dengan AJAX (tanpa reload halaman)
    let refreshInterval = null;

    // Start auto refresh if on dashboard
    if (document.getElementById('btnStart')) {
        // Update status setiap 3 detik menggunakan AJAX
        function updateStatus() {
            if (document.hidden) return;

            fetch('api/status.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data) {
                        const job = data.data.current_job;
                        const stats = data.data.stats;

                        // Update status badge
                        const statusBadge = document.getElementById('statusBadge');
                        if (statusBadge) {
                            statusBadge.className = 'badge ' + data.data.status_badge + ' fs-6 px-3 py-2';
                            statusBadge.textContent = data.data.status_text;
                        }

                        // Update progress bar
                        const progressBar = document.querySelector('.progress-bar');
                        const progressContainer = document.querySelector('.progress');
                        const panjangInfo = document.getElementById('panjangInfo');

                        if (job) {
                            if (progressBar && progressContainer) {
                                progressContainer.style.display = 'block';
                                const percentage = (job.potong_selesai / job.jumlah_potong) * 100;
                                progressBar.style.width = percentage + '%';
                                progressBar.setAttribute('aria-valuenow', job.potong_selesai);
                                progressBar.setAttribute('aria-valuemax', job.jumlah_potong);
                                progressBar.textContent = job.potong_selesai + ' / ' + job.jumlah_potong;
                                progressBar.className = 'progress-bar ' + data.data.status_badge;
                            }

                            // Update panjang info
                            if (panjangInfo) {
                                panjangInfo.innerHTML = 'Panjang: <strong>' + job.panjang_mm + ' mm</strong>';
                            }

                            // Update button states
                            const btnStart = document.getElementById('btnStart');
                            const btnStop = document.getElementById('btnStop');
                            const panjangInput = document.getElementById('panjang');
                            const jumlahInput = document.getElementById('jumlah');

                            if (job.status === 'RUNNING') {
                                if (btnStart) btnStart.disabled = true;
                                if (btnStop) btnStop.disabled = false;
                                if (panjangInput) panjangInput.readOnly = true;
                                if (jumlahInput) jumlahInput.readOnly = true;
                            } else {
                                if (btnStart) btnStart.disabled = false;
                                if (btnStop) btnStop.disabled = true;
                                if (panjangInput) panjangInput.readOnly = false;
                                if (jumlahInput) jumlahInput.readOnly = false;
                            }
                        } else {
                            // No active job
                            if (progressContainer) {
                                progressContainer.style.display = 'none';
                            }
                            if (panjangInfo) {
                                panjangInfo.textContent = 'Tidak ada pekerjaan aktif';
                            }

                            const btnStart = document.getElementById('btnStart');
                            const btnStop = document.getElementById('btnStop');
                            const panjangInput = document.getElementById('panjang');
                            const jumlahInput = document.getElementById('jumlah');

                            if (btnStart) btnStart.disabled = false;
                            if (btnStop) btnStop.disabled = true;
                            if (panjangInput) panjangInput.readOnly = false;
                            if (jumlahInput) jumlahInput.readOnly = false;
                        }

                        // Update statistics cards
                        if (stats) {
                            const totalJobEl = document.getElementById('totalJob');
                            const doneJobEl = document.getElementById('doneJob');
                            const runningJobEl = document.getElementById('runningJob');
                            const totalPotongEl = document.getElementById('totalPotong');

                            if (totalJobEl) totalJobEl.textContent = stats.total_job || 0;
                            if (doneJobEl) doneJobEl.textContent = stats.done_job || 0;
                            if (runningJobEl) runningJobEl.textContent = stats.running_job || 0;
                            if (totalPotongEl) totalPotongEl.textContent = stats.total_potong || 0;
                        }
                    }
                })
                .catch(error => {
                    console.error('Error updating status:', error);
                });
        }

        // Make updateStatus available globally
        window.updateStatus = updateStatus;

        // Update immediately on load
        updateStatus();

        // Then update every 3 seconds
        refreshInterval = setInterval(updateStatus, 3000);

        // Clear interval when page is hidden
        document.addEventListener('visibilitychange', function () {
            if (document.hidden) {
                if (refreshInterval) clearInterval(refreshInterval);
            } else {
                updateStatus(); // Update immediately when visible
                refreshInterval = setInterval(updateStatus, 3000);
            }
        });
    }

    // Fade in animation
    const cards = document.querySelectorAll('.card');
    cards.forEach((card, index) => {
        card.style.animationDelay = (index * 0.1) + 's';
        card.classList.add('fade-in');
    });
});

// Show Alert Modal
let alertModalInstance = null;

function showAlert(title, message, type) {
    const modal = document.getElementById('alertModal');
    if (!modal) return;

    const modalTitle = document.getElementById('alertModalTitle');
    const modalBody = document.getElementById('alertModalBody');

    if (modalTitle) modalTitle.textContent = title;
    if (modalBody) {
        modalBody.innerHTML = '<div class="alert alert-' + type + ' mb-0">' + message + '</div>';
    }

    // Reuse existing instance or create new one
    if (!alertModalInstance) {
        alertModalInstance = new bootstrap.Modal(modal, {
            backdrop: true,
            keyboard: true
        });

        // Clean up backdrop when modal is hidden
        modal.addEventListener('hidden.bs.modal', function () {
            // Remove any leftover backdrops
            const backdrops = document.querySelectorAll('.modal-backdrop');
            backdrops.forEach(backdrop => backdrop.remove());

            // Remove modal-open class from body
            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('padding-right');
            document.body.style.removeProperty('overflow');
        });
    }

    alertModalInstance.show();
}

// Hide Alert Modal
function hideAlert() {
    if (alertModalInstance) {
        alertModalInstance.hide();
    }
}

// Format number with thousand separator
function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

// Format date
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

