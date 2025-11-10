<!DOCTYPE html>

<html
  lang="en"
  class="light-style customizer-hide"
  dir="ltr"
  data-theme="theme-default"
  data-assets-path="<?= base_url('assets') ?>"
  data-template="vertical-menu-template-free"
>
  <head>
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
    />

    <title>Login</title>

    <meta name="description" content="" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= base_url('assets/img/favicon/favicon.ico')?>" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
      rel="stylesheet"
    />

    <!-- Icons. Uncomment required icon fonts -->
    <link rel="stylesheet" href="<?= base_url('assets/vendor/fonts/boxicons.css')?>" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/vendor/css/core.css')?>" class="template-customizer-core-css" />
    <link rel="stylesheet" href="<?= base_url('assets/vendor/css/theme-default.css')?>" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="<?= base_url('assets/css/demo.css')?>" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css')?>" />

    <!-- Page CSS -->
    <!-- Page -->
    <link rel="stylesheet" href="<?= base_url('assets/vendor/css/pages/page-auth.css')?>" />
    <!-- Helpers -->
    <script src="<?= base_url('assets/vendor/js/helpers.js')?>"></script>

    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="<?= base_url('assets/js/config.js')?>"></script>
  </head>

  <body>
    <!-- Content -->

    <div class="container-xxl">
      <div class="authentication-wrapper authentication-basic container-p-y">
        <div class="authentication-inner">
          <!-- Register -->
          <div class="card">
            <div class="card-body">
              <!-- Logo -->
              <div class="app-brand justify-content-center">
                <a href="<?= base_url('dashboard') ?>" class="app-brand-link gap-2">
                    <span class="app-brand-logo demo">
                    <img src="<?= base_url('assets/img/logo.svg') ?>" alt="Trading-Book" width="200" />
                    </span>
                    <span class="app-brand-text demo text-body fw-bolder" style="display:none;">Trading-Book</span>
                </a>
              </div>
              <!-- /Logo -->
              <h5 class="mb-2">Mulai tinjau aktivitas Trading kamu</h5>
              <p class="mb-4">Disiplin, konsisten & Profit !</p>

            <?php if (session()->getFlashdata('success') || session()->getFlashdata('error')) : ?>
            <div class="position-fixed top-0 end-0 py-3 px-2" style="z-index: 1055" data-delay="2000">
              <div class="bs-toast toast fade show bg-<?= session()->getFlashdata('success') ? 'success' : 'warning' ?> border-0 show" 
                    role="alert" 
                    aria-live="assertive" 
                    aria-atomic="true">
                <div class="toast-header">
                  <i class="bx bx-bell me-2"></i>
                  <div class="me-auto fw-semibold"><?= session()->getFlashdata('success') ? 'Berhasil' : 'Gagal' ?></div>
                  <div class="toast-body">
                    <?= session()->getFlashdata('success') ?? session()->getFlashdata('error') ?>
                  </div>
                  <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
              </div>
            </div>
            <?php endif; ?>

              <form id="formAuthentication" class="mb-3" action="<?= base_url('login/process') ?>" method="POST">
                <div class="mb-3">
                  <label for="email" class="form-label">Email or Username</label>
                  <input
                    type="text"
                    class="form-control"
                    id="email"
                    name="email"
                    placeholder="Enter your email or username"
                    autofocus
                  />
                </div>
                <div class="mb-3 form-password-toggle">
                  <div class="d-flex justify-content-between">
                    <label class="form-label" for="password">Password</label>
                    <a href="auth-forgot-password-basic.html">
                      <small>Forgot Password?</small>
                    </a>
                  </div>
                  <div class="input-group input-group-merge">
                    <input
                      type="password"
                      id="password"
                      class="form-control"
                      name="password"
                      placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                      aria-describedby="password"
                    />
                    <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                  </div>
                </div>
                <div class="mb-3">
                  <button class="btn btn-primary d-grid w-100" type="submit">Sign in</button>
                </div>
              </form>

              <p class="text-center">
                <span>Belum terdaftar?</span>
                <a href="<?= base_url('register')?>">
                  <span>Buat akun</span>
                </a>
              </p>
            </div>
          </div>
          <!-- /Register -->
        </div>
      </div>
    </div>

    <!-- / Content -->


    <!-- Core JS -->
    <!-- build:js assets/vendor/js/core.js -->
    <script src="<?= base_url('assets/vendor/libs/jquery/jquery.js')?>"></script>
    <script src="<?= base_url('assets/vendor/libs/popper/popper.js')?>"></script>
    <script src="<?= base_url('assets/vendor/js/bootstrap.js')?>"></script>
    <script src="<?= base_url('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js')?>"></script>

    <script src="<?= base_url('assets/vendor/js/menu.js')?>"></script>
    <!-- endbuild -->

    <!-- Vendors JS -->

    <!-- Main JS -->
    <script src="<?= base_url('assets/js/main.js')?>"></script>

    <!-- Toast JS -->
    <script src="<?= base_url('assets/js/ui-toasts.js')?>"></script>

    <!-- Page JS -->

    <!-- Place this tag in your head or just before your close body tag. -->
    <script async defer src="https://buttons.github.io/buttons.js"></script>
  </body>
</html>
