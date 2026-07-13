<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\EmployeeRequest;
use App\Models\Department;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = User::with('department', 'directSupervisor')
            ->orderBy('name')
            ->paginate(15);

        return view('pages.admin.employees.index', [
            'title' => 'Master Pegawai',
            'employees' => $employees,
        ]);
    }

    public function create()
    {
        return view('pages.admin.employees.form', [
            'title' => 'Tambah Pegawai',
            'employee' => new User(),
            'departments' => Department::orderBy('name')->get(),
            'supervisors' => User::orderBy('name')->get(),
        ]);
    }

    public function store(EmployeeRequest $request)
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);

        User::create($data);

        return redirect()->route('admin.employees.index')->with('success', 'Pegawai berhasil ditambahkan.');
    }

    public function edit(User $employee)
    {
        return view('pages.admin.employees.form', [
            'title' => 'Edit Pegawai',
            'employee' => $employee,
            'departments' => Department::orderBy('name')->get(),
            'supervisors' => User::where('id', '!=', $employee->id)->orderBy('name')->get(),
        ]);
    }

    public function update(EmployeeRequest $request, User $employee)
    {
        $data = $request->validated();

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $employee->update($data);

        return redirect()->route('admin.employees.index')->with('success', 'Pegawai berhasil diperbarui.');
    }

    public function destroy(User $employee)
    {
        $employee->delete();

        return redirect()->route('admin.employees.index')->with('success', 'Pegawai berhasil dihapus.');
    }
}
