<?php

namespace App\Console\Commands;

use App\Modules\CBT\Models\AssessmentModel;
use App\Modules\CBT\Models\QuestionBankModel;
use App\Modules\CBT\Models\QuestionModel;
use App\Modules\CBT\Models\SectionModel;
use App\Modules\SchoolManager\Models\ClassModel;
use App\Modules\SchoolManager\Models\StudentProfileModel;
use App\Modules\SchoolManager\Models\SubjectModel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MassAssignCourseToStudentCommand extends Command
{
    protected $signature = 'update:level';
    protected $description = 'Command description';

    public function handle()
    {
    //    $courses = SubjectModel::latest()->limit(7)->get()->pluck('uuid')->toArray();
            // $assessment = AssessmentModel::first();

            // ClassModel::get()->map( function($class)use($assessment){

            //     DB::table('assessment_classes')->insert(['uuid' => Str::ulid(), 'assessment_id' => $assessment->uuid, 'class_id' => $class->uuid ]);
            // });
                                                                                                                                             
                    
            // $class = ClassModel::get()->map( function($class)use($assessment){


            //     return SubjectModel::get()->map( function($sub) use($assessment, $class){
                    
            //         return [
            //             'uuid'                  => Str::ulid() ,
            //             'assessment_id'         => $assessment->uuid, 
            //             'subject_id'            => $sub->uuid, 
            //             'is_published'          => false, 
            //             'class_id'              => $class->uuid,
            //             'assessment_duration'   => (30 * 60),
            //             'start_date'            => now()->toDateTimeString(),
            //             'end_date'              => now()->addDay()->toDateTimeString(),
            //         ];
            //     });

            
                
            // });

            // $class->each( function($cls) {
                
            //     DB::table('assessment_subjects')->insert($cls->toArray() );

            // } );

            // DB::table('assessment_results')->update(['has_started' => 0, 'has_submitted' => 0, 'time_remaining' => (30 * 60) ]);


            $courses = (['GSS101', 'GSS211', 'GSS101', 'GSS111', 'GSS121', 'GSS131', 'GST111']);

            $assessment = AssessmentModel::first();
            $subjects = SubjectModel::whereIn('subject_code', $courses)->get()->pluck('uuid');
            $classes = ClassModel::get()->pluck('uuid');
        
        
            $questions = QuestionModel::where('assessment_id', $assessment->uuid)->whereIn('class_id', $classes);
        
        
            
            $subjects->each( function($course) use($questions, $assessment,$classes){
                
        
                $sectionCA = SectionModel::create(['uuid' => Str::ulid(), 'title' => 'CA', 'description' => 'This section carries 20 Marks', 'assessment_id' => $assessment->uuid, 'total_score' => 20, 'total_questions' => 10, 'section_code' => Str::random(6), 'question_type' => 'objectives' ]);
                $sectionExam = SectionModel::create(['uuid' => Str::ulid(), 'title' => 'Exam', 'description' => 'This section carries 70 Marks', 'assessment_id' => $assessment->uuid, 'total_score' => 70, 'total_questions' => 28, 'section_code' => Str::random(6), 'question_type' => 'objectives' ]);
        
            
                
                $classes->each( function( $class_id ) use($questions, $sectionCA, $course, $assessment){
        
                    $caQuestions = QuestionBankModel::inRandomOrder()->where('question_banks.assessment_id', $assessment->uuid)->where('question_banks.subject_id', $course)
                    ->join('questions', 'questions.question_bank_id', 'question_banks.uuid')
                    ->select('questions.*')->limit(130)->get()->map( function($ca) use( $sectionCA, $course, $assessment, $class_id){
        
                        return [
                            'section_id' => $sectionCA->uuid,
                            'question_id' => $ca->uuid,
                            'subject_id' => $course, 
                            'class_id' => $class_id, 
                            'uuid' => Str::ulid(), 
                            'assessment_id' => $assessment->uuid 
                        ];
        
        
                    } );
        
                    // dd( $caQuestions);
        
                    DB::table('assessment_questions')->insert($caQuestions->toArray());
                });
        
                $classes->map( function( $class_id ) use($questions, $sectionExam, $course, $assessment){
        
                    $caQuestions = $questions->limit(130)->get()->map( function($ca) use( $sectionExam, $course, $assessment, $class_id){
        
                        return [
                            'section_id' => $sectionExam->uuid,
                            'question_id' => $ca->uuid,
                            'subject_id' => $course, 
                            'class_id' => $class_id, 
                            'uuid' => Str::ulid(), 
                            'assessment_id' => $assessment->uuid 
                        ];
                    } );
        
                    DB::table('assessment_questions')->insert($caQuestions->toArray());
                });
        
            } );
    }
}
