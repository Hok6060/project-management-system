<?php

namespace App\LoanManagement\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone_number',
        'address',
        'customer_identifier',
    ];

    /**
     * Get the full name of the customer.
     */
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Get the loans for the customer.
     */
    public function loans()
    {
        return $this->hasMany(Loan::class);
    }
}