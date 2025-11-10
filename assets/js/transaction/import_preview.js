/**
 * import_preview.js
 * ------------------
 * Script untuk halaman "Preview Import MT5"
 * Menangani tombol simpan, validasi akun, reset form, dan modal hasil import.
 */

document.addEventListener('DOMContentLoaded', () => {
  const previewButton = document.getElementById('previewButton');
  if (!previewButton) return;

  const saveUrl = window.saveUrl || '';
  const tokenName = window.csrfTokenName || '';
  const tokenHash = window.csrfHash || '';

  previewButton.addEventListener('click', async () => {
    const accountInput = document.querySelector('input[name="account_id"]');
    const accountId = accountInput ? accountInput.value : '';
    const rows = Array.from(document.querySelectorAll('tbody tr[data-position]')).map(tr => ({
      position_id: tr.dataset.position,
      open_time: tr.dataset.open_time,
      symbol: tr.dataset.symbol,
      type: tr.dataset.type,
      lot_size: parseFloat(tr.dataset.lot_size) || 0,
      open_price: parseFloat(tr.dataset.open_price) || 0,
      close_price: parseFloat(tr.dataset.close_price) || 0,
      profit_loss: parseFloat(tr.dataset.profit_loss) || 0
    }));

    // Validasi: user wajib pilih akun
    if (!accountId) {
      alert('Silakan pilih akun trading terlebih dahulu sebelum menyimpan.');
      return;
    }

    if (!rows.length) {
      alert('Tidak ada data transaksi untuk disimpan.');
      return;
    }

    previewButton.disabled = true;
    previewButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Memproses...';

    try {
      const res = await fetch(saveUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          [tokenName]: tokenHash
        },
        body: JSON.stringify({ account_id: accountId, transactions: rows })
      });

      const data = await res.json();
      if (data.status !== 'success') throw new Error(data.message || 'Gagal menyimpan.');

      // === Modal hasil import ===
      const modalHTML = `
        <div class="modal fade" id="importResultModal" tabindex="-1">
          <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
              <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Hasil Import Data</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body">
                <div class="alert alert-info">${data.message}</div>
                <div id="importTables"></div>
              </div>
              <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                ${data.redirect ? `<a href="${data.redirect}" class="btn btn-success"><i class="bx bx-check-circle"></i> Lanjutkan</a>` : ''}
              </div>
            </div>
          </div>
        </div>`;

      document.body.insertAdjacentHTML('beforeend', modalHTML);
      const modal = new bootstrap.Modal(document.getElementById('importResultModal'));
      modal.show();

      // === Reset form upload setelah sukses import ===
      const uploadForm = document.getElementById('uploadForm');
      if (uploadForm) {
        uploadForm.reset(); // reset semua field standar

        // Hapus input file lama lalu ganti baru (biar benar-benar kosong)
        const oldFileInput = uploadForm.querySelector('input[type="file"]');
        if (oldFileInput) {
          const newInput = oldFileInput.cloneNode(true); // duplikasi elemen
          oldFileInput.parentNode.replaceChild(newInput, oldFileInput); // ganti elemen lama
        }

        // Kembalikan dropdown akun ke posisi default (option pertama kosong)
        const select = uploadForm.querySelector('select[name="account_id"]');
        if (select) select.selectedIndex = 0;
      }

      // === Tampilkan hasil duplikat / dilewati ===
      const container = document.getElementById('importTables');
      let html = '';
      if (data.duplicate_rows?.length) {
        html += '<h6>Duplikat</h6><ul>' +
          data.duplicate_rows.map(r => `<li>#${r.index} — ${r.reason}</li>`).join('') + '</ul>';
      }
      if (data.skipped_rows?.length) {
        html += '<h6>Dilewati</h6><ul>' +
          data.skipped_rows.map(r => `<li>#${r.index} — ${r.reason}</li>`).join('') + '</ul>';
      }
      container.innerHTML = html;

    } catch (err) {
      console.error(err);
      alert('Terjadi kesalahan: ' + err.message);
    } finally {
      previewButton.disabled = false;
      previewButton.innerHTML = '<i class="bx bx-save"></i> Simpan ke Database';
    }
  });
});
