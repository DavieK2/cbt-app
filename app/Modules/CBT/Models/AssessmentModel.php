<?php

namespace App\Modules\CBT\Models;

use App\Modules\CBT\Resources\QuestionListCollection;
use App\Modules\CBT\Tasks\QuestionListTasks;
use App\Modules\SchoolManager\Models\AcademicSessionModel;
use App\Modules\SchoolManager\Models\ClassModel;
use App\Modules\SchoolManager\Models\SubjectModel;
use App\Modules\SchoolManager\Models\TermModel;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class AssessmentModel extends Model
{
    use HasFactory, HasUlids;

    protected $primaryKey = 'uuid';

    protected $table = 'assessments';

    protected $guarded = ['uuid'];

    public function questions()
    {
        return $this->belongsToMany(QuestionModel::class, 'assessment_questions', 'assessment_id', 'question_id')->withPivot(['subject_id', 'class_id', 'uuid', 'section_id'])->withTimestamps();
    }

    public function assignQuestion($question_id, $subject_id = null, $class_id = null, $sectionId = null)
    {
        $class_code = $class_id;
        $class_id = ClassModel::firstWhere('class_code', $class_id)?->uuid;
        $sectionId = SectionModel::firstWhere('uuid', $sectionId)?->uuid;
        
        $this->questions()->syncWithoutDetaching([ $question_id => [ 'subject_id' => $subject_id, 'class_id' => $class_id, 'uuid' => Str::ulid(), 'section_id' => $sectionId ]]);

        return $this->cacheAssessmentQuestions($subject_id, $class_code);
    }

    public function assignQuestions(array $question_ids, $subject_id = null, $class_id = null, $sectionId = null)
    {
        $class_code = $class_id;
        $class_id = ClassModel::firstWhere('class_code', $class_id)?->uuid;
        $sectionId = SectionModel::firstWhere('uuid', $sectionId)?->uuid;
        
        $data = collect($question_ids)->map( fn($question_id) => ['question_id' => $question_id, 'subject_id' => $subject_id, 'class_id' => $class_id, 'uuid' => Str::ulid(), 'section_id' => $sectionId, 'assessment_id' => $this->uuid ])->toArray();

        DB::table('assessment_questions')->where( fn($query) => $query->whereIn('question_id', $question_ids)->where('class_id', $class_id)->where('assessment_id', $this->uuid)->where('subject_id', $subject_id)->where('section_id', $sectionId) )->delete();
        
        DB::table('assessment_questions')->insert( $data );

        return $this->cacheAssessmentQuestions($subject_id, $class_code);
        
    }

    public function unAssignQuestion($question_id, $class_id = null, $subject_id = null)
    {
        $class_code = $class_id;
       
        if( $this->is_standalone ){

            $this->questions()->detach($question_id);

        }else{

            $class_id = ClassModel::firstWhere('class_code', $class_id)->uuid;

            DB::table('assessment_questions')->where( fn($query) => $query->where('assessment_questions.question_id', $question_id)->where('assessment_questions.subject_id', $subject_id)->where('assessment_questions.class_id', $class_id)->where('assessment_questions.assessment_id', $this->uuid) )->limit(1)->delete();
        }

        return $this->cacheAssessmentQuestions($subject_id, $class_code);
    }

    public function unAssignQuestions( array $question_ids, $class_id = null, $subject_id = null)
    {
        $class_code = $class_id;

        if( $this->is_standalone ){

            $this->questions()->detach($question_ids);

        }else{

            $class_id = ClassModel::firstWhere('class_code', $class_id)->uuid;

            DB::table('assessment_questions')->where( fn($query) => $query->whereIn('assessment_questions.question_id', $question_ids)->where('assessment_questions.subject_id', $subject_id)->where('assessment_questions.class_id', $class_id)->where('assessment_questions.assessment_id', $this->uuid) )->delete();
        }

        return $this->cacheAssessmentQuestions($subject_id, $class_code);
    }

    public function classes()
    {
        return $this->belongsToMany(ClassModel::class, 'assessment_classes', 'assessment_id', 'class_id');
    }

    public function addClassesToAssessment($classes)
    {
        $this->classes()->detach();

        foreach( $classes as $class ){
            DB::table('assessment_classes')->insert(['uuid' => Str::ulid(), 'assessment_id' => $this->uuid, 'class_id' => $class ]);
        }
    }

    public function assessmentType()
    {
        return $this->belongsTo(AssessmentTypeModel::class, 'assessment_type_id');
    }

    public function session()
    {
        return $this->belongsTo(AcademicSessionModel::class, 'academic_session_id');
    }

    public function term()
    {
        return $this->belongsTo(TermModel::class, 'school_term_id');
    }

    public function subjects()
    {
        return $this->belongsToMany(SubjectModel::class, 'assessment_subjects', 'assessment_id', 'subject_id')
                    ->withPivot(['class_id', 'start_date', 'end_date', 'assessment_duration', 'is_published']);
    }

    public function addSubject(Collection $data)
    {
        $data->each(function($subject, $key) {

            DB::table('assessment_subjects')
                ->insert(
                    [
                        'uuid'                  => $subject['id'] ?? Str::ulid(),
                        'assessment_id'         => $this->uuid, 
                        'subject_id'            => $key, 
                        'is_published'          => $subject['is_published'] === 'Published' ? true : false, 
                        'class_id'              => $subject['class_id'],
                        'assessment_duration'   => $subject['assessment_duration'],
                        'start_date'            => $subject['start_date'],
                        'end_date'              => $subject['end_date'],
                    ]);
        });

        return ;
    }

    public function cacheAssessmentQuestions($subject_id, $class_code)
    {
        $questions = ( new QuestionListTasks() )->start([ 'assessmentId' => $this->uuid, 'subjectId' => $subject_id, 'classId' => $class_code ])->getAssignedQuestions()->all()->get();
 
        $questions = ( new QuestionListCollection( $questions ) )->toArray( request() );


        Cache::put("questions_{$this->assessment_code}_{$subject_id}_$class_code",  $questions );
    }

    public function hasCachedQuestions( $subject_id, $class_code )
    {
        return Cache::has("questions_{$this->assessment_code}_{$subject_id}_$class_code");
    }

    public function questionHasBeenAssigned( $question_id, $subject_id = null, $class_code = null )
    {
        if( $this->is_standalone ){

            return DB::table('assessment_questions')->where( fn($query) => $query->where('assessment_questions.question_id', $question_id)->where('assessment_questions.assessment_id', $this->uuid) )->exists();

        }else{

            $class_id = ClassModel::firstWhere('class_code', $class_code)->uuid;

            return DB::table('assessment_questions')->where( fn($query) => $query->where('assessment_questions.question_id', $question_id)->where('assessment_questions.subject_id', $subject_id)->where('assessment_questions.class_id', $class_id)->where('assessment_questions.assessment_id', $this->uuid) )->exists();
        }
    }

    public function getAssessmentQuestion( $question_id, $subject_id = null, $class_code = null )
    {
        if( $this->is_standalone ){

            return DB::table('assessment_questions')->where( fn($query) => $query->where('assessment_questions.question_id', $question_id)->where('assessment_questions.assessment_id', $this->uuid) )->limit(1);

        }else{

            $class_id = ClassModel::firstWhere('class_code', $class_code)->uuid;

            return DB::table('assessment_questions')->where( fn($query) => $query->where('assessment_questions.question_id', $question_id)->where('assessment_questions.subject_id', $subject_id)->where('assessment_questions.class_id', $class_id)->where('assessment_questions.assessment_id', $this->uuid) )->limit(1);
        }
    }

    protected function updateAssessmentSubjects($subject_id, $class_id)
    {
        $assessment_questions = DB::table('assessment_questions')->where( fn($query) => $query->where('assessment_questions.subject_id', $subject_id)->where('assessment_questions.class_id', $class_id)->where('assessment_questions.assessment_id', $this->uuid ) );
        
        return DB::table('assessment_subjects')->where( fn($query) => $query->where('assessment_id', $this->id)->where('subject_id', $subject_id)->where('class_id', $class_id) )->first()->update(['total_questions' => $total_questions, 'total_score' => $total_score ]);
    }
}
