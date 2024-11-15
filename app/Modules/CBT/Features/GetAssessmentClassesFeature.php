<?php

namespace App\Modules\CBT\Features;

use App\Contracts\BaseTasks;
use App\Contracts\FeatureContract;
use App\Modules\CBT\Models\AssessmentModel;
use App\Modules\CBT\Tasks\GetAssessmentClassesTasks;
use App\Modules\SchoolManager\Resources\ClassListCollection;

class GetAssessmentClassesFeature extends FeatureContract {

    public function __construct(protected AssessmentModel $assessment){
        $this->tasks = new GetAssessmentClassesTasks();
    }
    
    public function handle(BaseTasks $task, array $args = [])
    {
       try {

            $builder =  $task->start( $this->assessment )->getAssessmentClasses()->all();

            return $task::formatResponse( $builder , formatter: ClassListCollection::class );

       } catch (\Throwable $th) {
        
            throw $th;
       }
    }
}