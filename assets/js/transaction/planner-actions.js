document.addEventListener('DOMContentLoaded', () => {
  // === Jalankan script ini hanya di halaman Trading Planner ===
  const calendarEl = document.getElementById('plannerCalendar');
  if (!calendarEl) return;
  console.log('📘 Trading Planner script aktif');

  // === Inisialisasi elemen DOM ===
  const accountSel = document.getElementById('planAccount');
  const dateEl = document.getElementById('planDate');
  const targetEl = document.getElementById('planTarget');
  const lossEl = document.getElementById('planLoss');
  const notesEl = document.getElementById('planNotes');
  const saveBtn = document.getElementById('savePlanBtn');
  const autoBtn = document.getElementById('autoSuggestBtn');
  const evalCard = document.getElementById('evaluationCard');
  const evalContent = document.getElementById('evaluationContent');
  const BASE = `${BASE_URL}transaction/planner/`;

  // === Ambil equity (pakai balance akun sebagai acuan) ===
  async function getEquity(accountId) {
    if (!accountId) return 0;
    try {
      const res = await fetch(`${BASE_URL}user/accounts/get-active-equity?account_id=${accountId}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });
      const json = await res.json();
      if (json.equity !== undefined) {
        console.log('✅ Equity diambil:', json.equity);
        return parseFloat(json.equity || 0);
      }
      console.warn('⚠️ Respon equity tidak valid:', json);
      return 0;
    } catch (e) {
      console.error('❌ Gagal mengambil equity:', e);
      return 0;
    }
  }

  // === 💡 Hitung otomatis target & max loss (AI Suggestion) ===
  if (autoBtn) {
    autoBtn.addEventListener('click', async () => {
      if (autoBtn.disabled) return;
      autoBtn.disabled = true;

      if (!accountSel.value) {
        Swal.fire({ icon: 'warning', title: 'Pilih akun dulu sebelum menghitung otomatis.' });
        autoBtn.disabled = false;
        return;
      }

      Swal.fire({
        title: '⏳ Menghitung rekomendasi...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
      });

      try {
        const res = await fetch(`${BASE}suggest-plan?account_id=${accountSel.value}`, {
          headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const j = await res.json();
        Swal.close();

        if (j.status === 'success') {
          targetEl.value = j.target_profit_percent;
          lossEl.value = j.max_loss_percent;

          await Swal.fire({
            icon: 'info',
            title: '💡 Rekomendasi Sistem',
            html: `
              <div class="text-start">
                <p><b>Target Profit:</b> ${j.target_profit_percent}% 
                   (${j.target_profit.toLocaleString('en-US', { style: 'currency', currency: 'USD' })})</p>
                <p><b>Max Loss:</b> ${j.max_loss_percent}% 
                   (${j.max_loss.toLocaleString('en-US', { style: 'currency', currency: 'USD' })})</p>
                <hr>
                <p>${j.message}</p>
              </div>
            `,
            confirmButtonText: 'OK'
          });
        } else {
          Swal.fire({ icon: 'error', title: j.message || 'Gagal menghitung saran otomatis.' });
        }
      } catch (err) {
        console.error('❌ Error fetch suggestPlan:', err);
        Swal.close();
        Swal.fire({ icon: 'error', title: 'Gagal mengambil data dari server.' });
      } finally {
        setTimeout(() => (autoBtn.disabled = false), 800);
      }
    });
  }

  // === Cegah double submit global ===
  let isSaving = false;

  // === Simpan rencana trading (plan harian) ===
  async function savePlan() {
    if (isSaving) return;
    isSaving = true;
    saveBtn.disabled = true;
    const originalText = saveBtn.innerHTML;
    saveBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...`;

    const payload = {
      account_id: accountSel.value,
      plan_date: dateEl.value,
      target_profit_percent: parseFloat(targetEl.value || 0),
      max_loss_percent: parseFloat(lossEl.value || 0),
      equity: 0,
      notes: notesEl.value || ''
    };

    if (!payload.account_id || !payload.plan_date) {
      Swal.fire({ icon: 'warning', title: 'Akun dan tanggal harus diisi.' });
      saveBtn.innerHTML = originalText;
      isSaving = false;
      saveBtn.disabled = false;
      return;
    }

    payload.equity = await getEquity(payload.account_id);

    try {
      const res = await fetch(`${BASE}save`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(payload)
      });

      const j = await res.json();
      if (j.status === 'success') {
        toast('✅ Rencana disimpan', 'success');
        if (calendar) calendar.refetchEvents();
        // auto refresh evaluasi setelah simpan plan
        await evaluateRange(start, end, accountSel.value);
      } else {
        toast(j.message || '❌ Gagal menyimpan', 'error');
      }
    } catch (err) {
      console.error('❌ Error savePlan:', err);
      toast('❌ Gagal menyimpan rencana (network)', 'error');
    } finally {
      saveBtn.innerHTML = originalText;
      isSaving = false;
      setTimeout(() => { saveBtn.disabled = false; }, 600);
    }
  }

  // === Bind tombol save (interaktif dan aman) ===
  if (saveBtn) {
    saveBtn.addEventListener('click', async e => {
      e.preventDefault();
      if (saveBtn.disabled) return;
      await savePlan();
    });
  }

  // === Inisialisasi kalender FullCalendar ===
  let calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'listWeek',
    locale: 'id',
    height: 360,
    themeSystem: 'standard',

    headerToolbar: {
      left: 'prev,next today',
      center: 'title',
      right: ''
    },
    buttonText: {
      today: 'Minggu Ini'
    },

    titleFormat: { month: 'long', day: 'numeric' },
    noEventsText: 'Belum ada rencana trading minggu ini.',

    listDayFormat: { weekday: 'long', month: 'short', day: 'numeric' },
    listDaySideFormat: false,
    listEventTime: false,

    eventContent: function (arg) {
      const p = arg.event.extendedProps;
      const el = document.createElement('div');
      el.innerHTML = `
        <div class="d-flex align-items-center justify-content-between px-2 py-1 rounded shadow-sm"
            style="background:${p.max_loss_percent > 3 ? '#fee2e2' : '#dbeafe'};
                    border-left:4px solid ${p.max_loss_percent > 3 ? '#dc2626' : '#2563eb'};">
          <div class="small text-dark">
            <b>${p.target_profit_percent}% TP</b> — 
            <span class="text-muted">${p.notes || 'Tanpa catatan'}</span>
          </div>
          <span class="badge bg-${p.max_loss_percent > 3 ? 'danger' : 'primary'}">
            ${p.max_loss_percent}%
          </span>
        </div>`;
      return { domNodes: [el] };
    },

    events: async (fetchInfo, successCallback, failureCallback) => {
      try {
        const res = await fetch(`${BASE}events`, {
          headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();
        console.log(`🗓️ ${data.length} plan ditemukan (list view)`);
        const ev = (data || []).map(p => ({
          id: p.id,
          title: `🎯 TP ${p.target_profit_percent}%`,
          start: p.start || p.plan_date,
          allDay: true,
          extendedProps: p
        }));
        successCallback(ev);
      } catch (err) {
        console.error('❌ Gagal memuat event kalender:', err);
        failureCallback(err);
      }
    },

    eventClick: info => {
      const p = info.event.extendedProps;
      Swal.fire({
        title: '📘 Detail Rencana Trading',
        html: `
          <div class="text-start">
            <p><b>Tanggal:</b> ${p.plan_date || info.event.startStr}</p>
            <p><b>Account ID:</b> ${p.account_id}</p>
            <p><b>Target Profit:</b> ${p.target_profit_percent}%</p>
            <p><b>Max Loss:</b> ${p.max_loss_percent}%</p>
            <p><b>Catatan:</b> ${p.notes || '-'}</p>
          </div>
        `,
        icon: 'info',
        confirmButtonText: 'Tutup'
      });
    }
  });

  calendar.render();

  // === Evaluasi mingguan otomatis ===
  async function evaluateRange(startDate, endDate, accountId) {
    if (!accountId) {
      evalCard.style.display = 'none';
      return;
    }

    evalCard.style.display = 'block';
    evalContent.innerHTML = '<div class="text-muted small py-2">⏳ Menghitung evaluasi mingguan...</div>';

    try {
      const res = await fetch(`${BASE}evaluate-week?start=${startDate}&end=${endDate}&account_id=${accountId}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });
      const j = await res.json();
      if (j.status === 'ok') {
        evalContent.innerHTML = `<div class="animate__animated animate__fadeIn">${j.html}</div>`;
      } else if (j.status === 'empty') {
        evalContent.innerHTML = '<div class="text-muted small">Belum ada rencana trading minggu ini.</div>';
      } else {
        evalContent.innerHTML = '<div class="text-muted small">Tidak ada data evaluasi minggu ini.</div>';
      }
    } catch (err) {
      console.error('❌ Error evaluasi mingguan:', err);
      evalContent.innerHTML = '<div class="text-danger small">Gagal memuat evaluasi mingguan.</div>';
    }
  }

  // === Hitung range minggu (Senin–Minggu) ===
  const today = new Date();
  const start = (() => {
    const d = new Date();
    const day = d.getDay() === 0 ? 7 : d.getDay();
    d.setDate(d.getDate() - day + 1);
    return d.toISOString().slice(0, 10);
  })();
  const end = (() => {
    const d = new Date(start);
    d.setDate(d.getDate() + 6);
    return d.toISOString().slice(0, 10);
  })();

  // === Jalankan evaluasi otomatis saat akun dipilih ===
  if (accountSel) {
    accountSel.addEventListener('change', async () => {
      const equity = await getEquity(accountSel.value);
      if (!targetEl.value) {
        targetEl.placeholder = `Target (%) — equity: ${equity.toLocaleString()}`;
      }
      if (accountSel.value) {
        await evaluateRange(start, end, accountSel.value);
      }
    });
  }

  // === Helper Toast (SweetAlert mini) ===
  function toast(msg, type = 'info') {
    Swal.fire({
      toast: true,
      position: 'top-end',
      icon: type,
      title: msg,
      showConfirmButton: false,
      timer: 2200
    });
  }
});
