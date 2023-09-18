<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Modules\UserManager\Models\RoleModel;
use App\Modules\UserManager\Models\UserModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $role1 = RoleModel::create([
            'role_name' => 'teacher'
        ]);

        $role2 = RoleModel::create([
            'role_name' => 'student'
        ]);

        $role3 = RoleModel::create([
            'role_name' => 'admin'
        ]);

        UserModel::create([
            'fullname' => 'Test User',
            'email' => 'admin@soncal.com',
            'password' => Hash::make('SONCALadmin1234!'),
            'phone_no' => '2345678900000',
            'role_id' => $role3->id
        ]);

        UserModel::create([
            'fullname' => 'Test Teacher',
            'email' => 'teacher@example.com',
            'password' => Hash::make('password'),
            'phone_no' => '2345678900100',
            'role_id' => $role1->id
        ]);

       
    }
}
