<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RoleController extends Controller
{
    // List of all possible permissions in the system
    public static $permissionsList = [
        'manage_orders' => 'إدارة وتعديل الطلبات',
        'manage_products' => 'إضافة/تعديل المنتجات والأقسام',
        'manage_users' => 'إدارة الزبائن',
        'manage_content' => 'إدارة السلايدات الترويجية',
        'manage_financial' => 'إدارة الكوبونات وتفاصيل المبيعات',
        'manage_inventory' => 'إدارة التقارير والجرد',
        'manage_settings' => 'إدارة الإعدادات العامة للشركة',
        'manage_admins_roles' => 'إدارة المشرفين والصلاحيات',
    ];

    public function index()
    {
        $roles = Role::withCount('admins')->get();
        return Inertia::render('Roles/Index', [
            'roles' => $roles,
            'permissionsList' => self::$permissionsList
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles'],
            'permissions' => ['nullable', 'array'],
        ]);

        Role::create([
            'name' => $request->name,
            'permissions' => $request->permissions ?? [],
        ]);

        return back()->with('success', 'تم إضافة الدور بنجاح');
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name,' . $role->id],
            'permissions' => ['nullable', 'array'],
        ]);

        $role->update([
            'name' => $request->name,
            'permissions' => $request->permissions ?? [],
        ]);

        return back()->with('success', 'تم تحديث بيانات الدور بنجاح');
    }

    public function destroy(Role $role)
    {
        if ($role->admins()->count() > 0) {
            return back()->with('error', 'لا يمكن حذف هذا الدور لأنه مرتبط بمدراء (مشرفين) حاليين. يرجى تغيير أدوارهم أولاً.');
        }

        $role->delete();

        return back()->with('success', 'تم حذف الدور بنجاح');
    }
}
