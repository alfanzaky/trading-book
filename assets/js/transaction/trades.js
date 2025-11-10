document.addEventListener('DOMContentLoaded', () => {
  // === Elemen utama ===
  const tableBody = document.getElementById('transactionBody');
  const pagination = document.getElementById('paginationContainer');
  const searchInput = document.getElementById('searchInput');
  const startDate = document.getElementById('startDate');
  const endDate = document.getElementById('endDate');
  const filterBtn = document.getElementById('filterDateBtn');
  const resetBtn = document.getElementById('resetDateBtn');
  const accountFilter = document.getElementById('accountFilter');
  const uploadForm = document.getElementById('uploadForm');
  const previewBody = document.getElementById('previewTableBody');
  const saveBtn = document.getElementById('saveImportBtn');
  const previewModal = document.getElementById('previewModal')
    ? new bootstrap.Modal(document.getElementById('previewModal'))
    : null;

  let currentPage = 1, limit = 10;
  let previewData = [];
  let selectedAccountId = null;

  // === Utility Fetch JSON ===
  async function fetchJSON(url, options = {}) {
    try {
      const res = await fetch(url, {
        method: options.method || 'GET',
        credentials: 'include',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          ...(options.headers || {})
        },
        body: options.body || null
      });
      if (!res.ok) throw new Error(`${res.status} ${res.statusText}`);
      const type = res.headers.get('content-type') || '';
      return type.includes('json') ? res.json() : JSON.parse(await res.text());
    } catch (err) {
      console.error('❌ fetchJSON Error:', err);
      showSwal('Terjadi kesalahan koneksi ke server.', 'error');
      return { transactions: [], pager: { currentPage: 1, totalPages: 1 } };
    }
  }

  // === SweetAlert Helper ===
  function showSwal(message, type = 'info', toast = true) {
    Swal.fire({
      icon: type === 'danger' ? 'error' : type,
      title: message,
      toast: toast,
      position: toast ? 'top-end' : 'center',
      showConfirmButton: false,
      timer: 3500,
      timerProgressBar: true,
      customClass: { popup: 'shadow-sm' }
    });
  }

  // === Endpoint API ===
  const TRADE_URL = `${BASE_URL}transaction/trades`;
  const IMPORT_PREVIEW_URL = `${BASE_URL}transaction/import-report/preview`;
  const IMPORT_SAVE_URL = `${BASE_URL}transaction/import-report/save`;

  // === Load Halaman Transaksi ===
  async function loadPage(page = 1, query = '') {
    currentPage = page;
    const search = query || searchInput.value || '';
    const start = startDate.value || '';
    const end = endDate.value || '';
    const accountId = accountFilter?.value || '';

    tableBody.innerHTML = `
      <tr><td colspan="10" class="text-center text-muted py-4">
        <div class="spinner-border text-primary me-2"></div> Memuat data...
      </td></tr>`;

    try {
      const params = new URLSearchParams({
        page,
        search,
        start_date: start,
        end_date: end,
        account_id: accountId
      });
      const data = await fetchJSON(`${TRADE_URL}?${params}`);
      renderTable(data.transactions || []);
      renderPagination(data.pager || { currentPage: 1, totalPages: 1 });
    } catch (e) {
      showSwal('Gagal memuat data: ' + e.message, 'error');
      tableBody.innerHTML = `
        <tr><td colspan="10" class="text-center text-danger py-4">
          <i class="bx bx-error-circle me-1"></i> Gagal memuat data transaksi.
        </td></tr>`;
    }
  }

  // === Render Tabel Transaksi ===
  function renderTable(rows) {
    if (!rows.length) {
      tableBody.innerHTML = `
        <tr><td colspan="10" class="text-center py-4 text-muted">
          Tidak ada transaksi ditemukan.
        </td></tr>`;
      return;
    }

    const html = rows.map((t, i) => `
      <tr>
        <td>${(i + 1) + (currentPage - 1) * limit}</td>
        <td>${t.open_time ? new Date(t.open_time).toLocaleString('id-ID') : '-'}</td>
        <td>${t.account_name || '-'} (${t.account_login || '-'})</td>
        <td>${t.symbol}</td>
        <td><span class="badge bg-${t.type?.toLowerCase() === 'buy' ? 'success' : 'danger'}">${t.type}</span></td>
        <td>${(+t.lot_size).toFixed(2)}</td>
        <td>${(+t.open_price).toFixed(2)}</td>
        <td>${(+t.close_price).toFixed(2)}</td>
        <td class="${t.profit_loss >= 0 ? 'text-success fw-bold' : 'text-danger fw-bold'}">${(+t.profit_loss).toFixed(2)}</td>
        <td class="text-center">
          <button class="btn btn-sm btn-outline-primary detail-btn" data-json='${JSON.stringify(t)}'>
            <i class="bx bx-search-alt-2"></i>
          </button>
        </td>
      </tr>`).join('');
    tableBody.innerHTML = html;
  }

  // === Render Pagination ===
  function renderPagination(pager) {
    pagination.innerHTML = '';
    const { currentPage: c, totalPages: t } = pager;
    if (!t || t <= 1) return;

    const maxVisible = 5;
    let html = `<ul class="pagination pagination-sm mb-0">`;
    const add = (p, l, disabled = false, active = false) => `
      <li class="page-item ${disabled ? 'disabled' : ''} ${active ? 'active' : ''}">
        <a class="page-link" href="#" data-page="${p}">${l}</a>
      </li>`;
    html += add(c - 1, '«', c === 1);
    let start = Math.max(1, c - Math.floor(maxVisible / 2));
    let end = Math.min(t, start + maxVisible - 1);
    if (end - start < maxVisible - 1) start = Math.max(1, end - maxVisible + 1);
    if (start > 1) {
      html += add(1, '1');
      if (start > 2) html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
    }
    for (let i = start; i <= end; i++) html += add(i, i, false, i === c);
    if (end < t) {
      if (end < t - 1) html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
      html += add(t, t);
    }
    html += add(c + 1, '»', c === t);
    html += `</ul>`;
    pagination.innerHTML = html;
  }

  pagination.addEventListener('click', e => {
    const link = e.target.closest('a.page-link');
    if (!link) return;
    e.preventDefault();
    const page = parseInt(link.dataset.page);
    if (!isNaN(page)) loadPage(page, searchInput.value);
  });

  // === Filter dan Pencarian ===
  filterBtn.addEventListener('click', () => loadPage(1));
  resetBtn.addEventListener('click', () => {
    startDate.value = '';
    endDate.value = '';
    if (accountFilter) accountFilter.value = '';
    loadPage(1);
  });
  if (accountFilter) accountFilter.addEventListener('change', () => loadPage(1));

  let searchTimer;
  searchInput.addEventListener('input', e => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => loadPage(1, e.target.value), 400);
  });

  // === Upload File & Preview ===
  if (uploadForm) {
    uploadForm.addEventListener('submit', async e => {
      e.preventDefault();
      const formData = new FormData(uploadForm);
      selectedAccountId = formData.get('account_id') || null;
      previewBody.innerHTML = `
        <tr><td colspan="8" class="text-center py-4 text-muted">
          <div class="spinner-border text-primary me-2"></div> Memproses file...
        </td></tr>`;
      try {
        const res = await fetch(IMPORT_PREVIEW_URL, {
          method: 'POST',
          body: formData,
          credentials: 'include',
          headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();
        if (data.status !== 'success') {
          previewBody.innerHTML = `
            <tr><td colspan="8" class="text-center text-danger py-4">
              <i class="bx bx-error-circle me-1"></i> ${data.message}
            </td></tr>`;
          return;
        }
        previewData = data.preview || [];
        renderPreview(previewData);
        previewModal.show();
      } catch (err) {
        console.error(err);
        previewBody.innerHTML = `
          <tr><td colspan="8" class="text-center text-danger py-4">
            <i class="bx bx-error-circle me-1"></i> Gagal memproses file.
          </td></tr>`;
      }
    });
  }

  // === Render Preview Table ===
  function renderPreview(rows) {
    // Kalau data kosong
    if (!rows.length) {
      previewBody.innerHTML = `
        <tr><td colspan="8" class="text-center text-muted py-4">
          <i class="bx bx-file-find mb-2 fs-3 d-block opacity-75"></i>
          Tidak ada data untuk ditampilkan.
        </td></tr>`;

      // Reset summary
      document.getElementById('summaryTotalRows').textContent = '0';
      document.getElementById('summaryTotalProfit').textContent = '0.00';
      document.getElementById('summaryTopSymbol').textContent = '-';
      return;
    }

    // === Format angka ===
    const fmt = (v) => isNaN(v) ? '-' : Number(v).toFixed(2);

    // === Render tabel preview ===
    const html = rows.map((t, i) => `
      <tr>
        <td>${i + 1}</td>
        <td>${t.position_id}</td>
        <td>${t.symbol}</td>
        <td>
          <span class="badge bg-${t.type?.toLowerCase() === 'buy' ? 'success' : 'danger'}">
            ${t.type}
          </span>
        </td>
        <td>${fmt(t.lot_size)}</td>
        <td>${fmt(t.open_price)}</td>
        <td>${fmt(t.close_price)}</td>
        <td class="${t.profit_loss >= 0 ? 'text-success fw-bold text-end' : 'text-danger fw-bold text-end'}">
          ${fmt(t.profit_loss)}
        </td>
      </tr>
    `).join('');
    previewBody.innerHTML = html;

    // === Hitung summary statistik ===
    const totalRows = rows.length;
    const totalProfit = rows.reduce((sum, r) => sum + (+r.profit_loss || 0), 0);

    // Hitung pair terbanyak
    const freq = {};
    rows.forEach(r => freq[r.symbol] = (freq[r.symbol] || 0) + 1);
    const topSymbol = Object.entries(freq).sort((a, b) => b[1] - a[1])[0]?.[0] || '-';

    // === Elemen summary ===
    const totalRowsEl = document.getElementById('summaryTotalRows');
    const totalProfitEl = document.getElementById('summaryTotalProfit');
    const topSymbolEl = document.getElementById('summaryTopSymbol');

    // === Animasi angka naik ===
    function animateValue(element, start, end, duration, isDecimal = false) {
      const startTime = performance.now();
      function update(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        const value = start + (end - start) * progress;
        element.textContent = isDecimal
          ? (value >= 0 ? '+' : '') + value.toFixed(2)
          : Math.floor(value).toLocaleString('id-ID');
        if (progress < 1) requestAnimationFrame(update);
      }
      requestAnimationFrame(update);
    }

    // Jalankan animasi untuk jumlah dan profit
    const currentRows = parseInt(totalRowsEl.textContent.replace(/\D/g, '')) || 0;
    const currentProfit = parseFloat(totalProfitEl.textContent.replace(/[^\d.-]/g, '')) || 0;

    animateValue(totalRowsEl, currentRows, totalRows, 1000, false);
    animateValue(totalProfitEl, currentProfit, totalProfit, 1000, true);

    // Update symbol langsung (tanpa animasi)
    topSymbolEl.textContent = topSymbol;

    // Warna profit sesuai hasil
    totalProfitEl.className = totalProfit >= 0 ? 'text-success fw-bold' : 'text-danger fw-bold';
  }

  // === Simpan ke Database ===
  if (saveBtn) {
    saveBtn.addEventListener('click', async () => {
      if (!previewData.length) {
        showSwal('Tidak ada data untuk disimpan.', 'warning');
        return;
      }
      saveBtn.disabled = true;
      saveBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> Menyimpan...`;
      try {
        const res = await fetch(IMPORT_SAVE_URL, {
          method: 'POST',
          credentials: 'include',
          headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: JSON.stringify({
            transactions: previewData,
            account_id: selectedAccountId
          })
        });
        const result = await res.json();
        if (result.status === 'success') {
          showSwal(result.message || 'Data berhasil disimpan', 'success');
          previewModal.hide();
          loadPage(1);
        } else {
          showSwal(result.message || 'Gagal menyimpan data', 'error');
        }
      } catch (err) {
        console.error(err);
        showSwal('Terjadi kesalahan saat menyimpan data.', 'error');
      } finally {
        saveBtn.disabled = false;
        saveBtn.innerHTML = `<i class="bx bx-save me-1"></i> Simpan ke Database`;
      }
    });
  }

  // === Detail Transaksi (reusable) ===
  tableBody.addEventListener('click', e => {
    const btn = e.target.closest('.detail-btn');
    if (!btn) return;
    const data = JSON.parse(btn.dataset.json);
    showDetail(data);
  });

  function showDetail(data) {
    const modalEl = document.getElementById('detailModal');
    if (!modalEl) {
      console.warn('⚠️ detailModal tidak ditemukan di halaman.');
      return;
    }
    const modal = new bootstrap.Modal(modalEl);
    const detailBody = document.getElementById('detailBody');
    const updatedAt = document.getElementById('detailUpdatedAt');
    const labels = {
      position_id: 'Nomor Posisi',
      symbol: 'Instrumen',
      type: 'Tipe Transaksi',
      lot_size: 'Lot',
      open_price: 'Harga Buka',
      close_price: 'Harga Tutup',
      profit_loss: 'Profit / Loss',
      open_time: 'Waktu Buka',
      close_time: 'Waktu Tutup',
      note: 'Catatan'
    };
    const formatCurrency = (num) =>
      new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(num);
    const formatDate = (d) => (d ? new Date(d).toLocaleString('id-ID') : '-');
    const badge = (type) => {
      const t = (type || '').toLowerCase();
      if (t === 'buy') return `<span class="badge bg-success px-3 py-2">BUY</span>`;
      if (t === 'sell') return `<span class="badge bg-danger px-3 py-2">SELL</span>`;
      return `<span class="badge bg-secondary px-3 py-2">${type || '-'}</span>`;
    };
    let html = '';
    for (const [key, val] of Object.entries(data)) {
      if (!(key in labels)) continue;
      let display = val ?? '-';
      if (['open_time', 'close_time'].includes(key)) display = formatDate(val);
      if (key === 'type') display = badge(val);
      if (key === 'profit_loss') display = `${formatCurrency(val)} USD`;
      if (key.includes('price') || key === 'lot_size') display = formatCurrency(val);
      html += `
        <tr>
          <th class="text-muted" style="width: 40%;">${labels[key]}</th>
          <td>${display}</td>
        </tr>`;
    }
    detailBody.innerHTML =
      html || `<tr><td colspan="2" class="text-center text-muted py-4">Tidak ada detail transaksi.</td></tr>`;
    updatedAt.textContent = formatDate(data.updated_at);
    modal.show();
  }

  // === Init Load ===
  loadPage(1);
});
