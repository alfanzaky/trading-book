<?php

namespace App\Models;
use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';

    // Semua kolom yang bisa diisi / diupdate
    protected $allowedFields = [
        'username',
        'first_name',
        'last_name',
        'email',
        'password',
        'phone',
        'photo',
        'address',
        'state',
        'zipcode',
        'country',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;
}

