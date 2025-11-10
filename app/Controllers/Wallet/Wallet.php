<?php

namespace App\Controllers\Wallet;

use App\Controllers\BaseController;
use App\Models\WalletModel;
use App\Models\FundTransferModel;

class Wallet extends BaseController
{
    protected $walletModel;
    protected $transferModel;

    public function __construct()
    {
        $this->walletModel = new WalletModel();
        $this->transferModel = new FundTransferModel();
        helper(['form', 'url', 'number']);
    }

    public function index()
        {
            $userId = session()->get('user_id');

            // Ambil semua wallet user
            $wallets = $this->walletModel
                ->where('user_id', $userId)
                ->orderBy('is_default', 'DESC')
                ->findAll();

            // Wallet favorit (default)
            $favorite = $this->walletModel
                ->where('user_id', $userId)
                ->where('is_default', 1)
                ->first();

        // === Ambil transaksi wallet favorit ===
            $transactions = [];
            if ($favorite) {
                $transactions = $this->transferModel
                    ->groupStart()
                        ->where('from_wallet_id', $favorite['id'])
                        ->orWhere('to_wallet_id', $favorite['id'])
                    ->groupEnd()
                    ->orderBy('created_at', 'DESC')
                    ->limit(10)
                    ->findAll();
            }

            // === request via AJAX (untuk reloadWalletTable) ===
            if ($this->request->isAJAX()) {
                return view('wallet/wallet_list', [
                        'wallets' => $wallets
                    ])
                    . view('wallet/wallet_favorite', [
                        'wallets' => $wallets,
                        'transactions' => $transactions,
                        'favorite' => $favorite
                    ]);
            }

            // Render halaman utama
            return view('wallet/wallet_view', [
                'wallets' => $wallets,
                'transactions' => $transactions,
                'favorite' => $favorite
            ]);
            
        }

    public function create()
    {
        // Cek apakah request datang dari AJAX (modal)
        if ($this->request->isAJAX()) {
            return view('wallet/wallet_form', [
                'title' => 'Tambah Wallet (Modal)',
            ]);
        }

        // Jika diakses langsung (misal lewat URL manual)
        return redirect()->to('/wallet');
    }

    public function store()
    {
        $rules = [
            'wallet_type' => 'required|in_list[Bank,E-Wallet,Crypto]',
            'provider_name' => 'required|min_length[3]',
            'account_name' => 'required|min_length[3]',
            'account_number' => 'required|is_unique[wallets.account_number]',
            'currency' => 'required'
        ];

        if (!$this->validate($rules)) {
            return $this->respondError('Validasi gagal: ' . strip_tags($this->validator->listErrors()));
        }

        $userId = session()->get('user_id');
        $isDefault = $this->request->getPost('is_default') ? 1 : 0;

        if ($isDefault) {
            $this->walletModel->where('user_id', $userId)->set(['is_default' => 0])->update();
        }

        $this->walletModel->save([
            'user_id' => $userId,
            'wallet_type' => $this->request->getPost('wallet_type'),
            'provider_name' => $this->request->getPost('provider_name'),
            'account_name' => $this->request->getPost('account_name'),
            'account_number' => $this->request->getPost('account_number'),
            'balance' => $this->request->getPost('balance') ?? 0,
            'currency' => $this->request->getPost('currency'),
            'is_default' => $isDefault,
        ]);

        return $this->respondSuccess('Wallet berhasil ditambahkan.');
    }

    public function delete($id = null)
    {
        if (!$id || !is_numeric($id)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'ID wallet tidak valid.'
            ]);
        }

        $wallet = $this->walletModel->find($id);
        if (!$wallet) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Wallet tidak ditemukan.'
            ]);
        }

        $forceDelete = $this->request->getGet('force') === 'true';

        $used = $this->transferModel
            ->groupStart()
            ->where('from_wallet_id', $id)
            ->orWhere('to_wallet_id', $id)
            ->groupEnd()
            ->first();

        // Wallet punya histori
        if ($used && !$forceDelete) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' =>
                    'Wallet ini memiliki riwayat transaksi. ' .
                    'Menghapus wallet ini akan menghapus seluruh data transaksi yang terkait. ' .
                    'Apakah Anda yakin ingin melanjutkan penghapusan total? ' .
                    '(Klik "Ya, hapus semua" untuk konfirmasi).'
            ]);
        }

        // Jika force delete
        if ($used && $forceDelete) {
            $this->transferModel
                ->where('from_wallet_id', $id)
                ->orWhere('to_wallet_id', $id)
                ->delete();
        }

        $this->walletModel->delete($id);

        $msg = $forceDelete
            ? 'Wallet dan semua transaksi terkait berhasil dihapus permanen.'
            : 'Wallet berhasil dihapus.';

        return $this->response->setJSON(['status' => 'success', 'message' => $msg]);
    }

    public function set_default($id)
    {
        $userId = session()->get('user_id');
        $wallet = $this->walletModel->find($id);
        if (!$wallet) return $this->respondError('Wallet tidak ditemukan.');

        $this->walletModel->where('user_id', $userId)->set(['is_default' => 0])->update();
        $this->walletModel->update($id, ['is_default' => 1]);

        return $this->respondSuccess('Wallet dijadikan default.');
    }

    public function update($id)
    {
        $wallet = $this->walletModel->find($id);
        if (!$wallet) return $this->respondError('Wallet tidak ditemukan.');

        $rules = [
            'provider_name' => 'required|min_length[3]',
            'account_name' => 'required|min_length[3]',
            'currency' => 'required'
        ];

        if (!$this->validate($rules)) {
            return $this->respondError('Validasi gagal: ' . strip_tags($this->validator->listErrors()));
        }

        $userId = session()->get('user_id');
        $isDefault = $this->request->getPost('is_default') ? 1 : 0;

        if ($isDefault) {
            $this->walletModel->where('user_id', $userId)->set(['is_default' => 0])->update();
        }

        $this->walletModel->update($id, [
            'provider_name' => $this->request->getPost('provider_name'),
            'account_name' => $this->request->getPost('account_name'),
            'currency' => $this->request->getPost('currency'),
            'is_default' => $isDefault,
        ]);

        return $this->respondSuccess('Wallet berhasil diperbarui.');
    }

    /* =========================
       Helper JSON Responder
       ========================= */
    private function respondSuccess(string $msg, int $code = 200)
    {
        return $this->response->setStatusCode($code)
            ->setJSON(['status' => 'success', 'message' => $msg]);
    }

    private function respondError(string $msg, int $code = 400)
    {
        return $this->response->setStatusCode($code)
            ->setJSON(['status' => 'error', 'message' => $msg]);
    }
    
}
