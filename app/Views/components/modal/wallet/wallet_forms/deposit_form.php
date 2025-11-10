<form method="post" id="depositForm" class="bg-transparent">
  <?= csrf_field() ?>

  <!-- Judul mini -->
  <div class="mb-3 text-center">
    <h6 class="fw-bold mb-1"><i class="bx bx-money-withdraw me-1"></i> Form Deposit</h6>
    <small class="text-muted">Masukkan nominal dan keterangan deposit di bawah ini</small>
  </div>

  <!-- Nominal Deposit -->
  <div class="mb-3">
    <label for="amount" class="form-label fw-semibold mb-1">Nominal Deposit</label>
    <div class="d-flex align-items-center border rounded-3 px-2 py-1 bg-white shadow-sm-sm">
      <span class="fw-bold text-success me-2">IDR</span>
      <input type="number"
             name="amount"
             id="amount"
             step="0.01"
             min="0"
             class="form-control border-0 flex-fill text-end fw-semibold"
             placeholder="0.00"
             style="box-shadow:none; background:transparent;"
             required>
    </div>
    <small class="text-muted">Masukkan jumlah sesuai saldo yang ingin ditambahkan.</small>
  </div>

  <!-- Catatan -->
  <div class="mb-4">
    <label for="note" class="form-label fw-semibold mb-1">Catatan (Opsional)</label>
    <input type="text"
           name="note"
           id="note"
           class="form-control border-0 border-bottom px-0 rounded-0 shadow-none"
           placeholder="Contoh: Top up mingguan"
           style="background:transparent;">
  </div>

  <!-- Tombol -->
  <div class="text-end">
    <button type="submit"
            class="btn px-4 fw-semibold text-white"
            style="background:linear-gradient(90deg,#16a34a,#4ade80); border:none;">
      <i class="bx bx-upload me-1"></i> Deposit
    </button>
  </div>
</form>

<!-- Validasi ringan -->
<script>
document.getElementById('depositForm').addEventListener('submit', e => {
  const amount = parseFloat(document.getElementById('amount').value);
  if (isNaN(amount) || amount <= 0) {
    e.preventDefault();
    Swal.fire({
      icon: 'warning',
      title: 'Nominal tidak valid',
      text: 'Masukkan jumlah lebih besar dari 0 sebelum mengirim.',
      confirmButtonColor: '#16a34a'
    });
  }
});
</script>
