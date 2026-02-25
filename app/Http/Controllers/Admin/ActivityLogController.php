<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = AdminLog::with('admin')
            ->when($request->search, fn ($q) => $q->where('action', 'like', "%{$request->search}%"))
            ->when($request->date,   fn ($q) => $q->whereDate('created_at', $request->date))
            ->latest('created_at')
            ->paginate(50)
            ->withQueryString();

        return Inertia::render('ActivityLog/Index', [
            'logs'    => $logs,
            'filters' => $request->only(['search', 'date']),
        ]);
    }
}
