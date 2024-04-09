<?php

namespace App\Modules\CBT\Requests;

use App\Modules\CBT\Models\AssessmentModel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateQuestionBankRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title'         => [  Rule::requiredIf( AssessmentModel::find( request('assessmentId') )->is_standalone ) ],
            'assessmentId'  => 'required|exists:assessments,uuid',
            'subjectId'     => ['nullable','exists:subjects,uuid', Rule::requiredIf( ! AssessmentModel::find( request('assessmentId') )->is_standalone ) ],
            'classes'       => [ 'array', Rule::requiredIf( ! AssessmentModel::find( request('assessmentId') )->is_standalone ) ],
            'classes.*'     => 'exists:classes,class_code'
        ];
    }
}
