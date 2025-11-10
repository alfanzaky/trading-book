<form method="post" id="withdrawForm" class="bg-transparent">
  <?= csrf_field() ?>

  <!-- Judul mini -->
  <div class="mb-3 text-center">
    <h6 class="fw-bold mb-1 text-danger">
      <i class="bx bx-download me-1"></i> Form Withdraw
    </h6>
    <small class="text-muted">Isi jumlah dana yang ingin ditarik dari wallet kamu.</small>
  </div>

  <!-- Nominal Withdraw -->
  <div class="mb-3">
    <label for="amount" class="form-label fw-semibold">Nominal Withdraw</label>
    <div class="d-flex align-items-center border rounded-3 px-2 py-1 bg-white shadow-sm-sm">
      <span class="fw-bold text-danger me-2">IDR</span>
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
    <label for="note" class="form-label fw-semibold">Catatan (Opsional)</label>
    <input 
      type="text"
      name="note"
      id="note"
      class="form-control border-0 border-bottom px-0 rounded-0 shadow-none"
      placeholder="Contoh: Tarik ke rekening utama"
      style="background:transparent;">
  </div>

  <!-- Tombol -->
  <div class="text-end">
    <button type="submit"
            class="btn px-4 fw-semibold text-white"
            style="background: linear-gradient(90deg, #ef4444, #f97316); border:none;">
      <i class="bx bx-download me-1"></i> Withdraw
    </button>
  </div>
</form>

<!-- Validasi ringan -->
<script>
document.getElementById('withdrawForm').addEventListener('submit', e => {
  const input = document.getElementById('amount');
  const amount = parseFloat(input.value);
  const max = parseFloat(input.getAttribute('max'));

  if (isNaN(amount) || amount <= 0) {
    e.preventDefault();
    Swal.fire({
      icon: 'warning',
      title: 'Nominal tidak valid',
      text: 'Masukkan jumlah lebih besar dari 0 sebelum mengirim.',
      confirmButtonColor: '#ef4444'
    });
  } else if (amount > max) {
    e.preventDefault();
    Swal.fire({
      icon: 'error',
      title: 'Melebihi saldo',
      text: 'Nominal withdraw tidak boleh lebih dari saldo kamu.',
      confirmButtonColor: '#ef4444'
    });
  }
});
</script>
