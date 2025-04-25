<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->delete();

        $user = User::create([
            'name' => 'Admin',
            'apiEmail' => 'hamidou.balde@socgen.com',
            'apiPassword' => 'hamidou.balde@socgen.com',
            'email' => 'hamidou.balde@socgen.com',
            'password' => Hash::make('hamidou.balde@socgen.com'),
        ]);

        $user->syncRoles('admin');
    }
}
