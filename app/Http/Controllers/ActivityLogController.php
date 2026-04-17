<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $logQuery = ActivityLog::with('user')->orderBy('created_at', 'desc');

        if ($request->filled('log_action')) {
            $logQuery->where('action', $request->log_action);
        }
        if ($request->filled('log_from')) {
            $logQuery->whereDate('created_at', '>=', $request->log_from);
        }
        if ($request->filled('log_to')) {
            $logQuery->whereDate('created_at', '<=', $request->log_to);
        }

        $logs       = $logQuery->paginate(25)->withQueryString();
        $logActions = ActivityLog::select('action')->distinct()->orderBy('action')->pluck('action');

        return view('activity_log.index', compact('logs', 'logActions'));
    }
}
