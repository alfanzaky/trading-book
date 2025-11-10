<?php

namespace App\Controllers\Auth;
use App\Models\UserModel;
use App\Models\SettingModel;
use App\Models\WalletModel;
use CodeIgniter\Controller;

class Register extends Controller
{
    public function index()
    {
        helper(['form']);
        return view('auth/register');
    }

    public function store()
    {
        helper(['form']);

        $rules = [
            'username' => 'required|min_length[3]|max_length[50]',
            'email' => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[6]',
            'confirm_password' => 'matches[password]'
        ];

        if ($this->validate($rules)) {
            $userModel = new UserModel();
            $settingModel = new SettingModel();
            $walletModel = new WalletModel();

            $dataUser = [
                'username' => $this->request->getPost('username'),
                'email' => $this->request->getPost('email'),
                'photo' => 'assets/img/avatars/1.png',
                'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT)
            ];

            $userModel->save($dataUser);
            $userId = $userModel->getInsertID();

            // default settings
            $settingModel->save([
                'user_id' => $userId,
                'timezone' => 'Asia/Jakarta',
                'theme' => 'dark',
                'language' => 'id'
            ]);

            // default wallet
            $walletModel->save([
                'user_id' => $userId,
                'wallet_type' => 'Bank',
                'provider_name' => 'Default Bank',
                'account_name' => $dataUser['username'],
                'account_number' => '000-' . $userId,
                'balance' => 0.00,
                'currency' => 'IDR',
                'is_default' => 1
            ]);

            return redirect()->to('/register')->with('success', 'Registrasi berhasil! Silakan login.');
        }

        return view('auth/register', [
            'validation' => $this->validator
        ]);
    }
}
