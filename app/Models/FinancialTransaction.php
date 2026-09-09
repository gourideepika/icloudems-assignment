<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialTransaction extends Model
{
    protected $fillable = [
        'import_id',
        'module_id',
        'tran_id',
        'amount',
        'crdr',
        'tran_date',
        'acad_year',
        'fee_category',
        'entry_mode',
        'brid',
    ];

    public function details()
    {
        return $this->hasMany(
            FinancialTransactionDetail::class,
            'financial_tran_id'
        );
    }
}