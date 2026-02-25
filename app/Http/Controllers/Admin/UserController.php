<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::withCount('orders')
            ->when($request->search, fn ($q) => $q->where(function ($q2) use ($request) {
                $q2->where('first_name', 'like', "%{$request->search}%")
                   ->orWhere('last_name', 'like', "%{$request->search}%")
                   ->orWhere('phone', 'like', "%{$request->search}%");
            }))
            ->latest()
            ->paginate(50)
            ->withQueryString();

        return Inertia::render('Users/Index', [
            'users'   => $users,
            'filters' => $request->only('search'),
        ]);
    }

    public function updatePassword(Request $request, User $user)
    {
        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'password.required'  => 'كلمة المرور مطلوبة',
            'password.min'       => 'كلمة المرور يجب أن تكون 8 أحرف على الأقل',
            'password.confirmed' => 'تأكيد كلمة المرور غير متطابق',
        ]);

        $user->update(['password' => Hash::make($request->password)]);
        return back()->with('success', 'تم تغيير كلمة المرور');
    }

    public function toggleActive(User $user)
    {
        $user->update(['is_active' => ! $user->is_active]);
        return back()->with('success', $user->is_active ? 'تم تفعيل الحساب' : 'تم إيقاف الحساب');
    }

    public function destroy(User $user)
    {
        try {
            $user->delete();
            return back()->with('success', 'تم حذف الحساب نهائياً');
        } catch (\Illuminate\Database\QueryException $e) {
            return back()->with('error', 'لا يمكن حذف هذا المستخدم لأن لديه طلبات سابقة في النظام. يمكنك إيقاف حسابه بدلاً من مسحه.');
        }
    }
}
