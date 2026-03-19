@extends('layouts.app')
@section('title', 'RBAC Management')
@section('page-icon', 'people')
@section('page-title', 'Role-Based Access Control')
@section('breadcrumb', 'Admin / RBAC Management')

@section('content')
<div class="alert alert-warning mb-4">
    <i class="bi bi-shield-exclamation me-2"></i>
    <strong>FR2.2:</strong> Only Admins can modify access control settings.
    <strong>NFR2.1:</strong> All role assignments are validated and logged.
    <strong>NFR2.2:</strong> Integrated with centralized authentication.
</div>

<!-- Role Permission Matrix -->
<div class="card mb-4">
    <div class="card-header"><i class="bi bi-table"></i> Role Permission Matrix — FR2.1</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0" style="font-size:0.8rem;">
                <thead>
                    <tr>
                        <th>Permission</th>
                        <th style="color:#e63946;text-align:center;">Admin</th>
                        <th style="color:#2a9d8f;text-align:center;">Developer</th>
                        <th style="color:#f4a261;text-align:center;">Reviewer</th>
                        <th style="color:#00b4d8;text-align:center;">Approver</th>
                        <th style="color:#6b82a4;text-align:center;">Auditor</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                    $matrix = [
                        ['View Configs',             true, true, true, true, true],
                        ['Create Config',            true, true, false, false, false],
                        ['Edit Config',              true, true, false, false, false],
                        ['Delete Config',            true, false, false, false, false],
                        ['Submit Change Request',    true, true, false, false, false],
                        ['Review (Level 1)',         true, false, true, false, false],
                        ['Approve (Level 2)',        true, false, false, true, false],
                        ['Deploy Config',            true, false, false, false, false],
                        ['View Audit Logs',          true, false, false, false, true],
                        ['Export Audit Logs',        true, false, false, false, true],
                        ['Manage Roles (RBAC)',      true, false, false, false, false],
                        ['View Threat Model',        true, true, true, true, true],
                        ['Manage Threats',           true, false, false, false, false],
                        ['Read-Only Config Access',  true, false, true, true, true],
                    ];
                    @endphp
                    @foreach($matrix as [$perm, $admin, $dev, $rev, $app, $aud])
                    <tr>
                        <td style="font-weight:500;">{{ $perm }}</td>
                        @foreach([$admin,$dev,$rev,$app,$aud] as $allowed)
                        <td style="text-align:center;">
                            @if($allowed)
                                <i class="bi bi-check-circle-fill" style="color:var(--success);"></i>
                            @else
                                <i class="bi bi-dash-circle" style="color:var(--text-dim);"></i>
                            @endif
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- User Management -->
<div class="row g-4">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-people"></i> User Management
                <button class="btn btn-sm btn-accent ms-auto" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="bi bi-person-plus me-1"></i>Add User
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr><th>User</th><th>Email</th><th>Role</th><th>Status</th><th>Last Login</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="user-avatar" style="width:28px;height:28px;font-size:0.7rem;">{{ strtoupper(substr($user->name,0,1)) }}</div>
                                        <div style="font-size:0.84rem;font-weight:600;">{{ $user->name }}</div>
                                    </div>
                                </td>
                                <td class="mono" style="font-size:0.75rem;color:var(--text-muted);">{{ $user->email }}</td>
                                <td>
                                    <span class="role-badge badge-{{ strtolower($user->role) }}" style="font-size:0.65rem;padding:3px 9px;border-radius:10px;font-family:'JetBrains Mono',monospace;">
                                        {{ $user->role }}
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge {{ $user->is_active ? 'status-approved' : 'status-rejected' }}">
                                        {{ $user->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="mono" style="font-size:0.7rem;color:var(--text-muted);">
                                    {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}
                                </td>
                                <td>
                                    @if(auth()->id() !== $user->id)
                                    <div class="d-flex gap-1">
                                        <button class="btn btn-sm btn-outline-accent" data-bs-toggle="modal"
                                                data-bs-target="#editRoleModal"
                                                data-user-id="{{ $user->id }}"
                                                data-user-name="{{ $user->name }}"
                                                data-user-role="{{ $user->role }}">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <form method="POST" action="{{ route('rbac.toggle', $user) }}" style="display:inline;">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="btn btn-sm {{ $user->is_active ? 'btn-outline-danger' : 'btn-outline-success' }}"
                                                    onclick="return confirm('{{ $user->is_active ? 'Deactivate' : 'Activate' }} this user?')">
                                                <i class="bi bi-{{ $user->is_active ? 'person-slash' : 'person-check' }}"></i>
                                            </button>
                                        </form>
                                    </div>
                                    @else
                                    <span style="font-size:0.72rem;color:var(--text-muted);">Current user</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center py-4" style="color:var(--text-muted);">No users found</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Role Descriptions -->
    <div class="col-xl-4">
        <div class="card">
            <div class="card-header"><i class="bi bi-info-circle"></i> Role Definitions — FR2.1</div>
            <div class="card-body">
                @foreach([
                    ['Admin', 'danger', 'Full system access. Manages RBAC. Can approve and deploy any change.'],
                    ['Developer', 'accent', 'Creates and submits configuration change requests. Read access to all configs.'],
                    ['Reviewer', 'success', 'Level 1 approval. Reviews change requests and passes to Approver.'],
                    ['Approver', 'warning', 'Level 2 final approval. Triggers automated deployment upon sign-off.'],
                    ['Auditor', 'muted', 'Read-only access to audit logs and configuration history. No write access (FR1.3).'],
                ] as [$role, $color, $desc])
                <div class="mb-3 pb-3 border-top-cv" style="{{ $loop->first ? 'border-top:none!important' : '' }}">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="role-badge badge-{{ strtolower($role) }}" style="font-size:0.65rem;padding:3px 9px;border-radius:10px;font-family:'JetBrains Mono',monospace;">{{ $role }}</span>
                    </div>
                    <div style="font-size:0.78rem;color:var(--text-muted);">{{ $desc }}</div>
                </div>
                @endforeach
                <div class="pt-2 border-top-cv">
                    <div style="font-size:0.7rem;color:var(--text-muted);">
                        <i class="bi bi-shield me-1 text-accent"></i>NFR2.2: Roles integrate with LDAP/SSO authentication
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Role Modal -->
<div class="modal fade" id="editRoleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-person-gear me-2"></i>Edit User Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="" id="editRoleForm">
                @csrf @method('PATCH')
                <div class="modal-body">
                    <p style="font-size:0.84rem;color:var(--text-muted);" class="mb-3">
                        Changing role for: <strong id="editUserName" style="color:var(--text);"></strong>
                    </p>
                    <div class="mb-3">
                        <label class="form-label">New Role</label>
                        <select name="role" id="editUserRole" class="form-select" required>
                            @foreach(['Admin','Developer','Reviewer','Approver','Auditor'] as $r)
                            <option value="{{ $r }}">{{ $r }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="alert alert-warning" style="font-size:0.78rem;">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        NFR2.1: This role change will be logged and a second admin approval may be required.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-accent">Update Role</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-person-plus me-2"></i>Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('rbac.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select name="role" class="form-select" required>
                            @foreach(['Developer','Reviewer','Approver','Auditor'] as $r)
                            <option value="{{ $r }}">{{ $r }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Temporary Password</label>
                        <input type="password" name="password" class="form-control" required minlength="8">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-accent">Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('editRoleModal').addEventListener('show.bs.modal', function(e) {
    const btn = e.relatedTarget;
    document.getElementById('editUserName').textContent = btn.dataset.userName;
    document.getElementById('editUserRole').value = btn.dataset.userRole;
    document.getElementById('editRoleForm').action = '/rbac/' + btn.dataset.userId;
});
</script>
@endpush
