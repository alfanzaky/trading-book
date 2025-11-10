<?php

namespace App\Models;

use CodeIgniter\Model;

class SummaryModel extends Model
{
    protected $table = 'summary';
    protected $primaryKey = 'id';

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = ''; // hanya pakai created_at

    protected $allowedFields = [
        'user_id',
        'account_id',
        'period_start',
        'period_end',
        'total_trades',
        'win_rate',
        'profit_factor',
        'total_profit',
        'total_loss',
        'avg_profit',
        'avg_loss',
        'total_volume',
        'avg_time',
        'top_symbol',
        'notes',
        'warnings',
        'created_at'
    ];

    /** 
     * Ambil summary terakhir berdasarkan user. 
     */
    public function getLastSummary($userId, $accountId = null)
    {
        $builder = $this->where('user_id', $userId);
        if ($accountId) $builder->where('account_id', $accountId);
        return $builder->orderBy('created_at', 'DESC')->first();
    }

    /** 
     * Ambil semua summary dalam rentang waktu tertentu.
     */
    public function getPeriodSummary($userId, $startDate, $endDate, $accountId = null)
    {
        $builder = $this->where('user_id', $userId)
                        ->where('period_start >=', $startDate)
                        ->where('period_end <=', $endDate);
        if ($accountId) $builder->where('account_id', $accountId);
        return $builder->orderBy('period_end', 'DESC')->findAll();
    }

    /** 
     * Simpan atau update summary (daily/weekly).
     */
    public function saveSummary(array $data)
    {
        $existing = $this->where('user_id', $data['user_id'])
                         ->where('account_id', $data['account_id'])
                         ->where('period_start', $data['period_start'])
                         ->first();

        if ($existing) {
            $data['id'] = $existing['id'];
        }

        return $this->save($data);
    }

    /**
     * Hitung seluruh metrik statistik berdasarkan transaksi.
     * Digunakan untuk evaluasi harian/mingguan.
     */
    public function calculateStats(array $transactions)
    {
        if (empty($transactions)) {
            return [
                'total_trades' => 0,
                'total_profit' => 0,
                'total_loss'   => 0,
                'avg_profit'   => 0,
                'avg_loss'     => 0,
                'win_rate'     => 0,
                'profit_factor'=> 0,
                'total_volume' => 0,
                'avg_time'     => '0h 0m',
                'top_symbol'   => '-',
                'buy_sell'     => ['buy' => 0, 'sell' => 0]
            ];
        }

        $totalTrades = count($transactions);
        $profits = array_filter($transactions, fn($t) => $t['profit_loss'] > 0);
        $losses  = array_filter($transactions, fn($t) => $t['profit_loss'] < 0);

        $totalProfit = array_sum(array_column($profits, 'profit_loss'));
        $totalLoss   = array_sum(array_column($losses, 'profit_loss'));
        $winRate     = $totalTrades ? (count($profits) / $totalTrades) * 100 : 0;
        $profitFactor= ($totalLoss != 0) ? abs($totalProfit / $totalLoss) : 0;

        $avgProfit = count($profits) ? $totalProfit / count($profits) : 0;
        $avgLoss   = count($losses) ? $totalLoss / count($losses) : 0;
        $totalVolume = array_sum(array_column($transactions, 'lot_size'));

        // Buy vs Sell
        $buyCount  = count(array_filter($transactions, fn($t) => strtolower($t['type']) === 'buy'));
        $sellCount = count(array_filter($transactions, fn($t) => strtolower($t['type']) === 'sell'));
        $buySellDist = [
            'buy'  => $totalTrades ? round(($buyCount / $totalTrades) * 100, 2) : 0,
            'sell' => $totalTrades ? round(($sellCount / $totalTrades) * 100, 2) : 0
        ];

        // Average holding time
        $totalDurations = 0;
        foreach ($transactions as $t) {
            if (!empty($t['open_time']) && !empty($t['close_time'])) {
                $totalDurations += strtotime($t['close_time']) - strtotime($t['open_time']);
            }
        }
        $avgDuration = $totalTrades ? $totalDurations / $totalTrades : 0;
        $hours = floor($avgDuration / 3600);
        $minutes = floor(($avgDuration % 3600) / 60);
        $avgHoldingTime = "{$hours}h {$minutes}m";

        // Pair terbanyak
        $symbols = array_column($transactions, 'symbol');
        $pairCounts = array_count_values($symbols);
        arsort($pairCounts);
        $topSymbol = array_key_first($pairCounts) ?? '-';

        return [
            'total_trades' => $totalTrades,
            'total_profit' => round($totalProfit, 2),
            'total_loss'   => round($totalLoss, 2),
            'win_rate'     => round($winRate, 2),
            'profit_factor'=> round($profitFactor, 2),
            'avg_profit'   => round($avgProfit, 2),
            'avg_loss'     => round($avgLoss, 2),
            'total_volume' => round($totalVolume, 2),
            'avg_time'     => $avgHoldingTime,
            'top_symbol'   => $topSymbol,
            'buy_sell'     => $buySellDist
        ];
    }
}
