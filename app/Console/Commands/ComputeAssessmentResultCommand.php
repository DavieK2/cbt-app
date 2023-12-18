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
        $assessment = AssessmentModel::find(8);

        DB::table('assessment_sessions')
            ->join('student_profiles', 'student_profiles.id', '=', 'assessment_sessions.student_profile_id')
            ->where('assessment_sessions.assessment_id', $assessment->id)
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
    
                    $agg_score = floor( ( $student_score / $total_marks ) * ( $max_score ) );
    
                    DB::table('assessment_results')->where('student_profile_id', $studentId)->where('assessment_id', $assessment->id)->where('subject_id', $subjectId)->limit(1)->update(['total_score' => $agg_score ]);
                });
        });
    }
}
