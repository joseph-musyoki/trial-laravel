<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'title'=>['required','string','max:255',
            //unique:title+due_date to prevent duplicate tasks on the same day
            Rule::unique('tasks')->where(function($query){
                return $query->where('due_date',$this->input('due_date'));
            })],
            'due_date'=>['required','date','date_format:Y-m-d','after_or_equal:today'],
            'priority'=>['required', Rule::in(['low', 'medium', 'high'])],
        ];
    }
    public function messages(): array
    {
        return [
            'title.required'=>'The title field is required.',
            'title.string'=>'The title must be a string.',
            'title.max'=>'The title may not be greater than 255 characters.',
            'title.unique'=>'A task with the same title already exists for the selected due date.',
            'due_date.required'=>'The due date field is required.',
            'due_date.date'=>'The due date must be a valid date.',
            'due_date.date_format'=>'The due date does not match the format Y-m-d.',
            'due_date.after_or_equal'=>'The due date must be today or a future date.',
            'priority.required'=>'The priority field is required.',
            'priority.in'=>'The priority must be one of the following: low, medium, high.',
        ];
    }
}