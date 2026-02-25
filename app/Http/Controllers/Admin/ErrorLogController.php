<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Inertia\Inertia;

class ErrorLogController extends Controller
{
    public function index()
    {
        $logPath = storage_path('logs/laravel.log');
        $logs = [];

        if (File::exists($logPath)) {
            $content = File::get($logPath);
            // Splitting logs by the date pattern [YYYY-MM-DD HH:MM:SS]
            $pattern = '/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]/m';
            preg_match_all($pattern, $content, $matches, PREG_OFFSET_CAPTURE);
            
            $entries = [];
            foreach ($matches[0] as $index => $match) {
                $start = $match[1];
                $end = isset($matches[0][$index + 1]) ? $matches[0][$index + 1][1] : strlen($content);
                $entries[] = trim(substr($content, $start, $end - $start));
            }

            // Parse entries
            foreach (array_reverse($entries) as $entry) {
                // Match the format: [YYYY-MM-DD HH:MM:SS] ENV.LEVEL: Message
                if (preg_match('/^\[(.*?)\] (.*?)\.(.*?): (.*?)$/s', $entry, $parts)) {
                    $logs[] = [
                        'date'    => $parts[1],
                        'env'     => $parts[2],
                        'level'   => $parts[3],
                        'message' => trim($parts[4]),
                    ];
                }
                if (count($logs) >= 100) break; // Limit to last 100 logs
            }
        }

        return Inertia::render('ErrorLog/Index', ['logs' => $logs]);
    }

    public function clear()
    {
        $logPath = storage_path('logs/laravel.log');
        if (File::exists($logPath)) {
            File::put($logPath, '');
        }
        return back()->with('success', 'تم مسح السجل بنجاح');
    }
}
