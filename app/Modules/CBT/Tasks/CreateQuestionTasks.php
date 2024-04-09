<?php

namespace App\Modules\CBT\Tasks;

use App\Contracts\BaseTasks;
use App\Modules\CBT\Models\QuestionBankModel;
use App\Modules\CBT\Models\QuestionModel;
use App\Modules\CBT\Models\SectionModel;
use App\Modules\CBT\Models\TopicModel;
use App\Modules\SchoolManager\Models\ClassModel;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class CreateQuestionTasks extends BaseTasks{

    public function createQuestion()
    {
        
        $questionData = json_encode( $this->formatQuestionJSON( json_decode($this->item['question'], true) ) );
        
        $options = $this->formatQuestionOptions( $this->item['options'], $this->item['correctAnswer'] );

        // if( ! in_array($this->item['correctAnswer'], $this->item['options']) ){
        //     throw ValidationException::withMessages(['message' => 'The correct answer provided is not part of the options list provided']);
        // }

        $questionBank = QuestionBankModel::firstWhere('uuid', $this->item['questionBankId'] ?? null );
        $topicId = TopicModel::firstWhere('uuid', $this->item['topicId'] ?? null )?->uuid;
        $sectionId = SectionModel::firstWhere('uuid', $this->item['sectionId'] ?? null )?->uuid;

        if( $questionBank ){

            $question = $this->saveQuestionToDatabase( $questionData, $options, $questionBank->uuid, $questionBank->subject_id, $topicId, $sectionId );
            

            if( $questionBank->classes ){
                
                foreach ( json_decode($questionBank->classes, true ) as $class) {

                    $classId = ClassModel::firstWhere('class_code', $class)->uuid;
    
                    $question->classes()->syncWithoutDetaching( [ $classId => [ 'uuid' => Str::ulid() ] ] );
                }
            }
            

        }else{

            $question = $this->saveQuestionToDatabase( $questionData, $options );
        }


        return new static( [ ...$this->item, 'questionId'=> $question->uuid ] );
    }
    
    protected function saveQuestionToDatabase($questionData, $options, $questionBankId = null, $subjectId = null, $topicId = null, $sectionId = null)
    {
        return QuestionModel::create([
                    'uuid'              => Str::ulid(),
                    'assessment_id'     => $this->item['assessment']->uuid,
                    'user_id'           => request()->user()->uuid,
                    'question'          => $questionData,
                    'options'           => $options['options'],
                    'correct_answer'    => $options['correctAnswer'],
                    'question_score'    => $this->item['questionScore'] ?? 1,
                    'question_type'     => $this->item['questionType'],
                    'question_bank_id'  => $questionBankId,
                    'subject_id'        => $subjectId,
                    // 'class_id'          => ClassModel::firstWhere('class_code', $classId)?->id,
                    'topic_id'          => $topicId,
                    'section_id'        => $sectionId,
                ]);
    }

    public function formatQuestionJSON( array $questionData)
    {
        $questionContent = [];

        if( isset( $questionData['type']) ){

            foreach( $questionData['content']['content'] as $content){

                if( $content['type'] === 'image' && $content['attrs']['alt'] === 'ques_image' ){

                    $img = Image::make($content['attrs']['src'])->resize(500, null, function ($constraint) {
                        $constraint->aspectRatio();
                    });

                    $imgName = 'question_pics/'. Str::random(). '.jpg';

                    $img->save(public_path($imgName), 90);

                    $content['attrs']['src'] = "/$imgName";
                    $content['attrs']['alt'] = 'question_image';
                }

                if( $content['type'] === 'table'){
                    
                    foreach ($content['content'] as $key_one => $value) {
                       
                        foreach ($value['content'] as $key_two =>  $tableContent ) {
                            
                            foreach ($tableContent['content'] as $key_three => $contents) {
                               
                                if( $contents['type'] === 'image' && $contents['attrs']['alt'] === 'ques_image' ){

                                    $img = Image::make($contents['attrs']['src'])->resize(500, null, function ($constraint) {
                                        $constraint->aspectRatio();
                                    });
                
                                    $imgName = 'question_pics/'. Str::random(). '.jpg';
                
                                    $img->save(public_path($imgName), 90);
                
                                    $content['content'][$key_one]['content'][$key_two]['content'][$key_three]['attrs']['src'] = "/$imgName";
                                    $content['content'][$key_one]['content'][$key_two]['content'][$key_three]['attrs']['alt'] = 'question_image';
                                }
                            }
                        }
                    }
                }

                $questionContent[] = $content;
            }
            
        }

        $questionData['content']['content'] = $questionContent;

        return $questionData;
    }

    public function formatQuestionOptions( array $questionOptions, $correctAnswer )
    {
        
        $options = [];

        foreach( $questionOptions as $option ){

            if( $option['type'] === 'image' && $option['alt'] === 'ques_image'  ){

                $img = Image::make($option['content'])->resize(300, null, function ($constraint) {
                    $constraint->aspectRatio();
                });

                $imgName = 'question_pics/'. Str::random(). '.jpg';

                $img->save(public_path($imgName), 90);

                if( $correctAnswer ===  $option['content'] ){

                    $correctAnswer = "/$imgName";
                }

                $option['content'] = "/$imgName";
                $option['alt'] = 'question_image';

            }

            $options[] = $option;
        }

        return [
            'options'       => $options,
            'correctAnswer' => $correctAnswer
        ];
    }

    public function getImageContent(&$content){

        if( ! isset($content['content']) && $content['type'] === 'image' ){

           $content['attrs']['src'] = 'hello';

           return $content;
        }


        foreach ($content['content'] as &$tableContent) {
                    
            return $this->getImageContent($tableContent);

        }
          
    }
}