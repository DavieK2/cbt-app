<?php

namespace App\Modules\CBT\Tasks;

use App\Contracts\BaseTasks;
use App\Modules\CBT\Models\AssessmentModel;
use App\Modules\CBT\Models\QuestionBankModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QuestionBankTasks extends BaseTasks {


    public function createQuestionBank()
    {
        $question_bank = QuestionBankModel::create([
            'uuid'              =>  Str::ulid(),
            'title'             =>  $this->item['title'] ?? null,
            'assessment_id'     =>  AssessmentModel::firstWhere('uuid', $this->item['assessmentId'])->uuid,
            'subject_id'        =>  $this->item['subjectId'] ?? null,
            'user_id'           =>  request()->user()->uuid
        ]);

        return new static( [ ...$this->item, 'questionBankId' => $question_bank->uuid ]);
    }

    public function addClassesToQuestionBank()
    {
        if( isset( $this->item['classes'] ) ){

            QuestionBankModel::firstWhere('uuid', $this->item['questionBankId'])->update(['classes' => json_encode( $this->item['classes'] )]);
        }

        return new static( $this->item );

    }

    public function getQuestions()
    {
        $questions = DB::table('assessment_questions')
                        ->join('questions', 'questions.uuid', '=', 'assessment_questions.question_id')
                        ->join('assessments', 'assessments.uuid', '=', 'questions.assessment_id')
                        ->select('questions.*');


        if( isset( $this->item['subjectId'] ) ){

            $questions = $questions->where( fn($query) => $query->where('questions.subject_id', $this->item['subjectId']) );

        }

        // if( isset( $this->item['classId'] ) ){

        //     $classId = ClassModel::firstWhere( 'class_code', $this->item['classId'] )->uuid;
        //     $questions = $questions->where( fn($query) => $query->where('questions.class_id', $classId ) );

        // }

        // if( isset( $this->item['assessmentId']) ){
            $questions = $questions->where( fn($query) => $query->where('assessment_questions.assessment_id', AssessmentModel::first()?->uuid ) );
        // }
        
        if( isset( $questions ) ){


            $questions = $questions->leftJoin('topics', 'topics.uuid', '=', 'questions.topic_id')
                                    ->leftJoin('sections', 'sections.uuid', '=', 'questions.section_id')
                                    ->selectRaw("questions.*, sections.uuid as sectionId, sections.title as sectionTitle, topics.uuid as topicId")
                                    // ->whereNull('questions.assessmentId')
                                    ->orderBy('questions.created_at');
        }

        return new static([ ...$this->item, 'query' => $questions ]);
    }
    
}