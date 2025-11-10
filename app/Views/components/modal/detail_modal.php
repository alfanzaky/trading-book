<!-- Modal Detail Transaksi -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow-lg">
      <!-- Header -->
      <div class="modal-header bg-gradient text-white py-3" 
           style="background: linear-gradient(90deg, #007bff 0%, #00bcd4 100%);">
        <div>
          <h5 class="modal-title mb-0" id="detailModalLabel">
            <i class="bx bx-bar-chart-alt-2 me-2"></i> Detail Transaksi Trading
          </h5>
          <small class="opacity-75 d-block text-dark" id="detailSubtitle">
            Informasi lengkap dari transaksi yang kamu pilih.
          </small>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <!-- Body -->
      <div class="modal-body p-4 bg-light">
        <div class="alert alert-info d-flex align-items-center mb-3 shadow-sm" role="alert">
          <i class="bx bx-info-circle fs-4 me-2"></i>
          <div>
            <strong>Catatan:</strong> Data ini berasal dari laporan MT5 kamu dan mencerminkan hasil trading aktual.
          </div>
        </div>

        <table class="table table-hover table-striped table-bordered align-middle mb-0 rounded">
          <thead class="table-primary text-center">
            <tr>
              <th style="width: 35%">#</th>
              <th>Keterangan</th>
            </tr>
          </thead>
          <tbody id="detailBody">
            <tr><td colspan="2" class="text-center text-muted py-4">
              <div class="spinner-border text-primary me-2"></div> Memuat data transaksi...
            </td></tr>
          </tbody>
        </table>
      </div>

      <!-- Footer -->
      <div class="modal-footer justify-content-between bg-white border-0 border-top shadow-sm px-4 py-3">
        <small class="text-muted">
          <i class="bx bx-history me-1"></i> Terakhir diperbarui: 
          <span id="detailUpdatedAt">-</span>
        </small>
        <button type="button" class="btn btn-gradient-primary" 
                data-bs-dismiss="modal" 
                style="background: linear-gradient(90deg, #ffffffff, #0e9ee0ff); border: none;">
          <i class="bx bx-x-circle me-1"></i> Close
        </button>
      </div>
    </div>
  </div>
</div>
