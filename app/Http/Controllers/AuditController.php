<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{AuditLog, User};

class AuditController extends Controller
{
    public function index(Request $request)
    {
        // Only Admin and Auditor — FR1.3, FR2.1
        abort_unless(in_array(auth()->user()->role, ['Admin','Auditor']), 403);

        $query = AuditLog::with('user');

        if ($request->user_id)    $query->where('user_id', $request->user_id);
        if ($request->severity)   $query->where('severity', $request->severity);
        if ($request->event_type) $query->where('event_type', $request->event_type);
        if ($request->date_from)  $query->whereDate('created_at', '>=', $request->date_from);

        $stats = [
            'total'  => AuditLog::count(),
            'high'   => AuditLog::whereIn('severity',['high','critical'])->count(),
            'auth'   => AuditLog::where('event_type','auth')->count(),
            'config' => AuditLog::where('event_type','config')->count(),
        ];

        return view('audit.index', [
            'logs'  => $query->latest('created_at')->paginate(25),
            'users' => User::orderBy('name')->get(),
            'stats' => $stats,
        ]);
    }
}
