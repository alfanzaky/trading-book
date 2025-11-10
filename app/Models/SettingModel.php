<?php

namespace App\Models;
use CodeIgniter\Model;

class SettingModel extends Model
{
    protected $table = 'settings';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'user_id',
        'timezone',
        'theme',
        'language',
        'currency'
    ];

    public $timestamps = false;
}
