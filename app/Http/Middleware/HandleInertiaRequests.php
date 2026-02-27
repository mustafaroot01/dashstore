<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $admin   = $request->user('admin');
        $webUser = $request->user(); // default web guard

        return [
            ...parent::share($request),
            'categories' => \App\Models\Category::where('is_active', true)->with('subcategories')->get(),
            'auth' => [
                'admin'   => $admin ? $admin->load('role') : null,
                'user'    => $webUser ? [
                    'id'         => $webUser->id,
                    'full_name'  => $webUser->full_name,
                    'phone'      => $webUser->phone,
                    'first_name' => $webUser->first_name,
                ] : null,
            ],
            'flash' => [
                'success' => $request->session()->get('success'),
                'error'   => $request->session()->get('error'),
            ],
            'app_settings' => [
                'dashboard_name' => \App\Models\Setting::get('dashboard_name', 'أمواج ديالى'),
                'dashboard_logo' => \App\Models\Setting::get('dashboard_logo'),
            ],
        ];
    }
}
