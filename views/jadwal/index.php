<?php
/**
 * Halaman untuk jadwal peminjaman
 */

// Load model
require_once('models/Jadwal.php');
require_once('models/Sarpras.php');

// Inisialisasi model
$jadwalModel = new Jadwal($conn);
$sarprasModel = new Sarpras($conn);

// Get data sarpras untuk filter
$sarprasList = $sarprasModel->getAllSarpras(0, 1000);

// Filter sarpras
$sarpras_id = isset($_GET['sarpras_id']) ? (int)$_GET['sarpras_id'] : 0;

// Get tanggal default (bulan ini)
$currentMonth = date('m');
$currentYear = date('Y');
$firstDay = date('Y-m-01');
$lastDay = date('Y-m-t');

// Get data jadwal
$jadwal = $jadwalModel->getJadwalByDate($firstDay, $lastDay, $sarpras_id ?: null);

// Convert data untuk format kalender
$events = [];
foreach ($jadwal as $item) {
    // Set warna berdasarkan status
    $color = '#4E382B'; // Default
    if ($item['status'] == 'Disetujui') {
        $color = '#3498db';
    } else if ($item['status'] == 'Dipinjam') {
        $color = '#4E382B';
    } else if ($item['status'] == 'Terlambat') {
        $color = '#e74c3c';
    }
    
    $events[] = [
        'id' => $item['id'],
        'title' => $item['title'],
        'start' => $item['start'],
        'end' => $item['end'],
        'color' => $color,
        'url' => ROOT_URL . '/peminjaman/detail/' . $item['id'],
        'extendedProps' => [
            'kode' => $item['kode'],
            'peminjam' => $item['peminjam'],
            'status' => $item['status'],
            'sarpras_id' => $item['sarpras_id'],
            'tujuan' => $item['tujuan']
        ]
    ];
}

// Encode events untuk JavaScript
$eventsJson = json_encode($events);

// Jadwal hari ini
$jadwalToday = $jadwalModel->getJadwalHariIni();
?>

<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Jadwal Peminjaman</h1>
        <a href="<?php echo ROOT_URL; ?>/peminjaman/add" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i> Tambah Peminjaman
        </a>
    </div>
    
    <!-- Alert message -->
    <?php showAlert(); ?>
    
    <!-- Filter -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="<?php echo ROOT_URL; ?>/jadwal" class="row g-3">
                <div class="col-md-8">
                    <label for="sarpras_id" class="form-label">Filter Sarpras</label>
                    <select name="sarpras_id" id="sarpras_id" class="form-select">
                        <option value="">-- Semua Sarpras --</option>
                        <?php foreach ($sarprasList as $sarpras): ?>
                        <option value="<?php echo $sarpras['id']; ?>" <?php echo ($sarpras_id == $sarpras['id']) ? 'selected' : ''; ?>>
                            <?php echo $sarpras['nama']; ?> (<?php echo $sarpras['kode']; ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-filter me-2"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Calendar -->
    <div class="row g-3">
        <div class="col-lg-9">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-calendar-event me-2"></i> Kalender Peminjaman</h5>
                </div>
                <div class="card-body">
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3">
            <!-- Jadwal Hari Ini -->
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-calendar-check me-2"></i> Jadwal Hari Ini</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (count($jadwalToday) > 0): ?>
                    <div class="list-group">
                        <?php foreach ($jadwalToday as $item): ?>
                        <a href="<?php echo ROOT_URL; ?>/peminjaman/detail/<?php echo $item['id']; ?>" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1 text-truncate" style="max-width: 150px;"><?php echo $item['nama_sarpras']; ?></h6>
                                <small>
                                    <span class="badge rounded-pill 
                                        <?php 
                                            if ($item['status'] == 'Disetujui') echo 'bg-info';
                                            else if ($item['status'] == 'Dipinjam') echo 'bg-primary';
                                            else if ($item['status'] == 'Terlambat') echo 'bg-danger';
                                        ?>">
                                        <?php echo $item['status']; ?>
                                    </span>
                                </small>
                            </div>
                            <p class="mb-1 text-truncate" style="max-width: 200px;"><?php echo $item['nama_peminjam']; ?></p>
                            <small class="text-muted">
                                <i class="bi bi-clock me-1"></i> <?php echo date('H:i', strtotime($item['tanggal_pinjam'])); ?> - <?php echo date('H:i', strtotime($item['tanggal_kembali'])); ?>
                            </small>
                        </a>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-4">
                        <i class="bi bi-calendar2-check text-muted" style="font-size: 2rem;"></i>
                        <p class="mt-2 mb-0 text-muted">Tidak ada jadwal peminjaman hari ini</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Legenda -->
            
        </div>
    </div>
</div>

<!-- Modal Detail Event -->
<div class="modal fade" id="eventModal" tabindex="-1" aria-labelledby="eventModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="eventModalLabel">Detail Peminjaman</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table table-hover">
                    <tr>
                        <th width="35%">Kode Peminjaman</th>
                        <td width="65%" id="eventKode"></td>
                    </tr>
                    <tr>
                        <th>Nama Sarpras</th>
                        <td id="eventTitle"></td>
                    </tr>
                    <tr>
                        <th>Peminjam</th>
                        <td id="eventPeminjam"></td>
                    </tr>
                    <tr>
                        <th>Tanggal Pinjam</th>
                        <td id="eventStart"></td>
                    </tr>
                    <tr>
                        <th>Tanggal Kembali</th>
                        <td id="eventEnd"></td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td id="eventStatus"></td>
                    </tr>
                    <tr>
                        <th>Tujuan</th>
                        <td id="eventTujuan"></td>
                    </tr>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <a href="#" class="btn btn-primary" id="eventDetailLink">
                    <i class="bi bi-eye me-1"></i> Lihat Detail
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Load FullCalendar -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/locales/id.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize calendar with responsive settings
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: window.innerWidth < 768 ? 'listMonth' : 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listMonth'
        },
        locale: 'id',
        events: <?php echo $eventsJson; ?>,
        eventClick: function(info) {
            info.jsEvent.preventDefault(); // don't let the browser navigate
            
            // Set modal content
            document.getElementById('eventTitle').textContent = info.event.title.split(' - ')[0];
            document.getElementById('eventKode').textContent = info.event.extendedProps.kode;
            document.getElementById('eventPeminjam').textContent = info.event.extendedProps.peminjam;
            document.getElementById('eventStart').textContent = new Date(info.event.start).toLocaleDateString('id-ID', {
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric'
            });
            document.getElementById('eventEnd').textContent = new Date(info.event.end).toLocaleDateString('id-ID', {
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric'
            });
            
            // Set status with badge
            var statusBadge = '';
            switch(info.event.extendedProps.status) {
                case 'Disetujui':
                    statusBadge = '<span class="badge rounded-pill bg-info">Disetujui</span>';
                    break;
                case 'Dipinjam':
                    statusBadge = '<span class="badge rounded-pill bg-primary">Dipinjam</span>';
                    break;
                case 'Terlambat':
                    statusBadge = '<span class="badge rounded-pill bg-danger">Terlambat</span>';
                    break;
                default:
                    statusBadge = '<span class="badge rounded-pill bg-secondary">' + info.event.extendedProps.status + '</span>';
            }
            document.getElementById('eventStatus').innerHTML = statusBadge;
            document.getElementById('eventTujuan').textContent = info.event.extendedProps.tujuan;
            
            // Set detail link
            document.getElementById('eventDetailLink').href = info.event.url;
            
            // Show modal
            var modal = new bootstrap.Modal(document.getElementById('eventModal'));
            modal.show();
        },
        eventTimeFormat: {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        },
        dayMaxEvents: true,
        businessHours: {
            daysOfWeek: [1, 2, 3, 4, 5], // Monday - Friday
            startTime: '08:00',
            endTime: '16:00',
        },
        height: 'auto'
    });
    
    calendar.render();
    
    // Filter change auto submit
    document.getElementById('sarpras_id').addEventListener('change', function() {
        this.form.submit();
    });
    
    // Handle window resize to change calendar view
    window.addEventListener('resize', function() {
        var view = window.innerWidth < 768 ? 'listMonth' : 'dayGridMonth';
        calendar.changeView(view);
    });
});
</script>

<style>
/* Additional styles for calendar */
.fc-event {
    cursor: pointer;
    border: none;
    padding: 2px 4px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.fc-day-today {
    background-color: rgba(211, 206, 200, 0.3) !important;
}

.fc-header-toolbar {
    margin-bottom: 1rem !important;
}

.fc-toolbar-title {
    font-size: 1.25rem !important;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .fc-header-toolbar {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .fc-toolbar-title {
        font-size: 1.1rem !important;
    }
    
    .fc-button {
        padding: 0.2rem 0.5rem !important;
        font-size: 0.8rem !important;
    }
}
</style>
