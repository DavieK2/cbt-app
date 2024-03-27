<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Modules\SchoolManager\Models\AcademicSessionModel;
use App\Modules\SchoolManager\Models\ClassModel;
use App\Modules\UserManager\Models\RoleModel;
use App\Modules\UserManager\Models\UserModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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
            'uuid'     => Str::ulid(),
            'fullname' => 'Admin',
            'email' => 'admin@predegree.com',
            'password' => Hash::make('PREDEGREEadmin1234!'),
            'phone_no' => '2345678900000',
            'role_id' => $role3->uuid
        ]);

        UserModel::create([
            'fullname'  => 'Test Teacher',
            'email'     => 'teacher@example.com',
            'password'  => Hash::make('password'),
            'phone_no'  => '2345678900100',
            'role_id'   => $role1->uuid
        ]);

        ClassModel::create(['class_name' => '100 LEVEL', 'class_code' => '100 LEVEL', 'uuid' => Str::ulid() ]);

        AcademicSessionModel::create(['session' => '2023/2024', 'uuid' => Str::ulid()]);
    }
}
