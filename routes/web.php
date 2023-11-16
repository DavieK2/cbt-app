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
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Sanctum\PersonalAccessToken;
use PragmaRX\Google2FAQRCode\Google2FA;
use PragmaRX\Google2FAQRCode\QRCode\Bacon;

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


function patternChaser($str){
    
    $i = 2 ;

    $j = round( strlen($str) / 2 );

    while ( $i <= $j ){
        findPattern($i++, $str);
    }
}

function findPattern($number, $str) {

    $patterns = [];

    for( $i = 0 ; $i < ( strlen($str) - $number ) ; $i ++){

        $pattern = str_split($str);

        $pattern = array_slice( $pattern, $number, $i + $number );
        
        $str_index = strpos( $str, $pattern[$i] );
        
        if( $str_index && $str_index != $i){

            $patterns[] = $pattern;

            continue;
        }

    }
    dd( $patterns );
}

Route::get('/', function(){

    patternChaser('ddw444ddrttdddd');

    
    // DB::table('computed_assessment_results')
    //     ->join('student_profiles', 'student_profiles.id', '=', 'computed_assessment_results.student_profile_id')
    //     ->where(fn($query) => $query->where('computed_assessment_results.academic_session_id', 2)
    //                                 ->where('computed_assessment_results.school_term_id', 1)
    //                                 ->where('student_profiles.class_id', 3)

    //     )
    //     ->get()
    //     ->groupBy('subject_id')
    //     ->each(function($results, $subjectId){

    //         $subject = SubjectModel::find($subjectId);
    //         $headings = collect();

    //         $results = $results->map(function($result, $index) use($subject, $headings){

    //             $student = StudentProfileModel::find($result->student_profile_id);

    //             $assessment_results = json_decode($result->assessments);

    //             $total_max_score = collect($assessment_results)->sum('max_score');

    //             $assessment_results = collect($assessment_results)->mapWithKeys(fn($value) => [ strtoupper($value->title)." ($value->max_score)" => $value->score ])->toArray();
                
    //             $data = [
    //                 'S/N' => $index + 1,
    //                 'STUDENT NAME' => "$student->first_name $student->surname",
    //                 'REG NO' => $student->student_code,
    //                 'COURSE' => "$subject->subject_name ($subject->subject_code)",
    //                 ...$assessment_results,
    //                 "TOTAL SCORE ($total_max_score)" => $result->total_score,
    //                 "GRADE" => $result->grade,
    //                 'REMARKS' => $result->remarks
    //             ];

    //             $headings->push( array_keys($data) );

    //             return $data;
    //         });

    //         return Excel::store( new Export($results, $headings->first()), "$subject->subject_name.xlsx" );
            
    //     });
                                            

    // $student = StudentProfileModel::where('class_id', 2)->get()->each(function($student) use($assessment){
    //     $results = DB::table('assessment_results')
    //     ->join('student_profiles', 'student_profiles.id', '=', 'assessment_results.student_profile_id')
    //     ->join('classes', 'student_profiles.class_id', '=', 'classes.id')
    //     ->join('subjects', 'assessment_results.subject_id', '=', 'subjects.id')
    //     ->where(fn($query) => $query ->where('assessment_id', $assessment->id)->where('student_profile_id', $student->id))
    //     ->select('subjects.subject_name as subjectName', 'subjects.subject_code as subjectCode', 'assessment_results.total_score as score', 'assessment_results.grade', 'assessment_results.remarks as remarks')
    //     ->get()
    //     ->toArray();


    //     $studentName = "$student->first_name $student->surname";

    //     $pdf = Pdf::loadView('result',[
    //     'assessmentTitle'   => $assessment->title,
    //     'studentName'       => "$student->first_name $student->surname",
    //     'studentClass'      => $student->class->class_name,
    //     'studentPhoto'      => $student->profile_pic,
    //     'studentId'         => $student->student_code,
    //     'studentResults'    => $results,
    //     ]);

    //     $pdf->save("$studentName.pdf");
    // });

   
    
});

Route::middleware(['auth'])->group(function(){

    Route::get('/students/check-in/{assessment:uuid}', fn(AssessmentModel $assessment) => Inertia::render('CBT/CheckIn/Index', ['assessmentId' => $assessment->uuid]) );

    Route::get('/dashboard', fn() => Inertia::render('Dashboard/Index') );

    //Settings
    Route::get('/settings', fn() => Inertia::render('CBT/Settings/Index') );
    
    //Assessments
    Route::get('/assessments', fn() => Inertia::render('CBT/Assessment/Index') );

    Route::get('/assessments/standalone', fn() => Inertia::render('CBT/Assessment/standalone/StandaloneAssessment') );
    

    Route::get('/assessments/termly', fn() => Inertia::render('CBT/Assessment/termly/TermlyAssessment') );
    Route::get('/assessments/termly/classes/{assessment:uuid}', fn(AssessmentModel $assessment) => Inertia::render('CBT/Assessment/termly/TermlyAssessmentClasses', ['assessmentId' => $assessment->uuid, 'title' => $assessment->title ]) );
    Route::get('/assessments/termly/schedule/{assessment:uuid}', fn(AssessmentModel $assessment) => Inertia::render('CBT/Assessment/termly/TermlyAssessmentSchedule', ['assessmentId' => $assessment->uuid, 'title' => $assessment->title ]) );
    Route::get('/assessments/termly/view/{assessment:uuid}', fn(AssessmentModel $assessment) => Inertia::render('CBT/Assessment/termly/View', ['assessmentId' => $assessment->uuid, 'assessmentTitle' => $assessment->title ]) );

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

    Route::get('/questions/create/t/{assessment:uuid}/{subject}/{class}', function(AssessmentModel $assessment, SubjectModel $subject, $class){
        if( $assessment->is_standalone ) abort(404);
        return Inertia::render('CBT/Questions/Create', ['assessmentId' => $assessment->uuid, 'title' => $assessment->title, 'subjectId' => $subject->id, 'subjectTitle' => $subject->subject_name, 'classId' => $class ] );
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
   
    Route::get('/teacher/questions/{question_bank:uuid}', fn(QuestionBankModel $questionBank) => Inertia::render('CBT/Teacher/CreateQuestions', [ 'questionBankId' => $questionBank->uuid, 'assessmentId' => AssessmentModel::find($questionBank->assessment_id)->uuid  ]) );

});

// //CBT
Route::middleware(['auth:student', 'cbt', 'cbt.session'])->group(function() {

    Route::get('/completed/cbt/{assessment:uuid}', fn(AssessmentModel $assessment) => Inertia::render('CBT/Exams/Complete', [ 'assessmentId' => $assessment->uuid ] ) );

    Route::get('/cbt/{assessment:uuid}/s', fn(AssessmentModel $assessment) => Inertia::render('CBT/Exams/Standalone', [ 'assessmentId' => $assessment->uuid ]) ) ;
   
    Route::get('/cbt/save-session/student/{assessment:uuid}', [ ExamController::class, 'examSessionTimer' ]);
    
    Route::get('/cbt/{assessment:uuid}/t', fn(AssessmentModel $assessment) => Inertia::render('CBT/Exams/Termly/Index', [ 'assessmentId' => $assessment->uuid, 'assessmentTitle' => $assessment->title ]) ) ;

    Route::get('/cbt/{assessment:uuid}/t/i', fn(AssessmentModel $assessment) => Inertia::render('CBT/Exams/Termly/Exam', [ 'assessmentId' => $assessment->uuid ]) ) ;  
});









