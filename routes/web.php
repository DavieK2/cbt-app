<?php

use App\Models\User;
use App\Modules\CBT\Controllers\AssessmentResultController;
use App\Modules\CBT\Controllers\ExamController;
use App\Modules\CBT\Jobs\ImportStudentResultsJob;
use App\Modules\CBT\Models\AssessmentModel;
use App\Modules\CBT\Models\QuestionBankModel;
use App\Modules\CBT\Models\QuestionModel;
use App\Modules\Excel\Export;
use App\Modules\SchoolManager\Models\ClassModel;
use App\Modules\SchoolManager\Models\StudentProfileModel;
use App\Modules\SchoolManager\Models\SubjectModel;
use App\Modules\UserManager\Constants\UserManagerConstants;
use App\Services\CSVWriter;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Laravel\Sanctum\PersonalAccessToken;
use Maatwebsite\Excel\Facades\Excel;
use PragmaRX\Google2FAQRCode\Google2FA;
use PragmaRX\Google2FAQRCode\QRCode\Bacon;
use Tiptap\Editor;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

require __DIR__ . '/auth.php';



Route::get('/', function(){
  
    $assessment = AssessmentModel::latest()->first();

    return redirect("/cbt/$assessment->assessment_code"); 
    
});

Route::middleware(['auth'])->group(function(){

    Route::get('/students/check-in/{assessment:assessment_code}', fn(AssessmentModel $assessment) => Inertia::render('CBT/CheckIn/Index', ['assessmentId' => $assessment->uuid]) );

    Route::get('/dashboard', fn() => Inertia::render('Dashboard/Index') );

    //Settings
    Route::get('/settings', fn() => Inertia::render('CBT/Settings/Index') );
    
    //Assessments
    Route::get('/assessments', fn() => Inertia::render('CBT/Assessment/Index') );

    Route::get('/assessments/standalone', fn() => Inertia::render('CBT/Assessment/standalone/StandaloneAssessment') );
    Route::get('/assessments/quiz/question-banks/{assessment:uuid}', fn(AssessmentModel $assessment) => Inertia::render('CBT/Assessment/standalone/Index', ['assessmentId' => $assessment->uuid, 'assessmentTitle' => $assessment->title ]) );
    Route::get('/assessments/quiz/question-bank/create/{assessment:uuid}', fn(AssessmentModel $assessment) => Inertia::render('CBT/Assessment/standalone/question_bank/Create', ['assessmentId' => $assessment->uuid, 'assessmentTitle' => $assessment->title ]) );

    Route::get('/assessments/termly', fn() => Inertia::render('CBT/Assessment/termly/TermlyAssessment') );
    Route::get('/assessments/termly/classes/{assessment:uuid}', fn(AssessmentModel $assessment) => Inertia::render('CBT/Assessment/termly/TermlyAssessmentClasses', ['assessmentId' => $assessment->uuid, 'title' => $assessment->title ]) );
    Route::get('/assessments/termly/schedule/{assessment:uuid}', fn(AssessmentModel $assessment) => Inertia::render('CBT/Assessment/termly/TermlyAssessmentSchedule', ['assessmentId' => $assessment->uuid, 'title' => $assessment->title ]) );
    Route::get('/assessments/termly/view/{assessment:uuid}', fn(AssessmentModel $assessment) => Inertia::render('CBT/Assessment/termly/View', ['assessmentId' => $assessment->uuid, 'assessmentTitle' => $assessment->title ]) );
    Route::get('/assessments/termly/manage/{assessment:uuid}', fn(AssessmentModel $assessment) => Inertia::render('CBT/Assessment/termly/Manage', ['assessmentId' => $assessment->uuid, 'assessmentTitle' => $assessment->title ]) );
    
    Route::get('/assessments/termly/question_bank/create/{assessment:uuid}', fn(AssessmentModel $assessment) => Inertia::render('CBT/Assessment/termly/question_bank/Create', ['assessmentId' => $assessment->uuid, 'assessmentTitle' => $assessment->title ]) );
    
    Route::get('/assessments/termly/question_bank/edit/{question_bank:uuid}', function(QuestionBankModel $question_bank){
        $assessment = AssessmentModel::find( $question_bank->assessment_id );
        return Inertia::render('CBT/Assessment/termly/question_bank/Edit', ['assessmentId' => $assessment->uuid, 'assessmentTitle' => $assessment->title, 'subjectId' => $question_bank->subject_id, 'questionBankId' => $question_bank->uuid  ]);
    } );

    
    Route::get('/assessments/question_bank/sections/{question_bank:uuid}', function(QuestionBankModel $question_bank) {
        $assessment = AssessmentModel::find( $question_bank->assessment_id );
        return Inertia::render('CBT/Assessment/shared/question_bank/CreateSection', ['assessmentId' => $assessment->uuid, 'assessmentTitle' => $assessment->title, 'questionBankId' => $question_bank->uuid]);
    } );


    Route::get('/assessments/results/s/{assessment:uuid}', function( AssessmentModel $assessment ) {
        if( ! $assessment->is_standalone ) abort(404);
        return Inertia::render('CBT/Assessment/standalone/results/Index', [ 'assessmentId' => $assessment->uuid, 'assessmentTitle' => $assessment->title ] );
    } );

    Route::get('/assessments/results/t/{assessment:uuid}', function( AssessmentModel $assessment ) {
        if( $assessment->is_standalone ) abort(404);
        return Inertia::render('CBT/Assessment/termly/results/Index', [ 'assessmentId' => $assessment->uuid, 'assessmentTitle' => $assessment->title ] );
    } );

    Route::get('/assessments/student/result/{student}/{assessment:uuid}', function($student, AssessmentModel $assessment ) {
        if( $assessment->is_standalone ) abort(404);
        return Inertia::render('CBT/Assessment/termly/results/Result', [ 'assessmentId' => $assessment->uuid, 'assessmentTitle' => $assessment->title, 'studentId' => $student ] );
    } );

    //Assessment Types
    Route::get('/assessment-types', fn() => Inertia::render('CBT/Assessment/assessment_types/AssessmentTypesView') );

    //Questions
    Route::get('/questions/create/s/{assessment:uuid}', function(AssessmentModel $assessment){

        if( ! $assessment->is_standalone ) abort(404);

        return Inertia::render('CBT/Questions/Create', ['assessmentId' => $assessment->uuid, 'title' => $assessment->title ] );
    } );

    Route::get('/questions/create/t/{assessment:uuid}/{subject}/{class}', fn(AssessmentModel $assessment, $subject, $class) => Inertia::render('CBT/Questions/Create', ['assessmentId' => $assessment->uuid, 'subjectId' => $subject, 'classId' => $class, 'assessmentTitle' => $assessment->title, 'subjectTitle' => SubjectModel::find($subject)->subject_name, 'questionBankClasses' => ClassModel::firstWhere('class_code', $class)->class_name  ]) );
    
    Route::get('/assessment/questions/question-bank/{question_bank:uuid}', function(QuestionBankModel $question_bank){

        $assessment = AssessmentModel::find( $question_bank->assessment_id );

        if( $assessment->is_standalone ) {

            return Inertia::render('CBT/Assessment/shared/question_bank/Question', [ 'questionBankId' => $question_bank->uuid , 'assessmentId' => $assessment->uuid, 'assessmentTitle' => $assessment->title, 'subjectId' => $question_bank->subject_id ] );

        }

        $subject = SubjectModel::find( $question_bank->subject_id );

        $classes = collect( json_decode( $question_bank->classes, true ) )->flatMap( fn($class) => [ ClassModel::firstWhere('class_code', $class)->class_name ] )->toArray();

        $classes = implode(' | ', $classes);

        return Inertia::render('CBT/Assessment/shared/question_bank/Question', [ 'questionBankId' => $question_bank->uuid , 'assessmentId' => $assessment->uuid, 'questionBankClasses' => $classes, 'assessmentTitle' => $assessment->title, 'subjectTitle' => $subject->subject_name, 'subjectId' => $question_bank->subject_id ] );
    } );

    //Classes
    Route::get('/classes', fn() => Inertia::render('SchoolManager/Classes/Index') );

    //Subjects
    Route::get('/subjects', fn() => Inertia::render('SchoolManager/Subjects/Index') );

    //Students
    Route::get('/students', fn() => Inertia::render('SchoolManager/Students/Index') );

    Route::get('/students/create', fn() => Inertia::render('SchoolManager/Students/Create') );

    //Teachers
    Route::get('/teachers', fn() => Inertia::render('SchoolManager/Teachers/Index') );

    //Academic Sessions
    Route::get('/academic-sessions', fn() => Inertia::render('SchoolManager/Sessions/Index') );

    //Terms
    Route::get('/terms', fn() => Inertia::render('SchoolManager/Terms/Index') );
});



Route::middleware(['auth'])->group(function(){

    Route::get('/teacher/dashboard', fn() => Inertia::render('CBT/Teacher/Dashboard') );

    Route::get('/teacher/subject-topics', fn() => Inertia::render('CBT/Teacher/SubjectTopics') );
    
    Route::get('/teacher/class-subjects/{class:class_code}', fn(ClassModel $class) => Inertia::render('CBT/Teacher/Subjects', ['classCode' => $class->class_code]) );
    
    Route::get('/teacher/create-question-bank', fn() => Inertia::render('CBT/Teacher/QuestionsIndex') );

    Route::get('/teacher/create-question-bank/classes/{question_bank:uuid}', fn(QuestionBankModel $questionBank) => Inertia::render('CBT/Teacher/QuestionBankClasses', [ 'questionBankId' => $questionBank->uuid ]) );
    Route::get('/teacher/create-question-bank/sections/{question_bank:uuid}', fn(QuestionBankModel $questionBank) => Inertia::render('CBT/Teacher/QuestionBankSections', [ 'questionBankId' => $questionBank->uuid ]) );
   
    Route::get('/teacher/questions/{question_bank:uuid}', fn(QuestionBankModel $questionBank) => Inertia::render('CBT/Teacher/CreateQuestions', [ 'questionBankId' => $questionBank->uuid, 'assessmentId' => AssessmentModel::find($questionBank->assessment_id)->uuid, 'subjectId' => $questionBank->subject_id  ]) );

});

// //CBT
Route::middleware(['auth:student', 'cbt', 'cbt.session'])->group(function() {

    Route::get('/completed/cbt/{assessment:uuid}', fn(AssessmentModel $assessment) => Inertia::render('CBT/Exams/Complete', [ 'assessmentId' => $assessment->uuid, 'assessmentCode' => $assessment->assessment_code ] ) );

    Route::get('/cbt/{assessment:assessment_code}/s', fn(AssessmentModel $assessment) => Inertia::render('CBT/Exams/Standalone', [ 'assessmentId' => $assessment->uuid ]) ) ;
   
    Route::get('/sse/cbt/save-session/student/{assessment:uuid}', [ ExamController::class, 'examSessionTimer' ]);
    
    Route::get('/cbt/{assessment:assessment_code}/t', fn(AssessmentModel $assessment) => Inertia::render('CBT/Exams/Termly/Index', [ 'assessmentId' => $assessment->uuid, 'assessmentTitle' => $assessment->title, 'assessmentCode' => $assessment->assessment_code  ]) ) ;

    Route::get('/cbt/{assessment:uuid}/t/i', fn(AssessmentModel $assessment) => Inertia::render('CBT/Exams/Termly/Exam', [ 'assessmentId' => $assessment->uuid, 'assessmentCode' => $assessment->assessment_code ]) ) ;  
});









