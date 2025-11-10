<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold m-0">
      <span class="text-muted fw-light">Transaction /</span> Data transaksi trading
    </h4>
  </div>
  <div class="card shadow-sm">
    <div class="card-body">
      <!-- Search & Filter -->
      <div class="row g-3 align-items-center mb-3">
        <!-- Kolom Pilih Akun -->
        <div class="col-md-3">
          <select id="accountFilter" class="form-select">
            <option value=""> Tampilkan semua </option>
            <?php foreach ($accounts as $acc): ?>
              <option value="<?= $acc['id'] ?>">
                <?= esc($acc['account_name']) ?> - <?= esc($acc['login_id']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Kolom Search -->
        <div class="col-md-3">
          <input id="searchInput" type="text" class="form-control" placeholder="Cari symbol, type, atau akun...">
        </div>

        <!-- Kolom Filter Tanggal -->
        <div class="col-md-2">
          <input id="startDate" type="date" class="form-control" placeholder="Dari tanggal">
        </div>

        <div class="col-md-2">
          <input id="endDate" type="date" class="form-control" placeholder="Sampai tanggal">
        </div>

        <!-- Tombol Filter & Reset -->
        <div class="col-md-2 d-flex gap-2">
          <button id="filterDateBtn" class="btn btn-sm btn-outline-primary flex-fill">
            <i class="bx bx-filter"></i> Filter
          </button>
          <button id="resetDateBtn" class="btn btn-sm btn-outline-secondary flex-fill">
            <i class="bx bx-refresh"></i> Reset
          </button>
        </div>
      </div>
      <!-- Tabel Transaksi -->
      <div class="table-responsive text-nowrap border rounded">
        <table class="table table-hover table-sm mb-0 align-middle">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Tanggal</th>
              <th>Akun</th>
              <th>Symbol</th>
              <th>Type</th>
              <th>Lot</th>
              <th>Open</th>
              <th>Close</th>
              <th>PnL</th>
              <th class="text-center"><i class="bx bx-search-alt-2"></i></th>
            </tr>
          </thead>
          <tbody id="transactionBody">
            <tr>
              <td colspan="9" class="text-center py-4 text-muted">Memuat data...</td>
            </tr>
          </tbody>
        </table>
      </div>
      <!-- Pagination -->
      <div id="paginationContainer" class="mt-3 d-flex justify-content-center"></div>
      <hr class="my-4" />
      <h6 class="fw-bold mb-3"><i class="bx bx-upload"></i> Import data Report MT5</h6>
      <form id="uploadForm" action="<?= base_url('transaction/import-report/preview') ?>" method="post" enctype="multipart/form-data" class="row g-3">
        <?= csrf_field() ?>
        <div class="col-md-4">
          <label class="form-label">Pilih Akun:</label>
          <select id="account_id" name="account_id" class="form-select" required>
            <option value="" disabled selected>-- Pilih Akun Trading --</option>
            <?php foreach ($accounts as $acc): ?>
              <option value="<?= $acc['id'] ?>">
                <?= esc($acc['account_name']) ?> - <?= esc($acc['login_id']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-5">
          <label class="form-label">Upload File Report (.html / .xlsx):</label>
          <input type="file" name="report_file" class="form-control" accept=".html,.xlsx" required>
        </div>
        <div class="col-md-3 d-flex align-items-end">
          <button type="submit" class="btn btn-success w-100">
            <i class="bx bx-upload"></i> Upload & Preview
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
<?= $this->include('components/modal/preview_modal') ?>
<?= $this->include('components/modal/detail_modal') ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
  const BASE_URL = "<?= rtrim(base_url(), '/') ?>/";
</script>
<script src="<?= base_url('assets/js/transaction/trades.js') ?>"></script>
<?= $this->endSection() ?>