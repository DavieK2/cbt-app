<?php

namespace App\Modules\CBT\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\CBT\Models\AssessmentModel;
use App\Modules\CBT\Requests\GetStudentResultRequest;
use App\Modules\CBT\Requests\GetTermlyAssessmentResultRequest;
use App\Modules\CBT\Resources\AssessmentSubjectsCollection;
use App\Modules\CBT\Tasks\GetAssessmentSubjectsTasks;
use App\Modules\Excel\Export;
use App\Modules\SchoolManager\Models\ClassModel;
use App\Modules\SchoolManager\Models\StudentProfileModel;
use App\Modules\SchoolManager\Models\SubjectModel;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class AssessmentResultController extends Controller
{
    public function subjects(AssessmentModel $assessment)
    {
        $assessmentSubjects = (new GetAssessmentSubjectsTasks())->start(['assessment' => $assessment])->getSubjects()->all()->get();

        return new AssessmentSubjectsCollection($assessmentSubjects);
    }

    public function getTermlyAssessmentResults(GetTermlyAssessmentResultRequest $request)
    {
        set_time_limit(0);

        $data = $request->validated();

        $assessment = AssessmentModel::firstWhere('uuid', $data['assessmentId'] );
        $subject = SubjectModel::find( $data['subjectId'] ); 
        $class = ClassModel::find( $data['classId'] );

        DB::table('assessment_sessions')
            ->join('student_profiles', 'student_profiles.uuid', '=', 'assessment_sessions.student_profile_id')
            ->where( function($query) use($assessment, $subject){
                $query->where('assessment_sessions.assessment_id', $assessment->uuid)
                      ->where('assessment_sessions.subject_id', $subject->uuid);
            })
            ->where('student_profiles.class_id', $class->uuid)
            ->select('assessment_sessions.*', 'student_profiles.class_id')
            ->get()
            ->groupBy('student_profile_id')
            ->each(function($session, $studentId) use($assessment, $subject){
            
                $student = StudentProfileModel::find($studentId);

                $student_score = $session->sum('score');

                $max_score = $assessment->assessmentType->max_score;

                $total_marks = $assessment->questions()->where(fn($query) => $query->where('assessment_questions.subject_id', $subject->uuid)->where('assessment_questions.class_id', $student->class_id))->sum('question_score');

                $total_score = floor( (( ($student_score) / $total_marks ) * ( $max_score )) + 15 );

                $grade = match( true ){
                    ( $total_score >= 70 ) => 'A',
                    ( $total_score >= 60 && $total_score <= 69 ) => 'B',
                    ( $total_score >= 50 && $total_score <= 59 ) => 'C',
                    ( $total_score >= 45 && $total_score <= 49 ) => 'D',
                    ( $total_score >= 40 && $total_score <= 44 ) => 'E',
                    ( $total_score <= 39 ) => 'F',
                    default => NULL
                };

                $remarks = match( true ){
                    ( $total_score >= 70 ) => 'EXCELLENT',
                    ( $total_score >= 60 && $total_score <= 69 ) => 'VERY GOOD',
                    ( $total_score >= 50 && $total_score <= 59 ) => 'GOOD',
                    ( $total_score >= 45 && $total_score <= 49 ) => 'PASS',
                    ( $total_score >= 40 && $total_score <= 44 ) => 'FAIR',
                    ( $total_score <= 39 ) => 'FAIL',
                    default => NULL
                };

                $points = match( true ){
                    ( $total_score >= 70 ) => 5,
                    ( $total_score >= 60 && $total_score <= 69 ) => 4,
                    ( $total_score >= 50 && $total_score <= 59 ) => 3,
                    ( $total_score >= 45 && $total_score <= 49 ) => 2,
                    ( $total_score >= 40 && $total_score <= 44 ) => 1,
                    ( $total_score <= 39 ) => 0,
                    default => NULL
                };

                if( $total_score > 100){
                    $total_score = 98;
                }
                DB::table('assessment_results')->where('student_profile_id', $studentId)->where('assessment_id', $assessment->uuid)->where('subject_id', $subject->uuid)->limit(1)->update(['total_score' => $total_score, 'grade' => $grade, 'remarks' => $remarks, 'points' => $points ]);
        });
        

        $results = DB::table('assessment_results')
                        ->join('student_profiles', 'student_profiles.uuid', '=', 'assessment_results.student_profile_id')
                        ->join('subjects', 'subjects.uuid', '=', 'assessment_results.subject_id')
                        ->where( function($query) use($assessment, $subject){
                            $query->where('assessment_results.assessment_id', $assessment->uuid)
                                  ->where('assessment_results.subject_id', $subject->uuid);
                        })
                        ->where('student_profiles.class_id', $class->uuid)
                        ->select('assessment_results.total_score', 'assessment_results.remarks', 'assessment_results.grade', 'assessment_results.points', 'student_profiles.first_name', 'student_profiles.surname', 'student_profiles.student_code', 'subjects.subject_name', 'subjects.subject_code', 'student_profiles.uuid as studentId')
                        ->get()
                        ->map(function($result, $index){

                            return [
                                'S/N' => $index + 1,
                                'STUDENT NAME' => "$result->first_name $result->surname",
                                'REG NO' => $result->student_code,
                                'COURSE' => "$result->subject_name ($result->subject_code)",
                                "TOTAL SCORE" => $result->total_score,
                                "GRADE" => $result->grade,
                                "POINTS" => $result->points,
                                'REMARKS' => $result->remarks
                            ];
                        });
                       
        $headings = ['S/N','STUDENT NAME','REG NO','COURSE',"TOTAL SCORE","GRADE","POINTS",'REMARKS'];
        
        $path = "$assessment->uuid/$class->class_code/$subject->subject_name.xlsx";

        Excel::store( new Export($results, $headings), $path , 'results');

        return response()->json([
            'path' => url("results/".$path)
        ]);

    }

    public function getStudentResults(GetStudentResultRequest $request)
    {
        $data = $request->validated();

        $student = StudentProfileModel::find($data['studentId']);
        $assessmentId = AssessmentModel::firstWhere('uuid',$data['assessmentId'])->id;

        $results = DB::table('assessment_results')
                        ->join('student_profiles', 'student_profiles.id', '=', 'assessment_results.student_profile_id')
                        ->join('classes', 'student_profiles.class_id', '=', 'classes.id')
                        ->join('subjects', 'assessment_results.subject_id', '=', 'subjects.id')
                        ->where(fn($query) => $query ->where('assessment_id', $assessmentId)->where('student_profile_id', $data['studentId']))
                        ->select('subjects.subject_name as subjectName', 'subjects.subject_code as subjectCode', 'assessment_results.total_score as score', 'assessment_results.grade', 'assessment_results.remarks as remarks')
                        ->get()
                        ->toArray();

        return response()->json([
            'studentName'       => "$student->first_name $student->surname",
            'studentClass'      => $student->class->class_name,
            'studentPhoto'      => $student->profile_pic,
            'studentId'         => $student->student_code,
            'studentResults'    => $results,
        ]);
    }
}
