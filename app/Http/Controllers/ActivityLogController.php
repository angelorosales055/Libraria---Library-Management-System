<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Carbon\Carbon;

class ActivityLogController extends Controller
{
    public function index()
    {
        $totalActivities = ActivityLog::count();
        $userLogins = ActivityLog::forType('login')->count();
        $bookActions = ActivityLog::whereIn('type', ['checkout', 'return', 'renew', 'approval'])->count();
        $todayActivity = ActivityLog::today()->count();

        $logs = ActivityLog::with('user')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $typeBreakdown = ActivityLog::selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->orderByDesc('count')
            ->get();

        return view('activity-log.index', compact(
            'totalActivities',
            'userLogins',
            'bookActions',
            'todayActivity',
            'logs',
            'typeBreakdown'
        ));
    }
}

