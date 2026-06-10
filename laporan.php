<?php 
// 1. Memanggil header yang berisi session, koneksi database, dan struktur atas HTML/Bootstrap
include 'includes/header.php'; 

// =========================================================================
// --- 1. PROSES LOGIKA PHP & FILTERING TANGGAL ---
// =========================================================================

// 2. MENENTUKAN RENTANG TANGGAL DEFAULT (DEFAULT DATE RANGE)
// Menggunakan Null Coalescing Operator (??). Jika $_GET['tgl_mulai'] kosong (user belum menekan tombol filter),
// maka gunakan date('Y-m-01') yang berarti tanggal 1 di bulan dan tahun saat ini.
$tgl_mulai = $_GET['tgl_mulai'] ?? date('Y-m-01');

// Jika $_GET['tgl_selesai'] kosong, gunakan date('Y-m-d') yang berarti tanggal hari ini.
$tgl_selesai = $_GET['tgl_selesai'] ?? date('Y-m-d');

// 3. KUERI RELASIONAL (JOIN) UNTUK MENGAMBIL DATA MUTASI
// Mengambil data gabungan dari tabel 'stock_mutations' (alias m) dan tabel 'products' (alias p).
$query_laporan = "SELECT m.*, p.product_name, p.product_code, p.unit 
                  FROM stock_mutations m 
                  JOIN products p ON m.product_id = p.id 
                  WHERE m.date BETWEEN '$tgl_mulai' AND '$tgl_selesai' 
                  ORDER BY m.id DESC";

// Menjalankan kueri ke database
$data_laporan = mysqli_query($conn, $query_laporan);
?>

<style>
/* Instruksi CSS di dalam @media print hanya akan dibaca oleh komputer saat tombol Print ditekan atau disimpan ke PDF */
@media print {
    /* Menyembunyikan elemen antarmuka web yang tidak perlu dicetak ke kertas (sidebar, kotak filter, tombol, garis hr) */
    .sidebar, .filter-box, .btn-print, hr, .badge-akses {
        display: none !important;
    }
    
    /* Memaksa area konten utama untuk merentang penuh 100% lebar kertas cetakan */
    .main-content, .col-md-9 {
        width: 100% !important;
        margin-left: 0 !important;
        padding: 0 !important;
    }
    
    /* Mengubah warna latar web menjadi putih bersih agar hemat tinta printer */
    body {
        background-color: #fff !important;
        font-size: 12px;
    }
    
    /* Menghilangkan bayangan dan border dari kartu Bootstrap agar terlihat seperti dokumen kertas murni */
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    .table {
        width: 100% !important;
        border-collapse: collapse !important;
    }
    
    /* Menampilkan Kop Laporan yang sebelumnya disembunyikan di layar monitor */
    .print-header {
        display: block !important;
    }
}

/* Memastikan Kop Laporan tetap tersembunyi selama admin hanya melihat di layar monitor biasa */
.print-header {
    display: none; 
}
</style>

<div class="print-header text-center mb-4 border-bottom pb-3">
    <h2 class="fw-bold m-0">WARUNG KELONTONG</h2>
    <p class="m-0 text-muted">Laporan Rekapitulasi Mutasi Keluar Masuk Barang</p>
    <small class="fw-bold">Periode: <?= date('d/m/Y', strtotime($tgl_mulai)); ?> s/d <?= date('d/m/Y', strtotime($tgl_selesai)); ?></small>
</div>

<div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2 fw-bold text-dark m-0">Laporan Mutasi Barang</h1>
    
    <div class="d-flex gap-2 no-print">
        <button onclick="window.print()" class="btn btn-success fw-bold rounded shadow-sm">
            🖨️ Cetak Laporan / PDF
        </button>
        
        <button onclick="exportExcelMutasi()" class="btn btn-primary fw-bold rounded shadow-sm">
            📊 Cetak Excel
        </button>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4 filter-box">
    <div class="card-body">
        <h5 class="fw-bold text-secondary mb-3">Filter Periode Laporan</h5>
        <form action="" method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-bold">Dari Tanggal</label>
                <input type="date" name="tgl_mulai" class="form-control" value="<?= $tgl_mulai; ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold">Sampai Tanggal</label>
                <input type="date" name="tgl_selesai" class="form-control" value="<?= $tgl_selesai; ?>">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100 fw-bold">Cari Data Mutasi</button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 filter-box">
        <h6 class="m-0 fw-bold text-secondary">Histori Transakis Keluar Masuk Sembako</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped align-middle m-0">
                <thead class="table-light">
                    <tr>
                        <th>Tanggal</th>
                        <th>Kode Barang</th>
                        <th>Nama Barang</th>
                        <th>Tipe Mutasi</th>
                        <th>Jumlah / Kuantitas</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($data_laporan) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($data_laporan)): ?>
                        <tr>
                            <td><?= date('d-m-Y', strtotime($row['date'])); ?></td>
                            <td><code><?= $row['product_code']; ?></code></td>
                            <td class="fw-semibold text-dark"><?= $row['product_name']; ?></td>
                            <td>
                                <span class="badge <?= $row['type'] == 'masuk' ? 'bg-success' : 'bg-danger'; ?> text-uppercase px-2">
                                    <?= $row['type']; ?>
                                </span>
                            </td>
                            <td class="fw-bold"><?= $row['quantity']; ?> <?= $row['unit']; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">Tidak ada rekaman transaksi pada rentang tanggal terpilih.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
function exportExcelMutasi() {
    // Mencari tabel di halaman
    var table = document.querySelector(".table");
    
    // Mengubah tabel menjadi format Excel
    var wb = XLSX.utils.table_to_book(table, {sheet: "Laporan Mutasi"});
    
    // Proses download file
    XLSX.writeFile(wb, 'Laporan_Mutasi_Warung.xlsx');
}
</script>

<?php include 'includes/footer.php'; ?>
<?php include 'includes/footer.php'; ?>