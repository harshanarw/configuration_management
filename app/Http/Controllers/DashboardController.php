<?php
namespace App\Http\Controllers;

use App\Models\{Configuration, ChangeRequest, AuditLog, User};

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard.index', [
            'totalConfigs'     => Configuration::count(),
            'pendingApprovals' => ChangeRequest::where('status','pending')->count(),
            'deployedToday'    => ChangeRequest::where('status','deployed')
                                    ->whereDate('deployed_at', today())->count(),
            'auditEvents'      => AuditLog::whereDate('created_at', today())->count(),
            'recentRequests'   => ChangeRequest::with(['configuration','submitter'])
                                    ->latest()->take(6)->get(),
            'recentAudit'      => AuditLog::with('user')->latest('created_at')->take(8)->get(),
        ]);
    }
}
