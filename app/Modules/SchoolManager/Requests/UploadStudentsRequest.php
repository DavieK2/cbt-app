<?php

namespace App\Modules\SchoolManager\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadStudentsRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'file' => 'required|mimes:csv,xlsx,txt'
        ];
    }
}
