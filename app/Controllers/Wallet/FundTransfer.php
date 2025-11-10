<?php

namespace App\Controllers\Wallet;

use App\Controllers\BaseController;
use App\Models\WalletModel;
use App\Models\FundTransferModel;
use App\Models\AccountModel;

class FundTransfer extends BaseController
{
    protected $walletModel;
    protected $transferModel;
    protected $accountModel;
    protected $db;
    protected $userId;

    public function __construct()
    {
        helper(['form', 'url']);
        $this->walletModel   = new WalletModel();
        $this->transferModel = new FundTransferModel();
        $this->accountModel  = new AccountModel();
        $this->db            = \Config\Database::connect();

        // Proteksi login
        $this->userId = session()->get('user_id');
        if (!$this->userId) {
            redirect()->to('/login')->send();
            exit;
        }
    }

    /* ====================== TAMBAH DANA KE WALLET ====================== */
    public function deposit($walletId = null)
    {
        if (!$walletId || !is_numeric($walletId))
            return $this->response->setStatusCode(400)->setBody('Wallet ID tidak valid.');

        $wallet = $this->walletModel->where('user_id', $this->userId)->find($walletId);
        if (!$wallet)
            return $this->response->setStatusCode(404)->setBody('Wallet tidak ditemukan.');

        if ($this->request->getMethod(true) === 'GET')
            return view('components/modal/wallet/wallet_forms/deposit_form', ['wallet' => $wallet]);

        if ($this->request->getMethod(true) === 'POST') {
            $amount = (float)$this->request->getPost('amount');
            $note   = $this->request->getPost('note') ?? '';

            if ($amount <= 0)
                return $this->response->setStatusCode(400)->setBody('Nominal tidak valid.');

            try {
                $this->db->transStart();

                $this->transferModel->insert([
                    'user_id'        => $this->userId,
                    'from_wallet_id' => null,
                    'to_wallet_id'   => $walletId,
                    'amount'         => $amount,
                    'currency'       => $wallet['currency'],
                    'transfer_type'  => 'Tambah dana',
                    'status'         => 'Success',
                    'note'           => $note,
                ]);

                $this->walletModel->update($walletId, [
                    'balance' => $wallet['balance'] + $amount
                ]);

                $this->db->transComplete();
                return $this->response->setStatusCode(200)->setBody('Deposit berhasil.');
            } catch (\Throwable $e) {
                $this->db->transRollback();
                return $this->response->setStatusCode(500)->setBody('Gagal deposit: ' . $e->getMessage());
            }
        }

        return $this->response->setStatusCode(405)->setBody('Metode tidak diizinkan.');
    }

    /* ====================== TARIK DANA DARI WALLET ====================== */
    public function withdraw($walletId = null)
    {
        if (!$walletId || !is_numeric($walletId))
            return $this->response->setStatusCode(400)->setBody('Wallet ID tidak valid.');

        $wallet = $this->walletModel->where('user_id', $this->userId)->find($walletId);
        if (!$wallet)
            return $this->response->setStatusCode(404)->setBody('Wallet tidak ditemukan.');

        if ($this->request->getMethod(true) === 'GET')
            return view('components/modal/wallet/wallet_forms/withdraw_form', ['wallet' => $wallet]);

        if ($this->request->getMethod(true) === 'POST') {
            $amount = (float)$this->request->getPost('amount');
            $note   = $this->request->getPost('note') ?? '';

            if ($amount <= 0)
                return $this->response->setStatusCode(400)->setBody('Nominal tidak valid.');
            if ($amount > $wallet['balance'])
                return $this->response->setStatusCode(400)->setBody('Saldo tidak mencukupi.');

            try {
                $this->db->transStart();

                $this->transferModel->insert([
                    'user_id'        => $this->userId,
                    'from_wallet_id' => $walletId,
                    'to_wallet_id'   => null,
                    'amount'         => $amount,
                    'currency'       => $wallet['currency'],
                    'transfer_type'  => 'Tarik dana',
                    'status'         => 'Success',
                    'note'           => $note,
                ]);

                $this->walletModel->update($walletId, [
                    'balance' => $wallet['balance'] - $amount
                ]);

                $this->db->transComplete();
                return $this->response->setStatusCode(200)->setBody('Withdraw berhasil.');
            } catch (\Throwable $e) {
                $this->db->transRollback();
                return $this->response->setStatusCode(500)->setBody('Gagal withdraw: ' . $e->getMessage());
            }
        }

        return $this->response->setStatusCode(405)->setBody('Metode tidak diizinkan.');
    }

    /* ================== TRANSFER ANTAR WALLET / REKENING ================== */
    public function internal($walletId = null)
    {
        if (!$walletId || !is_numeric($walletId))
            return $this->response->setStatusCode(400)->setBody('Wallet ID tidak valid.');

        $wallet = $this->walletModel->where('user_id', $this->userId)->find($walletId);
        if (!$wallet)
            return $this->response->setStatusCode(404)->setBody('Wallet sumber tidak ditemukan.');

        $wallets = $this->walletModel
            ->where('user_id', $this->userId)
            ->where('id !=', $walletId)
            ->findAll();

        if ($this->request->getMethod(true) === 'GET')
            return view('components/modal/wallet/wallet_forms/transfer_form', [
                'wallet'  => $wallet,
                'wallets' => $wallets
            ]);

        if ($this->request->getMethod(true) === 'POST') {
            $toId   = (int)$this->request->getPost('to_wallet_id');
            $amount = (float)$this->request->getPost('amount');
            $note   = $this->request->getPost('note') ?? '';

            if ($amount <= 0)
                return $this->response->setStatusCode(400)->setBody('Nominal tidak valid.');
            if ($amount > $wallet['balance'])
                return $this->response->setStatusCode(400)->setBody('Saldo tidak mencukupi.');

            $targetWallet = $this->walletModel->where('user_id', $this->userId)->find($toId);
            if (!$targetWallet)
                return $this->response->setStatusCode(404)->setBody('Wallet tujuan tidak ditemukan.');

            try {
                $this->db->transStart();

                $this->transferModel->insert([
                    'user_id'        => $this->userId,
                    'from_wallet_id' => $walletId,
                    'to_wallet_id'   => $toId,
                    'amount'         => $amount,
                    'currency'       => $wallet['currency'],
                    'transfer_type'  => 'Transfer Antar Rekening',
                    'status'         => 'Success',
                    'note'           => $note,
                ]);

                $this->walletModel->update($walletId, ['balance' => $wallet['balance'] - $amount]);
                $this->walletModel->update($toId, ['balance' => $targetWallet['balance'] + $amount]);

                $this->db->transComplete();
                return $this->response->setStatusCode(200)->setBody('Transfer internal berhasil.');
            } catch (\Throwable $e) {
                $this->db->transRollback();
                return $this->response->setStatusCode(500)->setBody('Transfer gagal: ' . $e->getMessage());
            }
        }

        return $this->response->setStatusCode(405)->setBody('Metode tidak diizinkan.');
    }

    /* ================== DEPOSIT KE AKUN TRADING ================== */
    public function depositToAccount()
    {
        if ($this->request->getMethod(true) === 'GET') {
            $accountId = $this->request->getGet('account_id');
            $wallets   = $this->walletModel->where('user_id', $this->userId)->findAll();
            $account   = $this->accountModel->where('user_id', $this->userId)->find($accountId);

            if (!$account)
                return $this->response->setStatusCode(404)->setBody('Akun trading tidak ditemukan.');

            return view('components/modal/account/account_form/deposit_account_form.php', [
                'wallets' => $wallets,
                'account' => $account,
            ]);
        }

        if ($this->request->getMethod(true) === 'POST') {
            $walletId  = (int)$this->request->getPost('wallet_id');
            $accountId = (int)$this->request->getPost('account_id');
            $amount    = (float)$this->request->getPost('amount');
            $rate      = (float)$this->request->getPost('rate') ?: 1;
            $note      = $this->request->getPost('note') ?? '';

            $wallet  = $this->walletModel->find($walletId);
            $account = $this->accountModel->find($accountId);

            if (!$wallet || !$account)
                return $this->response->setStatusCode(404)->setBody('Wallet atau akun tidak ditemukan.');
            if ($amount <= 0)
                return $this->response->setStatusCode(400)->setBody('Nominal tidak valid.');
            if ($amount > $wallet['balance'])
                return $this->response->setStatusCode(400)->setBody('Saldo wallet tidak mencukupi.');

            $walletCurrency  = $wallet['currency'];
            $accountCurrency = $account['currency'];
            $convertedAmount = $walletCurrency !== $accountCurrency
                ? ($walletCurrency === 'IDR' ? ($amount / $rate) : ($amount * $rate))
                : $amount;

            try {
                $this->db->transStart();

                $this->transferModel->insert([
                    'user_id'          => $this->userId,
                    'from_wallet_id'   => $walletId,
                    'to_account_id'    => $accountId,
                    'amount'           => $amount,
                    'converted_amount' => $convertedAmount,
                    'currency'         => $walletCurrency,
                    'rate'             => $rate,
                    'transfer_type'    => 'Deposit ke Akun Trading',
                    'status'           => 'Success',
                    'note'             => $note,
                ]);

                $this->walletModel->update($walletId, ['balance' => $wallet['balance'] - $amount]);
                $this->accountModel->update($accountId, ['balance' => $account['balance'] + $convertedAmount]);

                $this->db->transComplete();
                return $this->response->setStatusCode(200)->setBody('Deposit berhasil.');
            } catch (\Throwable $e) {
                $this->db->transRollback();
                return $this->response->setStatusCode(500)->setBody('Gagal deposit: ' . $e->getMessage());
            }
        }

        return $this->response->setStatusCode(405)->setBody('Metode tidak diizinkan.');
    }

    /* ================== PENARIKAN DANA DARI AKUN TRADING ================== */
    public function withdrawFromAccount()
    {
        if ($this->request->getMethod(true) === 'GET') {
            $accountId = $this->request->getGet('account_id');
            $account   = $this->accountModel->where('user_id', $this->userId)->find($accountId);

            if (!$account)
                return $this->response->setStatusCode(404)->setBody('Akun trading tidak ditemukan.');

            $wallets = $this->walletModel->where('user_id', $this->userId)->findAll();

            return view('components/modal/account/account_form/withdraw_account_form', [
                'account' => $account,
                'wallets' => $wallets,
            ]);
        }

        if ($this->request->getMethod(true) === 'POST') {
            $accountId = (int)$this->request->getPost('account_id');
            $walletId  = (int)$this->request->getPost('wallet_id');
            $amount    = (float)$this->request->getPost('amount');
            $rate      = (float)$this->request->getPost('rate') ?: 1;
            $note      = $this->request->getPost('note') ?? '';

            $account = $this->accountModel->find($accountId);
            $wallet  = $this->walletModel->find($walletId);

            if (!$account || !$wallet)
                return $this->response->setStatusCode(404)->setBody('Akun atau wallet tidak ditemukan.');
            if ($amount <= 0)
                return $this->response->setStatusCode(400)->setBody('Nominal tidak valid.');
            if ($amount > $account['balance'])
                return $this->response->setStatusCode(400)->setBody('Saldo akun tidak mencukupi.');

            $accountCurrency = $account['currency'];
            $walletCurrency  = $wallet['currency'];
            $convertedAmount = $accountCurrency !== $walletCurrency
                ? ($accountCurrency === 'IDR' ? ($amount / $rate) : ($amount * $rate))
                : $amount;

            try {
                $this->db->transStart();

                $this->transferModel->insert([
                    'user_id'          => $this->userId,
                    'from_account_id'  => $accountId,
                    'to_wallet_id'     => $walletId,
                    'amount'           => $amount,
                    'converted_amount' => $convertedAmount,
                    'currency'         => $accountCurrency,
                    'rate'             => $rate,
                    'transfer_type'    => 'Penarikan Dana Akun Trading',
                    'status'           => 'Success',
                    'note'             => $note,
                ]);

                $this->accountModel->update($accountId, ['balance' => $account['balance'] - $amount]);
                $this->walletModel->update($walletId, ['balance' => $wallet['balance'] + $convertedAmount]);

                $this->db->transComplete();
                return $this->response->setStatusCode(200)->setBody('Withdraw berhasil diproses.');
            } catch (\Throwable $e) {
                $this->db->transRollback();
                return $this->response->setStatusCode(500)->setBody('Gagal withdraw: ' . $e->getMessage());
            }
        }

        return $this->response->setStatusCode(405)->setBody('Metode tidak diizinkan.');
    }

    /* ================== TRANSFER ANTAR AKUN TRADING ================== */
    public function transferBetweenAccounts()
    {
        if ($this->request->getMethod(true) === 'GET') {
            $fromId = $this->request->getGet('from_account_id');
            $fromAccount = $this->accountModel->where('user_id', $this->userId)->find($fromId);

            if (!$fromAccount)
                return $this->response->setStatusCode(404)->setBody('Akun sumber tidak ditemukan.');

            $accounts = $this->accountModel->where('user_id', $this->userId)->findAll();

            return view('components/modal/account/account_form/transfer_accounts_form', [
                'fromAccount' => $fromAccount,
                'accounts'    => $accounts,
            ]);
        }

        if ($this->request->getMethod(true) === 'POST') {
            $fromId = (int)$this->request->getPost('from_account_id');
            $toId   = (int)$this->request->getPost('to_account_id');
            $amount = (float)$this->request->getPost('amount');
            $note   = $this->request->getPost('note') ?? '';

            $fromAccount = $this->accountModel->find($fromId);
            $toAccount   = $this->accountModel->find($toId);

            if (!$fromAccount || !$toAccount)
                return $this->response->setStatusCode(404)->setBody('Akun sumber atau tujuan tidak ditemukan.');
            if ($amount <= 0)
                return $this->response->setStatusCode(400)->setBody('Nominal tidak valid.');
            if ($amount > $fromAccount['balance'])
                return $this->response->setStatusCode(400)->setBody('Saldo akun sumber tidak mencukupi.');

            try {
                $this->db->transStart();

                $this->transferModel->insert([
                    'user_id'         => $this->userId,
                    'from_account_id' => $fromId,
                    'to_account_id'   => $toId,
                    'amount'          => $amount,
                    'currency'        => $fromAccount['currency'],
                    'transfer_type'   => 'Transfer Antar Akun Trading',
                    'status'          => 'Success',
                    'note'            => $note,
                ]);

                $this->accountModel->update($fromId, ['balance' => $fromAccount['balance'] - $amount]);
                $this->accountModel->update($toId, ['balance' => $toAccount['balance'] + $amount]);

                $this->db->transComplete();
                return $this->response->setStatusCode(200)->setBody('Transfer antar akun berhasil.');
            } catch (\Throwable $e) {
                $this->db->transRollback();
                return $this->response->setStatusCode(500)->setBody('Gagal transfer: ' . $e->getMessage());
            }
        }

        return $this->response->setStatusCode(405)->setBody('Metode tidak diizinkan.');
    }
}
