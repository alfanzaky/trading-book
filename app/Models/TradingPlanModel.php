<?php

namespace App\Models;
use CodeIgniter\Model;

class TradingPlanModel extends Model
{
    protected $table = 'trading_plans';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'user_id',
        'account_id',
        'plan_date',
        'target_profit_percent',
        'max_loss_percent',
        'target_profit',
        'max_loss',
        'notes',
        'actual_profit',
        'actual_loss',
        'evaluation',
        'created_at',
        'updated_at'
    ];
}
