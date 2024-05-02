<?php

namespace App\Modules\CBT\Features;

use App\Contracts\BaseTasks;
use App\Contracts\FeatureContract;
use App\Modules\CBT\Models\AssessmentModel;
use App\Modules\CBT\Resources\GetAssessmentQuestionsFormatter;
use App\Modules\CBT\Tasks\GetAssessmentQuestionsTasks;
use Illuminate\Support\Facades\Cache;

class GetAssessmentQuestionsFeature extends FeatureContract {

    public function __construct(protected AssessmentModel $assessment){
        $this->tasks = new GetAssessmentQuestionsTasks();
    }
    
    public function handle(BaseTasks $task, array $args = [])
    {
        try {

            $cachedQuestions = $task->start([ ...$args, 'assessment' => $this->assessment ] )->getCachedQuestions();

            if( $cachedQuestions->get() ){

                return $task::formatResponse( $cachedQuestions, formatter: GetAssessmentQuestionsFormatter::class );
            }

            $builder =  $task->start([ ...$args, 'assessment' => $this->assessment ] )->getAssessmentQuestions();

            Cache::put("{$args['user']->uuid}_{$args['subjectId']}_{$this->assessment->assessment_code}_{$args['user']->class_id}", $builder->get() );
            
            return $task::formatResponse( $builder, formatter: GetAssessmentQuestionsFormatter::class );


        } catch (\Throwable $th) {

            throw $th;
        }
    }
}