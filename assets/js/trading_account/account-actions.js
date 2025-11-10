/**
 * account-actions.js
 * -------------------
 * Untuk aksi transaksi di halaman Akun Trading
 * (Deposit dari wallet, Withdraw ke wallet, Transfer antar akun)
 */

document.addEventListener('DOMContentLoaded', () => {
  const modalEl = document.getElementById('accountModal');
  if (!modalEl) return console.warn('⚠️ accountModal tidak ditemukan di DOM.');

  const modal = new bootstrap.Modal(modalEl);
  const modalContent = document.getElementById('accountModalContent');
  const modalTitle = document.getElementById('accountModalLabel');

  // Semua tombol dengan class .account-action
  document.querySelectorAll('.account-action').forEach(btn => {
    btn.addEventListener('click', async e => {
      e.preventDefault();

      const url = btn.dataset.url;
      const title = btn.dataset.title || 'Transaksi Akun Trading';
      if (!url) {
        console.error('data-url kosong pada tombol:', btn);
        return;
      }

      modalTitle.textContent = title;
      modalContent.innerHTML = loadingTemplate('Memuat form transaksi...');
      modal.show();

      try {
        // Ambil form dari server (GET)
        const res = await fetch(url, {
          method: 'GET',
          headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });

        if (!res.ok) throw new Error(`Gagal memuat form (${res.status})`);
        const html = await res.text();
        modalContent.innerHTML = html;

        const form = modalContent.querySelector('form');
        if (!form) {
          modalContent.innerHTML = `<div class="alert alert-warning">Form transaksi tidak ditemukan.</div>`;
          return;
        }

        form.addEventListener('submit', async ev => {
          ev.preventDefault();
          await handleAccountFormSubmit(form, url, modal, modalContent);
        }, { once: true });

      } catch (err) {
        modalContent.innerHTML = `
          <div class="alert alert-danger text-center py-4" role="alert">
            <i class="bx bx-error-circle me-2"></i> ${err.message}
          </div>
        `;
      }
    });
  });
});

/**
 * Submit handler
 */
async function handleAccountFormSubmit(form, url, modal, modalContent) {
  const formData = new FormData(form);

  modalContent.innerHTML = loadingTemplate('Memproses transaksi...');

  try {
    const res = await fetch(url, {
      method: 'POST',
      body: formData,
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });

    const type = res.headers.get('content-type') || '';
    const data = type.includes('json') ? await res.json() : { message: await res.text() };

    if (!res.ok || data.status === 'error') {
      throw new Error(data.message || 'Gagal memproses permintaan');
    }

    showToast(data.message || 'Transaksi berhasil disimpan.', 'success');
    modal.hide();
    setTimeout(() => location.reload(), 700);
  } catch (err) {
    modalContent.innerHTML = `
      <div class="alert alert-danger text-center mt-3" role="alert">
        <i class="bx bx-error-circle me-1"></i> ${err.message}
      </div>
    `;
  }
}


/**
 * Loading spinner
 */
function loadingTemplate(message) {
  return `
    <div class="text-center py-5">
      <div class="spinner-border text-primary mb-3" role="status"></div>
      <p class="text-muted mb-0">${message}</p>
    </div>
  `;
}

/**
 * Toast helper
 */
function showToast(message, type = 'info') {
  const colors = {
    success: 'bg-success',
    danger: 'bg-danger',
    warning: 'bg-warning text-dark',
    info: 'bg-primary'
  };
  const bg = colors[type] || colors.info;

  const toastEl = document.createElement('div');
  toastEl.className = `toast align-items-center text-white ${bg} border-0 position-fixed top-0 end-0 m-3 shadow`;
  toastEl.style.zIndex = 2000;
  toastEl.innerHTML = `
    <div class="d-flex">
      <div class="toast-body">${message}</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  `;
  document.body.appendChild(toastEl);

  const toast = new bootstrap.Toast(toastEl, { delay: 3500 });
  toast.show();
  toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
}
