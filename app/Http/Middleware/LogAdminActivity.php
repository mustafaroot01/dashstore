<?php

namespace App\Http\Middleware;

use App\Models\AdminLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogAdminActivity
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if ($request->method() !== 'GET' && Auth::guard('admin')->check()) {
            try {
                $this->log($request);
            } catch (\Throwable $e) {
                \Log::error('Admin log failed: ' . $e->getMessage());
            }
        }

        return $response;
    }

    private function log(Request $request): void
    {
        $admin = Auth::guard('admin')->user();

        AdminLog::create([
            'admin_id'   => $admin->id,
            'action'     => $this->describeAction($request),
            'ip_address' => $request->ip(),
            'device'     => $this->detectDevice($request->userAgent() ?? ''),
            'browser'    => $this->detectBrowser($request->userAgent() ?? ''),
        ]);
    }

    private function describeAction(Request $request): string
    {
        $method = $request->method();
        $path   = $request->path(); // e.g. "panel/products/5"
        $segs   = explode('/', $path); // ['panel','products','5']

        // Extract resource and ID from path
        // panel / resource / {id} / sub-action
        $resource  = $segs[1] ?? '';
        $id        = is_numeric($segs[2] ?? '') ? $segs[2] : null;
        $subAction = $segs[3]  ?? ($segs[2] ?? '');

        $resourceAr = [
            'products'     => 'منتج',
            'categories'   => 'قسم',
            'orders'       => 'طلب',
            'districts'    => 'قضاء',
            'users'        => 'مستخدم',
            'sliders'      => 'سلايدة',
            'coupons'      => 'كوبون',
            'settings'     => 'الإعدادات',
            'activity-log' => 'سجل النشاط',
            'login'        => 'تسجيل الدخول',
            'logout'       => 'تسجيل الخروج',
        ][$resource] ?? $resource;

        // Special sub-actions
        if ($subAction === 'status')               return "تغيير حالة {$resourceAr} #{$id}";
        if ($subAction === 'toggle-active')        return "تبديل حالة تفعيل {$resourceAr}" . ($id ? " #{$id}" : '');
        if ($subAction === 'toggle-availability')  return "تبديل توفر {$resourceAr} #{$id}";
        if ($subAction === 'password')             return "تغيير كلمة مرور مستخدم #{$id}";
        if ($subAction === 'reorder')              return "إعادة ترتيب {$resourceAr}";

        // Standard CRUD
        if ($resource === 'logout')  return 'قام بتسجيل الخروج';
        if ($resource === 'login')   return 'قام بتسجيل الدخول';

        return match($method) {
            'POST'   => $id ? "تعديل {$resourceAr} #{$id}"  : "إضافة {$resourceAr} جديد",
            'PUT',
            'PATCH'  => "تعديل {$resourceAr}" . ($id ? " #{$id}" : ''),
            'DELETE' => "حذف {$resourceAr}" . ($id ? " #{$id}" : ''),
            default  => "{$method} {$path}",
        };
    }

    private function detectDevice(string $ua): string
    {
        if (str_contains($ua, 'Windows'))   return 'Windows';
        if (str_contains($ua, 'Macintosh')) return 'Mac';
        if (str_contains($ua, 'Linux'))     return 'Linux';
        if (str_contains($ua, 'Android'))   return 'Android';
        if (str_contains($ua, 'iPhone'))    return 'iPhone';
        return 'Unknown';
    }

    private function detectBrowser(string $ua): string
    {
        if (str_contains($ua, 'Edg'))     return 'Edge';
        if (str_contains($ua, 'Chrome'))  return 'Chrome';
        if (str_contains($ua, 'Firefox')) return 'Firefox';
        if (str_contains($ua, 'Safari'))  return 'Safari';
        return 'Unknown';
    }
}
