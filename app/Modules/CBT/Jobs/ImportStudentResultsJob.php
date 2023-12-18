<?php

namespace App\Modules\CBT\Jobs;

use App\Modules\CBT\Models\AssessmentModel;
use App\Modules\CBT\Models\ExamResultsModel;
use App\Modules\SchoolManager\Models\StudentProfileModel;
use Illuminate\Foundation\Bus\Dispatchable;
use Spatie\SimpleExcel\SimpleExcelReader;
use Illuminate\Support\Str;

class ImportStudentResultsJob
{
    use Dispatchable;

   
    public function __construct()
    {
        //
    }

    public function handle()
    {
        $assessment = AssessmentModel::updateOrCreate([ 'id' => 9 ],[
            'id' => 9,
            'uuid'  => Str::ulid(),
            'title' => 'YEAR 2 NURSING CA OCTOBER 2021',
            'description' => 'Please read each question carefully before answering',
            'is_standalone' => 0,
            'assessment_type_id' => 6,
            'academic_session_id' => 4,
            'school_term_id' => 2
        ]);

        $assessment->addClassesToAssessment([2]);

        $paths = [
            26 => 'ANATOMY.xlsx',
            29 => 'FON.xlsx',
            21 => 'MEDICAL.xlsx',
            22 => 'SOCIOLOGY.xlsx',
            25 => 'CHN.xlsx',
            30 => 'NUTRITION.xlsx',
            23 => 'PHARMACOLOGY.xlsx',
            24 => 'POLITICS.xlsx',
            28 => 'REPRODUCTIVE.xlsx',
            27 => 'RESEARCH.xlsx'
        ];

        foreach ($paths as $key => $value) {
            $assessment->subjects()->syncWithoutDetaching([ $key => ['uuid' => Str::ulid(), 'class_id' => 2, 'assessment_duration' => 0, 'start_date' => now()->toDateTimeString(), 'end_date' => now()->toDateTimeString() ] ]);
        }

        foreach( $paths as $key => $path){

            SimpleExcelReader::create( base_path('CA/'.$path) )->getRows()->each(function($row) use($assessment, $key){

                $studentCode = $row['EXAM_NO'];
                $studentCode = str_pad($studentCode, 3, '0', STR_PAD_LEFT); 
                $studentCode = 'SOBNCAL/21/'.$studentCode;

                $studentId = StudentProfileModel::firstWhere('student_code', $studentCode)?->id;

                if( ! $studentId ) return;

                $score = $row['TOTAL'];
                
                ExamResultsModel::updateOrCreate([
                    'student_profile_id' => $studentId,
                    'assessment_id' => $assessment->id,
                    'subject_id' => $key,
                ],
                [
                    'uuid' => Str::ulid(),
                    'student_profile_id' => $studentId,
                    'assessment_id' => $assessment->id,
                    'subject_id' => $key,
                    'total_score' => floatval($score)
                ]);
            });
        }
        
    }
}
