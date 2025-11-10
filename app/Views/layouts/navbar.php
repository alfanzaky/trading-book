<nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme" id="layout-navbar">
  <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
    <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
      <i class="bx bx-menu bx-sm"></i>
    </a>
  </div>
  <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
      <!-- Motivational Ticker -->
    <div class="navbar-nav align-items-center w-100">
      <div class="nav-item d-flex align-items-center w-100">
        <marquee behavior="scroll" direction="left" scrollamount="6" class="text-muted fw-semibold" style="font-size: 14px;">
          💹 "Disiplin adalah kunci utama dalam trading — tanpa disiplin, strategi terbaik pun tak berguna."
          &nbsp;&nbsp;|&nbsp;&nbsp;
          📈 "Fokus pada proses, bukan hasil. Profit hanyalah bonus dari kebiasaan yang konsisten."
          &nbsp;&nbsp;|&nbsp;&nbsp;
          ⏳ "Sabar menunggu setup yang tepat lebih menguntungkan daripada terburu-buru masuk pasar."
          &nbsp;&nbsp;|&nbsp;&nbsp;
          💭 "Trading bukan soal menang setiap hari, tapi tentang bertahan jangka panjang."
        </marquee>
      </div>
    </div>
    <!-- /Motivational Ticker -->
    <ul class="navbar-nav flex-row align-items-center ms-auto">
      <!-- User -->
      <li class="nav-item navbar-dropdown dropdown-user dropdown">
        <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
        <?php 
          $photo = session()->get('photo') ?? 'assets/img/user/default.png';
          $photoPath = strpos($photo, 'assets/') === 0 ? $photo : 'assets/img/user/' . $photo;
        ?>
          <div class="avatar avatar-online">
            <img
              src="<?= base_url('assets/img/user/user.png') ?>"
              onerror="this.onerror=null;this.src='<?= base_url('assets/img/user/default.png') ?>';"
              alt="user-avatar"
              class="d-block rounded-circle"
            />
          </div>
        </a>
        <ul class="dropdown-menu dropdown-menu-end">
          <li>
            <a class="dropdown-item" href="#">
              <div class="d-flex">
                <div class="flex-shrink-0 me-3">
                  <div class="avatar avatar-online">
                    <img src="<?= base_url('assets/img/user/'. '/user.png') ?>" alt="User Avatar" class="w-px-40 h-auto rounded-circle" />
                  </div>
                </div>
                <div class="flex-grow-1">
                  <!-- Menampilkan data user dari session -->
                  <span class="fw-semibold d-block">
                    <?= session()->get('username') ?>
                  </span>
                  <small class="text-muted">
                    <?= session()->get('email') ?>
                  </small>
                </div>
              </div>
            </a>
          </li>
          <li> <div class="dropdown-divider"></div></li>
          <li>
            <a class="dropdown-item" href="<?= base_url('profile') ?>">
              <i class="bx bx-user me-2"></i>
              <span class="align-middle">My Profile</span>
            </a>
          </li>
          <li>
            <a class="dropdown-item" href="#">
              <i class="bx bx-cog me-2"></i>
              <span class="align-middle">Settings</span>
            </a>
          </li>
          <li><div class="dropdown-divider"></div></li>
          <!-- Tombol Logout -->
          <li>
            <a class="dropdown-item" href="<?= base_url('logout') ?>">
              <i class="bx bx-power-off me-2"></i>
              <span class="align-middle">Log Out</span>
            </a>
          </li>
        </ul>
      </li>
      <!--/ User -->
    </ul>
  </div>
</nav>