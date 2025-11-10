<?= $this->extend('layouts/main'); ?>
<?= $this->section('content'); ?>

<div class="container-xxl flex-grow-1 container-p-y">

  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold m-0">
      <span class="text-muted fw-light">Wallet /</span> Dashboard
    </h4>
    <a href="<?= base_url('wallet/create'); ?>" class="btn btn-primary shadow-sm">
      <i class="bx bx-plus-circle me-1"></i> Tambah Wallet
    </a>
  </div>
  <!-- Kontainer utama yang bisa di-refresh via AJAX -->
  <div id="walletContainer" class="row g-4">
    <!-- Wallet List -->
    <?= $this->include('wallet/wallet_list'); ?>
    <!-- Right Side: Favorite Wallet Activity -->
    <?= $this->include('wallet/wallet_favorite'); ?>
  </div>

</div>

<!-- include modal -->
<?= $this->include('components/modal/wallet/wallet_modal'); ?>

<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
<script src="<?= base_url('assets/js/wallet/wallet-actions.js'); ?>"></script>
<?= $this->endSection(); ?>
