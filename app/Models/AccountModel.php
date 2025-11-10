<?php

namespace App\Models;

use CodeIgniter\Model;

class AccountModel extends Model
{
    protected $table = 'accounts';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;

    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'user_id',
        'broker_name',
        'account_name',
        'uid',
        'account_type',
        'platform',
        'spread',
        'commission',
        'balance',
        'currency',
        'status',
        'login_id',
        'password',
        'investor_password',
        'server',
        'leverage',
        'created_at',
        'updated_at'
    ];

    // Otomatis handle timestamps
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Contoh fungsi khusus
    public function getActiveAccounts($userId)
    {
        return $this->where('user_id', $userId)
                    ->where('status', 'Active')
                    ->findAll();
    }

    public function getNonActiveAccounts($userId)
    {
        return $this->where('user_id', $userId)
                    ->where('status', 'Inactive')
                    ->findAll();
    }
}
