<?= $this->extend('layouts/main'); ?>
<?= $this->section('content'); ?>

<div class="container-xxl flex-grow-1 container-p-y">

  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold m-0">
      <span class="text-muted fw-light">Transaction /</span> Summary
    </h4>
  </div>

  <!-- Trading Psychology Card -->
  <div id="psychCard" class="card border-0 shadow-sm mb-4 bg-label-primary" style="display:none; transition: all .3s;">
    <div class="card-body d-flex align-items-start">
      <div id="psychIcon" class="me-3 p-3 rounded-3 bg-white shadow-sm d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
        <i class="bx bx-brain fs-3 text-primary"></i>
      </div>
      <div>
        <h6 class="fw-bold mb-2 text-primary">Trading Notes</h6>
        <div id="psychMessage" class="text-muted small">
          Analisis perilaku trading akan muncul di sini...
        </div>
      </div>
    </div>
  </div>

  <!-- Filter Section -->
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-light border-bottom py-3">
      <h6 class="card-title fw-bold mb-0 d-flex align-items-center text-primary">
        <i class="bx bx-filter-alt me-2"></i> Filter Data
      </h6>
    </div>
    <div class="card-body py-3">
      <div class="row g-3 align-items-end">
        <!-- Pilih Akun -->
        <div class="col-md-4 col-lg-3">
          <label for="accountFilter" class="form-label mb-1 fw-semibold text-muted small">Pilih Akun</label>
          <select id="accountFilter" class="form-select form-select-sm">
            <option value="">Tampilkan Semua</option>
            <?php foreach ($accounts as $acc): ?>
              <option value="<?= $acc['id'] ?>">
                <?= esc($acc['account_name']) ?> - <?= esc($acc['login_id']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Filter Tanggal -->
        <div class="col-md-4 col-lg-2">
          <label for="startDate" class="form-label mb-1 fw-semibold text-muted small">Dari Tanggal</label>
          <input id="startDate" type="date" class="form-control form-control-sm">
        </div>

        <div class="col-md-4 col-lg-2">
          <label for="endDate" class="form-label mb-1 fw-semibold text-muted small">Sampai Tanggal</label>
          <input id="endDate" type="date" class="form-control form-control-sm">
        </div>

        <!-- Tombol -->
        <div class="col-md-4 col-lg-3 d-flex gap-2">
          <button type="button" id="filterBtn" class="btn btn-sm btn-primary flex-fill d-flex align-items-center justify-content-center gap-1">
            <i class="bx bx-filter-alt"></i> Filter
          </button>
          <button type="button" id="resetBtn" class="btn btn-sm btn-outline-secondary flex-fill d-flex align-items-center justify-content-center gap-1">
            <i class="bx bx-refresh"></i> Reset
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Summary Cards -->
  <div class="row g-4">
    <?php
      $cards = [
        ['title' => 'Total Transaksi', 'id' => 'totalTrades', 'icon' => 'bx bx-bar-chart', 'color' => 'primary'],
        ['title' => 'Total Profit', 'id' => 'totalProfit', 'icon' => 'bx bx-trending-up', 'color' => 'success'],
        ['title' => 'Total Loss', 'id' => 'totalLoss', 'icon' => 'bx bx-trending-down', 'color' => 'danger'],
        ['title' => 'Win Rate', 'id' => 'winRate', 'icon' => 'bx bx-rocket', 'color' => 'warning']
      ];
      foreach ($cards as $c): ?>
      <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body d-flex align-items-center">
            <div class="me-3 bg-<?= $c['color'] ?> text-white p-3 rounded-3 d-flex align-items-center justify-content-center" style="width:50px; height:50px;">
              <i class="<?= $c['icon'] ?> fs-4"></i>
            </div>
            <div>
              <small class="text-muted"><?= $c['title'] ?></small>
              <h5 id="<?= $c['id'] ?>" class="mb-0 fw-bold">0</h5>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Row 2 -->
  <div class="row g-4 mt-1">
    <?php
      $cards2 = [
        ['title' => 'Average Profit', 'id' => 'avgProfit', 'icon' => 'bx bx-line-chart', 'color' => 'success'],
        ['title' => 'Average Loss', 'id' => 'avgLoss', 'icon' => 'bx bx-pulse', 'color' => 'danger'],
        ['title' => 'Average Holding Time', 'id' => 'avgTime', 'icon' => 'bx bx-time', 'color' => 'info'],
        ['title' => 'Total Volume (Lot)', 'id' => 'totalVolume', 'icon' => 'bx bx-layer', 'color' => 'secondary']
      ];
      foreach ($cards2 as $c): ?>
      <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body d-flex align-items-center">
            <div class="me-3 bg-<?= $c['color'] ?> text-white p-3 rounded-3 d-flex align-items-center justify-content-center" style="width:50px; height:50px;">
              <i class="<?= $c['icon'] ?> fs-4"></i>
            </div>
            <div>
              <small class="text-muted"><?= $c['title'] ?></small>
              <h5 id="<?= $c['id'] ?>" class="mb-0 fw-bold">0</h5>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

<!-- Row 3 -->
  <div class="row g-4 mt-1">
    <!-- Net Profit -->
    <div class="col-lg-4">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body d-flex align-items-center">
          <div class="me-3 bg-info text-white p-3 rounded-3 d-flex align-items-center justify-content-center" style="width:50px; height:50px;">
            <i class="bx bx-dollar-circle fs-4"></i>
          </div>
          <div>
            <small class="text-muted">Hasil Bersih (Net Profit)</small>
            <h5 id="netProfit" class="mb-0 fw-bold text-info">0</h5>
          </div>
        </div>
      </div>
    </div>

    <!-- Pair Terbanyak -->
    <div class="col-lg-4">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <h6 class="fw-bold mb-3"><i class="bx bx-bar-chart me-1"></i> Pair Terbanyak</h6>
          <div id="topSymbol" class="fs-5 fw-bold text-primary">-</div>
        </div>
      </div>
    </div>

    <!-- Trade Distribution -->
    <div class="col-lg-4">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <h6 class="fw-bold mb-3"><i class="bx bx-pie-chart-alt-2 me-1"></i> Trade Type Distribution</h6>
          <div id="tradeDist" class="fs-6">
            <span id="buyPercent" class="fw-bold text-success">0%</span> Buy &nbsp; | &nbsp;
            <span id="sellPercent" class="fw-bold text-danger">0%</span> Sell
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection(); ?>

<?= $this->section('scripts') ?>
<script>
  const BASE_URL = "<?= rtrim(base_url(), '/') ?>/";
</script>
<script src="<?= base_url('assets/js/transaction/summary-actions.js') ?>"></script>
<?= $this->endSection() ?>
