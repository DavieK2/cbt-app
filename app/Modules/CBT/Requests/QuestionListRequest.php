<?php

namespace App\Modules\CBT\Requests;

use App\Modules\CBT\Models\AssessmentModel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class QuestionListRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'questionBankId'    =>  ['exists:question_banks,uuid'],
            'assessmentId'      =>  [ Rule::requiredIf( is_null( request('questionBankId') ) ), 'exists:assessments,uuid'],
            'subjectId'         =>  [ Rule::requiredIf( request('assigned') == true && ( ! AssessmentModel::find( request('assessmentId') )?->is_standalone ) ), 'exists:subjects,uuid'],
            'classId'           =>  [ Rule::requiredIf( request('assigned') == true && ( ! AssessmentModel::find( request('assessmentId') )?->is_standalone ) ), 'exists:classes,class_code'],
            'assigned'          =>  'boolean',
            'filter'             =>  'json',
            'perPage'           =>  'required|int',
        ];
    }
}
