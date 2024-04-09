<?php

namespace App\Modules\CBT\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\CBT\Features\AssignQuestionToAssessmentFeature;
use App\Modules\CBT\Features\CreateQuestionFeature;
use App\Modules\CBT\Features\ImportQuestionsFeature;
use App\Modules\CBT\Features\QuestionBankFeature;
use App\Modules\CBT\Features\QuestionListFeature;
use App\Modules\CBT\Features\UnAssignQuestionFromAssessmentFeature;
use App\Modules\CBT\Features\UpdateQuestionFeature;
use App\Modules\CBT\Models\AssessmentModel;
use App\Modules\CBT\Models\QuestionBankModel;
use App\Modules\CBT\Models\QuestionModel;
use App\Modules\CBT\Models\SectionModel;
use App\Modules\CBT\Requests\AssignQuestionToAssessmentRequest;
use App\Modules\CBT\Requests\CreateAssessmentQuestionSectionRequest;
use App\Modules\CBT\Requests\CreateQuestionRequest;
use App\Modules\CBT\Requests\GetAssessmentQuestionSectionRequest;
use App\Modules\CBT\Requests\ImportQuestionsRequest;
use App\Modules\CBT\Requests\MassAssignQuestionsRequest;
use App\Modules\CBT\Requests\MassAssignUnQuestionsRequest;
use App\Modules\CBT\Requests\QuestionBankRequest;
use App\Modules\CBT\Requests\QuestionListRequest;
use App\Modules\CBT\Requests\UnAssignQuestionFromAssessmentRequest;
use App\Modules\CBT\Requests\UpdateQuestionRequest;
use App\Modules\SchoolManager\Models\ClassModel;

class QuestionController extends Controller
{
    public function create(AssessmentModel $assessment, CreateQuestionRequest $request)
    {
        return $this->serve( new CreateQuestionFeature( $assessment ), $request->validated() );
    }

    public function update(QuestionModel $question, UpdateQuestionRequest $request)
    {
        return $this->serve( new UpdateQuestionFeature( $question ), $request->validated() );
    }

    public function import(ImportQuestionsRequest $request)
    {
        return $this->serve( new ImportQuestionsFeature(), $request->validated() );
    }

    public function getQuestions(QuestionListRequest $request)
    {
        return $this->serve( new QuestionListFeature(), $request->validated() );
    }

    public function assignQuestionToAssessment( AssessmentModel $assessment,  AssignQuestionToAssessmentRequest $request )
    {
        return $this->serve( new AssignQuestionToAssessmentFeature( $assessment ), $request->validated() );
    }

    public function unAssignQuestionFromAssessment( AssessmentModel $assessment,  UnAssignQuestionFromAssessmentRequest $request )
    {
        return $this->serve( new UnAssignQuestionFromAssessmentFeature( $assessment ), $request->validated() );
    }

    public function getQuestionBank(QuestionBankRequest $request)
    {
        return $this->serve( new QuestionBankFeature(), $request->validated() );
    }

    public function getQuestionTypes()
    {
        return response()->json([
            'data' => QuestionModel::QUESTION_TYPES
        ]);
    }

    public function massAssignQuestions(AssessmentModel $assessment, MassAssignQuestionsRequest $request)
    {
        $data = $request->validated();

        foreach ( $data['questions'] as $question ) {

            $assessment->assignQuestion( $question, $data['subjectId'] ?? null, $data['classId'] ?? null, $data['sectionId'] ?? null );
            
        }

        return response()->json([
            'message' => 'Questions Successfully Assigned'
        ]);

    }

    public function massUnassignQuestions(AssessmentModel $assessment, MassAssignUnQuestionsRequest $request)
    {
        $data = $request->validated();

      
        foreach ( $data['questions'] as $question ) {
                
            $assessment->unAssignQuestion( $question, $data['classId'], $data['subjectId'] );
            
        }

        return response()->json([
            'message' => 'Questions Successfully Unassigned'
        ]);

    }

}
