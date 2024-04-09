<?php

namespace App\Modules\CBT\Features;

use App\Contracts\BaseTasks;
use App\Contracts\FeatureContract;
use App\Modules\CBT\Tasks\AssessmentListTasks;

class GetAssignedAssessmentsFeature extends FeatureContract {

    public function __construct(){
        $this->tasks = new AssessmentListTasks();
    }
    
    public function handle(BaseTasks $task, array $args = [])
    {
        try {
            
            $builder = $task->start($args)->getAssignedAssessments();

            return $task::formatResponse( $builder );
            
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
}