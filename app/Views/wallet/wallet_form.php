<div class="mb-3 border-bottom pb-2">
  <h5 class="mb-0 text-primary fw-semibold">
    <?= isset($wallet) ? 'Edit Wallet' : 'Tambah Wallet'; ?>
  </h5>
</div>

<form 
  id="walletForm"
  action="<?= isset($wallet) 
    ? base_url('wallet/update/' . $wallet['id']) 
    : base_url('wallet/store'); ?>" 
  method="post">

  <!-- Tipe Wallet -->
  <div class="mb-3">
      <label class="form-label">Tipe Wallet</label>
      <select name="wallet_type" class="form-select" required>
          <option value="">-- Pilih Tipe --</option>
          <option value="Bank" <?= isset($wallet) && $wallet['wallet_type'] == 'Bank' ? 'selected' : ''; ?>>Bank</option>
          <option value="E-Wallet" <?= isset($wallet) && $wallet['wallet_type'] == 'E-Wallet' ? 'selected' : ''; ?>>E-Wallet</option>
          <option value="Crypto" <?= isset($wallet) && $wallet['wallet_type'] == 'Crypto' ? 'selected' : ''; ?>>Crypto</option>
      </select>
  </div>

  <!-- Provider -->
  <div class="mb-3">
      <label class="form-label">Provider</label>
      <input type="text" name="provider_name" class="form-control" 
             placeholder="Contoh: BCA, Binance, GoPay..." 
             value="<?= isset($wallet) ? esc($wallet['provider_name']) : ''; ?>" required>
  </div>

  <!-- Nama Akun -->
  <div class="mb-3">
      <label class="form-label">Nama Akun</label>
      <input type="text" name="account_name" class="form-control"
             placeholder="Nama pemilik akun"
             value="<?= isset($wallet) ? esc($wallet['account_name']) : ''; ?>" required>
  </div>

  <!-- Nomor Akun -->
  <div class="mb-3">
      <label class="form-label">Nomor Akun / Rekening</label>
      <input type="text" name="account_number" class="form-control"
             placeholder="Nomor akun atau alamat wallet"
             value="<?= isset($wallet) ? esc($wallet['account_number']) : ''; ?>" required>
  </div>

  <!-- Saldo -->
  <div class="mb-3">
      <label class="form-label">Saldo</label>
      <input type="number" step="0.01" min="0" name="balance" class="form-control"
             placeholder="0.00"
             value="<?= isset($wallet) ? esc($wallet['balance']) : 0; ?>">
  </div>

  <!-- Currency -->
  <div class="mb-3">
      <label class="form-label">Mata Uang</label>
      <select name="currency" class="form-select">
          <?php
          $currencies = ['IDR', 'USD', 'EUR', 'JPY', 'USDT'];
          $selectedCurrency = isset($wallet) ? $wallet['currency'] : 'IDR';
          foreach ($currencies as $cur): ?>
              <option value="<?= $cur; ?>" <?= $cur == $selectedCurrency ? 'selected' : ''; ?>>
                  <?= $cur; ?>
              </option>
          <?php endforeach; ?>
      </select>
  </div>

  <!-- Default Checkbox -->
  <div class="form-check form-switch mb-4">
      <input class="form-check-input" type="checkbox" id="is_default" name="is_default"
             <?= isset($wallet) && $wallet['is_default'] ? 'checked' : ''; ?>>
      <label class="form-check-label" for="is_default">Jadikan wallet default</label>
  </div>

  <!-- Tombol -->
  <div class="d-flex justify-content-end gap-2">
      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="bx bx-x"></i> Tutup
      </button>
      <button type="submit" class="btn btn-primary">
          <i class="bx bx-save"></i> <?= isset($wallet) ? 'Update Wallet' : 'Simpan Wallet'; ?>
      </button>
  </div>
</form>
