<?php

namespace App\Modules\CBT\Tasks;

use App\Contracts\BaseTasks;
use App\Modules\CBT\Models\SectionModel;
use App\Modules\SchoolManager\Models\SubjectModel;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class GetAssessmentQuestionsTasks extends BaseTasks {

    public function getAssessmentQuestions()
    {
        if( $this->item['assessment']->is_standalone ){
            
            $questions = $this->item['assessment']->questions()->select('questions.uuid as questionId', 'questions.question as prompt', 'questions.options as choices');

        }else{
            
            $subjectId = SubjectModel::firstWhere('subject_code', $this->item['subjectId'])->uuid;

            $sections = SectionModel::where('assessment_id', $this->item['assessment']->uuid)->get();

            $examQuestions = [];

            foreach ($sections as $key => $section) {
                
                $data = DB::table('assessment_questions')
                        ->inRandomOrder()
                        ->join('questions', 'questions.uuid', '=', 'assessment_questions.question_id')
                        ->leftJoin('sections', 'sections.uuid', '=', 'assessment_questions.section_id')
                        ->where( fn($query) => $query->where( 'assessment_questions.subject_id', $subjectId )->where( 'assessment_questions.class_id', $this->item['user']->class_id )->where( 'assessment_questions.assessment_id', $this->item['assessment']->uuid )->where('assessment_questions.section_id', $section->uuid) )
                        ->select('questions.uuid as questionId', 'questions.question as prompt', 'questions.options as choices', 'assessment_questions.section_id', 'sections.title', 'questions.question_score as score')
                        ->limit($section->total_questions)
                        ->get()
                        ->toArray();
               
               
                if( count($examQuestions) === 0 ){


                   $examQuestions = $data;

                }else{

                    
                    $examQuestions = [...$examQuestions, ...$data];

  
                }

    
            }

        }

        $questions = collect($examQuestions);

        return new static(  $questions );
    }

    public function getCachedQuestions()
    {
        $questions = Cache::get("{$this->item['user']->uuid}_{$this->item['subjectId']}_{$this->item['assessment']->assessment_code}_{$this->item['user']->class_id}");

        return new static(  $questions );

    }
    
}