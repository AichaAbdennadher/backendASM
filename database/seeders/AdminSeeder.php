<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
class AdminSeeder extends Seeder
{
    public function run()
    {
        User::updateOrCreate(
            ['email' => 'abdennadheraicha19@gmail.com'],
            [
                'name' => 'Admin',
                'role'=> 0,
                'password' => Hash::make('aicha123'),
            ]
        );
      
    }
}
