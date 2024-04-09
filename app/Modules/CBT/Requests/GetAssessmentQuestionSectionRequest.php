<?php

namespace App\Modules\CBT\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GetAssessmentQuestionSectionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $assessment = $this->route('assessment');

        return [
            'classId'       => [ Rule::requiredIf( ! $assessment->is_standalone ), 'exists:classes,class_code'],
            'subjectId'     => [ Rule::requiredIf( ! $assessment->is_standalone ), 'exists:subjects,uuid'],
        ];
    }
}
