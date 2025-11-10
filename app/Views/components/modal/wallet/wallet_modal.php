<!-- Wallet Modal Component -->
<div class="modal fade" id="walletModal" tabindex="-1" aria-labelledby="walletModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-md">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      
      <!-- Header dippanggi di js dengan id untuk title modal -->
      <div class="modal-header text-white py-3" 
           id="walletModalHeader" 
           style="background: linear-gradient(90deg, #3b82f6, #06b6d4);">
        <div>
        <h5 class="modal-title mb-0 text-white" id="walletModalLabel">
          <i class="bx bx-wallet-alt me-2 text-white"></i> Wallet Action
        </h5>
          <small class="opacity-75" id="walletModalSubtitle">Kelola saldo wallet kamu di sini.</small> <!--  const subtitle = document.getElementById('walletModalSubtitle'); -->
        </div>
      </div>

      <!-- Body -->
      <div class="modal-body bg-light p-4" id="walletModalContent">
        <div class="text-center py-5 text-muted">
          <div class="spinner-border text-primary mb-3"></div>
          <p>Memuat formulir transaksi...</p>
        </div>
      </div>
    </div>
  </div>
</div>
