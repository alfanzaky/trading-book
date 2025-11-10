<?php
/**
 * trading_psych_helper.php
 * ------------------------
 * Helper analisis psikologi trading user.
 *
 * Bisa dipakai dua cara:
 * 1️⃣ analyzeTradingPsychology($summary) → untuk hasil summary dari controller
 * 2️⃣ analyzeTradingPsychologyWrapper($userId, $startDate, $endDate) → untuk analisis otomatis dari DB
 */

use Config\Database;

/**
 * 🔹 Fungsi utama — analisis berdasarkan summary array
 *
 * @param array $s
 * @return array
 */
if (!function_exists('analyzeTradingPsychology')) {
    function analyzeTradingPsychology(array $s): array
    {
        $warnings = [];

        if (($s['win_rate'] ?? 0) > 75 && abs((float)($s['avg_loss'] ?? 0)) > ((float)($s['avg_profit'] ?? 0)) * 1.5)
            $warnings[] = 'Win rate tinggi, tapi loss per trade besar — indikasi menahan rugi terlalu lama.';

        if (($s['win_rate'] ?? 0) < 40)
            $warnings[] = 'Win rate rendah. Evaluasi strategi entry dan money management.';

        if (($s['total_trades'] ?? 0) > 200)
            $warnings[] = 'Terlalu banyak transaksi. Bisa jadi overtrading.';

        if (($s['total_volume'] ?? 0) > 5 && ($s['total_profit'] ?? 0) < 0)
            $warnings[] = 'Volume besar tapi hasil negatif — tanda trading agresif tanpa kontrol risiko.';

        if (!empty($s['avg_time'])) {
            [$h, $m] = preg_split('/[hm\s]+/', $s['avg_time']);
            if ((int)$h === 0 && (int)$m < 5)
                $warnings[] = 'Holding time sangat singkat. Kemungkinan impulsif entry-exit.';
        }

        if (!empty($s['buy_sell'])) {
            if (($s['buy_sell']['buy'] ?? 0) > 90 || ($s['buy_sell']['sell'] ?? 0) > 90)
                $warnings[] = 'Dominan satu arah (buy/sell). Kurangi bias arah market.';
        }

        if (($s['win_rate'] ?? 0) > 60 && ($s['total_volume'] ?? 0) < 1)
            $warnings[] = 'Hasil bagus tapi volume kecil — potensi kurang percaya diri.';

        return $warnings;
    }
}

/**
 * 🔹 Fungsi pembungkus — analisa langsung dari database (hemat memori)
 *
 * @param int $userId
 * @param string|null $startDate
 * @param string|null $endDate
 * @return array{warnings:int,messages:array,summary:string}
 */
if (!function_exists('analyzeTradingPsychologyWrapper')) {
    function analyzeTradingPsychologyWrapper(int $userId, ?string $startDate = null, ?string $endDate = null): array
    {
        $result = [
            'warnings' => 0,
            'messages' => [],
            'summary'  => 'Tidak ada data psikologi yang tersedia.'
        ];

        try {
            $db = Database::connect();
            $builder = $db->table('transactions t');
            $builder->select('t.profit_loss, t.open_time')
                    ->join('accounts a', 'a.id = t.account_id', 'left')
                    ->where('a.user_id', $userId);

            if (!empty($startDate))
                $builder->where('DATE(t.open_time) >=', $startDate);
            if (!empty($endDate))
                $builder->where('DATE(t.open_time) <=', $endDate);

            // Hindari kehabisan memori kalau datanya ribuan
            $builder->limit(5000);

            $trades = $builder->get()->getResultArray();

            // 🟡 Jika tidak ada transaksi sama sekali
            if (empty($trades)) {
                $result['summary'] = '📭 Belum ada transaksi tercatat untuk periode ini.';
                return $result;
            }

            $count = count($trades);
            $win = $loss = $consecLoss = $maxConsecLoss = 0;
            $totalPL = 0.0;

            foreach ($trades as $t) {
                $pl = (float) ($t['profit_loss'] ?? 0);
                $totalPL += $pl;

                if ($pl > 0) {
                    $win++;
                    $consecLoss = 0;
                } else {
                    $loss++;
                    $consecLoss++;
                    if ($consecLoss > $maxConsecLoss) {
                        $maxConsecLoss = $consecLoss;
                    }
                }
            }

            $winrate = $count ? $win / $count : 0;
            $avgPL = $count ? $totalPL / $count : 0;

            // 🟠 Jika semua PL 0 (tidak ada hasil profit/loss real)
            if ($win === 0 && $loss === 0) {
                $result['summary'] = '📊 Data transaksi ditemukan tapi belum ada hasil profit/loss yang tercatat.';
                return $result;
            }

            // ==== Analisa heuristik ====
            $warnings = 0;
            $messages = [];

            if ($count > 25) {
                $warnings++;
                $messages[] = "Jumlah transaksi terlalu banyak ({$count}) — potensi overtrading.";
            }

            if ($maxConsecLoss >= 3) {
                $warnings++;
                $messages[] = "Terdeteksi {$maxConsecLoss} kali rugi berturut-turut — kemungkinan trading dilakukan secara emosional.";
            }

            if ($winrate < 0.45 && $count >= 10) {
                $warnings++;
                $messages[] = "Winrate hanya " . round($winrate * 100, 1) . "% — indikasi kehilangan fokus atau strategi tidak disiplin.";
            }

            if ($avgPL < 0) {
                $warnings++;
                $messages[] = "Rata-rata hasil per transaksi negatif (" . number_format($avgPL, 2) . "). Perlu kontrol risiko yang lebih ketat.";
            }

            $summary = empty($messages)
                ? "✅ Tidak ada peringatan signifikan. Disiplin tetap terjaga 👍"
                : '• ' . implode('<br>• ', array_map('esc', $messages));

            $result = [
                'warnings' => $warnings,
                'messages' => $messages,
                'summary'  => $summary
            ];
        } catch (\Throwable $e) {
            log_message('error', 'Gagal menganalisa psikologi trading: ' . $e->getMessage());
        }

        return $result;
    }
}
