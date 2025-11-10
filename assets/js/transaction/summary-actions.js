document.addEventListener('DOMContentLoaded', () => {
  // === ELEMENT SELECTOR ===
  const accountFilter = document.getElementById('accountFilter');
  const startDate = document.getElementById('startDate');
  const endDate = document.getElementById('endDate');
  const filterBtn = document.getElementById('filterBtn');
  const resetBtn = document.getElementById('resetBtn');

  const els = {
    totalTrades: document.getElementById('totalTrades'),
    totalProfit: document.getElementById('totalProfit'),
    totalLoss: document.getElementById('totalLoss'),
    winRate: document.getElementById('winRate'),
    avgProfit: document.getElementById('avgProfit'),
    avgLoss: document.getElementById('avgLoss'),
    avgTime: document.getElementById('avgTime'),
    topSymbol: document.getElementById('topSymbol'),
    netProfit: document.getElementById('netProfit'),
    buyPercent: document.getElementById('buyPercent'),
    sellPercent: document.getElementById('sellPercent'),
    totalVolume: document.getElementById('totalVolume'),
    psychCard: document.getElementById('psychCard'),
    psychIcon: document.getElementById('psychIcon'),
    psychMessage: document.getElementById('psychMessage')
  };

  const SUMMARY_URL = `${BASE_URL}transaction/summary/data`;

  // === UTILITY: Animasi Angka Naik ===
  function animateValue(element, start, end, duration, isDecimal = false, prefix = '', suffix = '') {
    const startTime = performance.now();
    function update(currentTime) {
      const elapsed = currentTime - startTime;
      const progress = Math.min(elapsed / duration, 1);
      const value = start + (end - start) * progress;
      element.textContent = prefix + (isDecimal ? value.toFixed(2) : Math.floor(value)) + suffix;
      if (progress < 1) requestAnimationFrame(update);
    }
    requestAnimationFrame(update);
  }

  // === UTILITY: Fetch JSON dari server ===
  async function fetchSummary(params = {}) {
    // buang parameter kosong biar URL bersih
    const cleanParams = Object.fromEntries(Object.entries(params).filter(([_, v]) => v !== ''));
    const query = new URLSearchParams(cleanParams).toString();
    const url = query ? `${SUMMARY_URL}?${query}` : SUMMARY_URL;

    const res = await fetch(url, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      credentials: 'include'
    });

    const text = await res.text();
    try {
      return JSON.parse(text);
    } catch (err) {
      console.error('❌ Response bukan JSON:\n', text);
      throw new Error('Server tidak mengembalikan JSON valid.');
    }
  }

  // === UPDATE CARD PSIKOLOGI ===
  function updatePsychCard(messages) {
    const card = els.psychCard;
    const icon = els.psychIcon;
    const msgContainer = els.psychMessage;

    card.style.display = 'block';
    card.classList.add('animate__animated', 'animate__fadeIn');

    if (!messages.length) {
      card.className = 'card border-0 shadow-sm mb-4 bg-label-success';
      icon.innerHTML = `<i class="bx bx-happy fs-3 text-success"></i>`;
      msgContainer.innerHTML = `
        <span class="text-success fw-semibold">Kondisi trading kamu stabil 🎯</span><br>
        Pertahankan disiplin, terus catat hasil trading, dan jaga emosi sebelum entry berikutnya.`;
    } else {
      card.className = 'card border-0 shadow-sm mb-4 bg-label-warning';
      icon.innerHTML = `<i class="bx bx-brain fs-3 text-warning"></i>`;
      msgContainer.innerHTML = `
        <ul class="mb-0 ps-3 small text-dark">
          ${messages.map(msg => `<li>${msg}</li>`).join('')}
        </ul>`;
    }
  }

  // === FALLBACK Analisis Psikologi Lokal ===
  function analyzeTradingPsychology(s) {
    const warnings = [];
    if (s.win_rate > 75 && Math.abs(s.avg_loss) > s.avg_profit * 1.5)
      warnings.push('Win rate tinggi, tapi rata-rata loss lebih besar dari profit — indikasi terlalu lama menahan rugi.');
    if (s.win_rate < 40)
      warnings.push('Win rate rendah. Evaluasi strategi entry dan money management.');
    if (s.total_trades > 200)
      warnings.push('Terlalu banyak transaksi. Potensi overtrading atau revenge trading.');
    if (s.total_volume > 5 && s.total_profit < 0)
      warnings.push('Volume besar tapi hasil negatif — tanda trading agresif tanpa kontrol risiko.');
    const [h, m] = s.avg_time.split(/[hm\s]+/).map(v => parseInt(v) || 0);
    if (h === 0 && m < 5)
      warnings.push('Holding time terlalu singkat. Kemungkinan impulsif entry-exit tanpa konfirmasi setup.');
    if (s.buy_sell.buy > 90 || s.buy_sell.sell > 90)
      warnings.push('Fokus hanya di satu arah market. Coba lebih fleksibel mengikuti tren.');
    if (s.win_rate > 60 && s.total_volume < 1)
      warnings.push('Hasil bagus tapi volume kecil — bisa jadi kamu terlalu berhati-hati atau takut entry besar.');
    return warnings;
  }

  // === TAMPILKAN DATA RINGKASAN ===
  async function loadSummary(initial = false) {
    const params = {
      account_id: accountFilter?.value || '',
      start_date: startDate.value || '',
      end_date: endDate.value || ''
    };

    // Saat pertama kali buka halaman, tampilkan semua data
    if (initial) {
      params.start_date = '';
      params.end_date = '';
    }

    // Reset angka dulu
    Object.values(els).forEach(el => {
      if (el && el.tagName === 'H5') el.textContent = '0';
    });

    try {
      const data = await fetchSummary(params);

      if (!data || data.status !== 'success') {
        els.psychCard.style.display = 'none';
        showEmptyNote('Tidak ada data ditemukan', data.message || 'Coba ubah filter akun atau tanggal.');
        return;
      }

      const s = data.summary;

      // === ANIMASI ANGKA ===
      animateValue(els.totalTrades, 0, s.total_trades, 800);
      animateValue(els.totalVolume, 0, s.total_volume, 800, true);
      animateValue(els.totalProfit, 0, s.total_profit, 800, true, s.total_profit >= 0 ? '+' : '');
      animateValue(els.totalLoss, 0, s.total_loss, 800, true);
      animateValue(els.winRate, 0, s.win_rate, 1000, true, '', '%');
      animateValue(els.avgProfit, 0, s.avg_profit, 800, true, s.avg_profit >= 0 ? '+' : '');
      animateValue(els.avgLoss, 0, s.avg_loss, 800, true);
      // === HASIL BERSIH (NET PROFIT) ===
      // === HASIL BERSIH (NET PROFIT) ===
      const totalProfit = parseFloat(s.total_profit || 0);
      const totalLoss = parseFloat(s.total_loss || 0);
      const net = totalProfit + totalLoss; // karena loss dari DB biasanya sudah negatif
      animateValue(
        els.netProfit,
        0,
        net,
        800,
        true,
        net >= 0 ? '+' : '',
        ''
      );
      els.netProfit.className = net >= 0 ? 'fw-bold text-success' : 'fw-bold text-danger';
      els.avgTime.textContent = s.avg_time;
      els.topSymbol.textContent = s.top_symbol || '-';
      els.buyPercent.textContent = s.buy_sell.buy + '%';
      els.sellPercent.textContent = s.buy_sell.sell + '%';

      // === WARNA DINAMIS ===
      els.totalProfit.className = s.total_profit >= 0 ? 'fw-bold text-success' : 'fw-bold text-danger';
      els.totalLoss.className = s.total_loss < 0 ? 'fw-bold text-danger' : 'fw-bold text-success';
      els.avgProfit.className = s.avg_profit >= 0 ? 'fw-bold text-success' : 'fw-bold text-danger';
      els.avgLoss.className = s.avg_loss < 0 ? 'fw-bold text-danger' : 'fw-bold text-success';

      // === ANALISIS PSIKOLOGI ===
      let warnings = [];
      let summaryText = '';

      if (data.psychology) {
        // hasil dari PHP wrapper baru
        if (Array.isArray(data.psychology.messages)) {
          warnings = data.psychology.messages;
          summaryText = data.psychology.summary || '';
        }
        // hasil lama (analyzeTradingPsychology biasa)
        else if (Array.isArray(data.psychology)) {
          warnings = data.psychology;
        }
      }

      // kalau gak ada warning tapi backend kasih summary (misalnya "📭 Belum ada transaksi")
      if (!warnings.length && summaryText) {
        els.psychCard.style.display = 'block';
        els.psychCard.className = 'card border-0 shadow-sm mb-4 bg-label-secondary';
        els.psychIcon.innerHTML = `<i class="bx bx-info-circle fs-3 text-muted"></i>`;
        els.psychMessage.innerHTML = `
          <span class="fw-semibold text-dark">Info Psikologi</span><br>
          <span class="small text-muted">${summaryText}</span>`;
        return;
      }

      // fallback ke analisis lokal
      if (!warnings.length) {
        warnings = analyzeTradingPsychology(s);
      }

      updatePsychCard(warnings);

    } catch (err) {
      console.error('❌ Error load summary:', err);
      showEmptyNote('Gagal memuat data', 'Terjadi kesalahan koneksi ke server.');
    }
  }

  // === TAMPILKAN KARTU “TIDAK ADA DATA” ===
  function showEmptyNote(title, message) {
    els.psychCard.style.display = 'block';
    els.psychCard.className = 'card border-0 shadow-sm mb-4 bg-label-secondary';
    els.psychIcon.innerHTML = `<i class="bx bx-info-circle fs-3 text-muted"></i>`;
    els.psychMessage.innerHTML = `
      <span class="fw-semibold text-dark">${title}</span><br>
      <span class="small text-muted">${message}</span>`;
  }

  // === EVENT HANDLING ===
  if (filterBtn) {
    filterBtn.addEventListener('click', e => {
      e.preventDefault();
      loadSummary(false);
    });
  }

  if (resetBtn) {
    resetBtn.addEventListener('click', e => {
      e.preventDefault();
      if (accountFilter) accountFilter.value = '';
      if (startDate) startDate.value = '';
      if (endDate) endDate.value = '';
      loadSummary(true);
    });
  }

  if (accountFilter) {
    accountFilter.addEventListener('change', () => loadSummary(false));
  }

  // === AUTO LOAD PERTAMA ===
  loadSummary(true);
});
