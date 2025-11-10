<!DOCTYPE html>
<html lang="en"
  dir="ltr"
  class="light-style layout-menu-fixed"
  data-theme="theme-default"
  data-assets-path="<?= base_url('assets/') ?>"
  data-template="vertical-menu-template-free">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0">
  <title><?= page_title() ?></title>
  <meta name="description" content="Dashboard trading & transaksi MT5">
  <meta name="author" content="Trading Book App">
  <meta name="theme-color" content="#696cff">

  <link rel="icon" type="image/x-icon" href="<?= base_url('assets/img/favicon/favicon.ico') ?>">

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <!-- Icons -->
  <link rel="stylesheet" href="<?= base_url('assets/vendor/fonts/boxicons.css') ?>" />
  <link href='https://cdn.boxicons.com/3.0.3/fonts/basic/boxicons.min.css' rel='stylesheet'>

  <!-- Core CSS -->
  <link rel="stylesheet" href="<?= base_url('assets/vendor/css/core.css') ?>" class="template-customizer-core-css">
  <link rel="stylesheet" href="<?= base_url('assets/vendor/css/theme-default.css') ?>" class="template-customizer-theme-css">
  <link rel="stylesheet" href="<?= base_url('assets/css/demo.css') ?>">
  <link rel="stylesheet" href="<?= base_url('assets/css/custom.css') ?>">

  <!-- Vendors CSS -->
  <link rel="stylesheet" href="<?= base_url('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') ?>">
  <link rel="stylesheet" href="<?= base_url('assets/vendor/libs/apex-charts/apex-charts.css') ?>">
  <!-- FullCalendar JS (wajib sebelum planner-actions.js) -->
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>

  <!-- Custom head section -->
  <?= $this->renderSection('head') ?>

  <!-- Helpers & Config -->
  <script src="<?= base_url('assets/vendor/js/helpers.js') ?>"></script>
  <script src="<?= base_url('assets/js/config.js') ?>"></script>
</head>

<body>
  <div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
      <?= $this->include('layouts/sidebar') ?>
      <div class="layout-page">
        <?= $this->include('layouts/navbar') ?>
        <div class="content-wrapper">
          <?= $this->renderSection('content') ?>
          <?= $this->include('layouts/footer') ?>
          <?= $this->include('components/swal_alert') ?>
          <div class="content-backdrop fade"></div>
        </div>
      </div>
    </div>
      
  </div>

  <!-- Core JS -->
  <script defer src="<?= base_url('assets/vendor/libs/jquery/jquery.js') ?>"></script>
  <script defer src="<?= base_url('assets/vendor/libs/popper/popper.js') ?>"></script>
  <script defer src="<?= base_url('assets/vendor/js/bootstrap.js') ?>"></script>
  <script defer src="<?= base_url('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') ?>"></script>
  <script defer src="<?= base_url('assets/vendor/js/menu.js') ?>"></script>

  <!-- Vendors -->
  <script defer src="<?= base_url('assets/vendor/libs/apex-charts/apexcharts.js') ?>"></script>

  <!-- Main -->
  <script defer src="<?= base_url('assets/js/main.js') ?>"></script>
  <script defer src="<?= base_url('assets/js/ui-toasts.js') ?>"></script>
  <script defer src="<?= base_url('assets/js/dashboards-analytics.js') ?>"></script>

  <!-- GitHub buttons -->
  <script async defer src="https://buttons.github.io/buttons.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <!-- Custom page scripts -->
  <?= $this->renderSection('scripts') ?>
</body>
</html>
