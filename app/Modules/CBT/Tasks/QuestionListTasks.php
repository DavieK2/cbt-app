<?php

namespace App\Modules\CBT\Tasks;

use App\Contracts\BaseTasks;
use App\Modules\CBT\Models\AssessmentModel;
use App\Modules\CBT\Models\QuestionBankModel;
use App\Modules\CBT\Models\QuestionModel;
use App\Modules\SchoolManager\Models\ClassModel;
use Illuminate\Support\Facades\DB;

class QuestionListTasks extends BaseTasks{

    public function getQuestions()
    {
        if( isset( $this->item['questionBankId'] ) ){

            $question_bank = QuestionBankModel::firstWhere( 'uuid', $this->item['questionBankId'] );
            $questions = QuestionModel::where( 'questions.question_bank_id', $question_bank->uuid )
                                        ->leftJoin('assessment_questions', 'questions.uuid', '=', 'assessment_questions.question_id')
                                        ->select('questions.*', 'assessment_questions.assessment_id as assessmentId');
        }

        if( isset( $this->item['assessmentId'] ) && ! isset( $this->item['questionBankId'] )){

            $assessment = AssessmentModel::firstWhere('uuid', $this->item['assessmentId'] );
            $questions = QuestionModel::where( 'questions.assessment_id', $assessment->uuid )
                                        ->leftJoin('assessment_questions', 'questions.uuid', '=', 'assessment_questions.question_id')
                                        ->select('questions.*', 'assessment_questions.assessment_id as assessmentId');
        }
        
        if( isset( $questions ) ){

            $questions = $questions->fromSub($questions, 'questions')
                                    ->leftJoin('topics', 'topics.uuid', '=', 'questions.topic_id')
                                    ->leftJoin('sections', 'sections.uuid', '=', 'questions.section_id')
                                    ->selectRaw('questions.*, sections.uuid as sectionId, sections.title as sectionTitle, topics.uuid as topicId, IF(questions.assessmentId = questions.assessment_id, "Assigned", "Not Assigned") as isAssigned')
                                    // ->whereNull('questions.assessmentId')
                                    ->orderBy('questions.created_at');
        }

        return new static( [ ...$this->item, 'query' => $questions ?? [] ] );
    }

    public function getAssignedQuestions()
    {   
            $questions = DB::table('assessment_questions')
                            ->join('questions', 'questions.uuid', '=', 'assessment_questions.question_id')
                            ->leftJoin('topics', 'topics.uuid', '=', 'questions.topic_id')
                            ->leftJoin('sections', 'sections.uuid', '=', 'assessment_questions.section_id')
                            ->where( function($query) {
                                
                                $query->where('assessment_questions.assessment_id', $this->item['assessmentId']);

                                if( isset( $this->item['subjectId'] ) ) $query->where('assessment_questions.subject_id', $this->item['subjectId']);
                                if( isset( $this->item['classId'] ) ) $query->where('assessment_questions.class_id', ClassModel::firstWhere('class_code', $this->item['classId'])->uuid);     

                            })
                            ->orderBy('questions.created_at')
                            ->select('questions.*', 'sections.uuid as sectionId', 'sections.title as sectionTitle', 'topics.uuid as topicId');


        return new static( [ ...$this->item, 'query' => $questions ?? [] ] );
    }
    
}