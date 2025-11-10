<?php

namespace App\Controllers\Transaction;

use App\Controllers\BaseController;
use App\Models\AccountModel;
use App\Models\TransactionModel;

class Trades extends BaseController
{
    protected $accountModel;
    protected $transactionModel;

    public function __construct()
    {
        $this->accountModel = new AccountModel();
        $this->transactionModel = new TransactionModel();
    }

    public function index()
    {
        $session = session();
        $userId = $session->get('user_id');

        if (!$userId) {
            return redirect()->to('/login');
        }

        // === Jika request via AJAX ===
        if ($this->request->isAJAX()) {
            return $this->handleAjax($userId);
        }

        // === Non-AJAX (load halaman view utama) ===
        $accounts = $this->accountModel
            ->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->findAll();

        return view('transaction/trades_view', [
            'title'    => 'Trade History',
            'accounts' => $accounts,
        ]);
    }

    /**
     * === Handle AJAX Pagination, Search, and Filter ===
     */
    private function handleAjax(int $userId)
    {
        $page   = max(1, (int) $this->request->getGet('page') ?? 1);
        $limit  = 10;
        $offset = ($page - 1) * $limit;
        $search = trim($this->request->getGet('search') ?? '');
        $start  = $this->request->getGet('start_date');
        $end    = $this->request->getGet('end_date');

        $builder = $this->transactionModel->builder();

        $builder->select('
                transactions.id,
                transactions.account_id,
                transactions.position_id,
                transactions.type,
                transactions.symbol,
                transactions.lot_size,
                transactions.open_price,
                transactions.close_price,
                transactions.open_time,
                transactions.close_time,
                transactions.profit_loss,
                accounts.account_name,
                accounts.login_id AS account_login
            ')
            ->join('accounts', 'accounts.id = transactions.account_id', 'left')
            ->where('accounts.user_id', $userId);

        // === Search filter ===
        $accountId = $this->request->getGet('account_id');
        if (!empty($accountId)) {
            $builder->where('transactions.account_id', $accountId);
        }
        
        if ($search !== '') {
            $builder->groupStart()
                ->like('transactions.symbol', $search)
                ->orLike('transactions.type', $search)
                ->orLike('accounts.account_name', $search)
                ->orLike('accounts.login_id', $search)
                ->groupEnd();
        }

        // === Date range filter ===
        if (!empty($start)) {
            $builder->where('transactions.open_time >=', $start . ' 00:00:00');
        }
        if (!empty($end)) {
            $builder->where('transactions.open_time <=', $end . ' 23:59:59');
        }

        // === Hitung total data (untuk pagination) ===
        $countBuilder = clone $builder;
        $totalRecords = $countBuilder->countAllResults(false);

        // === Ambil data halaman aktif ===
        $transactions = $builder
            ->orderBy('transactions.open_time', 'DESC')
            ->limit($limit, $offset)
            ->get()
            ->getResultArray();

        $totalPages = max(1, ceil($totalRecords / $limit));

        return $this->response->setJSON([
            'status'        => 'success',
            'transactions'  => $transactions,
            'pager' => [
                'currentPage'  => $page,
                'totalPages'   => $totalPages,
                'totalRecords' => $totalRecords,
                'limit'        => $limit,
            ],
        ]);
    }
}
