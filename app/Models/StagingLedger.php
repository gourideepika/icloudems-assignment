<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StagingLedger extends Model
{
    protected $fillable = [
        'import_id',
        'sr_no',
        'operation_date',
        'academic_year',
        'session',
        'alloted_category',
        'voucher_type',
        'voucher_no',
        'roll_no',
        'admno',
        'status',
        'fee_category',
        'faculty',
        'program',
        'department',
        'batch',
        'receipt_no',
        'fee_head',
        'due_amount',
        'paid_amount',
        'concession',
        'scholarship_amount',
        'reverse_concession_amount',
        'write_off',
        'adjusted_amount',
        'refund_amount',
        'fund_transfer_amount',
        'remark'
    ];
}
