<?php
$success = session()->getFlashdata('success');
$error   = session()->getFlashdata('error');
$warning = session()->getFlashdata('warning');
$info    = session()->getFlashdata('info');
?>

<?php if ($success || $error || $warning || $info): ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
  Swal.fire({
    icon: "<?= $success ? 'success' : ($error ? 'error' : ($warning ? 'warning' : 'info')) ?>",
    title: <?= json_encode($success ?? $error ?? $warning ?? $info) ?>,
    toast: true,
    position: "top-end",
    showConfirmButton: false,
    timer: 3500,
    timerProgressBar: true,
    customClass: {
      popup: 'shadow-sm'
    }
  });
});
</script>
<?php endif; ?>
