<?php

namespace App\Console\Commands;

use App\Modules\CBT\Models\AssessmentModel;
use App\Modules\SchoolManager\Models\ClassModel;
use App\Modules\SchoolManager\Models\StudentProfileModel;
use App\Modules\SchoolManager\Models\SubjectModel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MassAssignCourseToStudentCommand extends Command
{
    protected $signature = 'update:level';
    protected $description = 'Command description';

    public function handle()
    {
    //    $courses = SubjectModel::latest()->limit(7)->get()->pluck('uuid')->toArray();
            $assessment = AssessmentModel::first();

            ClassModel::get()->map( function($class)use($assessment){

                DB::table('assessment_classes')->insert(['uuid' => Str::ulid(), 'assessment_id' => $assessment->uuid, 'class_id' => $class->uuid ]);
            });
                

                

                    
            $class = ClassModel::get()->map( function($class)use($assessment){


                return SubjectModel::get()->map( function($sub) use($assessment, $class){
                    
                    return [
                        'uuid'                  => Str::ulid() ,
                        'assessment_id'         => $assessment->uuid, 
                        'subject_id'            => $sub->uuid, 
                        'is_published'          => false, 
                        'class_id'              => $class->uuid,
                        'assessment_duration'   => 30,
                        'start_date'            => now()->toDateTimeString(),
                        'end_date'              => now()->addDay()->toDateTimeString(),
                    ];
                });

            
                
            });

            $class->each( function($cls) {
                
                DB::table('assessment_subjects')->insert($cls->toArray() );

            } );
    }
}
