<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'not_in:'.User::$initialName, 'regex:/\s/'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($this->user()->id)],
            'affil' => ['required', 'string', 'max:255' ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.not_in' => '氏名を正しく入力してください。氏と名のあいだにはかならず「半角スペース」をいれてください。',
            'name.regex' => '氏と名のあいだにはかならず「半角スペース」をいれてください。',
        ];
    }
}
