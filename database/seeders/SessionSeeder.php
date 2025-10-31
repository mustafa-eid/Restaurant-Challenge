<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Str;

class SessionSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            DB::table('sessions')->insert([
                'id' => Str::uuid(),
                'user_id' => $user->id,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Seeder Script',
                'payload' => serialize(['data' => 'test']),
                'last_activity' => time(),
            ]);
        }
    }
}
