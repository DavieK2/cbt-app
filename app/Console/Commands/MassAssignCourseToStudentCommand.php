<?php

namespace App\Console\Commands;

use App\Modules\SchoolManager\Models\StudentProfileModel;
use Illuminate\Console\Command;

class MassAssignCourseToStudentCommand extends Command
{
    protected $signature = 'app:assign-course';
    protected $description = 'Command description';

    public function handle()
    {
        $students = StudentProfileModel::where('student_code', 'like', '%SOBMCAL/21%')->where('class_id', 3)->get();

        $students->each(function($student){

            $student->assignSubject([32,33, 34]);
    
        });
    }
}
