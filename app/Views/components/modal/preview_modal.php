<!-- Boxicons CDN (fix terbaru) -->
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

<!-- Modal Preview -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      
      <!-- Header -->
      <div class="modal-header text-white py-3" 
           style="background: linear-gradient(90deg, #0061ff 0%, #60efff 100%);">
        <h5 class="modal-title fw-bold d-flex align-items-center" id="previewModalLabel">
          <i class='bx bx-line-chart fs-4 me-2'></i> Preview Data Import
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <!-- Body -->
      <div class="modal-body bg-light p-4">
        
        <!-- Info Summary -->
        <div id="previewSummary" class="row g-3 mb-4">
          <!-- Jumlah Transaksi -->
          <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-white h-100">
              <div class="card-body py-3 px-4 d-flex align-items-center">
                <div class="me-3 bg-primary bg-opacity-10 text-primary p-3 rounded-3">
                  <i class='bx bx-list-ul fs-3'></i>
                </div>
                <div>
                  <div class="text-muted small mb-1">Jumlah Transaksi</div>
                  <h5 class="fw-bold mb-0" id="summaryTotalRows">0</h5>
                </div>
              </div>
            </div>
          </div>

          <!-- Total Profit/Loss -->
          <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-white h-100">
              <div class="card-body py-3 px-4 d-flex align-items-center">
                <div class="me-3 bg-success bg-opacity-10 text-success p-3 rounded-3">
                  <i class='bx bx-trending-up fs-3'></i>
                </div>
                <div>
                  <div class="text-muted small mb-1">Total Profit / Loss</div>
                  <h5 class="fw-bold mb-0" id="summaryTotalProfit">0.00</h5>
                </div>
              </div>
            </div>
          </div>

          <!-- Pair Terbanyak -->
          <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-white h-100">
              <div class="card-body py-3 px-4 d-flex align-items-center">
                <div class="me-3 bg-warning bg-opacity-10 text-warning p-3 rounded-3">
                  <i class='bx bx-pulse fs-3'></i>
                </div>
                <div>
                  <div class="text-muted small mb-1">Pair Terbanyak</div>
                  <h5 class="fw-bold mb-0" id="summaryTopSymbol">-</h5>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Table -->
        <div class="table-responsive shadow-sm border border-1 rounded-3 bg-white">
          <table class="table table-hover align-middle mb-0" id="previewTable">
            <thead class="table-primary text-uppercase small">
              <tr>
                <th>#</th>
                <th>Position ID</th>
                <th>Symbol</th>
                <th>Type</th>
                <th>Lot</th>
                <th>Open</th>
                <th>Close</th>
                <th class="text-end">Profit</th>
              </tr>
            </thead>
            <tbody id="previewTableBody">
              <tr>
                <td colspan="8" class="text-center text-muted py-4">
                  <i class='bx bx-file-find mb-2 fs-3 d-block opacity-75'></i>
                  Belum ada data untuk ditampilkan
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Footer -->
      <div class="modal-footer d-flex flex-column flex-sm-row align-items-center justify-content-between bg-white border-0 border-top px-4 py-3">
        <small class="text-muted fst-italic mb-2 mb-sm-0">
          <i class='bx bx-info-circle me-1'></i>
          Pastikan data yang tampil sudah sesuai sebelum disimpan.
        </small>
        <div class="d-flex gap-2">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
            <i class='bx bx-x me-1'></i> Tutup
          </button>
          <button type="button" class="btn text-white fw-bold" id="saveImportBtn"
                  style="background: linear-gradient(90deg,#00b09b,#96c93d); border: none;">
            <i class='bx bx-save me-1'></i> Simpan ke Database
          </button>
        </div>
      </div>
    </div>
  </div>
</div>
