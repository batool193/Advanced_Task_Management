<?php

namespace App\Http\Requests\task;

use App\Enums\TaskType;
use App\Enums\TaskPriorty;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class UpdateTaskRequest extends FormRequest
{
    /**
     * Indicates if the validator should stop on the first rule failure
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
     * @return array
     */
    public function rules()
    {
        return  [
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string|max:255',
            'type' => ['sometimes',  new Enum(TaskType::class)],
            'priority' => ['sometimes',  new Enum(TaskPriorty::class)],
            'dependent_task_ids' => 'sometimes|array', // tasks that this task depends on
            'dependent_task_ids.*' => 'exists:tasks,id',

        ];
    }


    /**
     * Get the custom messages for validator errors
     *
     * @return array
     */
    public function messages()
    {
        return [

            'Enum' => 'The :attribute must be a valid enum value.',
            'string'=>'the :attribute must be string',
            'exists' =>'The :attribute must exist.'

        ];
    }
    /**
     * Get custom attributes for validator errors
     *
     * @return array
     */

    public function attributes()
    {
        return [
            'title' => 'task title',
            'description' => 'task description',
            'type' => 'task type',
            'priority' => 'task priority',
            'depends_on'=>'depends on task'
        ];
    }
    /**
     * Handle a failed validation attempt
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @throws \Illuminate\Validation\ValidationException
     */

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new ValidationException($validator, response()->json([
            'message' => 'validation error',
            'errors' => $validator->errors()
        ], 403));
    }
}
