<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GenerateTermlyResults extends Command
{
    protected $signature = 'app:generate-termly-results';
    protected $description = 'Command description';

    public function handle()
    {
        $assessmentIds = [9];

        DB::table('assessment_results')
            ->whereIn('assessment_results.assessment_id', $assessmentIds)
            ->join('assessments', 'assessments.id', 'assessment_results.assessment_id')
            ->join('assessment_types', 'assessment_types.id', 'assessments.assessment_type_id')
            ->select('assessment_results.subject_id', 'assessments.academic_session_id', 'assessments.school_term_id', 'assessment_results.student_profile_id', 'assessment_results.total_score', 'assessment_types.type as title', 'assessment_types.max_score', 'assessment_results.assessment_id')
            ->get()
            ->groupBy('student_profile_id')
            ->each(function($results, $studentId){

                $subject_results = $results->groupBy('subject_id');

                $subject_results->each(function($result, $subjectId) use($studentId){

                    $total_score = $result->sum('total_score');

                    $grade = match( true ){
                        ( $total_score >= 80 ) => 'A',
                        ( $total_score >= 70 && $total_score < 80 ) => 'B',
                        ( $total_score >= 60 && $total_score < 70 ) => 'C',
                        ( $total_score >= 50 && $total_score < 60 ) => 'D',
                        ( $total_score < 50 ) => 'F',
                        default => NULL
                    };

                    $remarks = match( true ){
                        ( $total_score >= 80 ) => 'Distinction',
                        ( $total_score >= 70 && $total_score < 80 ) => 'Upper Credit',
                        ( $total_score >= 60 && $total_score < 70 ) => 'Lower Credit',
                        ( $total_score >= 50 && $total_score < 60 ) => 'Pass',
                        ( $total_score < 50 ) => 'Fail',
                        default => NULL
                    };

                    
                    $assessments = $result->map(fn($assessment) => ['assessment_id' => $assessment->assessment_id, 'title' => $assessment->title, 'score' => $assessment->total_score, 'max_score' => $assessment->max_score ]);


                    DB::table('computed_assessment_results')->updateOrInsert([
                        'student_profile_id' => $studentId,
                        'subject_id' => $subjectId,
                        'academic_session_id' => $result[0]->academic_session_id,
                        'school_term_id' => $result[0]->school_term_id,
                    ],
                    [
                        'id' => Str::ulid(),
                        'student_profile_id' => $studentId,
                        'subject_id' => $subjectId,
                        'academic_session_id' => $result[0]->academic_session_id,
                        'school_term_id' => $result[0]->school_term_id,
                        'assessments' => json_encode( $assessments->toArray() ),
                        'total_score' => $total_score,
                        'grade' => $grade,
                        'remarks' => $remarks,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                });
            });
    }
}
