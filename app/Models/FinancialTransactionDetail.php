<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialTransactionDetail extends Model
{
    protected $fillable = [
        'financial_tran_id',
        'module_id',
        'amount',
        'head_id',
        'crdr',
        'head_name',
    ];

    public function transaction()
    {
        return $this->belongsTo(
            FinancialTransaction::class,
            'financial_tran_id'
        );
    }
}