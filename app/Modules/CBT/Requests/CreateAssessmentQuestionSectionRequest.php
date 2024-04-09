<?php

namespace App\Modules\CBT\Requests;

use App\Modules\CBT\Models\QuestionModel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateAssessmentQuestionSectionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $assessment = $this->route('assessment');

        return [
            'classId'           => [ Rule::requiredIf( ! $assessment->is_standalone )],
            'subjectId'         => [ Rule::requiredIf( ! $assessment->is_standalone )],
            'title'             => 'required|string',
            'questionType'      => 'required|string|in:'.implode(',', QuestionModel::QUESTION_TYPES),
            'description'       => 'required|string'
        ];
    }
}
