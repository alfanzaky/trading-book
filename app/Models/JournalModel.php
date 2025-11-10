<?php

namespace App\Models;

use CodeIgniter\Model;

class JournalModel extends Model
{
    protected $table = 'journal_entries';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $createdField = 'created_at';

    protected $allowedFields = [
        'user_id',
        'account_id',
        'title',
        'content',
        'mood',
        'created_at'
    ];

    public function getByUser($userId)
    {
        return $this->where('user_id', $userId)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    public function getByAccount($accountId, $userId)
    {
        return $this->where(['account_id' => $accountId, 'user_id' => $userId])
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }
}
