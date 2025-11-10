<?php

namespace App\Models;

use CodeIgniter\Model;

class WalletModel extends Model
{
    protected $table = 'wallets';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'user_id',
        'wallet_type',
        'provider_name',
        'account_name',
        'account_number',
        'balance',
        'currency',
        'is_default',
        'created_at',
        'updated_at'
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
