document.addEventListener('DOMContentLoaded', () => {
  const modalEl = document.getElementById('walletModal');
  const modal = modalEl ? new bootstrap.Modal(modalEl) : null;
  const modalContent = document.getElementById('walletModalContent');
  const modalTitle = document.getElementById('walletModalLabel');
  const tableWrapper = document.querySelector('.col-lg-4');
  const rightSidebar = document.querySelector('.col-lg-8');

  // ========= Toast =========
  const toast = (msg, icon = 'success') => {
    Swal.fire({
      toast: true,
      position: 'top-end',
      icon,
      title: msg,
      showConfirmButton: false,
      timer: 2500,
      timerProgressBar: true,
      customClass: { popup: 'shadow rounded border-0' }
    });
  };

  // ========= Spinner =========
  const loadingTemplate = (text = 'Memuat...') => `
    <div class="text-center py-5">
      <div class="spinner-border text-primary mb-3" role="status"></div>
      <p class="text-muted mb-0">${text}</p>
    </div>
  `;

  // ========= Overlay =========
  const showOverlay = () => {
    if (document.getElementById('ajaxOverlay')) return;
    const overlay = document.createElement('div');
    overlay.id = 'ajaxOverlay';
    overlay.className =
      'position-fixed top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center bg-white bg-opacity-50';
    overlay.style.zIndex = 20000;
    overlay.innerHTML = '<div class="spinner-border text-primary"></div>';
    document.body.appendChild(overlay);
  };

  const hideOverlay = () => {
    const el = document.getElementById('ajaxOverlay');
    if (el) el.remove();
  };

  // ========= AJAX util =========
  async function doAjax(url, options = {}) {
    const res = await fetch(url, {
      method: options.method || 'GET',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      body: options.body || null
    });

    const type = res.headers.get('content-type') || '';
    if (!res.ok) {
      try {
        const j = await res.json();
        throw new Error(j.message || 'Kesalahan server');
      } catch {
        throw new Error('Kesalahan server');
      }
    }
    return type.includes('json') ? res.json() : res.text();
  }

  // ================= Wallet Table Reload =================
  async function reloadWalletTable() {
    if (!tableWrapper || !rightSidebar) return;

    if (typeof showOverlay === 'function') showOverlay();

    try {
      const url = window.location.pathname; // gunakan path, bukan full href
      const response = await fetch(url, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });

      if (!response.ok) throw new Error('Gagal memuat ulang data wallet');

      const html = await response.text();
      const tempDoc = new DOMParser().parseFromString(html, 'text/html');

      const newTable = tempDoc.querySelector('.col-lg-4');
      const newSidebar = tempDoc.querySelector('.col-lg-8');

      if (newTable) tableWrapper.innerHTML = newTable.innerHTML;
      if (newSidebar) rightSidebar.innerHTML = newSidebar.innerHTML;

      if (typeof bindWalletEvents === 'function') bindWalletEvents();

      if (typeof toast === 'function') toast('Data wallet diperbarui', 'success');
      else console.info('Data wallet diperbarui');
    } catch (err) {
      if (typeof toast === 'function') toast(err.message, 'error');
      else console.error(err);
    } finally {
      if (typeof hideOverlay === 'function') hideOverlay();
    }
  }

  // Buat tersedia global
  window.reloadWalletTable = reloadWalletTable;

  // ========= Modal Theme Switcher =========
  function setWalletModalTheme(actionType = 'default') {
    const header = document.getElementById('walletModalHeader');
    const title = document.getElementById('walletModalLabel');
    const subtitle = document.getElementById('walletModalSubtitle');

    let color, icon, text, desc;

    switch (actionType.toLowerCase()) {
      case 'deposit':
        color = 'linear-gradient(90deg, #16a34a, #4ade80)';
        icon = 'bx bx-upload';
        text = 'Deposit Wallet';
        desc = 'Tambahkan saldo ke wallet.';
        break;

      case 'withdraw':
        color = 'linear-gradient(90deg, #f97316, #facc15)';
        icon = 'bx bx-download';
        text = 'Withdraw Wallet';
        desc = 'Tarik saldo dari wallet.';
        break;

      case 'transfer':
        color = 'linear-gradient(90deg, #6366f1, #a855f7)';
        icon = 'bx bx-transfer-alt';
        text = 'Transfer Wallet';
        desc = 'Transfer antar wallet.';
        break;

      default:
        color = 'linear-gradient(90deg, #3b82f6, #06b6d4)';
        icon = 'bx bx-wallet-alt';
        text = 'Wallet Action';
        desc = 'Kelola saldo wallet kamu di sini.';
    }

    header.style.background = color;
    title.innerHTML = `<i class="${icon} me-2"></i> ${text}`;
    subtitle.textContent = desc;
  }

  // ========= Event bindings =========
  function bindWalletEvents() {
    // --- Set default ---
    document.querySelectorAll('.btn-set-default').forEach(btn => {
      btn.onclick = async e => {
        e.preventDefault();
        const ask = await Swal.fire({
          title: 'Jadikan wallet ini sebagai default?',
          icon: 'question',
          showCancelButton: true,
          confirmButtonText: 'Ya',
          cancelButtonText: 'Batal',
          confirmButtonColor: '#2563eb'
        });
        if (!ask.isConfirmed) return;
        try {
          const data = await doAjax(btn.dataset.url);
          toast(data.message, 'success');
          reloadWalletTable();
        } catch (err) {
          toast(err.message, 'error');
        }
      };
    });

    // --- Delete wallet ---
    document.querySelectorAll('.btn-delete-wallet').forEach(btn => {
      btn.onclick = async e => {
        e.preventDefault();
        const url = btn.dataset.url;
        try {
          const data = await doAjax(url);
          if (data.status === 'error' && data.message.includes('riwayat transaksi')) {
            const confirmForce = await Swal.fire({
              title: 'Wallet Memiliki Riwayat Transaksi',
              html: `<p>${data.message}</p>
                     <p class="text-danger mt-2"><i class="bx bx-error-circle"></i>
                     Semua data transaksi terkait akan dihapus permanen.</p>`,
              icon: 'warning',
              showCancelButton: true,
              confirmButtonText: 'Ya, hapus semua',
              cancelButtonText: 'Batal',
              confirmButtonColor: '#d33'
            });
            if (confirmForce.isConfirmed) {
              const forceUrl = `${url}?force=true`;
              const forceDelete = await doAjax(forceUrl);
              toast(forceDelete.message, 'success');
              reloadWalletTable();
            }
          } else {
            toast(data.message, 'success');
            reloadWalletTable();
          }
        } catch (err) {
          toast(err.message || 'Gagal menghapus wallet', 'error');
        }
      };
    });

    // --- Create Wallet ---
    const createBtn = document.querySelector('a[href*="wallet/create"]');
    if (createBtn) {
      createBtn.onclick = async e => {
        e.preventDefault();
        if (!modal) return;

        const url = createBtn.getAttribute('href');
        modalTitle.textContent = 'Tambah Wallet';
        modalContent.innerHTML = loadingTemplate('Memuat form...');
        modal.show();

        try {
          const html = await doAjax(url);
          modalContent.innerHTML = html;
          const form = modalContent.querySelector('form');
          if (!form) return;

          form.addEventListener('submit', async ev => {
            ev.preventDefault();
            modalContent.innerHTML = loadingTemplate('Menyimpan data...');
            try {
              const result = await doAjax(form.action, {
                method: 'POST',
                body: new FormData(form)
              });
              toast(result.message || 'Wallet berhasil ditambahkan', 'success');
              modal.hide();
              reloadWalletTable();
            } catch (err) {
              modalContent.innerHTML = `
                <div class="alert alert-danger text-center mt-3">
                  <i class="bx bx-error-circle me-1"></i> ${err.message || 'Gagal menyimpan data.'}
                </div>`;
            }
          }, { once: true });
        } catch (err) {
          modalContent.innerHTML = `
            <div class="alert alert-danger text-center py-4">
              <i class="bx bx-error-circle me-1"></i> Gagal memuat form wallet.
            </div>`;
        }
      };
    }

    // --- Deposit / Withdraw / Transfer ---
    document.querySelectorAll('.wallet-action').forEach(btn => {
      btn.onclick = async e => {
        e.preventDefault();
        if (!modal) return;

        const url = btn.dataset.url;
        const title = btn.dataset.title?.toLowerCase() || '';

        // Ubah tema modal berdasarkan aksi
        if (title.includes('deposit') || url.includes('deposit')) {
          setWalletModalTheme('deposit');
        } else if (title.includes('withdraw') || url.includes('withdraw')) {
          setWalletModalTheme('withdraw');
        } else if (title.includes('transfer') || url.includes('transfer')) {
          setWalletModalTheme('transfer');
        } else {
          setWalletModalTheme('default');
        }

        modalContent.innerHTML = loadingTemplate('Memuat form...');
        modal.show();

        try {
          const html = await doAjax(url);
          modalContent.innerHTML = html;
          const form = modalContent.querySelector('form');
          if (!form) return;

          form.addEventListener('submit', async ev => {
            ev.preventDefault();
            modalContent.innerHTML = loadingTemplate('Memproses transaksi...');
            try {
              const result = await doAjax(url, {
                method: 'POST',
                body: new FormData(form)
              });
              toast(result.message, 'success');
              modal.hide();
              reloadWalletTable();
            } catch {
              modalContent.innerHTML = `
                <div class="alert alert-danger text-center mt-3">
                  <i class="bx bx-error-circle me-1"></i> Gagal menyimpan data.
                </div>`;
            }
          }, { once: true });
        } catch {
          modalContent.innerHTML = `
            <div class="alert alert-danger text-center py-4">
              <i class="bx bx-error-circle me-1"></i> Gagal memuat form.
            </div>`;
        }
      };
    });
  }

  // Bersihkan modal setelah ditutup
  if (modalEl) {
    modalEl.addEventListener('hidden.bs.modal', () => {
      modalContent.innerHTML = '';
    });
  }

  // Jalankan binding awal
  bindWalletEvents();
});
