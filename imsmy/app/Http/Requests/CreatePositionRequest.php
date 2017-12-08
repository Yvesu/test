<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class CreatePositionRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'department'   =>   'required',
            'name'         =>   'required',
            'description'  =>   'required',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'department.required'   =>  'A department is required',
            'name.required' => 'A title is required',
            'description.required'  => 'A message is required',
        ];
    }
}
