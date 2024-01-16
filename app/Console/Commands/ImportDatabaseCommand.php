<?php

namespace App\Console\Commands;

use App\Modules\SchoolManager\Models\StudentProfileModel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ImportDatabaseCommand extends Command
{
    protected $signature = 'app:import-database';
    protected $description = 'Command description';

    public function handle()
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        
        $students = DB::table('student_profiles_update')->where('student_code', 'like', '%SOBMCAL/21%')->where('class_id', 3)->get();

        $students->each(function( $student ){

            StudentProfileModel::firstWhere('student_code', $student->student_code)?->update([ 'profile_pic' => $student->profile_pic ]);

        });
    }
}
