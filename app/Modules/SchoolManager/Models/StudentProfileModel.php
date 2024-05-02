<?php

namespace App\Modules\SchoolManager\Models;

use App\Modules\CBT\Models\AssessmentModel;
use App\Modules\CBT\Models\CheckInModel;
use App\Modules\CBT\Models\ExamResultsModel;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;

class StudentProfileModel extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasUlids;

    protected $table = 'student_profiles';

    protected $primaryKey = 'uuid';

    protected $guarded = ['uuid'];

    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function faculty()
    {
        return $this->belongsTo(FacultyModel::class, 'faculty_id');
    }

    public function department()
    {
        return $this->belongsTo(DepartmentModel::class, 'department_id');
    }

    public function session()
    {
        return $this->belongsTo(AcademicSessionModel::class, 'academic_session_id');
    }

    public function subjects()
    {
        return $this->belongsToMany(SubjectModel::class, 'student_subjects', 'student_profile_id', 'subject_id');
    }

    public function assignSubject(array $subjects)
    {

        $this->subjects()->detach();

        $data = collect($subjects)->map( fn($subject) => ['student_profile_id' => $this->uuid, 'subject_id' => $subject, 'uuid' => Str::ulid() ]  )->toArray();

        DB::table('student_subjects')->insert( $data );
        
    }

    public function assessmentSession()
    {
        return $this->belongsToMany(AssessmentModel::class, 'assessment_sessions', 'student_profile_id', 'question_id' )->withPivot(['assessment_id', 'subject_id', 'student_answer', 'marked_for_review', 'score', 'uuid' ])->withTimestamps();
    }

    public function saveStudentResponse(AssessmentModel $assessment, $questionId, $studentAnswer, $markedForReview, $score, $subjectId = null, $sectionId = null, $sectionTitle = null )
    {
        $subjectId = SubjectModel::firstWhere('subject_code', $subjectId)?->uuid;
        
        $session = DB::table('assessment_sessions')->where(fn($query) => $query->where('student_profile_id', $this->uuid)
                                                                    ->where('assessment_id', $assessment->uuid)
                                                                    ->where('question_id',$questionId)
                                                                    ->where('subject_id', $subjectId)
                                                                    ->where('section_id', $sectionId)
        )->limit(1);

        if( $session->count() > 0) {

            $session->update([
                'student_answer'    => $studentAnswer, 
                'marked_for_review' => $markedForReview, 
                'score'             => $score, 
            ]);

        } else {

            DB::table('assessment_sessions')->insert(
                [
                    'uuid'              => Str::ulid(),
                    'student_profile_id' => $this->uuid, 
                    'assessment_id'     => $assessment->uuid, 
                    'question_id'       => $questionId,
                    'student_answer'    => $studentAnswer, 
                    'marked_for_review' => $markedForReview,
                    'section_id'        => $sectionId,
                    'section_title'     => $sectionTitle,
                    'score'             => $score, 
                    'subject_id'        => $subjectId
                ] 
            );
        }

        return ;

    }
    
    public function checkIns()
    {
        return $this->hasOne(CheckInModel::class, 'student_checkins', 'student_profile_id');
    }

    public function results()
    {
        return $this->hasMany(ExamResultsModel::class, 'assessment_results', 'student_profile_id');
    }
}
