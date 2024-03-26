<?php

namespace App\Console\Commands;

use App\Modules\CBT\Models\AssessmentModel;
use App\Modules\SchoolManager\Models\StudentProfileModel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ComputeAssessmentResultCommand extends Command
{
    protected $signature = 'compute:result';
    protected $description = 'Command description';

    public function handle()
    {
        set_time_limit(0);


        $assessment = AssessmentModel::first();

        DB::table('assessment_sessions')
            ->join('student_profiles', 'student_profiles.uuid', '=', 'assessment_sessions.student_profile_id')
            ->where( function($query) use($assessment){
                $query->where('assessment_sessions.assessment_id', $assessment->uuid);
            })
            ->select('assessment_sessions.*', 'student_profiles.class_id')
            ->get()
            ->groupBy('student_profile_id')
            ->each(function($session, $studentId) use($assessment){
            
                $sub = $session->groupBy('subject_id');
    
                $sub->each(function($s, $subjectId) use($studentId, $assessment){
    
                    $student = StudentProfileModel::find($studentId);
    
                    $student_score = $s->sum('score');
    
                    $max_score = $assessment->assessmentType->max_score;
    
                    $total_marks = $assessment->questions()->where(fn($query) => $query->where('assessment_questions.subject_id', $subjectId)->where('assessment_questions.class_id', $student->class_id))->sum('question_score');

                    $total_score = floor( ( ($student_score) / $total_marks ) * ( $max_score ) );
    
                    $grade = match( true ){
                        ( $total_score >= 70 ) => 'A',
                        ( $total_score >= 60 && $total_score < 69 ) => 'B',
                        ( $total_score >= 50 && $total_score < 59 ) => 'C',
                        ( $total_score >= 45 && $total_score < 49 ) => 'D',
                        ( $total_score >= 40 && $total_score < 44 ) => 'E',
                        ( $total_score <= 39 ) => 'F',
                        default => NULL
                    };
    
                    $remarks = match( true ){
                        ( $total_score >= 70 ) => 'EXCELLENT',
                        ( $total_score >= 60 && $total_score < 69 ) => 'VERY GOOD',
                        ( $total_score >= 50 && $total_score < 59 ) => 'GOOD',
                        ( $total_score >= 45 && $total_score < 49 ) => 'PASS',
                        ( $total_score >= 40 && $total_score < 44 ) => 'FAIR',
                        ( $total_score <= 39 ) => 'FAIL',
                        default => NULL
                    };

                    $points = match( true ){
                        ( $total_score >= 70 ) => 5,
                        ( $total_score >= 60 && $total_score < 69 ) => 4,
                        ( $total_score >= 50 && $total_score < 59 ) => 3,
                        ( $total_score >= 45 && $total_score < 49 ) => 2,
                        ( $total_score >= 40 && $total_score < 44 ) => 1,
                        ( $total_score <= 39 ) => 0,
                        default => NULL
                    };
    
                    DB::table('assessment_results')->where('student_profile_id', $studentId)->where('assessment_id', $assessment->uuid)->where('subject_id', $subjectId)->limit(1)->update(['total_score' => $total_score, 'grade' => $grade, 'remarks' => $remarks, 'points' => $points ]);
                });
           
        });
    }
}
