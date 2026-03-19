<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\{User, AuditLog};

class RbacController extends Controller
{
    public function __construct()
    {
        // Only Admin — FR2.2
        $this->middleware(function ($request, $next) {
            abort_unless(auth()->user()->role === 'Admin', 403, 'Admin access required — FR2.2');
            return $next($request);
        });
    }

    public function index()
    {
        return view('rbac.index', [
            'users' => User::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'role'     => 'required|in:Admin,Developer,Reviewer,Approver,Auditor',
            'password' => 'required|min:8',
        ]);

        $user = User::create([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'role'      => $validated['role'],
            'password'  => Hash::make($validated['password']),
            'is_active' => true,
        ]);

        AuditLog::record(
            'Admin created new user: '.$user->name.' with role '.$user->role,
            'rbac', 'high', 'User', $user->id
        );

        return redirect()->route('rbac.index')->with('success', 'User created successfully.');
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'role' => 'required|in:Admin,Developer,Reviewer,Approver,Auditor',
        ]);

        $oldRole = $user->role;
        $user->update(['role' => $validated['role']]);

        // NFR2.1 — role assignment logged
        AuditLog::record(
            'Admin changed role for '.$user->name.' from '.$oldRole.' to '.$validated['role'],
            'rbac', 'high', 'User', $user->id,
            ['old_role' => $oldRole, 'new_role' => $validated['role']]
        );

        return redirect()->route('rbac.index')->with('success', 'Role updated and logged.');
    }

    public function toggle(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);

        AuditLog::record(
            'Admin '.($user->is_active ? 'activated' : 'deactivated').' user: '.$user->name,
            'rbac', 'high', 'User', $user->id
        );

        return redirect()->route('rbac.index')
                         ->with('success', 'User '.($user->is_active ? 'activated' : 'deactivated').'.');
    }
}
