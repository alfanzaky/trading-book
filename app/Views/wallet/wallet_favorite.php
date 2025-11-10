<div class="col-lg-4">
    <div class="card border-0 shadow-sm h-100">
    <?php
        $favorite = null;
        foreach ($wallets as $w) {
        if ($w['is_default']) { $favorite = $w; break; }
        }
    ?>

    <?php if ($favorite): ?>
        <div class="card-header d-flex justify-content-between align-items-center py-3 px-3"
            style="background: linear-gradient(135deg, #2563eb, #38bdf8); color: #fff;">
        <div>
            <h5 class="fw-bold mb-0 text-white"><?= esc($favorite['provider_name']); ?></h5>
            <small class="text-white-75">
            <?= esc($favorite['account_name']); ?> • <?= esc($favorite['wallet_type']); ?>
            </small>
        </div>
        <div class="text-end">
            <h5 class="fw-bold mb-0"><?= number_format($favorite['balance'], 2); ?></h5>
            <small><?= esc($favorite['currency']); ?></small>
        </div>
        </div>

        <div class="card-body" style="max-height: 340px; overflow-y: auto;">
        <?php if (!empty($transactions)): ?>
            <ul class="list-group list-group-flush small">
            <?php foreach ($transactions as $t): ?>
                <?php
                $isDeposit  = stripos($t['transfer_type'], 'Deposit') !== false;
                $isWithdraw = stripos($t['transfer_type'], 'Withdraw') !== false;
                $isTransfer = stripos($t['transfer_type'], 'Transfer') !== false;
                $icon = $isDeposit ? 'bx-download text-danger'
                        : ($isWithdraw ? 'bx-upload text-success' : 'bx-transfer text-secondary');
                $color = $isDeposit ? 'text-success'
                        : ($isWithdraw ? 'text-danger' : 'text-secondary');

                // tampilkan konversi jika ada perbedaan mata uang
                $hasConversion = isset($t['converted_amount']) && 
                                $t['converted_amount'] > 0 &&
                                isset($t['rate']) && 
                                $t['rate'] != 1;

                $amountLabel = number_format($t['amount'], 2) . ' ' . esc($t['currency']);
                $convertedLabel = $hasConversion 
                    ? '<br><small class="text-muted">≈ ' . number_format($t['converted_amount'], 2) . ' ' . esc($t['target_currency'] ?? '') . '</small>'
                    : '';
                ?>
                <li class="list-group-item d-flex justify-content-between align-items-start">
                <div class="me-2"><i class="bx <?= $icon; ?> fs-5"></i></div>
                <div class="flex-fill">
                    <strong><?= esc($t['transfer_type']); ?></strong>
                    <small class="d-block text-muted"><?= date('d M H:i', strtotime($t['created_at'])); ?></small>
                </div>
                <div class="text-end fw-semibold <?= $color; ?>">
                    <?= $amountLabel; ?>
                    <?= $convertedLabel; ?>
                </div>
                </li>
            <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <div class="text-center text-muted py-4">
            <i class="bx bx-time-five fs-2 d-block mb-2"></i>Belum ada transaksi.
            </div>
        <?php endif; ?>
        </div>

    <?php else: ?>
        <div class="card-body text-center text-muted py-5">
        <i class="bx bx-star fs-1 d-block mb-2"></i>Belum ada wallet favorit.
        </div>
    <?php endif; ?>
    </div>
</div>