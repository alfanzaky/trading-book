<?php

namespace App\Models;

use CodeIgniter\Model;

class TransactionModel extends Model
{
    protected $table = 'transactions';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $allowedFields = [
        'user_id',        // <— tambahkan ini
        'account_id',
        'position_id',
        'type',
        'symbol',
        'open_time',
        'close_time',
        'lot_size',
        'open_price',
        'close_price',
        'profit_loss',
        'note',
        'created_at',
        'updated_at'
    ];

    // --- Ambil semua transaksi milik akun tertentu ---
    public function getByAccount($accountId)
    {
        return $this->where('account_id', $accountId)
                    ->orderBy('open_time', 'DESC')
                    ->findAll();
    }

    // --- Import batch hasil parsing MT5 (insertBatch aman) ---
    public function importBatch(array $parsedRows)
    {
        if (empty($parsedRows)) {
            return false;
        }

        // hindari duplikasi position_id (kalau sudah ada)
        foreach ($parsedRows as $i => $row) {
            if (isset($row['position_id']) && $row['position_id']) {
                $exists = $this->where([
                    'account_id'  => $row['account_id'],
                    'position_id' => $row['position_id']
                ])->first();

                if ($exists) {
                    unset($parsedRows[$i]); // skip jika sudah ada
                }
            }
        }

        if (!empty($parsedRows)) {
            return $this->insertBatch(array_values($parsedRows));
        }

        return false;
    }

    // --- Statistik cepat untuk dashboard ---
    public function getAccountStats($accountId)
    {
        $builder = $this->select('
            COUNT(*) AS total_trades,
            SUM(CASE WHEN profit_loss > 0 THEN 1 ELSE 0 END) AS win_trades,
            SUM(profit_loss) AS total_profit,
            AVG(profit_loss) AS avg_profit_loss,
            SUM(CASE WHEN profit_loss < 0 THEN 1 ELSE 0 END) AS loss_trades,
            AVG(TIMESTAMPDIFF(MINUTE, open_time, close_time)) AS avg_duration
        ')
        ->where('account_id', $accountId);

        $result = $builder->first();

        $totalTrades = (int)($result['total_trades'] ?? 0);
        $winTrades   = (int)($result['win_trades'] ?? 0);
        $lossTrades  = (int)($result['loss_trades'] ?? 0);
        $totalProfit = (float)($result['total_profit'] ?? 0);
        $avgProfit   = (float)($result['avg_profit_loss'] ?? 0);
        $avgDuration = (float)($result['avg_duration'] ?? 0);

        $winRate = $totalTrades > 0
            ? round(($winTrades / $totalTrades) * 100, 2)
            : 0;

        return [
            'total_trades' => $totalTrades,
            'win_trades'   => $winTrades,
            'loss_trades'  => $lossTrades,
            'win_rate'     => $winRate,
            'total_profit' => $totalProfit,
            'avg_profit'   => $avgProfit,
            'avg_duration' => $avgDuration, // menit
        ];
    }
}
