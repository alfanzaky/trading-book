<form id="transferAccountsForm">
  <div class="modal-body">

    <!-- Akun Trading Sumber -->
    <div class="mb-3">
      <label class="form-label fw-semibold">Akun Sumber</label>
      <input type="text" class="form-control" 
             value="<?= esc($fromAccount['broker_name']); ?> (<?= esc($fromAccount['login_id']); ?>)" disabled>
      <input type="hidden" name="from_account_id" value="<?= esc($fromAccount['id']); ?>">
      <div class="text-muted small mt-1">
        Saldo saat ini: <strong class="text-success">
          <?= number_format($fromAccount['balance'], 2) ?> <?= esc($fromAccount['currency']); ?>
        </strong>
      </div>
    </div>

    <!-- Akun Tujuan -->
    <div class="mb-3">
      <label class="form-label fw-semibold">Akun Tujuan</label>
      <select name="to_account_id" class="form-select" required>
        <option value="">-- Pilih Akun Tujuan --</option>
        <?php foreach ($accounts as $a): ?>
          <?php if ($a['id'] != $fromAccount['id']): // hide akun sumber ?>
            <option value="<?= $a['id']; ?>">
              <?= esc($a['broker_name']); ?> (<?= esc($a['login_id']); ?>)
            </option>
          <?php endif; ?>
        <?php endforeach; ?>
      </select>
    </div>

    <!-- Nominal Transfer -->
    <div class="mb-3">
      <label class="form-label fw-semibold">Nominal Transfer</label>
      <input type="number" name="amount" step="0.01" min="0.01" 
             class="form-control" placeholder="0.00" required>
    </div>

    <!-- Catatan -->
    <div class="mb-3">
      <label class="form-label fw-semibold">Catatan (Opsional)</label>
      <textarea name="note" class="form-control" rows="2" 
                placeholder="Keterangan tambahan..."></textarea>
    </div>

  </div>

  <div class="modal-footer border-0 pt-0">
    <button type="submit" class="btn btn-secondary w-100">
      <i class="bx bx-transfer-alt me-1"></i> Kirim Transfer
    </button>
  </div>
</form>
