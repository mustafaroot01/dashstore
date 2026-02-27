<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class SettingController extends Controller
{
    public function index()
    {
        return Inertia::render('Settings/Index', [
            'settings' => Setting::getAllKeyed(),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'otp_channel'     => ['required', 'in:whatsapp,sms'],
            'invoice_name'    => ['required', 'string', 'max:100'],
            'invoice_phone'   => ['required', 'string', 'max:20'],
            'invoice_address' => ['required', 'string', 'max:255'],
            'invoice_footer'  => ['nullable', 'string', 'max:500'],
            'invoice_logo'    => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'invoice_prefix'  => ['nullable', 'string', 'max:10'],
            'dashboard_name'     => ['nullable', 'string', 'max:50'],
            'dashboard_logo'     => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'telegram_bot_token' => ['nullable', 'string', 'max:255'],
            'telegram_chat_id'   => ['nullable', 'string', 'max:255'],
        ]);

        if ($request->hasFile('invoice_logo')) {
            $oldInvoice = Setting::get('invoice_logo');
            if ($oldInvoice) Storage::disk('public')->delete($oldInvoice);
            $data['invoice_logo'] = $request->file('invoice_logo')->store('settings', 'public');
        }

        if ($request->hasFile('dashboard_logo')) {
            $oldDash = Setting::get('dashboard_logo');
            if ($oldDash) Storage::disk('public')->delete($oldDash);
            $data['dashboard_logo'] = $request->file('dashboard_logo')->store('settings', 'public');
        }

        foreach ($data as $key => $value) {
            if ($value !== null) {
                Setting::set($key, $value);
            }
        }

        return back()->with('success', 'تم حفظ الإعدادات');
    }
}
