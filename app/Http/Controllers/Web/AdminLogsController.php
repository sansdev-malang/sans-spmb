<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class AdminLogsController extends Controller
{
    public function index(Request $request)
    {
        $logPath = storage_path('logs/laravel.log');
        $logs = [];

        if (File::exists($logPath)) {
            $fileContent = File::get($logPath);
            // Split content into individual log entries
            // Laravel logs entries start with timestamp in format [YYYY-MM-DD HH:MM:SS]
            $pattern = '/\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\]/';
            
            // Find all timestamps
            preg_match_all($pattern, $fileContent, $matches, PREG_OFFSET_CAPTURE);
            
            $offsets = $matches[0];
            $count = count($offsets);
            
            for ($i = 0; $i < $count; $i++) {
                $start = $offsets[$i][1];
                $end = ($i < $count - 1) ? $offsets[$i + 1][1] : strlen($fileContent);
                $entry = substr($fileContent, $start, $end - $start);
                
                // Parse timestamp, level, env, message, trace
                if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]\s+(\w+)\.(\w+):\s+(.*)$/s', $entry, $entryMatches)) {
                    $timestamp = $entryMatches[1];
                    $env = $entryMatches[2];
                    $level = strtoupper($entryMatches[3]);
                    $restOfMessage = $entryMatches[4];
                    
                    // Split message and trace
                    $lines = explode("\n", $restOfMessage);
                    $message = array_shift($lines);
                    $trace = implode("\n", $lines);
                    
                    $logs[] = [
                        'timestamp' => $timestamp,
                        'env' => $env,
                        'level' => $level,
                        'message' => trim($message),
                        'trace' => trim($trace)
                    ];
                }
            }
            
            // Show newest first
            $logs = array_reverse($logs);
        }

        // Apply filters
        $search = $request->input('search');
        $level = $request->input('level');

        if ($search || $level) {
            $logs = array_filter($logs, function ($log) use ($search, $level) {
                if ($level && $log['level'] !== strtoupper($level)) {
                    return false;
                }
                if ($search) {
                    $searchLower = strtolower($search);
                    $msgMatch = strpos(strtolower($log['message']), $searchLower) !== false;
                    $traceMatch = strpos(strtolower($log['trace']), $searchLower) !== false;
                    $timeMatch = strpos(strtolower($log['timestamp']), $searchLower) !== false;
                    if (!$msgMatch && !$traceMatch && !$timeMatch) {
                        return false;
                    }
                }
                return true;
            });
        }

        // Paginate manually (slice array)
        $total = count($logs);
        
        $limit = $request->input('limit', '50');
        if ($limit === 'all') {
            $perPage = $total > 0 ? $total : 50;
        } else {
            $perPage = intval($limit);
            if ($perPage < 5) $perPage = 50;
        }

        $page = intval($request->input('page', 1));
        if ($page < 1) $page = 1;
        $offset = ($page - 1) * $perPage;
        
        $paginatedLogs = array_slice($logs, $offset, $perPage);
        $totalPages = $perPage > 0 ? ceil($total / $perPage) : 1;

        return view('admin.logs', [
            'logs' => $paginatedLogs,
            'total' => $total,
            'page' => $page,
            'totalPages' => $totalPages,
            'search' => $search,
            'level' => $level,
            'limit' => $limit
        ]);
    }

    public function clear()
    {
        $logPath = storage_path('logs/laravel.log');
        if (File::exists($logPath)) {
            File::put($logPath, '');
        }
        return redirect()->route('admin.logs')->with('success', 'Log sistem berhasil dibersihkan.');
    }
}
