<?php

namespace App\Console\Commands;

use App\Modules\Excel\Export;
use App\Modules\SchoolManager\Models\StudentProfileModel;
use App\Modules\SchoolManager\Models\SubjectModel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class GenerateTermlyExamReport extends Command
{
    protected $signature = 'app:generate-termly-exam-report';
    protected $description = 'Command description';

    public function handle()
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        
        DB::table('computed_assessment_results')
        ->join('student_profiles', 'student_profiles.id', '=', 'computed_assessment_results.student_profile_id')
        ->where(fn($query) => $query->where('computed_assessment_results.academic_session_id', 4)
                                    ->where('computed_assessment_results.school_term_id', 2)
                                    ->where('student_profiles.class_id', 2)

        )
        ->get()
        ->groupBy('subject_id')
        ->each(function($results, $subjectId){

            $subject = SubjectModel::find($subjectId);
            $headings = collect();

            $results = $results->map(function($result, $index) use($subject, $headings){

                $student = StudentProfileModel::find($result->student_profile_id);

                $assessment_results = json_decode($result->assessments);

                $total_max_score = collect($assessment_results)->sum('max_score');

                $assessment_results = collect($assessment_results)->mapWithKeys(fn($value) => [ strtoupper($value->title)." ($value->max_score)" => $value->score ])->toArray();
                
                $data = [
                    'S/N' => $index + 1,
                    'STUDENT NAME' => "$student->first_name $student->surname",
                    'REG NO' => $student->student_code,
                    'COURSE' => "$subject->subject_name ($subject->subject_code)",
                    ...$assessment_results,
                    "TOTAL SCORE ($total_max_score)" => $result->total_score,
                    "GRADE" => $result->grade,
                    'REMARKS' => $result->remarks
                ];

                $headings->push( array_keys($data) );

                return $data;
            });

            return Excel::store( new Export($results, $headings->first()), "$subject->subject_name.xlsx" );
            
        });
    }
}
