<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('employee')?->id;

        return [
            'nik' => ['required', 'string', 'max:255', Rule::unique('users', 'nik')->ignore($userId)],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'password' => [$userId ? 'nullable' : 'required', 'string', 'min:8'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'job_level' => ['required', 'integer', 'in:1,2,3,4'],
            'direct_supervisor_id' => ['nullable', 'exists:users,id'],
            'final_supervisor_id' => ['nullable', 'exists:users,id'],
            'instansi' => ['required', 'string', 'in:PERUMDAM_TD,KOPKARTIRTA'],
            'employment_status' => ['required', 'string', 'in:MAGANG,KONTRAK,TETAP'],
            'leave_balance' => ['required', 'integer', 'min:0'],
            'is_admin' => ['sometimes', 'boolean'],
            'photo' => ['nullable', 'image', 'max:2048'],
        ];
    }
}
