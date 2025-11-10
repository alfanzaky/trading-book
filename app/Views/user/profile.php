<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container-xxl flex-grow-1 container-p-y">
  <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">User Menu /</span> Profile</h4>

  <div class="row">
    <div class="col-md-12">
      <div class="card mb-4">
        <h5 class="card-header">Profile Details</h5>
      <!-- ✅ Form dikirim ke controller update -->
      <form id="formAccountSettings" 
            method="POST" 
            action="<?= base_url('profile/update') ?>" 
            enctype="multipart/form-data">
        <!-- Account -->
        <div class="card-body">
          <div class="d-flex align-items-start align-items-sm-center gap-4">
            <img
              src="<?= base_url('assets/img/user/user.png') ?>"
              onerror="this.onerror=null;this.src='<?= base_url('assets/img/user/default.png') ?>';"
              alt="user-avatar"
              class="d-block rounded"
              height="100"
              width="100"
              id="uploadedAvatar"
            />

            <div class="button-wrapper">
              <label for="upload" class="btn btn-primary me-2 mb-4" tabindex="0">
                <span class="d-none d-sm-block">Upload</span>
                <i class="bx bx-upload d-block d-sm-none"></i>
                <input
                  type="file"
                  id="upload"
                  name="upload"
                  class="account-file-input"
                  hidden
                  accept="image/png, image/jpeg"
                />
              </label>

              <p class="text-muted mb-0">Allowed JPG, GIF or PNG. Max size of 800K</p>
            </div>
          </div>
        </div>

        <hr class="my-0" />

        <div class="card-body">
            <div class="row">
              <div class="mb-3 col-md-6">
                <label for="firstName" class="form-label">First Name</label>
                <input class="form-control" type="text" id="firstName" name="firstName" 
                       value="<?= $user['first_name'] ?? '' ?>" autofocus />
              </div>
              <div class="mb-3 col-md-6">
                <label for="lastName" class="form-label">Last Name</label>
                <input class="form-control" type="text" name="lastName" id="lastName"
                       value="<?= $user['last_name'] ?? '' ?>" />
              </div>
              <div class="mb-3 col-md-6">
                <label for="email" class="form-label">E-mail</label>
                <input class="form-control" type="text" id="email" name="email"
                       value="<?= $user['email'] ?? '' ?>" placeholder="your@email.com" />
              </div>
              <div class="mb-3 col-md-6">
                <label class="form-label" for="phoneNumber">Phone Number</label>
                <div class="input-group input-group-merge">
                  <span class="input-group-text">+62</span>
                  <input type="text" id="phoneNumber" name="phoneNumber"
                         class="form-control"
                         value="<?= $user['phone'] ?? '' ?>"
                         placeholder="8123456789" />
                </div>
              </div>
              <div class="mb-3 col-md-6">
                <label for="address" class="form-label">Address</label>
                <input type="text" class="form-control" id="address" name="address"
                       value="<?= $user['address'] ?? '' ?>" placeholder="Address" />
              </div>
              <div class="mb-3 col-md-6">
                <label for="state" class="form-label">State</label>
                <input class="form-control" type="text" id="state" name="state"
                       value="<?= $user['state'] ?? '' ?>" placeholder="Jakarta" />
              </div>
              <div class="mb-3 col-md-6">
                <label for="zipCode" class="form-label">Zip Code</label>
                <input type="text" class="form-control" id="zipCode" name="zipCode"
                       value="<?= $user['zipcode'] ?? '' ?>"
                       placeholder="231465" maxlength="6" />
              </div>
              <div class="mb-3 col-md-6">
                <label class="form-label" for="country">Country</label>
                <select id="country" name="country" class="select2 form-select">
                  <option value="">Select</option>
                  <option value="Indonesia" <?= $user['country'] == 'Indonesia' ? 'selected' : '' ?>>Indonesia</option>
                  <option value="United States" <?= $user['country'] == 'United States' ? 'selected' : '' ?>>United States</option>
                  <option value="Japan" <?= $user['country'] == 'Japan' ? 'selected' : '' ?>>Japan</option>
                  <option value="Germany" <?= $user['country'] == 'Germany' ? 'selected' : '' ?>>Germany</option>
                  <option value="Others" <?= $user['country'] == 'Others' ? 'selected' : '' ?>>Others</option>
                </select>
              </div>
              <div class="mb-3 col-md-6">
                <label for="language" class="form-label">Language</label>
                <select id="language" name="language" class="select2 form-select">
                  <option value="">Select Language</option>
                  <option value="en" <?= $setting['language'] == 'en' ? 'selected' : '' ?>>English</option>
                  <option value="id" <?= $setting['language'] == 'id' ? 'selected' : '' ?>>Indonesian</option>
                </select>
              </div>
              <div class="mb-3 col-md-6">
                <label for="timezone" class="form-label">Timezone</label>
                <select id="timezone" name="timezone" class="select2 form-select">
                  <option value="">Select Timezone</option>
                  <option value="Asia/Jakarta" <?= $setting['timezone'] == 'Asia/Jakarta' ? 'selected' : '' ?>>(GMT+7:00) Asia/Jakarta</option>
                  <option value="Asia/Singapore" <?= $setting['timezone'] == 'Asia/Singapore' ? 'selected' : '' ?>>(GMT+8:00) Asia/Singapore</option>
                </select>
              </div>
              <div class="mb-3 col-md-6">
                <label for="currency" class="form-label">Currency</label>
                <select id="currency" name="currency" class="select2 form-select">
                  <option value="">Select Currency</option>
                  <option value="USD" <?= $setting['currency'] == 'USD' ? 'selected' : '' ?>>USD</option>
                  <option value="EUR" <?= $setting['currency'] == 'EUR' ? 'selected' : '' ?>>Euro</option>
                  <option value="IDR" <?= $setting['currency'] == 'IDR' ? 'selected' : '' ?>>IDR</option>
                </select>
              </div>
            </div>
            <div class="mt-2">
              <button type="submit" class="btn btn-primary me-2">Save changes</button>
            </div>
        </div>
      </form>
        <!-- /Account -->
      </div>

      <div class="card">
        <h5 class="card-header">Delete Account</h5>
        <div class="card-body">
          <div class="mb-3 col-12 mb-0">
            <div class="alert alert-warning">
              <h6 class="alert-heading fw-bold mb-1">Are you sure you want to delete your account?</h6>
              <p class="mb-0">Once you delete your account, there is no going back. Please be certain.</p>
            </div>
          </div>
          <form id="formAccountDeactivation" onsubmit="return false">
            <div class="form-check mb-3">
              <input class="form-check-input" type="checkbox" name="accountActivation" id="accountActivation" />
              <label class="form-check-label" for="accountActivation">
                I confirm my account deactivation
              </label>
            </div>
            <button type="submit" class="btn btn-danger deactivate-account">Deactivate Account</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Preview JS (tanpa ubah tampilan) -->
<script>
document.getElementById('upload').addEventListener('change', function(e) {
  const file = e.target.files[0];
  if (file) {
    document.getElementById('uploadedAvatar').src = URL.createObjectURL(file);
  }
});
</script>

<?= $this->endSection() ?>
