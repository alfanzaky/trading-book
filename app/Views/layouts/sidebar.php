<?php $uri = service('uri'); ?>

<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
  <div class="app-brand demo">
    <a href="<?= base_url('dashboard') ?>" class="app-brand-link d-flex align-items-center">
      <span class="app-brand-logo demo">
        <svg
          width="26"
          viewBox="0 0 200 200"
          xmlns="http://www.w3.org/2000/svg"
        >
          <defs>
            <!-- gradient utama -->
            <linearGradient id="gradTrading" x1="0%" y1="0%" x2="100%" y2="100%">
              <stop offset="0%" stop-color="#696CFF"/>
              <stop offset="50%" stop-color="#2EC4B6"/>
              <stop offset="100%" stop-color="#6C63FF"/>
            </linearGradient>

            <!-- bayangan lembut -->
            <filter id="shadowSoft" x="-50%" y="-50%" width="200%" height="200%">
              <feDropShadow dx="0" dy="3" stdDeviation="4" flood-color="#000" flood-opacity="0.2"/>
            </filter>
          </defs>

          <!-- Simbol open book -->
          <g transform="translate(20,30)" filter="url(#shadowSoft)">
            <path d="M0 20 Q40 0 80 20 Q120 0 160 20 L160 100 Q120 80 80 90 Q40 80 0 100 Z" fill="url(#gradTrading)"/>
            <!-- Lipatan tengah -->
            <path d="M80 20 L80 90" stroke="white" stroke-opacity="0.25" stroke-width="3"/>

            <!-- Candlestick kiri -->
            <g transform="translate(40,45)">
              <rect x="0" y="20" width="8" height="30" rx="2" ry="2" fill="#fff"/>
              <rect x="3" y="0" width="2" height="50" fill="#fff" opacity="0.4"/>
            </g>
            <!-- Candlestick tengah -->
            <g transform="translate(65,35)">
              <rect x="0" y="10" width="8" height="40" rx="2" ry="2" fill="#fff"/>
              <rect x="3" y="-5" width="2" height="55" fill="#fff" opacity="0.4"/>
            </g>
            <!-- Candlestick kanan -->
            <g transform="translate(90,50)">
              <rect x="0" y="25" width="8" height="25" rx="2" ry="2" fill="#fff"/>
              <rect x="3" y="10" width="2" height="40" fill="#fff" opacity="0.4"/>
            </g>

            <!-- Arah panah profit -->
            <path d="M115 35 L145 10 L150 15 L125 40 Z" fill="#FFD166"/>
            <path d="M115 45 L145 15" stroke="#FFD166" stroke-width="6" stroke-linecap="round"/>
          </g>
        </svg>
      </span>
      <span class="app-brand-text demo menu-text fw-bolder ms-2 text-primary">
        Trading<span class="text-secondary">Book</span>
      </span>
    </a>

    <a href="javascript:void(0);" 
      class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none rounded-circle"
      style="width: 36px; height: 36px; background-color: rgba(105,108,255,0.1);">
      <i class="bx bx-chevron-left bx-sm align-middle text-primary"></i>
    </a>
  </div>

  <div class="menu-inner-shadow"></div>

  <ul class="menu-inner py-1">
    <!-- Dashboard -->
    <li class="menu-item <?= ($uri->getSegment(1) === 'dashboard') ? 'active' : '' ?>">
      <a href="<?= base_url('dashboard') ?>" class="menu-link">
        <i class="menu-icon tf-icons bx bxs-dashboard text-primary"></i>
        <div>Dashboard</div>
      </a>
    </li>

    <!-- Wallet -->
    <li class="menu-item <?= ($uri->getSegment(1) === 'wallet') ? 'active' : '' ?>">
      <a href="<?= base_url('wallet') ?>" class="menu-link">
        <i class="menu-icon tf-icons bx bxs-wallet text-primary"></i>
        <div>Wallet</div>
      </a>
    </li>

    <!-- Trading Accounts -->
    <li class="menu-item <?= ($uri->getSegment(1) === 'accounts') ? 'active' : '' ?>">
      <a href="<?= base_url('accounts') ?>" class="menu-link">
        <i class="menu-icon tf-icons bx bxs-bitcoin text-warning"></i>
        <div>Trading Accounts</div>
      </a>
    </li>

    <!-- User -->
    <?php $usermenu = ['profile', 'jurnal']; ?>
    <li class="menu-item <?= in_array($uri->getSegment(1), $usermenu) ? 'active open' : '' ?>">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons bx bxs-user text-primary"></i>
        <div>User</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item <?= ($uri->getSegment(1) === 'profile') ? 'active' : '' ?>">
          <a href="<?= base_url('profile') ?>" class="menu-link">
            <div>Profile</div>
          </a>
        </li>
        <li class="menu-item <?= ($uri->getSegment(1) === 'jurnal') ? 'active' : '' ?>">
          <a href="<?= base_url('jurnal') ?>" class="menu-link">
            <div>Catatan</div>
          </a>
        </li>
      </ul>
    </li>

    <!-- Transaction -->
    <li class="menu-header small text-uppercase">
      <span class="menu-header-text">Transaction</span>
    </li>

    <!-- Trades -->
    <li class="menu-item <?= ($uri->getSegment(2) === 'trades') ? 'active' : '' ?>">
      <a href="<?= base_url('transaction/trades') ?>" class="menu-link">
        <i class="menu-icon tf-icons bx bxs-receipt text-primary"></i>
        <div>Trades</div>
      </a>
    </li>

    <!-- Summary -->
    <li class="menu-item <?= ($uri->getSegment(2) === 'summary') ? 'active' : '' ?>">
      <a href="<?= base_url('transaction/summary') ?>" class="menu-link">
        <i class="menu-icon tf-icons bx bx-brain text-warning"></i>
        <div>Summary</div>
      </a>
    </li>

  <!-- Trading Planner -->
  <li class="menu-item <?= ($uri->getSegment(2) === 'planner') ? 'active' : '' ?>">
    <a href="<?= base_url('transaction/planner') ?>" class="menu-link">
      <i class="menu-icon tf-icons bx bx-calendar-check text-info"></i>
      <div>Trading Planner</div>
    </a>
  </li>
    <!-- Money Management -->
    <?php $moneyManagementMenu = ['reports', 'budgeting', 'preferences']; ?>
    <li class="menu-header small text-uppercase"><span class="menu-header-text">Money Management</span></li>

    <li class="menu-item <?= in_array($uri->getSegment(1), $moneyManagementMenu) ? 'active open' : '' ?>">
      <a href="javascript:void(0)" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons bx bx-dollar" style="color:#18e90a"></i>
        <div>Expense Tracker</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item <?= ($uri->getSegment(1) === 'reports') ? 'active' : '' ?>">
          <a href="<?= base_url('reports') ?>" class="menu-link"><div>Reports</div></a>
        </li>
        <li class="menu-item <?= ($uri->getSegment(1) === 'budgeting') ? 'active' : '' ?>">
          <a href="<?= base_url('budgeting') ?>" class="menu-link"><div>Budgeting</div></a>
        </li>
        <li class="menu-item <?= ($uri->getSegment(1) === 'preferences') ? 'active' : '' ?>">
          <a href="<?= base_url('preferences') ?>" class="menu-link"><div>Preference</div></a>
        </li>
      </ul>
    </li>

    <li class="menu-item <?= ($uri->getSegment(2) === 'income') ? 'active' : '' ?>">
      <a href="<?= base_url('money/income') ?>" class="menu-link">
        <i class="menu-icon tf-icons bx bx-piggy-bank" style="color:#6818d3"></i>
        <div>Income Tracker</div>
      </a>
    </li>

    <li class="menu-item <?= ($uri->getSegment(2) === 'goals') ? 'active' : '' ?>">
      <a href="<?= base_url('money/goals') ?>" class="menu-link">
        <i class="menu-icon tf-icons bx bxs-target" style="color:#f99210"></i>
        <div>Goals</div>
      </a>
    </li>

    <li class="menu-item <?= ($uri->getSegment(2) === 'trading-allocation') ? 'active' : '' ?>">
      <a href="<?= base_url('money/trading-allocation') ?>" class="menu-link">
        <i class='menu-icon tf-icons bx  bx-arrow-in-up-left-stroke-circle'  style='color:#ff0307'  ></i> 
        <div>Trading Allocation</div>
      </a>
    </li>

    <!-- Help -->
    <li class="menu-header small text-uppercase"><span class="menu-header-text">Help</span></li>
    <li class="menu-item">
      <a href="<?= base_url('guide/user') ?>" class="menu-link">
        <i class="menu-icon tf-icons bx bx-help-circle"></i>
        <div>User Guide</div>
      </a>
    </li>
  </ul>
</aside>
