<?php

use CodeIgniter\Router\RouteCollection;
use App\Controllers\Auth\Login;
use App\Controllers\Auth\Register;
use App\Controllers\Dashboard\Dashboard;
use App\Controllers\User\Accounts;
use App\Controllers\User\Profile;
use App\Controllers\Transaction\Trades;
use App\Controllers\Transaction\ImportReport;
use App\Controllers\Transaction\TradingPlanner;
use App\Controllers\Transaction\Summary;



/**
 * @var RouteCollection $routes
 */
$routes->get('/', [Login::class, 'index']);

$routes->get('login', [Login::class, 'index']);
$routes->post('login/process', [Login::class, 'process']);
$routes->get('logout', [Login::class, 'logout']);

$routes->get('/register', [Register::class, 'index']);
$routes->post('/register/store', [Register::class, 'store']);


$routes->get('dashboard', [Dashboard::class, 'index']);

$routes->get('profile', [Profile::class, 'index']);
$routes->post('profile/update', [Profile::class, 'update']);

// === Route group biar rapi ===
$routes->group('user', ['namespace' => 'App\Controllers\User'], function($routes) {
    $routes->get('accounts', 'Accounts::index'); // http://localhost/trading-book/user/accounts
    $routes->post('accounts/add', 'Accounts::add'); // http://localhost/trading-book/user/accounts/add
    $routes->get('accounts/get-active-equity', 'Accounts::getActiveEquity'); // http://localhost/trading-book/user/accounts/get-active-equity
});

// === Alias biar bisa diakses langsung tanpa prefix 'user' ===
$routes->get('accounts', [Accounts::class, 'index']); // http://localhost/trading-book/accounts

$routes->group('transaction', ['namespace' => 'App\Controllers\Transaction'], function($routes) {

    // === Halaman Trade List ===
    $routes->get('trades', [Trades::class, 'index']);

    // === Import Report ===
    $routes->post('import-report/preview', [ImportReport::class, 'preview']); // upload + parsing
    $routes->post('import-report/save', [ImportReport::class, 'save']);       // simpan ke database

    // === Summary (harian + data JSON) ===
    $routes->get('summary', [Summary::class, 'index']);
    $routes->get('summary/data', [Summary::class, 'data']); // ✅ endpoint untuk AJAX

    // === Trading Planner ===
    $routes->get('planner', [TradingPlanner::class, 'index']); // halaman utama planner
    $routes->post('planner/save', [TradingPlanner::class, 'save']); // simpan plan
    $routes->get('planner/events', [TradingPlanner::class, 'events']); // kalender event
    $routes->get('planner/suggest-plan', [TradingPlanner::class, 'suggestPlan']); // AI suggestion
    $routes->get('planner/evaluate-week', [TradingPlanner::class, 'evaluateWeek']); // evaluasi mingguan otomatis

});




$routes->group('wallet', ['namespace' => 'App\Controllers\Wallet'], static function($routes) {
    $routes->get('/', 'Wallet::index');
    $routes->get('create', 'Wallet::create');
    $routes->post('store', 'Wallet::store');
    $routes->get('edit/(:num)', 'Wallet::edit/$1');
    $routes->post('update/(:num)', 'Wallet::update/$1');
    $routes->get('delete/(:num)', 'Wallet::delete/$1');
    $routes->get('set_default/(:num)', 'Wallet::set_default/$1'); // ← Tambahkan ini
    $routes->get('detail/(:num)', 'Wallet::detail/$1');
});

$routes->group('transfer', ['namespace' => 'App\Controllers\Wallet'], static function($routes) {
    $routes->match(['GET', 'POST'], 'deposit/(:segment)', 'FundTransfer::deposit/$1');
    $routes->match(['GET', 'POST'], 'withdraw/(:segment)', 'FundTransfer::withdraw/$1');
    $routes->match(['GET', 'POST'], 'internal/(:segment)', 'FundTransfer::internal/$1');

    // ✅ versi benar
    $routes->match(['GET', 'POST'], 'deposit-to-account', 'FundTransfer::depositToAccount');
    $routes->match(['GET', 'POST'], 'withdraw-from-account', 'FundTransfer::withdrawFromAccount');
    $routes->match(['GET', 'POST'], 'transfer-between-accounts', 'FundTransfer::transferBetweenAccounts');
});







