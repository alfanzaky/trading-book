<?php

namespace App\Controllers\Dashboard;
use App\Controllers\BaseController;
use CodeIgniter\HTTP\RedirectResponse;

class Dashboard extends BaseController
{
    public function index(): RedirectResponse|string
    {
        $session = session();
        if (!$session->get('isLoggedIn')) {
            // kalau belum login, redirect ke halaman login
            return redirect()->to('/login');
        }

        return view('dashboard/index');
    }
}
