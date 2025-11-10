<?php

namespace App\Controllers\User;

use App\Controllers\BaseController;
use App\Models\AccountModel;
use CodeIgniter\HTTP\ResponseInterface;

class Accounts extends BaseController
{
    protected $accountModel;

    public function __construct()
        {
            $this->accountModel = new AccountModel();
        }

    public function index(): string|ResponseInterface
        {
            $session = session();
            if (!$session->get('isLoggedIn')) {
                return redirect()->to('/login');
            }

            $userId = $session->get('user_id');
            $data = [
                'activeAccounts'    => $this->accountModel->getActiveAccounts($userId),
                'nonActiveAccounts' => $this->accountModel->getNonActiveAccounts($userId),
            ];

            return view('user/accounts', $data);
        }

    public function add()
        {
            $session = session();
            if (!$session->get('isLoggedIn')) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Harus login terlebih dahulu'
                ])->setStatusCode(401);
            }

            $data = [
                'user_id'           => $session->get('user_id'),
                'broker_name'       => $this->request->getPost('broker_name'),
                'account_name'      => $this->request->getPost('account_name'),
                'uid'               => $this->request->getPost('uid'),
                'account_type'      => $this->request->getPost('account_type'),
                'platform'          => $this->request->getPost('platform'),
                'spread'            => $this->request->getPost('spread'),
                'commission'        => $this->request->getPost('commission'),
                'balance'           => $this->request->getPost('balance') ?: 0.00,
                'currency'          => $this->request->getPost('currency') ?: 'USD',
                'status'            => $this->request->getPost('status') ?: 'Active',
                'login_id'          => $this->request->getPost('login_id'),
                'password'          => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
                'investor_password' => $this->request->getPost('investor_password'),
                'server'            => $this->request->getPost('server'),
                'leverage'          => $this->request->getPost('leverage'),
            ];

            try {
                $this->accountModel->save($data);
                return $this->response->setJSON([
                    'status'  => 'success',
                    'message' => 'Akun trading berhasil ditambahkan.',
                    'data'    => $data
                ]);
            } catch (\Exception $e) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'Gagal menyimpan akun: ' . $e->getMessage()
                ])->setStatusCode(500);
            }
        }

    /**
     * Ambil equity akun aktif berdasarkan user & account_id
     * digunakan oleh Trading Planner (planner-actions.js)
     */
    public function getActiveEquity()
    {
        $userId = session()->get('user_id');
        $accountId = $this->request->getGet('account_id');

        if (!$userId || !$accountId) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Parameter user_id atau account_id tidak valid.',
                'equity' => 0
            ])->setStatusCode(400);
        }

        // Ambil balance sebagai nilai equity (karena kolom equity belum ada)
        $account = $this->accountModel
            ->select('balance AS equity')
            ->where('user_id', $userId)
            ->where('id', $accountId)
            ->first();

        if (!$account) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Akun tidak ditemukan.',
                'equity' => 0
            ])->setStatusCode(404);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Equity berhasil diambil dari balance.',
            'equity' => (float)$account['equity']
        ]);
    }

    }
