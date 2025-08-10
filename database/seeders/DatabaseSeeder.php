<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Oeung Chheanghok',
            'email' => 'oeungchheanghok@gmail.com',
            'password' => bcrypt('123123123'),
            'role' => 'admin',
            'notify_by_email' => false,
            'notify_by_telegram' => false,
            'telegram_chat_id' => '711877587'
        ]);
    }
}
