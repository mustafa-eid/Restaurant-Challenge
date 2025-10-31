<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PasswordResetTokenSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('password_reset_tokens')->insert([
            'email' => 'admin@example.com',
            'token' => Str::random(60),
            'created_at' => now(),
        ]);
    }
}
