<?php

namespace App\Modules\SchoolManager\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateSudentProfileRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'firstName' => 'required',
            'lastName' => 'required',
            'regNo' => 'required',
            'profilePic' => 'required',
            'level' => 'required',
            'session' => 'required',
            'phoneNo' => 'required',
            'programOfStudy' => 'required',
            'email' => 'required',
        ];
    }
}
