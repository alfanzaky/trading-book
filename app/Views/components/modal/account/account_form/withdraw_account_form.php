<form id="withdrawAccountForm">
  <div class="modal-body">

    <!-- Akun Trading Sumber -->
    <div class="mb-3">
      <label class="form-label fw-semibold">Akun Trading Sumber</label>
      <input type="text" class="form-control" 
             value="<?= esc($account['broker_name']); ?> (<?= esc($account['login_id']); ?>)" disabled>
      <input type="hidden" name="account_id" value="<?= esc($account['id']); ?>">
      <div class="text-muted small mt-1">
        Saldo saat ini: <strong class="text-success">
          <?= number_format($account['balance'], 2) ?> <?= esc($account['currency']); ?>
        </strong>
      </div>
    </div>

    <!-- Wallet Tujuan -->
    <div class="mb-3">
      <label class="form-label fw-semibold">Pilih Wallet Tujuan</label>
      <select name="wallet_id" id="walletSelect" class="form-select" required>
        <option value="">-- Pilih Wallet --</option>
        <?php foreach ($wallets as $w): ?>
          <option value="<?= $w['id']; ?>" data-currency="<?= esc($w['currency']); ?>">
            <?= esc($w['provider_name']); ?> (<?= esc($w['currency']); ?>)
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <!-- Nominal -->
    <div class="mb-3">
      <label class="form-label fw-semibold">Nominal Withdraw (USD)</label>
      <input type="number" name="amount" step="0.01" min="0.01" 
             class="form-control" placeholder="0.00" required>
    </div>

    <!-- Kurs -->
    <div class="mb-3">
      <label class="form-label fw-semibold">Kurs (Rate)</label>
      <input type="number" step="0.0001" name="rate" class="form-control" placeholder="Contoh: 15500" required>
      <div id="rateInfo" class="form-text text-muted">
        Isi kurs sesuai nilai tukar terkini antara 
        <span id="fromCurrency"><?= esc($account['currency']); ?></span> → 
        <span id="toCurrency">?</span>.
      </div>
    </div>

    <!-- Catatan -->
    <div class="mb-3">
      <label class="form-label fw-semibold">Catatan (Opsional)</label>
      <textarea name="note" class="form-control" rows="2" 
                placeholder="Keterangan tambahan..."></textarea>
    </div>
  </div>

  <div class="modal-footer border-0 pt-0">
    <button type="submit" class="btn btn-danger w-100">
      <i class="bx bx-upload me-1"></i> Tarik Dana
    </button>
  </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const walletSelect = document.getElementById('walletSelect');
  const toCurrency = document.getElementById('toCurrency');

  walletSelect.addEventListener('change', e => {
    const selected = walletSelect.options[walletSelect.selectedIndex];
    toCurrency.textContent = selected.dataset.currency || '?';
  });
});
</script>
