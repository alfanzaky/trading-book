document.addEventListener('DOMContentLoaded', async () => {
  const els = {
    profit: document.getElementById('dashTotalProfit'),
    loss: document.getElementById('dashTotalLoss'),
    psych: document.getElementById('dashPsychSummary'),
    volume: document.getElementById('dashVolume'),
    win: document.getElementById('dashWinRate'),
    txList: document.getElementById('recentTransactions')
  };

  const ENDPOINT = `${BASE_URL}transaction/summary/data`;

  // === Helper: animasi angka ===
  function animateValue(el, start, end, duration, prefix = '$', colorize = false) {
    if (!el) return;
    let startTime;
    const isNegative = end < 0;
    function step(t) {
      if (!startTime) startTime = t;
      const p = Math.min((t - startTime) / duration, 1);
      const val = start + (end - start) * p;
      const sign = val >= 0 ? '+' : '';
      el.textContent = `${prefix}${sign}${val.toFixed(2)}`;
      if (colorize) {
        el.className = val >= 0 ? 'fw-bold text-success' : 'fw-bold text-danger';
      }
      if (p < 1) requestAnimationFrame(step);
    }
    requestAnimationFrame(step);
  }

  // === Helper: group transaksi per tanggal ===
  function groupTransactionsByDate(trades) {
    const map = {};
    trades.forEach(t => {
      const date = t.close_time?.slice(0, 10) || 'Unknown';
      if (!map[date]) map[date] = 0;
      map[date] += parseFloat(t.profit || 0);
    });
    return Object.entries(map).map(([date, value]) => ({ date, profit: value }));
  }

  // === Load data utama dashboard ===
  async function loadDashboard() {
    try {
      const res = await fetch(ENDPOINT, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
      const data = await res.json();
      if (data.status !== 'success') throw new Error('Data tidak valid');

      const s = data.summary;
      const totalProfit = parseFloat(s.total_profit || 0);
      const totalLoss = parseFloat(s.total_loss || 0);
      const totalVolume = parseFloat(s.total_volume || 0);
      const winRate = parseFloat(s.win_rate || 0);
      const net = totalProfit + totalLoss; // loss sudah negatif

      // === Update tampilan angka ===
      animateValue(els.profit, 0, totalProfit, 1000, '$', true);
      animateValue(els.loss, 0, totalLoss, 1000, '$', true);
      els.volume.textContent = `${totalVolume.toFixed(2)} lot`;
      els.win.textContent = `${winRate.toFixed(1)}%`;
      els.psych.textContent = data.psychology?.summary || 'Belum ada data psikologi.';

      // === Chart ambil dari transaksi ===
      renderChartFromTrades(s.recent_trades || []);

      // === Daftar transaksi ===
      renderTransactions(s.recent_trades || []);
    } catch (e) {
      console.warn('⚠️ Dashboard fallback aktif, demo mode on:', e.message);
      renderChartFromTrades([
        { close_time: '2025-11-03', profit: 25.4 },
        { close_time: '2025-11-04', profit: -10.8 },
        { close_time: '2025-11-05', profit: 45.2 },
        { close_time: '2025-11-06', profit: -8.4 },
        { close_time: '2025-11-07', profit: 20.1 }
      ]);
      els.profit.textContent = '+245.20';
      els.loss.textContent = '-120.50';
      els.psych.textContent = '📊 Mode demo aktif.';
      els.txList.innerHTML = `<li>EURUSD +25.6</li><li>XAUUSD -12.4</li><li>USDJPY +8.3</li>`;
    }
  }

  // === Chart generator dari data transaksi ===
  function renderChartFromTrades(trades) {
    const el = document.querySelector('#weeklyPerformanceChart');
    if (!el || !trades.length) return;

    const grouped = groupTransactionsByDate(trades);
    const categories = grouped.map(x => x.date);
    const profits = grouped.map(x => x.profit);

    new ApexCharts(el, {
      chart: {
        type: 'line',
        height: 300,
        toolbar: { show: false }
      },
      stroke: {
        curve: 'smooth',
        width: 3
      },
      markers: {
        size: 5
      },
      colors: ['#2563eb'],
      series: [
        {
          name: 'Net Profit',
          data: profits
        }
      ],
      xaxis: {
        categories: categories,
        labels: { rotate: -45 }
      },
      yaxis: {
        labels: { formatter: val => `$${val.toFixed(2)}` }
      },
      tooltip: {
        y: { formatter: val => `$${val.toFixed(2)}` }
      }
    }).render();
  }

  // === Render transaksi terakhir ===
  function renderTransactions(trades) {
    if (!els.txList) return;
    els.txList.innerHTML = trades.length
      ? trades
          .slice(0, 5)
          .map(
            t => `
          <li class="d-flex justify-content-between mb-2">
            <span>${t.symbol || '-'}</span>
            <span class="${t.profit >= 0 ? 'text-success' : 'text-danger'} fw-bold">
              ${t.profit >= 0 ? '+' : ''}${parseFloat(t.profit || 0).toFixed(2)}
            </span>
          </li>`
          )
          .join('')
      : '<li>Tidak ada transaksi terbaru.</li>';
  }

  loadDashboard();
});
