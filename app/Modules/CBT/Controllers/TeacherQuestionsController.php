<?php

namespace App\Modules\CBT\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\CBT\Models\QuestionBankModel;
use App\Modules\CBT\Models\TopicModel;
use App\Modules\CBT\Requests\GetQuestionTopicsRequest;
use App\Modules\SchoolManager\Models\ClassModel;
use Illuminate\Support\Facades\DB;

class TeacherQuestionsController extends Controller
{
    public function getQuestionTopics(GetQuestionTopicsRequest $request)
    {
        $data = $request->validated();

        $topics = [];

        $question_bank = QuestionBankModel::firstWhere('uuid', $data['questionBankId']);

        $question_classes = $question_bank->classes ? json_decode( $question_bank->classes, true ) : [];

        foreach ($question_classes as $question_class) {
            
            $class = ClassModel::firstWhere('class_code', $question_class);

            $class_topics = DB::table('class_topics')
                                ->where('class_id', $class->id)
                                ->where('subject_id', $question_bank->subject_id)
                                ->get()
                                ->pluck('topic_id')
                                ->toArray();

            if( empty($topics) ){

                $topics = [...$topics, ...$class_topics ];

            }else{

                $topics = array_intersect($topics, $class_topics);
            }
        }

       $topics = TopicModel::whereIn('id', $topics)->select('uuid as topicId', 'title as topicTitle')->get()->toArray();

       return response()->json(['data' => $topics]);
    }
}
