<?php

namespace App\Modules\CBT\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\CBT\Features\AddClassesToQuestionBankFeature;
use App\Modules\CBT\Features\CreateQuestionBankFeature;
use App\Modules\CBT\Models\AssessmentModel;
use App\Modules\CBT\Models\QuestionBankModel;
use App\Modules\CBT\Models\SectionModel;
use App\Modules\CBT\Requests\AddClassesToQuestionBankRequest;
use App\Modules\CBT\Requests\AddSectionsToQuestionBankRequest;
use App\Modules\CBT\Requests\CreateQuestionBankRequest;
use App\Modules\CBT\Requests\CreateSectionRequest;
use App\Modules\CBT\Requests\DeleteSectionRequest;
use App\Modules\CBT\Requests\GetQuestionBankRequest;
use App\Modules\CBT\Requests\UpdateSectionRequest;
use App\Modules\SchoolManager\Models\ClassModel;
use App\Modules\SchoolManager\Models\SubjectModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QuestionBankController extends Controller
{
    public function getQuestionBanks(GetQuestionBankRequest $request)
    {
        $data = $request->validated();

        $question_banks = QuestionBankModel::where( fn($query) => $query->where('question_banks.user_id', request()->user()->uuid)
                                                                    ->where('question_banks.assessment_id', AssessmentModel::firstWhere('uuid', $data['assessmentId'])->id)
                                                                    ->where('question_banks.subject_id', $data['subjectId']) 
                                                                    )
                                        ->join('subjects', 'subjects.id', '=', 'question_banks.subject_id')
                                        ->select('question_banks.*', 'subjects.subject_name')
                                        ->get();

                                        
        $question_banks = $question_banks->map(function($question_bank) use($question_banks){

            return [
                'questionBankId' => $question_bank->uuid,
                'subject'        => $question_bank->subject_name,
                'totalQuestions' => $question_bank->questions()->count(),
                'totalSections'  => count( json_decode($question_bank->section_ids, true) ?? [] ),
                'classes'        => collect(json_decode($question_bank->classes, true))->flatMap( fn($class) => [ ClassModel::firstWhere('class_code', $class)->class_name ] )->toArray(),
            ];
        });
        
        return response()->json([
            'data' => $question_banks
        ]);
    }

    public function getAssessmentSubjects(AssessmentModel $assessment)
    {

        $assessment_subjects = DB::table('assessment_subjects')
                                ->where('assessment_id', $assessment->uuid)
                                ->distinct()
                                ->pluck('subject_id');

        $user_subjects = DB::table('user_subjects')
                            ->where('user_id', request()->user()->uuid)
                            ->pluck('subject_id');

        $subjects = $user_subjects->intersect($assessment_subjects)->toArray();

        $subjects = SubjectModel::whereIn('uuid', $subjects)->select('subject_name as subjectName', 'uuid as subjectId')->get();

        return response()->json([
             'data' => $subjects
        ]);
    }

    public function getAssessmentClasses(QuestionBankModel $question_bank)
    {
        $user_classes = DB::table('user_class_subjects')
                            ->where( fn( $query ) => $query->where('user_id', request()->user()->uuid)->where('subject_id', $question_bank->subject_id ))
                            ->pluck('class_id');

        $assessment_classes = DB::table('assessment_subjects')
                                ->where( fn( $query ) => $query->where('assessment_id', $question_bank->assessment_id)->where('subject_id', $question_bank->subject_id ))
                                ->pluck('class_id');
        
        $classes = $user_classes->intersect($assessment_classes);

        $classes = ClassModel::whereIn('uuid', $classes)->select('class_name', 'class_code')->get();

        return response()->json([
            'data' => $classes
        ]);
    }

    public function create(CreateQuestionBankRequest $request)
    {
        return $this->serve( new CreateQuestionBankFeature, $request->validated() );
        
    }

    public function addClasses(AddClassesToQuestionBankRequest $request)
    {
        return $this->serve( new AddClassesToQuestionBankFeature, $request->validated() );
    }

    public function getClasses(QuestionBankModel $question_bank)
    {
        $classes = collect( json_decode( $question_bank->classes, true) )->map( function($class){

            $class = ClassModel::firstWhere('class_code', $class);

            return [
                'class_code' => $class->class_code,
                'class_name' => $class->class_name,
            ];
        
        } );

        return response()->json([
            'data' => $classes
        ]);
    }

    public function createSection(CreateSectionRequest $request)
    {
        $data = $request->validated();

        $question_bank = QuestionBankModel::firstWhere('uuid', $data['questionBankId']);

        $section = SectionModel::create([
            'uuid' => Str::ulid(),
            'section_code' => Str::random(5),
            'question_type' => $data['questionType'],
            'question_bank_id' => $question_bank->uuid,
            'title' => $data['title'],
            'description' => $data['description']
        ]);

        $question_bank_sections = [ ...json_decode($question_bank->section_ids, true) ?? [], $section->uuid ];

        $question_bank->update(['section_ids' => json_encode( $question_bank_sections )]);

        return response()->json([
            'message' => 'Section Created Successfully'
        ]);
    }

    public function addSections(AddSectionsToQuestionBankRequest $request)
    {
        $data = $request->validated();

        $question_bank = QuestionBankModel::firstWhere('uuid', $data['questionBankId']);
        $sections = SectionModel::where('question_bank_id', $question_bank->uuid)->get()->pluck('uuid')->toArray();

        $question_bank->update(['section_ids' => json_encode( $sections )]);

        return response()->json([
            'message' => 'Sections Successfully added to Question Bank'
        ]);
    }

    public function getSections(QuestionBankModel $question_bank)
    {
        $sections = SectionModel::where('question_bank_id', $question_bank->uuid)
                                ->select('uuid as sectionId', 'title  as sectionTitle', 'description as sectionDescription', 'question_type as questionType')
                                ->get();

        return response()->json([
            'data' => $sections
        ]);
    }

    public function updateSection(SectionModel $section, UpdateSectionRequest $request)
    {
        $data = $request->validated();

        $section->update(['title' => $data['sectionTitle'], 'description' => $data['sectionDescription'], 'question_type' => $data['questionType'] ]);
    }

    public function deleteSection(SectionModel $section, DeleteSectionRequest $request)
    {
        $data = $request->validated();

        $question_bank = QuestionBankModel::firstWhere('uuid', $data['questionBankId']);

        $question_bank_sections = json_decode($question_bank->section_ids, true);

        $question_bank_sections = array_flip($question_bank_sections);

        unset($question_bank_sections[$section->uuid]);

        $question_bank_sections = array_flip($question_bank_sections);

        $question_bank->update(['section_ids' => json_encode( $question_bank_sections )]);

        $section->delete();

        return response()->json([
            'message' => 'Sections Successfully deleted'
        ]);
    }
}
