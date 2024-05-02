<?php

namespace App\Modules\CBT\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\CBT\Features\GetAssessmentQuestionsFeature;
use App\Modules\CBT\Models\AssessmentModel;
use App\Modules\CBT\Models\CheckInModel;
use App\Modules\CBT\Models\ExamResultsModel;
use App\Modules\CBT\Models\QuestionModel;
use App\Modules\CBT\Requests\ExamSessionTimerRequest;
use App\Modules\CBT\Requests\GetAssessmentQuestionsRequest;
use App\Modules\CBT\Requests\GetStudentExamResponsesRequest;
use App\Modules\CBT\Requests\SaveStudentExamSessionResponsesRequest;
use App\Modules\CBT\Requests\StartStudentExamSessionRequest;
use App\Modules\CBT\Requests\SubmitStudentExamRequest;
use App\Modules\CBT\Tasks\GetAssessmentQuestionsTasks;
use App\Modules\SchoolManager\Models\DepartmentModel;
use App\Modules\SchoolManager\Models\StudentProfileModel;
use App\Modules\SchoolManager\Models\SubjectModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ExamController extends Controller
{
    
    public function getAssessmentQuestions(AssessmentModel $assessment, GetAssessmentQuestionsRequest $request)
    {
        return $this->serve( new GetAssessmentQuestionsFeature($assessment), [ ...$request->validated(), 'user' => request()->user() ] );
    }

    public function startStudentStandaloneExamSession(AssessmentModel $assessment)
    {
        date_default_timezone_set('Africa/Lagos');

        $studentId = request()->user()->uuid;

        $student_session = ExamResultsModel::where('student_profile_id', $studentId)->where('assessment_id', $assessment->uuid)->first();

        $end_time = now()->addSeconds($assessment->assessment_duration)->toDateTimeString();
        $start_time = now()->toDateTimeString();

        $student_session->update([ 'start_time' => $start_time, 'end_time' => $end_time, 'has_started' => true ]);

        return response()->json(['timeLeft' => 'Success']);
    }

    public function getStudentStandaloneExamSession(AssessmentModel $assessment)
    {

        date_default_timezone_set('Africa/Lagos');

        $student = request()->user();

        $student_result = ExamResultsModel::firstOrCreate(['student_profile_id' => $student->uuid, 'assessment_id' => $assessment->uuid ],[
            'uuid'                 => Str::ulid(),
            'student_profile_id'    => $student->uuid,
            'assessment_id'        => $assessment->uuid,
            'time_remaining'       => $assessment->assessment_duration
        ]);

        $instructions = $assessment->description;
        $total_questions = $assessment->questions()->count();
        $total_marks = $assessment->questions()->sum('question_score');
        $duration = $assessment->assessment_duration;
        $assessment_title = $assessment->title;

        if( $student_result->has_started ){

            $end_time = now()->addSeconds($student_result->time_remaining)->toDateTimeString();

            $student_result->update([ 'end_time' => $end_time, 'tries' => intval($student_result->tries) - 1 ]);
           
            $time_remaining = $student_result->time_remaining;

        }else{

            $time_remaining = $duration;
        }

        return response()->json([
            'studentName'           => $student->surname ." ".$student->first_name,
            'studentCode'           => $student->student_code,
            'studentId'             => $student->uuid,
            'studentPhoto'          => $student->profile_pic,
            'hasStarted'            => $student_result->has_started,
            'instructions'          => $instructions,
            'totalQuestions'        => $total_questions,
            'totalScore'            => $total_marks,
            'assessmentDuration'    => $duration,
            'assessmentTitle'       => $assessment_title,
            'remainingTime'         => intval($time_remaining),
            'remainingTries'        => $student_result->tries
        ]);     
        
    }


    public function examSessionTimer(AssessmentModel $assessment, ExamSessionTimerRequest $request)
    {
        // set_time_limit(0);

        // date_default_timezone_set('Africa/Lagos');

        // ignore_user_abort(true);

        // header("Cache-Control: no-store");
        // header("Content-Type: text/event-stream");
        // header("X-Accel-Buffering: no");
        // header("Connection: keep-alive");
        // header("Access-Control-Allow-Origin: *");

        // ob_implicit_flush( true );
        
        // $studentId = auth()->guard('student')->user()->uuid;

        // $data = $request->validated();

        // if( $assessment->is_standalone ){

        //     $student_session = ExamResultsModel::where('student_profile_id', $studentId)->where('assessment_id', $assessment->uuid)->first();

        // }else{

        //     $subjectId = SubjectModel::firstWhere('subject_code',$data['subjectId'] )->uuid;
            
        //     $student_session = ExamResultsModel::where('student_profile_id', $studentId)->where('assessment_id', $assessment->uuid)->where('subject_id', $subjectId)->first();
        // }

        // $end_time = $student_session->end_time;
        // $start_time = now()->toDateTimeString();

        // $time_remaining = strtotime($end_time) - strtotime($start_time);        
        

        // $time_remaining = 15000;

        // while ($time_remaining > 0) {
       

        //     echo 'data: '. $time_remaining . "\n\n";
           
        //     $time_remaining -- ;

        //     // $student_session->update(['time_remaining' => $time_remaining]);
            
        //     // ob_flush();
        //     flush();

        //     if (connection_aborted()) break;

        //     sleep(1);
        // }

        // echo 'data: '. 0 . "\n\n";

        // ob_end_flush();

    }

    public function getStudentExamSessionResponses(AssessmentModel $assessment, GetStudentExamResponsesRequest $request)
    {
        $studentId = request()->user()->uuid;

        $data = $request->validated();

        $student_responses = DB::table('assessment_sessions')
                                ->where(function($query) use($assessment, $studentId, $data){
                                    $query->where('assessment_sessions.student_profile_id', $studentId)
                                        ->where('assessment_sessions.assessment_id', $assessment->uuid);

                                    if( isset( $data['subjectId'] ) && $data['subjectId'] ){
                                        $subjectId = SubjectModel::firstWhere('subject_code',$data['subjectId'] )->uuid;
                                        $query->where('assessment_sessions.subject_id', $subjectId);
                                    }
                                })
                                ->join('questions', 'questions.uuid', '=', 'assessment_sessions.question_id')
                                ->select('assessment_sessions.student_answer as studentAnswer', 'assessment_sessions.uuid as sessionId', 'assessment_sessions.marked_for_review as markedForReview', 'questions.uuid as questionId')
                                ->get();

        $student_responses = $student_responses->map( function($response) {

            $not_answered = ( is_null( $response->studentAnswer ) );

            return [
                'sessionId'         => $response->sessionId,
                'questionId'        => $response->questionId,
                'selectedAnswer'    => trim($response->studentAnswer),
                'markedForReview'   => $response->markedForReview,
                'notAnswered'       => $not_answered
            ];
        });


        
        return response()->json([
                'data' => $student_responses->toArray()
        ]);
    }

    public function saveStudentExamSessionAnswer(AssessmentModel $assessment, SaveStudentExamSessionResponsesRequest $request)
    {
        $student = request()->user();

        $data = $request->validated();

        $question = QuestionModel::firstWhere('uuid', $data['questionId']);

        $score = trim(strtolower($data['studentAnswer'])) == trim(strtolower($question->correct_answer)) ? $question->question_score : 0;

        $student->saveStudentResponse($assessment,  $question->uuid, $data['studentAnswer'], $data['markedForReview'], $score, $data['subjectId'] ?? null , $data['sectionId'] ?? null, $data['sectionTitle'] ?? null );

        return response()->json(['message' => 'Answer Saved']);
    }


    public function getStudentTermlyExamAssessments(AssessmentModel $assessment)
    {
        date_default_timezone_set('Africa/Lagos');
        
        $student = request()->user();

        $student_class = $student->class_id;

        $student_subjects = $student->subjects()->get()->pluck('uuid')->toArray();

        $student_session = ExamResultsModel::whereIn('subject_id', $student_subjects)
                                            ->where('assessment_id', $assessment->uuid)
                                            ->where('student_profile_id', $student->uuid)
                                            ->where('has_submitted', true)
                                            ->get()
                                            ->pluck('subject_id')
                                            ->toArray();

        foreach ( $student_subjects as $key => $value ) {
           if( in_array( $value, $student_session ) ) unset( $student_subjects[$key] );    
        }

        $available_subjects = DB::table('assessment_subjects')
                                ->where( fn($query) => $query->whereIn('assessment_subjects.subject_id', $student_subjects)->where('assessment_subjects.class_id', $student_class)->where('assessment_subjects.is_published', true)->whereBetween('assessment_subjects.start_date',  [ now()->startOfDay()->toDateTimeString(), now()->toDateTimeString() ] ) )
                                ->join('subjects', 'subjects.uuid', '=', 'assessment_subjects.subject_id')
                                ->select('assessment_subjects.assessment_duration as duration', 'subjects.subject_name as subjectName', 'subjects.subject_code as subjectCode', 'subjects.uuid as subId')
                                ->get()
                                ->toArray();

        $checked_in_subjects = CheckInModel::where('student_profile_id', $student->uuid)->where('assessment_id', $assessment->uuid)->first()->subject_ids;
        
        $newAvailableSubjects = [];

        foreach ( $available_subjects as $key => $subject ) {
           
            if( ! in_array( $subject->subId, json_decode($checked_in_subjects, true) ) ) continue;

            $data = [
                'duration'      =>  $subject->duration,
                'subjectName'   =>  $subject->subjectName,
                'subjectCode'   =>  $subject->subjectCode
            ];

            $newAvailableSubjects[] = $data;
        }
       
        return response()->json([
                'data' => $newAvailableSubjects
        ]);

    }

    public function getStudentTermlyExamAssessmentSession(AssessmentModel $assessment, SubjectModel $subject)
    {
        date_default_timezone_set('Africa/Lagos');

        $student = request()->user();

        $assessment_subject = $assessment->subjects()->where('assessment_subjects.subject_id', $subject->uuid)->where('assessment_subjects.class_id', $student->class_id)->first();

        $student_result = ExamResultsModel::firstOrCreate(['student_profile_id' => $student->uuid, 'assessment_id' => $assessment->uuid, 'subject_id' => $subject->uuid ],[
                            'uuid'                 => Str::ulid(),
                            'student_profile_id'    => $student->uuid,
                            'assessment_id'        => $assessment->uuid,
                            'subject_id'           => $subject->uuid,
                            'time_remaining'       => $assessment_subject->pivot->assessment_duration
        ]);

        $instructions = $assessment->description;
        
        $exams_questions = ( new GetAssessmentQuestionsTasks() )->start(['subjectId' => $subject->subject_code, 'assessment' => $assessment, 'user' => request()->user() ])->getAssessmentQuestions()->get();


        $total_questions = $exams_questions->count();
        $total_marks = $exams_questions->sum('score');
       
       
        $duration = $assessment_subject->pivot->assessment_duration;
        $assessment_title = $assessment->title;
        $session = $assessment->session?->session;
        $program_of_study = $student->program_of_study;
        $level = $student->class?->class_name;
        $faculty = $student->faculty?->faculty_name;
        $department = $student->department?->department_name;

        if( $student_result->has_started ){

            $end_time = now()->addSeconds( $student_result->time_remaining )->toDateTimeString();

            $student_result->update([ 'end_time' => $end_time, 'tries' => intval($student_result->tries) - 1 ]);
           
            $time_remaining = $student_result->time_remaining;

        }else{

            $time_remaining = $duration;
        }

        return response()->json([
            'studentName'           => $student->surname ." ".$student->first_name,
            'studentCode'           => $student->student_code,
            'studentId'             => $student->uuid,
            'level'                 => $level,
            'faculty'               => $faculty,
            'department'            => $department,
            'studentPhoto'          => $student->profile_pic,
            'programOfStudy'        => $program_of_study,
            'assessmentSession'     => $session,
            'studentPhoto'          => $student->profile_pic,
            'hasStarted'            => $student_result->has_started,
            'instructions'          => $instructions,
            'totalQuestions'        => $total_questions,
            'totalScore'            => $total_marks,
            'assessmentDuration'    => $duration,
            'assessmentTitle'       => "$assessment_title ($subject->subject_name)",
            'remainingTime'         => intval($time_remaining),
            'remainingTries'        => $student_result->tries,
            'subjectId'             => $subject->uuid
        ]);     
    }

    public function startStudentTermlyExamSession(AssessmentModel $assessment, SubjectModel $subject)
    {
        date_default_timezone_set('Africa/Lagos');

        $student= request()->user();

        $student_session = ExamResultsModel::where( fn($query) => $query->where('subject_id', $subject->uuid)
                                                                        ->where('assessment_id', $assessment->uuid)
                                                                        ->where('student_profile_id', $student->uuid)
                                                                  )->first();

        $assessment_subject = $assessment->subjects()->where('assessment_subjects.subject_id', $subject->uuid)->where('assessment_subjects.class_id', $student->class_id)->first();

        $end_time = now()->addSeconds( $assessment_subject->pivot->assessment_duration )->toDateTimeString();
        $start_time = now()->toDateTimeString();

        $student_session->update([ 'start_time' => $start_time, 'end_time' => $end_time, 'has_started' => true ]);

        return response()->json(['timeLeft' => 'Success']);
    }

    public function submitExam(AssessmentModel $assessment, SubmitStudentExamRequest $request)
    {
        $studentId = request()->user()->uuid;
        
        $data = $request->validated();

        if( $assessment->is_standalone ){

            $student_session = ExamResultsModel::where('student_profile_id', $studentId)->where('assessment_id', $assessment->uuid)->first();

        }else{

            $subjectId = SubjectModel::firstWhere('subject_code',$data['subjectId'] )->uuid;
            
            $student_session = ExamResultsModel::where('student_profile_id', $studentId)->where('assessment_id', $assessment->uuid)->where('subject_id', $subjectId)->first();
        }

        $url = $assessment->is_standalone ? url("/cbt/$assessment->assessment_code") : url("/cbt/$assessment->assessment_code/t");

        $student_session->update(['has_submitted' => true ]);

        return response()->json(['message' => 'Success', 'url' => $url ]);

    }


    public function checkInStudentData()
    {        
        $data = request()->validate([
            'studentId' => 'required|exists:student_profiles,student_code',
        ]);

        $assessment = AssessmentModel::find( request('assessmentId') );


        $student = StudentProfileModel::firstWhere('student_code', $data['studentId']);

        $subjects = $assessment->subjects()->where( 'class_id', $student->class_id )->get()->intersect( $student->subjects()->get() );

        $student = StudentProfileModel::firstWhere('student_code', $data['studentId']);

        return response()->json([
            'studentName'       =>     $student->first_name. " ". $student->surname,
            'studentCode'       =>     $student->student_code,
            'studentClass'      =>     $student->class->class_name,
            'studentPhoto'      =>     $student->profile_pic,
            'subjects'          =>     $subjects->map(fn($subject) => ['subjectId' => $subject->uuid, 'subjectName' => $subject->subject_name, 'subjectCode' => $subject->subject_code ])
        ]);

    }

    public function checkInStudent(AssessmentModel $assessment)
    {
        date_default_timezone_set('Africa/Lagos');

        $data = request()->validate([
            'studentId' => 'required|exists:student_profiles,student_code',
            'subjects'  => 'required|array'
        ]);

        $student = StudentProfileModel::firstWhere('student_code', $data['studentId']);

        $schedule = DB::table('assessment_schedules')->where('assessment_id' , $assessment->uuid )->where('department', $student->department_id)->first();

        if( is_null( $schedule ) ){

            return response([
                'error' => 'No Schedule found for your department today'
            ]);
        }

       
        if( Carbon::parse($schedule->start_time)->gt( now() ) ){

            return response([
                'error' => 'Sorry, your department is not scheduled to partake for the exams at this time'
            ]);

        }

        if( Carbon::parse($schedule->end_time)->lt( now() ) ){

            return response([
                'error' => 'Sorry, the time for your department to take this exam has elapsed'
            ]);

        }

        if( CheckInModel::where('assessment_id', $assessment->uuid)->where('student_profile_id', $student->uuid)->first() ){

            return response([
                'error' => 'Sorry, you have already been checked in'
            ]);

        }
        
        CheckInModel::updateOrCreate([ 'assessment_id' => $assessment->uuid, 'student_profile_id' => $student ], [
            'uuid'                  => Str::ulid(),
            'assessment_id'         => $assessment->uuid,
            'subject_ids'           => json_encode($data['subjects']),
            'student_profile_id'     => $student->uuid,
            'checked_in_at'         => now(),
            'checked_in_by'         => request()->user()->fullname,
            'checked_in_expires_at' => now()->endOfDay()
        ]);

        return response()->json(['Message' => 'Checked In']);

    }
}
