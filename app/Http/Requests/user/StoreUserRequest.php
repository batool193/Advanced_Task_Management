<?php

namespace App\Http\Requests\user;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreUserRequest extends FormRequest
{
    /**
     * Indicates if the validation should stop on the first failure
     *
     * @var bool
     */
    protected $stopOnFirstFailure = true;
    /**
     * Determine if the user is authorized to make this request
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }
    /**
     * Get the validation rules that apply to the request
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */

    public function rules()
    {
        $rules = [
            'name' => 'required|string|max:50|regex:/^[a-zA-Z\s]+$/',
            'email' => 'required|string|email|unique:users,email,',
            'password' => 'required|string|min:8|max:30|confirmed',
            'role' => 'required|string',
        ];

        return $rules;
    }
    /**
     * Get the custom validation messages
     *
     * @return array<string, string>
     */

    public function messages()
    {
        return [
            'required' => ':attribute is required',
            'string' => ':attribute must be a string',
            'email' => ':attribute must be a valid email address',
            'unique' => ':attribute has already been taken',
            'regex' => ':attribute must contain only letters',
            'confirm'=>':attribute must be confirmed'

        ];
    }
    /**
     * Get custom attributes for validator errors
     *
     * @return array<string, string>
     */
    public function attributes()
    {
        return [
            'name' => 'user name',
            'email' => 'user email address',
            'password' => 'user password',
            'role'=>'user role'
        ];
    }
    /**
     * Handle a failed validation attempt
     *
     * @param Validator $validator
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'message' => 'validation error',
            'errors' => $validator->errors(),
        ], 400));
    }
}
