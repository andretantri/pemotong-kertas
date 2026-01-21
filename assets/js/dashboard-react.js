/**
 * Dashboard React Component
 * Auto-refresh dengan React via CDN (tanpa JSX untuk kompatibilitas lebih baik)
 */

const { useState, useEffect, useCallback, Fragment } = React;
const { createElement: h } = React;

// Helper functions untuk status
function getStatusText(status) {
    const statuses = {
        'READY': 'Siap',
        'RUNNING': 'Berjalan',
        'STOPPED': 'Dihentikan',
        'DONE': 'Selesai',
        'ERROR': 'Error'
    };
    return statuses[status] || status;
}

function getStatusBadgeClass(status) {
    const classes = {
        'READY': 'bg-secondary',
        'RUNNING': 'bg-primary',
        'STOPPED': 'bg-warning',
        'DONE': 'bg-success',
        'ERROR': 'bg-danger'
    };
    return classes[status] || 'bg-secondary';
}

function formatDate(dateString) {
    const date = new Date(dateString);
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    return `${day}/${month}/${year} ${hours}:${minutes}`;
}

// Dashboard Component
function DashboardApp() {
    const [status, setStatus] = useState({
        currentJob: null,
        stats: {
            total_job: 0,
            done_job: 0,
            running_job: 0,
            total_potong: 0
        },
        recentJobs: [],
        status: 'READY',
        statusText: 'Siap',
        statusBadge: 'bg-secondary'
    });

    // Connection state
    const [connectionStatus, setConnectionStatus] = useState('CHECKING'); // CONNECTED, DISCONNECTED, CHECKING
    const [machineIp, setMachineIp] = useState('');
    const [loading, setLoading] = useState(false);
    const [loadingJobId, setLoadingJobId] = useState(null); // Track job yang sedang loading
    const [panjang, setPanjang] = useState(100);
    const [jumlah, setJumlah] = useState(10);

    // Fetch Connection Status
    const fetchConnection = useCallback(async () => {
        try {
            const response = await fetch('api/check_connection.php');
            const data = await response.json();

            if (data.success && data.data && data.data.connected) {
                setConnectionStatus('CONNECTED');
            } else {
                setConnectionStatus('DISCONNECTED');
            }
        } catch (error) {
            console.error('Error fetching connection:', error);
            setConnectionStatus('DISCONNECTED');
        }
    }, []);

    // Fetch status dari API
    const fetchStatus = useCallback(async (checkLoading = false) => {
        try {
            const response = await fetch('api/status.php');
            const data = await response.json();

            if (data.success && data.data) {
                const currentJob = data.data.current_job;
                const jobStatus = data.data.status;

                setStatus({
                    currentJob: currentJob,
                    stats: data.data.stats,
                    recentJobs: data.data.recent_jobs || [],
                    status: jobStatus,
                    statusText: data.data.status_text,
                    statusBadge: data.data.status_badge
                });

                if (data.data.machine_ip) {
                    setMachineIp(data.data.machine_ip);
                }

                // Update input values jika ada job aktif
                if (currentJob) {
                    setPanjang(currentJob.panjang_mm);
                    setJumlah(currentJob.jumlah_potong);
                }

                // Check jika sedang loading dan job sudah selesai/error
                if (checkLoading && loadingJobId) {
                    if (currentJob && currentJob.id === loadingJobId) {
                        // Job masih aktif, check status
                        if (jobStatus === 'DONE' || jobStatus === 'ERROR' || jobStatus === 'STOPPED') {
                            // Job selesai atau error - stop loading
                            setLoading(false);
                            setLoadingJobId(null);

                            if (jobStatus === 'ERROR') {
                                showAlert('Error', 'Pekerjaan mengalami error! Silakan cek koneksi ESP8266.', 'danger');
                            } else if (jobStatus === 'DONE') {
                                showAlert('Berhasil', 'Pekerjaan selesai dengan sukses!', 'success');
                            } else if (jobStatus === 'STOPPED') {
                                showAlert('Info', 'Pekerjaan dihentikan', 'warning');
                            }
                        }
                        // Jika masih RUNNING, loading tetap aktif
                    } else {
                        // Job tidak ditemukan atau sudah tidak aktif
                        // Mungkin job sudah selesai atau dihapus
                        setLoading(false);
                        setLoadingJobId(null);

                        // Check jika ada job lain yang DONE atau ERROR
                        if (data.data.recent_jobs && data.data.recent_jobs.length > 0) {
                            const foundJob = data.data.recent_jobs.find(j => j.id === loadingJobId);
                            if (foundJob) {
                                if (foundJob.status === 'DONE') {
                                    showAlert('Berhasil', 'Pekerjaan selesai!', 'success');
                                } else if (foundJob.status === 'ERROR') {
                                    showAlert('Error', 'Pekerjaan mengalami error!', 'danger');
                                }
                            }
                        }
                    }
                }
            }
        } catch (error) {
            console.error('Error fetching status:', error);
            if (checkLoading && loadingJobId) {
                setLoading(false);
                setLoadingJobId(null);
            }
        }
    }, [loadingJobId]);

    // Auto-refresh setiap 2 detik (atau 1 detik jika sedang loading)
    useEffect(() => {
        // Fetch immediately
        fetchStatus();
        fetchConnection();

        // Determine refresh interval based on loading state
        const refreshInterval = loading ? 1000 : 2000;

        // Connection polling interval (every 5 seconds)
        const connectionInterval = setInterval(() => {
            if (!document.hidden) {
                fetchConnection();
            }
        }, 5000);

        // Then fetch main status every interval
        const interval = setInterval(() => {
            if (!document.hidden) {
                fetchStatus(loading); // Pass loading flag to check status
            }
        }, refreshInterval);

        // Cleanup interval on unmount
        return () => {
            clearInterval(interval);
            clearInterval(connectionInterval);
        };
    }, [fetchStatus, fetchConnection, loading]);

    // Pause refresh when tab is hidden
    useEffect(() => {
        const handleVisibilityChange = () => {
            if (!document.hidden) {
                fetchStatus();
                fetchConnection();
            }
        };

        document.addEventListener('visibilitychange', handleVisibilityChange);
        return () => document.removeEventListener('visibilitychange', handleVisibilityChange);
    }, [fetchStatus, fetchConnection]);

    // Handle Start
    const handleStart = async () => {
        if (!panjang || !jumlah || parseInt(panjang) < 1 || parseInt(jumlah) < 1) {
            showAlert('Error', 'Panjang dan jumlah harus diisi dengan benar!', 'danger');
            return;
        }

        if (connectionStatus !== 'CONNECTED') {
            showAlert('Error', 'Mesin tidak terhubung! Pastikan ESP8266 menyala dan terhubung WiFi.', 'danger');
            fetchConnection(); // Retry connection check
            return;
        }

        if (confirm('Mulai pekerjaan potong?\nPanjang: ' + panjang + ' mm\nJumlah: ' + jumlah + ' potong')) {
            setLoading(true);
            try {
                const response = await fetch('api/start.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        panjang: parseInt(panjang),
                        jumlah: parseInt(jumlah)
                    })
                });

                const data = await response.json();

                if (data.success) {
                    // Get job ID and estimated time from response
                    const jobId = data.data && data.data.job_id ? data.data.job_id : null;
                    const estimatedSeconds = data.data && data.data.estimated_seconds ? data.data.estimated_seconds : 30;

                    if (jobId) {
                        setLoadingJobId(jobId);
                    }

                    // Start monitoring job status
                    setTimeout(() => fetchStatus(true), 500);

                    // Set timer to auto-complete job after estimated time
                    setTimeout(async () => {
                        try {
                            // Mark job as DONE in database
                            await fetch('api/complete_job.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ job_id: jobId })
                            });

                            // Stop loading and refresh
                            setLoading(false);
                            setLoadingJobId(null);
                            fetchStatus();
                            showAlert('Berhasil', 'Pekerjaan selesai!', 'success');
                        } catch (err) {
                            console.error('Error completing job:', err);
                            setLoading(false);
                            setLoadingJobId(null);
                        }
                    }, estimatedSeconds * 1000);

                    // Don't stop loading here - let timer handle it
                } else {
                    // Error starting job
                    setLoading(false);
                    setLoadingJobId(null);
                    showAlert('Error', data.message, 'danger');
                }
            } catch (error) {
                // Network or other error
                setLoading(false);
                setLoadingJobId(null);
                showAlert('Error', 'Terjadi kesalahan: ' + error, 'danger');
            }
        }
    };


    const isRunning = status.status === 'RUNNING';
    const progressPercentage = status.currentJob
        ? (status.currentJob.potong_selesai / status.currentJob.jumlah_potong) * 100
        : 0;

    // Show loading overlay when loading
    const showLoadingOverlay = loading && loadingJobId;

    // Render menggunakan React.createElement (tanpa JSX)
    return h(Fragment, null,
        // Loading Overlay
        showLoadingOverlay ? h('div', {
            className: 'position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center',
            style: {
                backgroundColor: 'rgba(0, 0, 0, 0.5)',
                zIndex: 9999
            }
        },
            h('div', {
                className: 'bg-white rounded p-4 text-center',
                style: { minWidth: '300px' }
            },
                h('div', { className: 'spinner-border text-primary mb-3', role: 'status' },
                    h('span', { className: 'visually-hidden' }, 'Loading...')
                ),
                h('h5', { className: 'mb-2' }, 'Memproses...'),
                h('p', { className: 'text-muted mb-0' }, 'Pekerjaan sedang berjalan, harap tunggu...'),
                status.currentJob ? h('div', { className: 'mt-3' },
                    h('div', { className: 'progress' },
                        h('div', {
                            className: 'progress-bar progress-bar-striped progress-bar-animated',
                            role: 'progressbar',
                            style: { width: progressPercentage + '%' },
                            'aria-valuenow': status.currentJob.potong_selesai,
                            'aria-valuemin': '0',
                            'aria-valuemax': status.currentJob.jumlah_potong
                        }, status.currentJob.potong_selesai + ' / ' + status.currentJob.jumlah_potong)
                    ),
                    h('small', { className: 'text-muted' }, 'Progress: ' + Math.round(progressPercentage) + '%')
                ) : null
            )
        ) : null,

        // Statistics Cards
        h('div', { className: 'row mb-4' },
            h('div', { className: 'col-md-3 mb-3' },
                h('div', { className: 'card stats-card' },
                    h('div', { className: 'card-body text-center' },
                        h('div', { className: 'stats-icon' },
                            h('i', { className: 'bi bi-clipboard-data' })
                        ),
                        h('div', { className: 'stats-value' }, status.stats.total_job || 0),
                        h('div', { className: 'stats-label' }, 'Total Pekerjaan')
                    )
                )
            ),
            h('div', { className: 'col-md-3 mb-3' },
                h('div', { className: 'card stats-card success' },
                    h('div', { className: 'card-body text-center' },
                        h('div', { className: 'stats-icon' },
                            h('i', { className: 'bi bi-check-circle' })
                        ),
                        h('div', { className: 'stats-value' }, status.stats.done_job || 0),
                        h('div', { className: 'stats-label' }, 'Selesai')
                    )
                )
            ),
            h('div', { className: 'col-md-3 mb-3' },
                h('div', { className: 'card stats-card warning' },
                    h('div', { className: 'card-body text-center' },
                        h('div', { className: 'stats-icon' },
                            h('i', { className: 'bi bi-play-circle' })
                        ),
                        h('div', { className: 'stats-value' }, status.stats.running_job || 0),
                        h('div', { className: 'stats-label' }, 'Berjalan')
                    )
                )
            ),
            h('div', { className: 'col-md-3 mb-3' },
                h('div', { className: 'card stats-card danger' },
                    h('div', { className: 'card-body text-center' },
                        h('div', { className: 'stats-icon' },
                            h('i', { className: 'bi bi-scissors' })
                        ),
                        h('div', { className: 'stats-value' }, status.stats.total_potong || 0),
                        h('div', { className: 'stats-label' }, 'Total Potong')
                    )
                )
            )
        ),

        // Status Card
        h('div', { className: 'card mb-4' },
            h('div', { className: 'card-header d-flex justify-content-between align-items-center' },
                h('h5', { className: 'mb-0' },
                    h('i', { className: 'bi bi-info-circle-fill me-2' }),
                    ' Status Mesin'
                ),
                // Connection Indicator
                h('div', { className: 'd-flex align-items-center' },
                    h('span', { className: 'text-muted small me-3 d-none d-sm-inline' },
                        h('i', { className: 'bi bi-hdd-network me-1' }),
                        machineIp || '-'
                    ),
                    h('span', {
                        className: `badge rounded-pill ${connectionStatus === 'CONNECTED' ? 'bg-success' : (connectionStatus === 'CHECKING' ? 'bg-secondary' : 'bg-danger')} me-2`
                    },
                        h('i', { className: `bi ${connectionStatus === 'CONNECTED' ? 'bi-wifi' : (connectionStatus === 'CHECKING' ? 'bi-hourglass-split' : 'bi-wifi-off')} me-1` }),
                        connectionStatus === 'CONNECTED' ? 'Terhubung' : (connectionStatus === 'CHECKING' ? 'Mengecek...' : 'Terputus')
                    ),
                    connectionStatus === 'DISCONNECTED' ? h('button', {
                        className: 'btn btn-sm btn-outline-secondary',
                        onClick: fetchConnection,
                        title: 'Coba Hubungkan Ulang'
                    }, h('i', { className: 'bi bi-arrow-clockwise' })) : null
                )
            ),
            h('div', { className: 'card-body' },
                h('div', { className: 'row align-items-center' },
                    h('div', { className: 'col-md-6 mb-3 mb-md-0' },
                        h('h6', { className: 'text-muted mb-2' }, 'Status Pekerjaan'),
                        h('span', { className: 'badge ' + status.statusBadge + ' fs-6 px-3 py-2' },
                            status.statusText
                        )
                    ),
                    h('div', { className: 'col-md-6' },
                        status.currentJob ? h(Fragment, null,
                            h('h6', { className: 'text-muted mb-2' }, 'Progress'),
                            h('div', { className: 'progress mb-2' },
                                h('div', {
                                    className: 'progress-bar ' + status.statusBadge,
                                    role: 'progressbar',
                                    style: { width: progressPercentage + '%' },
                                    'aria-valuenow': status.currentJob.potong_selesai,
                                    'aria-valuemin': '0',
                                    'aria-valuemax': status.currentJob.jumlah_potong
                                }, status.currentJob.potong_selesai + ' / ' + status.currentJob.jumlah_potong)
                            ),
                            h('small', { className: 'text-muted' },
                                'Panjang: ',
                                h('strong', null, status.currentJob.panjang_mm + ' mm')
                            )
                        ) : h('p', { className: 'text-muted mb-0' }, 'Tidak ada pekerjaan aktif')
                    )
                )
            )
        ),

        // Control Panel
        h('div', { className: 'card mb-4' },
            h('div', { className: 'card-header' },
                h('h5', null,
                    h('i', { className: 'bi bi-sliders' }),
                    ' Kontrol Mesin'
                )
            ),
            h('div', { className: 'card-body' },
                h('div', { className: 'row mb-4' },
                    h('div', { className: 'col-md-6 mb-3' },
                        h('label', { htmlFor: 'panjang', className: 'form-label' },
                            h('i', { className: 'bi bi-rulers' }),
                            ' Panjang Potong (mm)'
                        ),
                        h('div', { className: 'input-group input-group-lg' },
                            h('input', {
                                type: 'number',
                                className: 'form-control',
                                id: 'panjang',
                                min: '1',
                                max: '10000',
                                value: panjang,
                                onChange: (e) => setPanjang(e.target.value),
                                disabled: isRunning || loading,
                                required: true
                            }),
                            h('span', { className: 'input-group-text' }, 'mm')
                        )
                    ),
                    h('div', { className: 'col-md-6 mb-3' },
                        h('label', { htmlFor: 'jumlah', className: 'form-label' },
                            h('i', { className: 'bi bi-123' }),
                            ' Jumlah Potong'
                        ),
                        h('div', { className: 'input-group input-group-lg' },
                            h('input', {
                                type: 'number',
                                className: 'form-control',
                                id: 'jumlah',
                                min: '1',
                                max: '1000',
                                value: jumlah,
                                onChange: (e) => setJumlah(e.target.value),
                                disabled: isRunning || loading,
                                required: true
                            }),
                            h('span', { className: 'input-group-text' }, 'potong')
                        )
                    )
                ),
                h('div', { className: 'row' },
                    h('div', { className: 'col-12 mb-2' },
                        connectionStatus !== 'CONNECTED' ?
                            h('div', { className: 'alert alert-danger d-flex align-items-center' },
                                h('i', { className: 'bi bi-exclamation-triangle-fill me-2' }),
                                h('div', null, 'Mesin tidak terhubung. Periksa koneksi WiFi ESP8266.')
                            ) : null,
                        h('button', {
                            type: 'button',
                            className: 'btn btn-start btn-lg w-100',
                            onClick: handleStart,
                            disabled: isRunning || loading || connectionStatus !== 'CONNECTED'
                        },
                            loading && loadingJobId ? h(Fragment, null,
                                h('span', {
                                    className: 'spinner-border spinner-border-sm me-2',
                                    role: 'status',
                                    'aria-hidden': 'true'
                                }),
                                ' MEMPROSES...'
                            ) : h(Fragment, null,
                                h('i', { className: 'bi bi-play-fill' }),
                                ' START'
                            )
                        )
                    )
                )
            )
        ),

        // Recent Jobs Table
        h('div', { className: 'card' },
            h('div', { className: 'card-header' },
                h('h5', null,
                    h('i', { className: 'bi bi-clock-history' }),
                    ' Pekerjaan Terakhir'
                )
            ),
            h('div', { className: 'card-body' },
                h('div', { className: 'table-responsive' },
                    h('table', { className: 'table' },
                        h('thead', null,
                            h('tr', null,
                                h('th', null, 'ID'),
                                h('th', null, 'Panjang'),
                                h('th', null, 'Jumlah'),
                                h('th', null, 'Selesai'),
                                h('th', null, 'Status'),
                                h('th', null, 'Waktu')
                            )
                        ),
                        h('tbody', null,
                            status.recentJobs.length === 0 ?
                                h('tr', null,
                                    h('td', { colSpan: 6, className: 'text-center text-muted' }, 'Belum ada pekerjaan')
                                ) :
                                status.recentJobs.map((job) =>
                                    h('tr', { key: job.id },
                                        h('td', null, '#' + job.id),
                                        h('td', null, job.panjang_mm + ' mm'),
                                        h('td', null, job.jumlah_potong),
                                        h('td', null, job.potong_selesai),
                                        h('td', null,
                                            h('span', { className: 'badge ' + getStatusBadgeClass(job.status) },
                                                getStatusText(job.status)
                                            )
                                        ),
                                        h('td', null, formatDate(job.created_at))
                                    )
                                )
                        )
                    )
                )
            )
        )
    );
}

// Render React App ketika DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', renderApp);
} else {
    renderApp();
}

function renderApp() {
    const container = document.getElementById('react-dashboard');
    if (container) {
        const root = ReactDOM.createRoot(container);
        root.render(h(DashboardApp));
    }
}
