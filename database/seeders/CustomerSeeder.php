<?php

namespace Database\Seeders;

use App\LoanManagement\Models\Customer;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        Customer::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'phone_number' => '123-456-7890',
            'address' => '123 Main St, Anytown, USA',
            'customer_identifier' => 'C00001',
        ]);
    }
}