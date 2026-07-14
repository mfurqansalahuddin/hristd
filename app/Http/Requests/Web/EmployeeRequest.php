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
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($userId)],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'password' => [$userId ? 'nullable' : 'required', 'string', 'min:8'],
            'department_id' => ['required', 'exists:departments,id'],
            'job_level' => ['required', 'integer', 'in:1,2,3,4'],
            'instansi' => ['required', 'string', 'in:PERUMDAM_TD,KOPKARTIRDA'],
            'employment_status' => ['required', 'string', 'in:TETAP,PEGAWAI_80,PRAMAGANG,MAGANG,KONTRAK'],
            'leave_balance' => ['required', 'integer', 'min:0'],
            'is_admin' => ['sometimes', 'boolean'],
            'photo' => ['nullable', 'image', 'max:2048'],
        ];
    }
}
