<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Modules\SchoolManager\Models\AcademicSessionModel;
use App\Modules\SchoolManager\Models\ClassModel;
use App\Modules\SchoolManager\Models\DepartmentModel;
use App\Modules\UserManager\Models\RoleModel;
use App\Modules\UserManager\Models\UserModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $role1 = RoleModel::create([
            'role_name' => 'teacher'
        ]);

        $role3 = RoleModel::create([
            'role_name' => 'admin'
        ]);

        $role4 = RoleModel::create([
            'role_name' => 'checkin'
        ]);

        $role5 = RoleModel::create([
            'role_name' => 'capture'
        ]);

        UserModel::create([
            'uuid'     => Str::ulid(),
            'fullname' => 'Admin',
            'email' => 'admin@gss.com',
            'password' => Hash::make('GSSSadmin1234!'),
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

        UserModel::create([
            'fullname'  => 'Check-In User 5',
            'email'     => 'checkin5@gss.com',
            'password'  => Hash::make('ADMINchecker123356#'),
            'phone_no'  => '2344455654321',
            'role_id'   => $role4->uuid
        ]);

        UserModel::create([
            'fullname'  => 'Check-In User 1',
            'email'     => 'checkin1@gss.com',
            'password'  => Hash::make('ADMINchecker1234777#'),
            'phone_no'  => '234999888754321',
            'role_id'   => $role4->uuid
        ]);

        UserModel::create([
            'fullname'  => 'Check-In User 2',
            'email'     => 'checkin2@gss.com',
            'password'  => Hash::make('ADMINchecker1234333#'),
            'phone_no'  => '23499887654321',
            'role_id'   => $role4->uuid
        ]);

        UserModel::create([
            'fullname'  => 'Check-In User 3',
            'email'     => 'checkin3@gss.com',
            'password'  => Hash::make('ADMINchecker1234665#'),
            'phone_no'  => '23490324674321',
            'role_id'   => $role4->uuid
        ]);

        UserModel::create([
            'fullname'  => 'Check-In User 4',
            'email'     => 'checkin4@gss.com',
            'password'  => Hash::make('ADMINchecker1234443#'),
            'phone_no'  => '234908763331',
            'role_id'   => $role4->uuid
        ]);

        UserModel::create([
            'fullname'  => 'Check-In User 6',
            'email'     => 'checkin6@gss.com',
            'password'  => Hash::make('ADMINchecker1234123#'),
            'phone_no'  => '200087654321',
            'role_id'   => $role4->uuid
        ]);


        UserModel::create([
            'fullname'  => 'Check-In User 7',
            'email'     => 'checkin7@gss.com',
            'password'  => Hash::make('ADMINchecker1234ee3#'),
            'phone_no'  => '234666654321',
            'role_id'   => $role4->uuid
        ]);


        UserModel::create([
            'fullname'  => 'Capture Admin',
            'email'     => 'capture@gss.com',
            'password'  => Hash::make('ADMINelcap1234#'),
            'phone_no'  => '23490879994621',
            'role_id'   => $role5->uuid
        ]);


        Schema::disableForeignKeyConstraints();

        DB::unprepared( file_get_contents( base_path('faculties.sql')) );
        DB::unprepared( file_get_contents( base_path('classes.sql')) );
        DB::unprepared( file_get_contents(base_path('subjects.sql')) );
        DB::unprepared( file_get_contents(base_path('departments.sql')) );
        
        DB::unprepared( file_get_contents(base_path('student_profiles_newer.sql')) );

        
        DB::table('departments_old')->get()->each( fn($dep) => DepartmentModel::create(['department_name' => $dep->name ]));
        
        Schema::dropIfExists('departments_old');

        AcademicSessionModel::create(['session' => '2022/2023', 'uuid' => Str::ulid()]);
        AcademicSessionModel::create(['session' => '2023/2024', 'uuid' => Str::ulid()]);
    }
}
