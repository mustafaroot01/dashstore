<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PrivacyPolicyController extends Controller
{
    public function index()
    {
        return Inertia::render('PrivacyPolicy/Index', [
            'content' => Setting::get('privacy_policy', ''),
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'content' => ['required', 'string'],
        ]);

        Setting::set('privacy_policy', $request->content);

        return back()->with('success', 'تم حفظ سياسة الخصوصية');
    }
}
