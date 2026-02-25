<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class AdminUserController extends Controller
{
    public function index()
    {
        $admins = Admin::with('role')->get();
        $roles = Role::all();
        
        return Inertia::render('Admins/Index', [
            'admins' => $admins,
            'roles' => $roles
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:150', 'unique:admins'],
            'password' => ['required', 'string', 'min:8'],
            'role_id' => ['nullable', 'exists:roles,id'],
        ]);

        Admin::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password), // Eloquent cast uses 'hashed' but we explicitly hash for best practices just in case
            'role_id' => $request->role_id,
        ]);

        return back()->with('success', 'تم إضافة المشرف بنجاح');
    }

    public function update(Request $request, Admin $admin)
    {
        if ($admin->id === 1) {
            return back()->with('error', 'لا يمكن التعديل على صاحب الحساب الرئيسي.');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:150', 'unique:admins,email,' . $admin->id],
            'password' => ['nullable', 'string', 'min:8'],
            'role_id' => ['nullable', 'exists:roles,id'],
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role_id' => $request->role_id,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $admin->update($data);

        return back()->with('success', 'تم تحديث بيانات المشرف بنجاح');
    }

    public function destroy(Admin $admin)
    {
        if ($admin->id === 1) {
            return back()->with('error', 'لا يمكن حذف حساب المشرف الرئيسي.');
        }

        // Don't allow an admin to delete themselves to prevent accidental lockouts
        if ($admin->id === auth('admin')->id()) {
            return back()->with('error', 'عذراً لا يمكنك حذف حسابك الشخصي أثناء تسجيل الدخول منه.');
        }

        $admin->delete();

        return back()->with('success', 'تم حذف حساب المشرف بنجاح');
    }
}
