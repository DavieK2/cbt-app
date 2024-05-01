<?php

namespace App\Console\Commands;

use App\Modules\SchoolManager\Models\ClassModel;
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
        StudentProfileModel::where('student_code', 'like', '%SOBNCAL/22/%')->get()->each(function($student) {

            $class = ClassModel::firstWhere('class_name', '200 LEVEL')->uuid;

            $student->update(['class_id' =>  $class ]);

        });

        StudentProfileModel::where('student_code', 'like', '%SOBNCAL/21/%')->get()->each(function($student) {

            $class = ClassModel::firstWhere('class_name', '300 LEVEL')->uuid;

            $student->update(['class_id' =>  $class ]);

        });
    }
}
