<?php

namespace App\Controllers\Transaction;

use App\Controllers\BaseController;
use App\Models\TradingPlanModel;
use App\Models\TransactionModel;
use App\Models\AccountModel;
use App\Models\SummaryModel;

class TradingPlanner extends BaseController
{
    protected $planModel, $transactionModel, $accountModel, $summaryModel;

    public function __construct()
    {
        $this->planModel = new TradingPlanModel();
        $this->transactionModel = new TransactionModel();
        $this->accountModel = new AccountModel();
        $this->summaryModel = new SummaryModel();
    }

    public function index()
    {
        $userId = session()->get('user_id');
        if (!$userId) return redirect()->to('/login');

        $accounts = $this->accountModel
            ->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->findAll();

        return view('transaction/trading_planner_view', [
            'title' => 'Trading Planner',
            'accounts' => $accounts
        ]);
    }

    public function save()
    {
        $userId = session()->get('user_id');
        if (!$userId) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Belum login.']);
        }

        $data = $this->request->getJSON(true);
        if (empty($data)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Data tidak valid.']);
        }

        $accountId = (int) ($data['account_id'] ?? 0);
        $equity = (float) ($data['equity'] ?? 0);
        $targetPercent = (float) ($data['target_profit_percent'] ?? 0);
        $lossPercent = (float) ($data['max_loss_percent'] ?? 0);

        if (!$accountId || !$data['plan_date']) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Akun dan tanggal wajib diisi.']);
        }

        $targetValue = ($targetPercent / 100) * $equity;
        $lossValue = ($lossPercent / 100) * $equity;

        $payload = [
            'user_id' => $userId,
            'account_id' => $accountId,
            'plan_date' => $data['plan_date'],
            'target_profit_percent' => $targetPercent,
            'max_loss_percent' => $lossPercent,
            'target_profit' => $targetValue,
            'max_loss' => $lossValue,
            'notes' => trim($data['notes'] ?? ''),
            'actual_profit' => 0,
            'actual_loss' => 0,
            'evaluation' => ''
        ];

        $this->planModel->insert($payload);
        return $this->response->setJSON(['status' => 'success', 'message' => 'Rencana trading harian disimpan.']);
    }

    public function events()
    {
        $userId = session()->get('user_id');
        if (!$userId) return $this->response->setJSON([]);

        $plans = $this->planModel
            ->where('user_id', $userId)
            ->orderBy('plan_date', 'ASC')
            ->findAll();

        $data = array_map(fn($p) => [
            'id' => $p['id'],
            'title' => "TP {$p['target_profit_percent']}%",
            'start' => $p['plan_date'],
            'plan_date' => $p['plan_date'],
            'account_id' => $p['account_id'],
            'target_profit_percent' => $p['target_profit_percent'],
            'max_loss_percent' => $p['max_loss_percent'],
            'notes' => $p['notes']
        ], $plans);

        return $this->response->setJSON($data);
    }

    /**
     * 💡 Hitung otomatis target berdasarkan summary terakhir & psikologi.
     */
    public function suggestPlan()
    {
        $userId = session()->get('user_id');
        $accountId = $this->request->getGet('account_id');
        $mode = strtolower($this->request->getGet('mode') ?? 'normal');

        if (!$userId || !$accountId) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Parameter tidak lengkap (user/account).'
            ]);
        }

        $account = $this->accountModel
            ->select('balance')
            ->where('user_id', $userId)
            ->where('id', $accountId)
            ->first();

        if (!$account) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Akun tidak ditemukan.'
            ]);
        }

        $balance = (float) $account['balance'];

        // === Ambil summary terakhir user ===
        $summary = $this->summaryModel->getLastSummary($userId, $accountId);
        $winRate = isset($summary['win_rate']) ? (float)$summary['win_rate'] : 50;
        $totalProfit = isset($summary['total_profit']) ? (float)$summary['total_profit'] : 0;
        $totalLoss = isset($summary['total_loss']) ? abs((float)$summary['total_loss']) : 0;
        $notes = strtolower($summary['notes'] ?? '');

        // === Hitung indikator tambahan
        $profitFactor = ($totalLoss > 0) ? round($totalProfit / $totalLoss, 2) : ($totalProfit > 0 ? 999 : 0);
        $netProfitRatio = round((($totalProfit - $totalLoss) / max($balance, 1)) * 100, 2);

        // === Default target
        $targetPercent = 1.5;
        $maxLossPercent = 1.0;
        $message = "ℹ️ Target dihitung berdasarkan performa, efisiensi strategi, dan psikologi.";

        // === Mode dasar berdasar balance
        if ($balance <= 200) {
            if ($mode === 'agresif') {
                $targetPercent = 20;
                $maxLossPercent = 10;
                $message = "🔥 Mode Agresif diaktifkan — target 20%, risiko 10%.";
            } else {
                $targetPercent = 10;
                $maxLossPercent = 5;
                $message = "⚖️ Mode Normal untuk akun kecil — target 10%, risiko 5%.";
            }
        } elseif ($balance <= 500) {
            $targetPercent = ($mode === 'agresif') ? 12 : 8;
            $maxLossPercent = ($mode === 'agresif') ? 6 : 4;
        } elseif ($balance <= 1000) {
            $targetPercent = 5;
            $maxLossPercent = 3;
        } else {
            $targetPercent = 3;
            $maxLossPercent = 2;
        }

        // === Analisis berbasis performa real
        if ($profitFactor > 1.5 && $netProfitRatio > 0) {
            $targetPercent += 3;
            $message .= " ✅ Strategi efisien (PF {$profitFactor}) — target profit ditingkatkan.";
        } elseif ($profitFactor < 1 && $netProfitRatio < 0) {
            $targetPercent = max(1.0, $targetPercent - 5);
            $maxLossPercent = max(1.0, $maxLossPercent - 2);
            $message .= " ⚠️ Strategi belum efisien (PF {$profitFactor}) — target dan risiko diturunkan.";
        }

        // === Winrate check vs PF
        if ($winRate > 70 && $profitFactor < 1.2) {
            $message .= " ⚠️ Win rate tinggi tapi profit factor rendah — indikasi win kecil, loss besar.";
            $targetPercent = max(1.0, $targetPercent - 2);
        } elseif ($winRate < 40 && $profitFactor > 1.5) {
            $message .= " 💡 Win rate rendah tapi PF bagus — strategi high-RR, tetap lanjutkan pola ini.";
        }

        // === Catatan psikologi
        if (str_contains($notes, 'impulsive') || str_contains($notes, 'emosi') || str_contains($notes, 'frustrasi')) {
            $targetPercent = max(1.0, $targetPercent - 3);
            $maxLossPercent = max(1.0, $maxLossPercent - 2);
            $message .= " ⚠️ Emosi/impulsif terdeteksi — sistem menurunkan target dan risiko.";
        } elseif (str_contains($notes, 'discipline') || str_contains($notes, 'calm') || str_contains($notes, 'tenang')) {
            $targetPercent += 1;
            $message .= " ✅ Kondisi psikologis stabil — target dinaikkan sedikit.";
        }

        // === Safety limit
        $targetPercent = min($targetPercent, 25);
        $maxLossPercent = min($maxLossPercent, 12);

        // === Hitung nilai nominal
        $targetProfit = round(($targetPercent / 100) * $balance, 2);
        $maxLoss = round(($maxLossPercent / 100) * $balance, 2);
        $riskRatio = $maxLossPercent > 0 ? round($targetPercent / $maxLossPercent, 2) : 0;

        // === Peringatan risiko
        if ($riskRatio > 2.5) {
            $message .= " ⚠️ Rasio risk-reward terlalu tinggi ({$riskRatio}). Harap sesuaikan posisi.";
        }

        return $this->response->setJSON([
            'status' => 'success',
            'mode' => ucfirst($mode),
            'balance' => $balance,
            'win_rate' => $winRate,
            'profit_factor' => $profitFactor,
            'net_profit_ratio' => $netProfitRatio,
            'target_profit_percent' => $targetPercent,
            'max_loss_percent' => $maxLossPercent,
            'target_profit' => $targetProfit,
            'max_loss' => $maxLoss,
            'risk_ratio' => $riskRatio,
            'message' => $message
        ]);
    }

    public function evaluateWeek()
    {
        $userId = session()->get('user_id');
        $accountId = $this->request->getGet('account_id');
        $start = $this->request->getGet('start');
        $end = $this->request->getGet('end');

        if (!$userId || !$accountId) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Parameter tidak lengkap.']);
        }

        // Ambil rencana trading di minggu tersebut
        $plans = $this->planModel
            ->where('user_id', $userId)
            ->where('account_id', $accountId)
            ->where('plan_date >=', $start)
            ->where('plan_date <=', $end)
            ->orderBy('plan_date', 'ASC')
            ->findAll();

        if (empty($plans)) {
            return $this->response->setJSON(['status' => 'empty']);
        }

        // Ambil data transaksi pada range tanggal yang sama
        $transactions = $this->transactionModel
            ->where('user_id', $userId)
            ->where('account_id', $accountId)
            ->where('close_time >=', $start . ' 00:00:00')
            ->where('close_time <=', $end . ' 23:59:59')
            ->findAll();

        // Hitung total profit/loss
        $actualProfit = array_sum(array_map(fn($t) => $t['profit_loss'] ?? 0, $transactions));
        $targetProfit = array_sum(array_map(fn($p) => $p['target_profit'] ?? 0, $plans));
        $maxLoss = array_sum(array_map(fn($p) => $p['max_loss'] ?? 0, $plans));

        // Hitung rasio risiko
        $riskRatio = ($maxLoss > 0) ? round($actualProfit / abs($maxLoss), 2) : 0;

        // Ambil ringkasan psikologi mingguan
        $psych = $this->summaryModel->getPsychologySummary($userId, $accountId, $start, $end) ?? [];
        $psychWarnings = isset($psych['warnings']) ? (int)$psych['warnings'] : 0;

        // Buat hasil evaluasi
        $evaluation = '';

        if ($actualProfit >= $targetProfit) {
            $evaluation .= "✅ Target profit mingguan tercapai.<br>";
        } else {
            $evaluation .= "❌ Target belum tercapai.<br>";
        }

        if ($riskRatio < 1.5) {
            $evaluation .= "⚠️ Risk ratio terlalu tinggi ({$riskRatio}).<br>";
        } else {
            $evaluation .= "📊 Risk ratio aman ({$riskRatio}).<br>";
        }

        if ($psychWarnings > 2) {
            $evaluation .= "⚠️ Aktivitas trading minggu ini menunjukkan ketidakseimbangan emosi / overtrading.<br>";
        }

        // Susun HTML hasil evaluasi
        $html = '
        <div class="text-start">
            <p><b>Periode:</b> ' . esc($start) . ' s.d. ' . esc($end) . '</p>
            <p><b>Target Profit:</b> $' . number_format($targetProfit, 2) . '</p>
            <p><b>Actual Profit:</b> $' . number_format($actualProfit, 2) . '</p>
            <p><b>Max Loss:</b> $' . number_format($maxLoss, 2) . '</p>
            <p><b>Risk Ratio:</b> ' . $riskRatio . '</p>
            <hr>
            <div>' . $evaluation . '</div>
        </div>
        ';

        return $this->response->setJSON([
            'status' => 'ok',
            'html' => $html
        ]);
    }

}
