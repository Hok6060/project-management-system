<?php

namespace App\LoanManagement\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_id',
        'name',
        'status',
        'progress',
        'details',
        'output',
    ];

    protected $casts = [
        'details' => 'array',
    ];
}