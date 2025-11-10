<?php

use App\Models\AccountModel;

/**
 * Ambil equity akun aktif berdasarkan user_id dan account_id.
 * Digunakan oleh Trading Planner, Wallet, dll.
 *
 * @param int $userId
 * @param int $accountId
 * @return array
 * [
 *   'status'  => 'success' | 'error',
 *   'message' => string,
 *   'equity'  => float
 * ]
 */
if (!function_exists('get_active_equity')) {
    function get_active_equity(int $userId, int $accountId): array
    {
        if (!$userId || !$accountId) {
            return [
                'status'  => 'error',
                'message' => 'Parameter user_id atau account_id tidak valid.',
                'equity'  => 0
            ];
        }

        $accountModel = new AccountModel();
        $account = $accountModel
            ->select('balance AS equity')
            ->where('user_id', $userId)
            ->where('id', $accountId)
            ->first();

        if (!$account) {
            return [
                'status'  => 'error',
                'message' => 'Akun tidak ditemukan.',
                'equity'  => 0
            ];
        }

        return [
            'status'  => 'success',
            'message' => 'Equity berhasil diambil dari balance.',
            'equity'  => (float) $account['equity']
        ];
    }
}
