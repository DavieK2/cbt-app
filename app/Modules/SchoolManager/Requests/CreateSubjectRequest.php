<?php

namespace App\Modules\SchoolManager\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateSubjectRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'subjectName' => 'required'
        ];
    }
}
