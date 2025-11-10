<form method="post" id="transferForm" class="bg-transparent">
  <?= csrf_field() ?>

  <!-- Header -->
  <div class="mb-3 text-center">
    <h6 class="fw-bold mb-1 text-primary">
      <i class="bx bx-transfer-alt me-1"></i> Form Transfer Dana
    </h6>
    <small class="text-muted">Pilih wallet tujuan dan masukkan nominal transfer kamu.</small>
  </div>

  <!-- Pilih Wallet Tujuan -->
  <div class="mb-3">
    <label for="to_wallet_id" class="form-label fw-semibold">
      <i class="bx bx-wallet me-1"></i> Pilih Wallet Tujuan
    </label>
    <select name="to_wallet_id" id="to_wallet_id" class="form-select shadow-sm" required>
      <option value="">-- Pilih Wallet Tujuan --</option>
      <?php foreach ($wallets as $w): ?>
        <?php if ($w['id'] != $wallet['id']): // jangan tampilkan wallet asal ?>
          <option value="<?= $w['id']; ?>">
            <?= esc($w['provider_name']); ?> — <?= esc($w['account_name']); ?>
          </option>
        <?php endif; ?>
      <?php endforeach; ?>
    </select>
    <small class="text-muted">Wallet asal: <span class="fw-semibold"><?= esc($wallet['provider_name']); ?> (<?= esc($wallet['account_name']); ?>)</span></small>
  </div>

  <!-- Nominal Transfer -->
  <div class="mb-3">
    <label for="amount" class="form-label fw-semibold">
      <i class="bx bx-money me-1"></i> Nominal Transfer
    </label>
    <div class="d-flex align-items-center border rounded-3 px-2 py-1 bg-white shadow-sm-sm">
      <span class="fw-bold text-primary me-2">IDR</span>
      <input
        type="number"
        name="amount"
        id="amount"
        step="0.01"
        min="0"
        max="<?= $wallet['balance']; ?>"
        class="form-control border-0 flex-fill text-end fw-semibold"
        placeholder="Masukkan jumlah (maks: <?= number_format($wallet['balance'], 2); ?>)"
        style="box-shadow:none; background:transparent;"
        required>
    </div>
    <small class="text-muted">
      Saldo saat ini: <span class="fw-semibold text-success"><?= number_format($wallet['balance'], 2); ?> <?= esc($wallet['currency']); ?></span>
    </small>
  </div>

  <!-- Catatan -->
  <div class="mb-4">
    <label for="note" class="form-label fw-semibold">
      <i class="bx bx-edit-alt me-1"></i> Catatan (Opsional)
    </label>
    <input
      type="text"
      name="note"
      id="note"
      class="form-control border-0 border-bottom px-0 rounded-0 shadow-none"
      placeholder="Contoh: Pindah dana ke akun trading"
      style="background:transparent;">
  </div>

  <!-- Tombol -->
  <div class="text-end">
    <button type="submit"
            class="btn px-4 fw-semibold text-white"
            style="background: linear-gradient(90deg, #6366f1, #8b5cf6); border: none;">
      <i class="bx bx-send me-1"></i> Transfer
    </button>
  </div>
</form>

<!-- Validasi ringan -->
<script>
document.getElementById('transferForm').addEventListener('submit', e => {
  const amountInput = document.getElementById('amount');
  const amount = parseFloat(amountInput.value);
  const max = parseFloat(amountInput.getAttribute('max'));
  const toWallet = document.getElementById('to_wallet_id').value;

  if (!toWallet) {
    e.preventDefault();
    Swal.fire({
      icon: 'warning',
      title: 'Wallet tujuan belum dipilih',
      text: 'Silakan pilih wallet tujuan terlebih dahulu.',
      confirmButtonColor: '#6366f1'
    });
    return;
  }

  if (isNaN(amount) || amount <= 0) {
    e.preventDefault();
    Swal.fire({
      icon: 'warning',
      title: 'Nominal tidak valid',
      text: 'Masukkan jumlah lebih besar dari 0 sebelum mengirim.',
      confirmButtonColor: '#6366f1'
    });
  } else if (amount > max) {
    e.preventDefault();
    Swal.fire({
      icon: 'error',
      title: 'Melebihi saldo',
      text: 'Nominal transfer tidak boleh lebih dari saldo kamu.',
      confirmButtonColor: '#6366f1'
    });
  }
});
</script>
