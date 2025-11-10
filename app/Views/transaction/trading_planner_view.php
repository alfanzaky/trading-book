<?= $this->extend('layouts/main'); ?>
<?= $this->section('content'); ?>

<div class="container-xxl flex-grow-1 container-p-y">

  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-2">
    <h4 class="fw-bold m-0">
      <span class="text-muted fw-light">Transaction /</span> Trading Planner
    </h4>
    <div class="text-muted small d-none d-md-block">
      <i class="bx bx-info-circle me-1"></i>
      Rencanakan target harianmu secara realistis untuk hasil optimal.
    </div>
  </div>

  <!-- Main Content -->
  <div class="row g-4 align-items-stretch">
    <!-- Left: Form + Evaluasi -->
    <div class="col-lg-6 d-flex flex-column" style="gap: 1rem;">
      
      <!-- Card: Form Rencana -->
      <div class="card border-0 shadow-sm flex-fill">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
          <h6 class="mb-0 fw-bold text-primary">
            <i class="bx bx-target-lock me-1"></i> Rencana Harian
          </h6>
          <span class="badge bg-label-primary px-3 py-2 small">Perencanaan</span>
        </div>

        <div class="card-body">
          <!-- Pilih Akun -->
          <div class="mb-3">
            <label class="form-label small text-muted fw-semibold">
              <i class="bx bx-wallet me-1"></i> Pilih Akun
            </label>
            <select id="planAccount" class="form-select form-select-sm shadow-sm border-1">
              <option value="">— Pilih akun aktif —</option>
              <?php foreach ($accounts as $acc): ?>
                <option value="<?= $acc['id'] ?>">
                  <?= esc($acc['account_name']) ?> — <?= esc($acc['login_id']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Tanggal -->
          <div class="mb-3">
            <label class="form-label small text-muted fw-semibold">
              <i class="bx bx-calendar-event me-1"></i> Tanggal Rencana
            </label>
            <input type="date" id="planDate" class="form-control form-control-sm shadow-sm">
            <small class="text-muted fst-italic">Pilih tanggal target trading kamu.</small>
          </div>

          <!-- Target & Max Loss -->
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label small text-muted fw-semibold">🎯 Target Profit (%)</label>
              <div class="input-group input-group-sm">
                <input type="number" id="planTarget" class="form-control" placeholder="2">
                <span class="input-group-text bg-label-success text-success fw-bold">%</span>
              </div>
              <small class="text-muted fst-italic">Persentase profit yang ingin dicapai.</small>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label small text-muted fw-semibold">⚠️ Max Loss (%)</label>
              <div class="input-group input-group-sm">
                <input type="number" id="planLoss" class="form-control" placeholder="1">
                <span class="input-group-text bg-label-danger text-danger fw-bold">%</span>
              </div>
              <small class="text-muted fst-italic">Batas kerugian maksimal untuk hari itu.</small>
            </div>
          </div>

          <!-- Tombol Otomatis & Simpan -->
          <div class="mt-3 d-flex justify-content-between align-items-center">
            <button id="autoSuggestBtn" type="button" class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1">
              <i class="bx bx-brain"></i> Hitung Otomatis
            </button>
            <button id="savePlanBtn" type="button" class="btn btn-primary btn-sm d-flex align-items-center gap-1">
              <i class="bx bx-save"></i> Simpan Rencana
            </button>
          </div>

          <!-- Notes -->
          <div class="mt-3">
            <label class="form-label small text-muted fw-semibold">
              <i class="bx bx-note me-1"></i> Catatan Tambahan
            </label>
            <textarea id="planNotes" class="form-control form-control-sm shadow-sm" rows="3"
              placeholder="Contoh: Fokus pada pair EURUSD sesi London, hindari entry beruntun..."></textarea>
          </div>
        </div>
      </div>

      <!-- Card: Evaluasi Mingguan -->
      <div id="evaluationCard" class="card border-0 shadow-sm flex-fill" style="min-height: 160px;">
        <div class="card-header bg-light d-flex align-items-center">
          <h6 class="mb-0 fw-bold text-primary flex-grow-1">
            <i class="bx bx-bar-chart-alt me-1"></i> Evaluasi Mingguan
          </h6>
          <span class="badge bg-label-info px-3 py-2 small">Auto Generated</span>
        </div>
        <div class="card-body">
          <div id="evaluationContent" class="text-muted small py-2">
            ⏳ Data evaluasi akan muncul setelah minggu trading berakhir...
          </div>
        </div>
      </div>
    </div>

  <!-- Right: Calendar -->
  <div class="col-lg-6">
    <div class="card border-0 shadow-sm h-100 d-flex flex-column">
      <div class="card-header bg-light d-flex justify-content-between align-items-center sticky-top">
        <h6 class="mb-0 fw-bold text-primary">
          <i class="bx bx-calendar me-1"></i> Kalender Rencana
        </h6>
        <span class="text-muted small d-flex align-items-center">
          <i class="bx bx-info-circle me-1"></i> Klik rencana untuk detail
        </span>
      </div>

      <div class="card-body p-2 flex-grow-1">
        <div id="plannerCalendar" class="border rounded shadow-sm mb-3"
          style="
            height: 360px; 
            padding: 4px; 
            background-color: #fafbfc; 
            border: 1px solid #e0e0e0;
            transition: all 0.3s ease;
          ">
        </div>

        <!-- Tutorial Informasi User -->
        <div class="border-top pt-3 small text-muted">
          <h6 class="fw-bold text-primary mb-2">
            <i class="bx bx-help-circle me-1"></i> Panduan Singkat
          </h6>
          <p class="mb-2">
            📘 <b>Langkah 1:</b> Pilih akun aktif dulu sebelum isi form.  
            Sistem otomatis mengambil <em>equity</em> terkini dari akun tersebut.
          </p>
          <p class="mb-2">
            💡 <b>Langkah 2:</b> Isi target profit (%) dan max loss (%),  
            atau klik <b>“Hitung Otomatis”</b> agar sistem bantu hitung target realistis.
          </p>
          <p class="mb-2">
            💾 <b>Langkah 3:</b> Klik <b>“Simpan Rencana”</b> — rencanamu muncul otomatis di kalender biru di atas.
          </p>
          <p class="mb-2">
            📅 <b>Kalender:</b> Menampilkan semua rencana trading.  
            Klik tanggal untuk menambah plan, atau klik event untuk lihat detail.
          </p>
          <p class="mb-2">
            📊 <b>Evaluasi Mingguan:</b> Sistem otomatis bandingkan hasil minggu ini dengan target kamu.  
            Kalau performa bagus → <b>✅ Target tercapai</b>. Kalau terlalu agresif → <b>⚠️ Overtrading</b>.
          </p>
          <p class="text-info mb-0">
            🧠 <b>Tips:</b> Gunakan fitur <span class="fw-semibold text-primary">“Hitung Otomatis”</span>  
            secara rutin agar strategi kamu tetap seimbang tanpa emosi berlebih.
          </p>
        </div>
      </div>
    </div>
  </div>
  </div>
</div>

<?= $this->endSection(); ?>
<?= $this->section('scripts'); ?>
<script>
  const BASE_URL = "<?= base_url(); ?>";
</script>
<script src="<?= base_url('assets/js/transaction/planner-actions.js'); ?>"></script>
<?= $this->endSection(); ?>
