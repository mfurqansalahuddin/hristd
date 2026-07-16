<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\EmployeeRequest;
use App\Models\Department;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class EmployeeController extends Controller
{
    public function index()
    {
        return view('pages.admin.employees.index', [
            'title' => 'Data Pegawai',
        ]);
    }

    public function create()
    {
        return view('pages.admin.employees.form', [
            'title' => 'Tambah Pegawai',
            'employee' => new User,
            'departments' => $this->departmentOptions(),
        ]);
    }

    public function store(EmployeeRequest $request)
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('employees', 'public');
        }
        unset($data['photo']);

        User::create($data);

        return redirect()->route('admin.employees.index')->with('success', 'Pegawai berhasil ditambahkan.');
    }

    public function edit(User $employee)
    {
        return view('pages.admin.employees.form', [
            'title' => 'Edit Pegawai',
            'employee' => $employee,
            'departments' => $this->departmentOptions($employee),
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

        if ($request->hasFile('photo')) {
            if ($employee->photo_path) {
                Storage::disk('public')->delete($employee->photo_path);
            }
            $data['photo_path'] = $request->file('photo')->store('employees', 'public');
        }
        unset($data['photo']);

        $employee->update($data);

        return redirect()->route('admin.employees.index')->with('success', 'Pegawai berhasil diperbarui.');
    }

    public function destroy(User $employee)
    {
        $employee->delete();

        return redirect()->route('admin.employees.index')->with('success', 'Pegawai berhasil dihapus.');
    }

    /**
     * Daftar departemen untuk dropdown kaskade pada form pegawai (§6 plan.md). Departemen
     * yang sudah punya kepala aktif (job_level 1-3) tetap ditampilkan dan tetap bisa dipilih,
     * hanya ditandai `occupied_by` sebagai peringatan (bukan dikunci) — supaya mutasi/ganti
     * jabatan tidak pernah terhalang validasi, admin cukup diberi tahu siapa yang perlu
     * dipindah lebih dulu.
     */
    private function departmentOptions(?User $employee = null): Collection
    {
        $departments = Department::orderBy('name')->get(['id', 'name', 'type', 'parent_department_id']);
        $namesById = $departments->pluck('name', 'id');

        $headsByDepartment = User::whereIn('job_level', [1, 2, 3])
            ->when($employee?->exists, fn ($query) => $query->where('id', '!=', $employee->id))
            ->get(['name', 'department_id'])
            ->keyBy('department_id');

        return $departments->map(fn (Department $department) => [
            'id' => $department->id,
            'name' => $department->name,
            'type' => $department->type,
            'parent_name' => $namesById->get($department->parent_department_id),
            'occupied_by' => $headsByDepartment->get($department->id)?->name,
        ]);
    }
}
