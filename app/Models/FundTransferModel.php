<?php

namespace App\Models;

use CodeIgniter\Model;

class FundTransferModel extends Model
{
    protected $table = 'fund_transfers';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;

    protected $allowedFields = [
    'user_id',
    'from_wallet_id',
    'to_wallet_id',
    'from_account_id',
    'to_account_id',
    'amount',
    'currency',
    'rate',
    'converted_amount',
    'transfer_type',
    'status',
    'note',
    'created_at',
    'updated_at'
];


    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $beforeInsert = ['setDefaultStatus', 'sanitizeTransferDirection'];
    protected $beforeUpdate = ['sanitizeTransferDirection'];

    /**
     * Set default status jika tidak ditentukan.
     */
    protected function setDefaultStatus(array $data)
    {
        if (!isset($data['data']['status'])) {
            $data['data']['status'] = 'Pending';
        }
        return $data;
    }

    /**
     * Validasi arah dana sesuai jenis transfer
     * - Deposit: from_wallet_id = NULL, to_wallet_id wajib
     * - Withdraw: to_wallet_id = NULL, from_wallet_id wajib
     * - Internal Transfer: keduanya wajib dan tidak boleh sama
     */
    protected function sanitizeTransferDirection(array $data)
    {
        if (!isset($data['data']['transfer_type'])) {
            return $data;
        }

        $type = $data['data']['transfer_type'];

        switch ($type) {
            case 'Deposit':
                $data['data']['from_wallet_id'] = null;
                break;
            case 'Withdraw':
                $data['data']['to_wallet_id'] = null;
                break;
            case 'Internal Transfer':
                if (
                    isset($data['data']['from_wallet_id'], $data['data']['to_wallet_id'])
                    && $data['data']['from_wallet_id'] === $data['data']['to_wallet_id']
                ) {
                    throw new \RuntimeException('from_wallet_id dan to_wallet_id tidak boleh sama untuk Internal Transfer');
                }
                break;
        }
        return $data;
    }

    /* ==================== CUSTOM FUNCTIONS ==================== */

    /**
     * Ambil semua transfer milik user tertentu.
     */
    public function getByUser($userId, $limit = null)
    {
        $builder = $this->where('user_id', $userId)
                        ->orderBy('created_at', 'DESC');
        if ($limit) {
            $builder->limit($limit);
        }
        return $builder->findAll();
    }

    /**
     * Ambil semua transfer yang melibatkan wallet tertentu (masuk/keluar).
     */
    public function getByWallet($walletId, $limit = null)
    {
        $builder = $this->groupStart()
                        ->where('from_wallet_id', $walletId)
                        ->orWhere('to_wallet_id', $walletId)
                        ->groupEnd()
                        ->orderBy('created_at', 'DESC');
        if ($limit) {
            $builder->limit($limit);
        }
        return $builder->findAll();
    }

    /**
     * Dapatkan ringkasan total masuk, keluar, dan net untuk wallet.
     */
    public function getWalletSummary($walletId)
    {
        $in = $this->selectSum('amount')
            ->where('to_wallet_id', $walletId)
            ->where('status', 'Success')
            ->get()
            ->getRow()
            ->amount ?? 0;

        $out = $this->selectSum('amount')
            ->where('from_wallet_id', $walletId)
            ->where('status', 'Success')
            ->get()
            ->getRow()
            ->amount ?? 0;

        return [
            'total_in'  => (float) $in,
            'total_out' => (float) $out,
            'net'       => (float) $in - (float) $out,
        ];
    }

    /**
     * Update status transfer (Pending -> Success / Failed)
     */
    public function updateStatus($id, $status)
    {
        $valid = ['Pending', 'Success', 'Failed'];
        if (!in_array($status, $valid)) {
            throw new \InvalidArgumentException("Status transfer tidak valid: {$status}");
        }

        return $this->update($id, [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Catat deposit baru.
     */
    public function recordDeposit($walletId, $userId, $amount, $currency = 'IDR', $note = null)
    {
        return $this->insert([
            'user_id' => $userId,
            'to_wallet_id' => $walletId,
            'amount' => $amount,
            'currency' => $currency,
            'transfer_type' => 'Deposit',
            'status' => 'Success',
            'note' => $note
        ]);
    }

    /**
     * Catat withdraw baru.
     */
    public function recordWithdraw($walletId, $userId, $amount, $currency = 'IDR', $note = null)
    {
        return $this->insert([
            'user_id' => $userId,
            'from_wallet_id' => $walletId,
            'amount' => $amount,
            'currency' => $currency,
            'transfer_type' => 'Withdraw',
            'status' => 'Success',
            'note' => $note
        ]);
    }

    /**
     * Catat internal transfer antar wallet.
     */
    public function recordTransfer($fromId, $toId, $userId, $amount, $currency = 'IDR', $note = null)
    {
        return $this->insert([
            'user_id' => $userId,
            'from_wallet_id' => $fromId,
            'to_wallet_id' => $toId,
            'amount' => $amount,
            'currency' => $currency,
            'transfer_type' => 'Internal Transfer',
            'status' => 'Success',
            'note' => $note
        ]);
    }
}
