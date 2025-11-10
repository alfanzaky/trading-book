<?php

namespace App\Controllers\Transaction;

use App\Controllers\BaseController;
use App\Models\AccountModel;
use App\Models\SummaryModel;

class Summary extends BaseController
{
    protected $accountModel;
    protected $summaryModel;

    public function __construct()
    {
        $this->accountModel = new AccountModel();
        $this->summaryModel = new SummaryModel();
        helper('trading_psych');
    }

    public function index()
    {
        $userId = session()->get('user_id');
        if (!$userId) return redirect()->to('/login');

        $accounts = $this->accountModel
            ->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->findAll();

        return view('transaction/summary_view', [
            'title' => 'Trading Summary',
            'accounts' => $accounts
        ]);
    }

    /**
     * 🔄 Endpoint untuk summary data (JSON)
     */
    public function data()
    {
        $userId = session()->get('user_id');
        if (!$userId) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'User belum login']);
        }

        $db = \Config\Database::connect();
        $accountId = $this->request->getGet('account_id');
        $startDate = $this->request->getGet('start_date');
        $endDate   = $this->request->getGet('end_date');

        // =============== BASE SUMMARY QUERY ==================
        $sql = "
            SELECT
                COUNT(t.id) AS total_trades,
                SUM(CASE WHEN t.profit_loss > 0 THEN 1 ELSE 0 END) AS win_trades,
                SUM(CASE WHEN t.profit_loss < 0 THEN 1 ELSE 0 END) AS loss_trades,
                SUM(t.lot_size) AS total_volume,
                SUM(CASE WHEN t.profit_loss > 0 THEN t.profit_loss ELSE 0 END) AS total_profit,
                SUM(CASE WHEN t.profit_loss < 0 THEN t.profit_loss ELSE 0 END) AS total_loss,
                AVG(CASE WHEN t.profit_loss > 0 THEN t.profit_loss END) AS avg_profit,
                AVG(CASE WHEN t.profit_loss < 0 THEN t.profit_loss END) AS avg_loss
            FROM transactions t
            JOIN accounts a ON a.id = t.account_id
            WHERE a.user_id = ?
        ";
        $params = [$userId];

        if (!empty($accountId)) {
            $sql .= " AND t.account_id = ? ";
            $params[] = $accountId;
        }
        if (!empty($startDate)) {
            $sql .= " AND t.open_time >= ? ";
            $params[] = $startDate . ' 00:00:00';
        }
        if (!empty($endDate)) {
            $sql .= " AND t.open_time <= ? ";
            $params[] = $endDate . ' 23:59:59';
        }

        $summary = $db->query($sql, $params)->getRowArray() ?? [];
        $summary = array_map(fn($v) => $v ?? 0, $summary);

        // =============== DERIVED METRICS ==================
        $totalTrades = (int) $summary['total_trades'];
        $winTrades   = (int) $summary['win_trades'];
        $winRate     = $totalTrades ? round(($winTrades / $totalTrades) * 100, 2) : 0;
        $profitFactor = abs($summary['total_loss']) > 0
            ? round(($summary['total_profit'] / abs($summary['total_loss'])), 2)
            : 0;

        // =============== PAIR TERBANYAK ==================
        $sqlPair = "
            SELECT t.symbol, COUNT(*) AS c
            FROM transactions t
            JOIN accounts a ON a.id = t.account_id
            WHERE a.user_id = ?
        ";
        $paramsPair = [$userId];
        if (!empty($accountId)) {
            $sqlPair .= " AND t.account_id = ? ";
            $paramsPair[] = $accountId;
        }
        if (!empty($startDate)) {
            $sqlPair .= " AND t.open_time >= ? ";
            $paramsPair[] = $startDate . ' 00:00:00';
        }
        if (!empty($endDate)) {
            $sqlPair .= " AND t.open_time <= ? ";
            $paramsPair[] = $endDate . ' 23:59:59';
        }
        $sqlPair .= " GROUP BY t.symbol ORDER BY c DESC LIMIT 1";
        $pairRow = $db->query($sqlPair, $paramsPair)->getRowArray();
        $topSymbol = $pairRow['symbol'] ?? '-';

        // =============== TRADE TYPE DISTRIBUTION ==================
        $sqlType = "
            SELECT 
                SUM(CASE WHEN LOWER(t.type)='buy' THEN 1 ELSE 0 END) AS buy_count,
                SUM(CASE WHEN LOWER(t.type)='sell' THEN 1 ELSE 0 END) AS sell_count
            FROM transactions t
            JOIN accounts a ON a.id = t.account_id
            WHERE a.user_id = ?
        ";
        $paramsType = [$userId];
        if (!empty($accountId)) {
            $sqlType .= " AND t.account_id = ? ";
            $paramsType[] = $accountId;
        }
        if (!empty($startDate)) {
            $sqlType .= " AND t.open_time >= ? ";
            $paramsType[] = $startDate . ' 00:00:00';
        }
        if (!empty($endDate)) {
            $sqlType .= " AND t.open_time <= ? ";
            $paramsType[] = $endDate . ' 23:59:59';
        }

        $typeRow = $db->query($sqlType, $paramsType)->getRowArray() ?? [];
        $buy = (int) ($typeRow['buy_count'] ?? 0);
        $sell = (int) ($typeRow['sell_count'] ?? 0);
        $buySell = [
            'buy'  => $totalTrades ? round(($buy / $totalTrades) * 100, 2) : 0,
            'sell' => $totalTrades ? round(($sell / $totalTrades) * 100, 2) : 0
        ];

        // =============== AVERAGE HOLDING TIME ==================
        $sqlTime = "
            SELECT AVG(TIMESTAMPDIFF(SECOND, t.open_time, t.close_time)) AS avg_sec
            FROM transactions t
            JOIN accounts a ON a.id = t.account_id
            WHERE a.user_id = ?
        ";
        $paramsTime = [$userId];
        if (!empty($accountId)) {
            $sqlTime .= " AND t.account_id = ? ";
            $paramsTime[] = $accountId;
        }
        if (!empty($startDate)) {
            $sqlTime .= " AND t.open_time >= ? ";
            $paramsTime[] = $startDate . ' 00:00:00';
        }
        if (!empty($endDate)) {
            $sqlTime .= " AND t.open_time <= ? ";
            $paramsTime[] = $endDate . ' 23:59:59';
        }

        $timeRow = $db->query($sqlTime, $paramsTime)->getRowArray() ?? [];
        $avgSec = (int) ($timeRow['avg_sec'] ?? 0);
        $hours = floor($avgSec / 3600);
        $minutes = floor(($avgSec % 3600) / 60);
        $avgTime = "{$hours}h {$minutes}m";

        // =============== FINAL SUMMARY ARRAY ==================
        $summary = array_merge($summary, [
            'win_rate'      => $winRate,
            'profit_factor' => $profitFactor,
            'top_symbol'    => $topSymbol,
            'buy_sell'      => $buySell,
            'avg_time'      => $avgTime
        ]);

        // =============== PSYCHOLOGY ANALYSIS ==================
        $psychology = analyzeTradingPsychologyWrapper($userId, $startDate, $endDate);

        // =============== SAVE SUMMARY ==================
        $this->summaryModel->saveSummary([
            'user_id'       => $userId,
            'account_id'    => $accountId ?? 0,
            'period_start'  => $startDate ?? date('Y-m-d'),
            'period_end'    => $endDate ?? date('Y-m-d'),
            'total_trades'  => $summary['total_trades'],
            'win_rate'      => $winRate,
            'profit_factor' => $profitFactor,
            'total_profit'  => $summary['total_profit'],
            'total_loss'    => $summary['total_loss'],
            'notes'         => $psychology['summary'] ?? 'Auto summary generated',
            'warnings'      => $psychology['warnings'] ?? 0
        ]);

        // =============== RETURN JSON ==================
        return $this->response->setJSON([
            'status'     => 'success',
            'summary'    => $summary,
            'psychology' => $psychology
        ]);
    }
}
