<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container-xxl flex-grow-1 container-p-y">
  <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Transaction /</span> Hasil Import MT5</h4>

  <div class="card">
    <h5 class="card-header">Daftar Transaksi</h5>
    <div class="card-body">
      <form method="get" class="mb-3">
        <label class="form-label">Pilih Akun:</label>
        <select name="account_id" class="form-select" onchange="this.form.submit()">
          <option value="">-- Semua Akun --</option>
          <?php foreach ($accounts as $acc): ?>
            <option value="<?= $acc['id'] ?>" <?= $account_id == $acc['id'] ? 'selected' : '' ?>>
              <?= esc($acc['broker_name']) ?> (<?= esc($acc['uid']) ?>)
            </option>
          <?php endforeach; ?>
        </select>
      </form>

      <?php if (empty($transactions)): ?>
        <div class="alert alert-info">Belum ada data transaksi untuk akun ini.</div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-bordered table-striped align-middle">
            <thead class="table-dark">
              <tr>
                <th>#</th>
                <th>Tanggal</th>
                <th>Symbol</th>
                <th>Type</th>
                <th>Lot</th>
                <th>Open</th>
                <th>Close</th>
                <th>Profit</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($transactions as $i => $t): ?>
                <tr>
                  <td><?= $i+1 ?></td>
                  <td><?= esc(date('d M Y', strtotime($t['created_at']))) ?></td>
                  <td><?= esc($t['symbol']) ?></td>
                  <td><?= esc($t['type']) ?></td>
                  <td><?= number_format($t['lot_size'], 2) ?></td>
                  <td><?= number_format($t['open_price'], 2) ?></td>
                  <td><?= number_format($t['close_price'], 2) ?></td>
                  <td class="<?= $t['profit_loss'] >= 0 ? 'text-success' : 'text-danger' ?>">
                    <?= number_format($t['profit_loss'], 2) ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
