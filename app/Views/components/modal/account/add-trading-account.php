<!-- Modal -->
<div class="modal fade" id="addAccountModal" tabindex="-1" aria-labelledby="addAccountLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form action="<?= base_url('accounts/add') ?>" method="POST" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addAccountLabel">Add Trading Account</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row g-2">
          <div class="col-md-6">
            <label>Broker Name</label>
            <input type="text" name="broker_name" class="form-control" required>
          </div>
          <div class="col-md-6">
            <label>UID</label>
            <input type="text" name="uid" class="form-control" placeholder="UID or Email" required>
          </div>
        </div>
          <div class="mb-2 mt-2">
          <label>Account name</label>
          <input type="text" name="account_name" class="form-control" required>
        </div>
        <div class="row g-2">
          <div class="col-md-6 mb-2">
          <label>Account Type</label>
          <select name="account_type" class="form-select" required>
            <option value="Standard">Standard</option>
            <option value="Pro">Pro</option>
            <option value="Pro">Zero</option>
            <option value="Mini">Mini</option>
            <option value="Demo">Demo</option>
          </select>
          </div>
          <div class="col-md-6 mb-2">
            <label>Currency</label>
            <select name="currency" class="form-select" required>
              <option value="USD">USD</option>
              <option value="IDR">IDR</option>
            </select>
          </div>
        </div>
        <div class="mb-2">
          <label>Platform</label>
          <select name="platform" class="form-select" required>
            <option value="MT5">MT5</option>
            <option value="MT4">MT4</option>
          </select>
        </div>
        <div class="mb-2">
          <label>Login ID</label>
          <input type="number" name="login_id" class="form-control" required>
        </div>
        <div class="mb-2">
          <label>Password login</label>
          <input type="password" name="password" class="form-control" required>
        </div>
        <div class="row g-2">
          <div class="col-md-6 mb-2">
            <label>Spread</label>
            <input type="number" name="spread" class="form-control">
          </div>
          <div class="col-md-6 mb-2">
            <label>commission</label>
            <input type="number" name="commission" class="form-control">
          </div>
        </div>
        <div class="mb-2">
          <label>Server</label>
          <input type="text" name="server" class="form-control" required>
        </div>
        <div class="mb-2">
          <label>Leverage</label>
          <input type="text" name="leverage" class="form-control" placeholder="1:100">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">close</button>
        <button type="submit" class="btn btn-primary">Save</button>
      </div>
    </form>
  </div>
</div>
