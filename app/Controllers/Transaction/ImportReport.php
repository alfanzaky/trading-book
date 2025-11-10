<?php

namespace App\Controllers\Transaction;

use App\Controllers\BaseController;
use App\Models\TransactionModel;

helper('mt5_report');

class ImportReport extends BaseController
{
    /**
     * Upload dan preview file MT5 report
     */
    public function preview()
    {
        $userId = session()->get('user_id');
        if (!$userId) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'User tidak login'
            ]);
        }

        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Invalid request type'
            ])->setStatusCode(400);
        }

        $file = $this->request->getFile('report_file');
        $accountId = $this->request->getPost('account_id');

        if (!$file || !$file->isValid()) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'File tidak valid'
            ]);
        }

        $path = WRITEPATH . 'uploads/mt5_reports/';
        if (!is_dir($path)) mkdir($path, 0777, true);

        $newName = $file->getRandomName();
        $file->move($path, $newName);
        $fullPath = $path . $newName;

        $previewData = parse_mt5_xlsx($fullPath, $accountId);

        if (empty($previewData)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal membaca data dari file. Format tidak dikenali atau kosong.'
            ]);
        }

        return $this->response
            ->setContentType('application/json')
            ->setJSON([
                'status' => 'success',
                'preview' => $previewData
            ]);
    }

    /**
     * Simpan hasil import ke database
     */
    public function save()
    {
        $userId = session()->get('user_id');
        if (!$userId) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'User tidak login'
            ]);
        }

        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Invalid request type'
            ])->setStatusCode(400);
        }

        $data = $this->request->getJSON(true);
        $transactions = $data['transactions'] ?? [];
        $accountId = $data['account_id'] ?? null;

        if (empty($transactions) || !$accountId) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Data transaksi atau akun tidak lengkap'
            ]);
        }

        $transactionModel = new TransactionModel();
        $count = 0;
        $skipped = 0;
        $duplicates = 0;
        $skippedRows = [];
        $duplicateRows = [];
        $validRows = [];

        foreach ($transactions as $i => $t) {
            $positionId = trim($t['position_id'] ?? '');
            if ($positionId === '') {
                $skipped++;
                $skippedRows[] = ['index' => $i + 1, 'reason' => 'position_id kosong'];
                continue;
            }

            $exists = $transactionModel
                ->where('account_id', $accountId)
                ->where('position_id', $positionId)
                ->first();

            if ($exists) {
                $duplicates++;
                $duplicateRows[] = ['index' => $i + 1, 'reason' => "Duplikat position_id: $positionId"];
                continue;
            }

            $symbol = strtoupper(trim($t['symbol'] ?? ''));
            $type   = strtolower(trim($t['type'] ?? ''));
            if ($symbol === '' || !in_array($type, ['buy', 'sell'])) {
                $skipped++;
                $skippedRows[] = ['index' => $i + 1, 'reason' => 'Data tidak valid (symbol/type kosong)'];
                continue;
            }

            $validRows[] = [
                'account_id'  => $accountId,
                'position_id' => $positionId,
                'type'        => strtoupper($type),
                'symbol'      => $symbol,
                'lot_size'    => (float)($t['lot_size'] ?? 0),
                'open_price'  => (float)($t['open_price'] ?? 0),
                'close_price' => (float)($t['close_price'] ?? 0),
                'profit_loss' => (float)($t['profit_loss'] ?? 0),
                'open_time'   => $t['open_time'] ?? null,
                'close_time'  => $t['close_time'] ?? null,
                'created_at'  => date('Y-m-d H:i:s'),
            ];
        }

        if (!empty($validRows)) {
            $transactionModel->insertBatch($validRows);
            $count = count($validRows);
        }

        return $this->response
            ->setContentType('application/json')
            ->setJSON([
                'status' => 'success',
                'message' => "$count transaksi disimpan. ($skipped dilewati, $duplicates duplikat diabaikan)",
                'skipped_rows' => $skippedRows,
                'duplicate_rows' => $duplicateRows,
                'redirect' => base_url('transaction/trades')
            ]);
    }
}
