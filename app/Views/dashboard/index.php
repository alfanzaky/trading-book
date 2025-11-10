<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container-xxl flex-grow-1 container-p-y">

  <!-- === Greeting + Profit Cards === -->
  <div class="row">
    <?= $this->include('dashboard/partials/top_cards'); ?>
  </div>

  <!-- === Revenue + Sidebar === -->
  <div class="row">
    <?= $this->include('dashboard/partials/mid_section'); ?>
  </div>

  <!-- === Bottom Section === -->
  <div class="row">
    <?= $this->include('dashboard/partials/bottom_section'); ?>
  </div>

</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
  const BASE_URL = "<?= base_url() ?>";
</script>
<script src="<?= base_url('assets/js/dashboard/dashboard.js') ?>"></script>
<?= $this->endSection() ?>
