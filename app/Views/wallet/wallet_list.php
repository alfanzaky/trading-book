<div class="col-lg-8">
    <div class="card border-0 shadow-sm h-100">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h5 class="m-0 fw-semibold">
        <i class="bx bx-wallet-alt me-2 text-primary"></i>Daftar Wallet
        </h5>
    </div>
    <div class="card h-100 border-0 shadow-sm">
        <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light">
            <tr>
                <th class="text-center">#</th>
                <th>Tipe</th>
                <th>Nama Bank</th>
                <th>Nama Pemilik</th>
                <th>Saldo</th>
                <th class="text-center">Default</th>
                <th class="text-center">Menu</th>
            </tr>
            </thead>

            <tbody>
            <?php if (!empty($wallets)): ?>
                <?php $no = 1; foreach ($wallets as $row): ?>
                <tr>
                    <td class="text-center fw-semibold"><?= $no++; ?></td>
                    <td><span class="badge bg-label-info"><?= esc($row['wallet_type']); ?></span></td>
                    <td><?= esc($row['provider_name']); ?></td>
                    <td><?= esc($row['account_name']); ?></td>
                    <td class="fw-semibold"><?= number_format($row['balance'], 2); ?> <?= esc($row['currency']); ?></td>

                    <!-- Default -->
                    <td class="text-center">
                    <?php if ($row['is_default']): ?>
                        <i class="bx bx-star text-warning fs-5" title="Wallet Favorit"></i>
                    <?php else: ?>
                        <a href="#"
                        class="text-muted btn-set-default"
                        data-url="<?= base_url('wallet/set_default/' . $row['id']); ?>">
                        <i class="bx bx-star"></i>
                        </a>
                    <?php endif; ?>
                    </td>

                    <!-- Aksi -->
                    <td class="text-center">
                    <div class="dropdown position-static">
                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                        <i class="bx bx-dots-vertical-rounded"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                        <a class="dropdown-item wallet-action"
                            href="#"
                            data-url="<?= site_url('transfer/deposit/' . $row['id']); ?>"
                            data-title="Deposit ke <?= esc($row['account_name']); ?>">
                            <i class="bx bx-upload text-success me-2"></i> Deposit
                        </a>
                        <a class="dropdown-item wallet-action"
                            href="#"
                            data-url="<?= site_url('transfer/withdraw/' . $row['id']); ?>"
                            data-title="Withdraw dari <?= esc($row['account_name']); ?>">
                            <i class="bx bx-download text-danger me-2"></i> Withdraw
                        </a>
                        <a class="dropdown-item wallet-action"
                            href="#"
                            data-url="<?= site_url('transfer/internal/' . $row['id']); ?>"
                            data-title="Transfer dari <?= esc($row['account_name']); ?>">
                            <i class="bx bx-transfer text-secondary me-2"></i> Transfer
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="<?= base_url('wallet/edit/' . $row['id']); ?>">
                            <i class="bx bx-edit-alt text-warning me-2"></i> Edit
                        </a>
                        <a class="dropdown-item text-danger btn-delete-wallet"
                            href="#"
                            data-url="<?= base_url('wallet/delete/' . $row['id']); ?>">
                            <i class="bx bx-trash me-2"></i> Hapus
                        </a>
                        </div>
                    </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                <td colspan="7" class="text-center text-muted py-5">
                    <i class="bx bx-wallet fs-2 d-block mb-2"></i> Belum ada data wallet.
                </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>
    </div>
</div>