<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="container-xxl flex-grow-1 container-p-y">
  <h4 class="fw-bold py-3 mb-4">
    <span class="text-muted fw-light">Transaction /</span> Preview Import MT5
  </h4>

  <div class="card shadow-sm">
    <h5 class="card-header">Preview Report: <?= esc($file_name) ?></h5>
    <div class="card-body">
      <?php if (empty($preview)): ?>
        <div class="alert alert-warning mb-0">Tidak ada data transaksi terbaca dari file.</div>
      <?php else: ?>
      <form id="saveForm">
        <?= csrf_field() ?>
        <input type="hidden" name="account_id" value="<?= esc($account_id) ?>">

        <div class="table-responsive">
          <table class="table table-bordered table-sm align-middle">
            <thead class="table-dark">
              <tr>
                <th>#</th><th>Position ID</th><th>Tanggal</th><th>Symbol</th>
                <th>Type</th><th>Lot</th><th>Open</th><th>Close</th><th>Profit/Loss</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($preview as $i => $row): ?>
              <tr data-position="<?= esc($row['position_id']) ?>"
                  data-open_time="<?= esc($row['created_at']) ?>"
                  data-symbol="<?= esc($row['symbol']) ?>"
                  data-type="<?= esc($row['type']) ?>"
                  data-lot_size="<?= esc($row['lot_size']) ?>"
                  data-open_price="<?= esc($row['open_price']) ?>"
                  data-close_price="<?= esc($row['close_price']) ?>"
                  data-profit_loss="<?= esc($row['profit_loss']) ?>">
                <td><?= $i+1 ?></td>
                <td><?= esc($row['position_id']) ?></td>
                <td><?= esc($row['created_at']) ?></td>
                <td><?= esc($row['symbol']) ?></td>
                <td><?= esc($row['type']) ?></td>
                <td><?= number_format($row['lot_size'],2) ?></td>
                <td><?= number_format($row['open_price'],2) ?></td>
                <td><?= number_format($row['close_price'],2) ?></td>
                <td class="<?= $row['profit_loss'] >= 0 ? 'text-success' : 'text-danger' ?>">
                  <?= number_format($row['profit_loss'],2) ?>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <div class="mt-4 d-flex justify-content-between">
          <a href="<?= base_url('jurnal') ?>" class="btn btn-secondary">
            <i class="bx bx-arrow-back"></i> Kembali
          </a>
          <button type="button" id="previewButton" class="btn btn-success">
            <i class="bx bx-save"></i> Simpan ke Database
          </button>
        </div>
      </form>
      <?php endif; ?>
    </div>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
  // Kirim variabel PHP ke JS global (bisa diakses di import_preview.js)
  window.saveUrl = "<?= base_url('transaction/import-report/save') ?>";
  window.csrfTokenName = "<?= csrf_token() ?>";
  window.csrfHash = "<?= csrf_hash() ?>";
</script>
<script src="<?= base_url('assets/js/transaction/import_preview.js') ?>"></script>
<?= $this->endSection() ?>

<?= $this->endSection() ?>
