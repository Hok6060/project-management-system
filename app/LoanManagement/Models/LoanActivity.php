<?php

namespace App\LoanManagement\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanActivity extends Model
{
    use HasFactory;

    protected $fillable = ['loan_id', 'user_id', 'description', 'details'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}