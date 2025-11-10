<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container-xxl flex-grow-1 container-p-y">

  <!-- HEADER -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">
      <span class="text-muted fw-light">User Menu /</span> Account Trading
    </h4>
    <button type="button" 
            class="btn btn-primary rounded-pill shadow-sm" 
            data-bs-toggle="modal" 
            data-bs-target="#addAccountModal">
      <i class="bx bx-plus"></i> Add Account
    </button>
  </div>

  <!-- NAVIGATION TABS -->
  <ul class="nav nav-pills mb-4" id="accountTabs" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link active" id="active-tab" data-bs-toggle="pill" data-bs-target="#active" type="button">
        <i class="bx bx-check-circle me-1"></i> Active
      </button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="non-active-tab" data-bs-toggle="pill" data-bs-target="#non-active" type="button">
        <i class="bx bx-block me-1"></i> Non Active
      </button>
    </li>
  </ul>

  <!-- TAB CONTENT -->
  <div class="tab-content" id="accountTabsContent">

    <!-- ACTIVE ACCOUNTS -->
    <div class="tab-pane fade show active" id="active" role="tabpanel">
      <?php if (!empty($activeAccounts)): ?>
      <div class="row g-4">
        <?php foreach ($activeAccounts as $account): ?>
        <div class="col-xl-4 col-md-6 col-sm-12">
          <div class="card border-0 shadow-sm h-100 hover-card">
            <div class="card-body d-flex flex-column justify-content-between">

              <!-- Header -->
              <div>
                <div class="d-flex justify-content-between align-items-start mb-2">
                  <h5 class="fw-semibold text-primary mb-0">
                    <i class="bx bx-briefcase-alt-2 me-1 text-primary"></i>
                    <?= esc($account['broker_name']) ?>
                  </h5>
                  <span class="badge bg-success rounded-pill"><?= esc($account['status']) ?></span>
                </div>
                <p class="small text-muted mb-3">
                  <?= esc($account['platform']) ?> • <?= esc($account['account_type']) ?>
                </p>

                <!-- Info -->
                <ul class="list-unstyled mb-3 small">
                  <li><strong>Name:</strong> <?= esc($account['account_name']) ?></li>
                  <li><strong>Login ID:</strong> <?= esc($account['login_id']) ?></li>
                  <li><strong>Server:</strong> <?= esc($account['server']) ?></li>
                  <li><strong>Leverage:</strong> <?= esc($account['leverage']) ?></li>
                </ul>
              </div>

              <!-- Balance -->
              <div class="d-flex justify-content-between align-items-center bg-light rounded px-3 py-2 mb-3">
                <h6 class="fw-bold mb-0 text-dark">Balance</h6>
                <span class="fw-bold text-success">
                  <?= number_format($account['balance'], 2) ?> <?= esc($account['currency']) ?>
                </span>
              </div>

              <!-- Quick Actions -->
              <div class="d-flex justify-content-end align-items-center">
                <div class="dropdown">
                  <button class="btn btn-sm btn-outline-primary dropdown-toggle rounded-pill"
                          type="button"
                          id="dropdownMenuAccount<?= $account['id']; ?>"
                          data-bs-toggle="dropdown"
                          aria-expanded="false">
                    <i class="bx bx-dots-vertical-rounded fs-5"></i>
                  </button>

                  <ul class="dropdown-menu dropdown-menu-end shadow fade"
                      aria-labelledby="dropdownMenuAccount<?= $account['id']; ?>">

                    <!-- Deposit ke akun trading -->
                    <li>
                      <a class="dropdown-item account-action"
                         data-url="<?= base_url('transfer/deposit-to-account?account_id=' . $account['id']); ?>"
                         data-title="Deposit ke <?= esc($account['broker_name']); ?> (<?= esc($account['login_id']); ?>)"
                         href="#">
                        <i class="bx bx-download me-2 text-success"></i> Deposit
                      </a>
                    </li>

                    <!-- Withdraw dari akun trading -->
                    <li>
                      <a class="dropdown-item account-action"
                         data-url="<?= base_url('transfer/withdraw-from-account?account_id=' . $account['id']); ?>"
                         data-title="Withdraw dari <?= esc($account['broker_name']); ?> (<?= esc($account['login_id']); ?>)"
                         href="#">
                        <i class="bx bx-upload me-2 text-danger"></i> Withdraw
                      </a>
                    </li>

                    <!-- Transfer antar akun trading -->
                    <li>
                      <a class="dropdown-item account-action"
                         data-url="<?= base_url('transfer/transfer-between-accounts?from_account_id=' . $account['id']); ?>"
                         data-title="Transfer antar Akun Trading"
                         href="#">
                        <i class="bx bx-transfer-alt me-2 text-secondary"></i> Transfer
                      </a>
                    </li>

                    <li><hr class="dropdown-divider"></li>

                    <!-- Edit -->
                    <li>
                      <a class="dropdown-item" href="<?= base_url('accounts/edit/' . $account['id']); ?>">
                        <i class="bx bx-edit-alt me-2 text-warning"></i> Edit
                      </a>
                    </li>

                    <!-- Delete -->
                    <li>
                      <a class="dropdown-item text-danger"
                         href="<?= base_url('accounts/delete/' . $account['id']); ?>"
                         onclick="return confirm('Yakin mau hapus akun trading ini?');">
                        <i class="bx bx-trash me-2 text-danger"></i> Hapus
                      </a>
                    </li>
                  </ul>
                </div>
              </div>

            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <?php else: ?>
      <div class="text-center py-5 text-muted">
        <i class="bx bx-wallet-alt display-1 d-block mb-3"></i>
        <h5>No active accounts found</h5>
        <p>Add your first trading account to get started.</p>
      </div>
      <?php endif; ?>
    </div>

    <!-- NON ACTIVE ACCOUNTS -->
    <div class="tab-pane fade" id="non-active" role="tabpanel">
      <?php if (!empty($nonActiveAccounts)): ?>
        <div class="row g-3">
          <?php foreach ($nonActiveAccounts as $account): ?>
          <div class="col-xl-4 col-md-6 col-sm-12">
            <div class="card border-0 shadow-sm h-100">
              <div class="card-body text-center">
                <h5 class="card-title text-secondary"><?= esc($account['broker_name']) ?></h5>
                <p class="small text-muted mb-2"><?= esc($account['platform']) ?> • <?= esc($account['account_type']) ?></p>
                <p><strong>Login ID:</strong> <?= esc($account['login_id']) ?></p>
                <p><strong>Server:</strong> <?= esc($account['server']) ?></p>
                <span class="badge bg-secondary"><?= esc($account['status']) ?></span>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="text-center py-5 text-muted">
          <i class="bx bx-user-x display-1 d-block mb-3"></i>
          <h5>No non-active accounts found</h5>
        </div>
      <?php endif; ?>
    </div>

  </div>
</div>

<!-- Modals -->
<?= $this->include('components/modal/account/add-trading-account'); ?>
<?= $this->include('components/modal/account/account_modal'); ?>

<style>
.hover-card:hover {
  transform: translateY(-5px);
  transition: 0.25s ease;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
}
.dropdown-item i {
  width: 18px;
}
</style>

<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
<script src="<?= base_url('assets/js/trading_account/account-actions.js'); ?>"></script>
<?= $this->endSection(); ?>
