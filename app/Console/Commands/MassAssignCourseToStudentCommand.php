<?php

namespace App\Console\Commands;

use App\Modules\SchoolManager\Models\StudentProfileModel;
use App\Modules\SchoolManager\Models\SubjectModel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MassAssignCourseToStudentCommand extends Command
{
    protected $signature = 'update:level';
    protected $description = 'Command description';

    public function handle()
    {
       $courses = SubjectModel::latest()->limit(7)->get()->pluck('uuid')->toArray();

        StudentProfileModel::where('student_code', 'like', '%SOBMCAL/22/%')->get()->each(function($student) use($courses){

            $student->assignSubject( $courses );

        });
    }
}
