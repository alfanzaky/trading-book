<?php

namespace App\Controllers\User;
use App\Controllers\BaseController;
use CodeIgniter\HTTP\RedirectResponse;
use App\Models\UserModel;
use App\Models\SettingModel;

class Profile extends BaseController
{
    public function index(): RedirectResponse|string
    {

        $session = session();

        // Cek login
        if (!$session->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $userModel = new UserModel();
        $settingModel = new SettingModel();

        // Ambil data user & setting
        $user = $userModel->find($session->get('user_id'));
        $setting = $settingModel->where('user_id', $session->get('user_id'))->first();

        if (!$setting) {
            $setting = [
                'language' => '',
                'timezone' => '',
                'currency' => ''
            ];
        }

        $data = [
            'user' => $user,
            'setting' => $setting
        ];

        return view('user/profile', $data);
    }

    public function update()
    {
        $session = session();
        $userId = $session->get('user_id');

        $userModel = new UserModel();
        $settingModel = new SettingModel();

        // --- Data dari form ---
        $userData = [
            'first_name' => $this->request->getPost('firstName'),
            'last_name'  => $this->request->getPost('lastName'),
            'email'      => $this->request->getPost('email'),
            'phone'      => $this->request->getPost('phoneNumber'),
            'address'    => $this->request->getPost('address'),
            'state'      => $this->request->getPost('state'),
            'zipcode'    => $this->request->getPost('zipCode'),
            'country'    => $this->request->getPost('country'),
            'photo'      => 'user.png' // nama tetap
        ];

        // --- Upload foto manual ---
        $file = $this->request->getFile('upload');

        if ($file && $file->isValid()) {
            $uploadDir  = FCPATH . 'assets/img/user/';
            $targetFile = $uploadDir . 'user.png';

            // Pastikan folder ada
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Hapus foto lama
            if (file_exists($targetFile)) {
                unlink($targetFile);
            }

            // Pindahkan file baru
            if (move_uploaded_file($file->getTempName(), $targetFile)) {
                $userData['photo'] = 'user.png';
                $session->set('photo', 'assets/img/user/user.png');
            } else {
                log_message('error', '❌ move_uploaded_file() gagal. Cek permission folder assets/img/user/');
            }
        } else {
            log_message('debug', 'Tidak ada file baru diupload.');
        }

        // Simpan ke tabel users
        $userModel->update($userId, $userData);

        // --- Update setting user ---
        $settingData = [
            'language' => $this->request->getPost('language'),
            'timezone' => $this->request->getPost('timezone'),
            'currency' => $this->request->getPost('currency')
        ];

        $existing = $settingModel->where('user_id', $userId)->first();
        if ($existing) {
            $settingModel->update($existing['id'], $settingData);
        } else {
            $settingData['user_id'] = $userId;
            $settingModel->insert($settingData);
        }

        $session->setFlashdata('success', '✅ Profile updated successfully!');
        return redirect()->to(base_url('profile'));
    }
}
