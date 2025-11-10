<div class="col-lg-8 mb-4 order-0">
  <div class="card">
    <div class="d-flex align-items-end row">
      <div class="col-sm-7">
        <div class="card-body">
          <h5 class="card-title text-primary" id="dashGreeting">Congratulations John! 🎉</h5>
          <p class="mb-4" id="dashGreetingText">
            You have done <span class="fw-bold">72%</span> more trades today.
          </p>
          <a href="<?= base_url('transaction/summary') ?>" class="btn btn-sm btn-outline-primary">View Report</a>
        </div>
      </div>
      <div class="col-sm-5 text-center">
        <div class="card-body pb-0 px-0 px-md-4">
          <img src="<?= base_url('assets/img/illustrations/man-with-laptop-light.png') ?>"
            height="140"
            alt="Trading Analytics"
            data-app-dark-img="illustrations/man-with-laptop-dark.png"
            data-app-light-img="illustrations/man-with-laptop-light.png" />
        </div>
      </div>
    </div>
  </div>
</div>

<div class="col-lg-4 col-md-4 order-1">
  <div class="row">
    <div class="col-lg-6 col-md-12 col-6 mb-4">
      <div class="card">
        <div class="card-body text-center">
          <h6 class="fw-semibold text-muted mb-1">Total Profit</h6>
          <h3 id="dashTotalProfit" class="fw-bold text-success mb-0">$0</h3>
          <small class="text-muted">This Week</small>
        </div>
      </div>
    </div>
    <div class="col-lg-6 col-md-12 col-6 mb-4">
      <div class="card">
        <div class="card-body text-center">
          <h6 class="fw-semibold text-muted mb-1">Total Loss</h6>
          <h3 id="dashTotalLoss" class="fw-bold text-danger mb-0">$0</h3>
          <small class="text-muted">This Week</small>
        </div>
      </div>
    </div>
  </div>
</div>
