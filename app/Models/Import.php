<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Import extends Model
{
    protected $fillable = [
        'file_name',
        'status',
        'total_records',
        'processed_records',
        'success_records',
        'failed_records',
        'source_due_amount',
        'parent_amount',
        'child_amount',
        'error_message',
    ];
}
