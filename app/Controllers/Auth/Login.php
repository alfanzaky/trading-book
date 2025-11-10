<?php

namespace App\Controllers\Auth;
use App\Controllers\BaseController;
use App\Models\UserModel;

class Login extends BaseController
{
    public function index()
    {
        $session = session();

        // kalau sudah login, langsung ke dashboard
        if ($session->get('isLoggedIn')) {
            return redirect()->to('/dashboard');
        }

        return view('auth/login');
    }

    public function process()
    {
        $session = session();

        // Kalau user sudah login, jangan bisa login lagi
        if ($session->get('isLoggedIn')) {
            return redirect()->to(base_url('dashboard'));
        }

        $userModel = new UserModel();
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        $user = $userModel->where('email', $email)->first();

        if ($user && password_verify($password, $user['password'])) {
            $sessionData = [
                'user_id'   => $user['id'],
                'username'  => $user['username'],
                'email'     => $user['email'],
                'photo'     => $user['photo'] ?? 'assets/img/avatars/1.png',
                'isLoggedIn' => true // disamakan
            ];
            $session->set($sessionData);
            return redirect()->to(base_url('dashboard'));
        }

        $session->setFlashdata('error', 'Email atau password salah.');
        return redirect()->to(base_url('login'));
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to(base_url('login'));
    }
}
