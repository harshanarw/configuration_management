@extends('layouts.app')
@section('title', 'Threat Model')
@section('page-icon', 'shield-exclamation')
@section('page-title', 'STRIDE Threat Model')
@section('breadcrumb', 'Security / Threat Model')

@section('content')
<div class="alert alert-info mb-4">
    <i class="bi bi-shield-lock me-2"></i>
    <strong>STRIDE Analysis:</strong> Spoofing · Tampering · Repudiation · Information Disclosure · Denial of Service · Elevation of Privilege
    — structured per data flow interaction as documented in Section 7 of the Configuration Management Policy.
</div>

<!-- Threat Stats -->
<div class="row g-3 mb-4">
    @php
    $threats = [
        ['T1','Spoofing Identity','User → API Gateway','Attacker impersonates a legitimate user to gain unauthorized access.','JWT tokens + Multi-Factor Authentication (MFA)','high','auth','mitigated'],
        ['T2','Tampering with Data','RBAC Module','Attacker modifies role permissions incorrectly.','Peer review & logging. Admin actions require second admin approval.','high','rbac','mitigated'],
        ['T3','Tampering with Data','Config Repository','Unauthorized modification of configuration data in repository.','Enforce signed commits. Main branch protected. Only CI/CD can push.','critical','config','mitigated'],
        ['T4','Repudiation','User → API Gateway','User denies submitting a change request or approval.','Comprehensive audit logging with user ID, timestamp, and hash chain.','medium','audit','mitigated'],
        ['T5','Repudiation','Approval Engine','Approver denies having approved a change.','Digital signatures / MFA required for final approval steps.','high','approval','mitigated'],
        ['T6','Information Disclosure','Config Repository','Sensitive config data stored in plain text (passwords, keys).','Secrets Management: integrate HashiCorp Vault. Never store secrets in repo.','critical','config','open'],
        ['T7','Information Disclosure','RBAC Module','List of users and their roles exposed through API.','Data minimization: RBAC returns only permitted/denied, not full role data.','medium','rbac','mitigated'],
        ['T8','Denial of Service','Approval Engine','Reviewer/Approver unavailable causing workflow delays.','Workflow redundancy: fallback reviewers, escalation timeouts.','medium','workflow','mitigated'],
        ['T9','Denial of Service','API Gateway','Attackers overwhelm system with requests causing disruption.','Rate limiting at API Gateway, DDoS protection.','high','network','mitigated'],
        ['T10','Elevation of Privilege','API Gateway → RBAC','Attacker bypasses RBAC checks or gains admin privileges.','Principle of least privilege, regular access reviews, immutable RBAC.','critical','rbac','mitigated'],
        ['T11','Tampering with Logs','Audit & Logging Service','Attacker modifies or deletes logs to cover their tracks.','WORM storage system. Cryptographic hash chains on log entries.','critical','audit','mitigated'],
        ['T12','Repudiation','Audit & Logging Service','System cannot prove a user took an action.','Secure logging with user ID, timestamp, and cryptographic hash log chains.','high','audit','mitigated'],
    ];
    @endphp

    @foreach([
        ['Total Threats', count($threats), 'accent', 'shield-exclamation'],
        ['Critical', collect($threats)->filter(fn($t)=>$t[6]==='critical')->count(), 'danger', 'exclamation-diamond'],
        ['High', collect($threats)->filter(fn($t)=>$t[6]==='high')->count(), 'warning', 'exclamation-triangle'],
        ['Mitigated', collect($threats)->filter(fn($t)=>$t[7]==='mitigated')->count(), 'success', 'shield-check'],
    ] as [$label,$count,$color,$icon])
    <div class="col-6 col-xl-3">
        <div class="stat-card {{ $color }}">
            <div class="stat-number">{{ $count }}</div>
            <div class="stat-label">{{ $label }}</div>
            <i class="bi bi-{{ $icon }} stat-icon"></i>
        </div>
    </div>
    @endforeach
</div>

<!-- STRIDE Threat Table -->
<div class="card mb-4">
    <div class="card-header"><i class="bi bi-table"></i> STRIDE Threat Register</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>STRIDE Type</th>
                        <th>Component</th>
                        <th>Threat Description</th>
                        <th>Mitigation</th>
                        <th>Severity</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($threats as [$id, $type, $component, $description, $mitigation, $severity, $category, $status])
                    <tr>
                        <td class="mono text-accent">{{ $id }}</td>
                        <td>
                            @php
                            $strideColors = [
                                'Spoofing'=>'status-rejected',
                                'Tampering'=>'status-pending',
                                'Repudiation'=>'status-draft',
                                'Information Disclosure'=>'status-approved',
                                'Denial of Service'=>'status-rejected',
                                'Elevation of Privilege'=>'status-rejected',
                            ];
                            $firstWord = explode(' ', $type)[0];
                            @endphp
                            <span class="status-badge {{ $strideColors[$firstWord] ?? 'status-draft' }}" style="white-space:nowrap;">
                                {{ $type }}
                            </span>
                        </td>
                        <td style="font-size:0.78rem;font-weight:600;">{{ $component }}</td>
                        <td style="font-size:0.78rem;color:var(--text-muted);max-width:200px;">{{ $description }}</td>
                        <td style="font-size:0.78rem;max-width:220px;">{{ $mitigation }}</td>
                        <td>
                            @php $sevClass = ['low'=>'status-approved','medium'=>'status-pending','high'=>'status-rejected','critical'=>'status-rejected'][$severity] ?? 'status-draft'; @endphp
                            <span class="status-badge {{ $sevClass }}"
                                  style="{{ $severity === 'critical' ? 'background:rgba(230,57,70,0.3);border-color:rgba(230,57,70,0.6);font-weight:700;' : '' }}">
                                {{ ucfirst($severity) }}
                            </span>
                        </td>
                        <td>
                            <span class="status-badge {{ $status === 'mitigated' ? 'status-approved' : 'status-rejected' }}">
                                <i class="bi bi-{{ $status === 'mitigated' ? 'shield-check' : 'exclamation-circle' }} me-1"></i>
                                {{ ucfirst($status) }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Data Flow Architecture -->
<div class="card mb-4">
    <div class="card-header"><i class="bi bi-diagram-3"></i> System Architecture & Trust Boundaries</div>
    <div class="card-body">
        <div class="row g-4">
            <!-- External Entities -->
            <div class="col-md-3">
                <div style="border:2px dashed var(--border);border-radius:10px;padding:16px;">
                    <div style="font-size:0.65rem;letter-spacing:0.12em;text-transform:uppercase;color:var(--text-muted);margin-bottom:12px;font-family:'JetBrains Mono',monospace;">External Entities</div>
                    @foreach([['A1','Developer','accent'],['A2','Reviewer','success'],['A3','Approver','warning'],['A4','Auditor','muted'],['A5','Admin','danger']] as [$id,$name,$color])
                    <div style="background:var(--bg-3);border:1px solid var(--border);border-radius:8px;padding:8px 12px;margin-bottom:8px;display:flex;justify-content:space-between;align-items:center;">
                        <span class="mono" style="font-size:0.68rem;color:var(--accent);">{{ $id }}</span>
                        <span style="font-size:0.78rem;font-weight:600;">{{ $name }}</span>
                        <span class="role-badge badge-{{ strtolower($name) }}" style="font-size:0.58rem;padding:2px 6px;border-radius:6px;font-family:'JetBrains Mono',monospace;">{{ ucfirst($name) }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Application Tier -->
            <div class="col-md-5">
                <div style="border:2px solid var(--primary);border-radius:10px;padding:16px;">
                    <div style="font-size:0.65rem;letter-spacing:0.12em;text-transform:uppercase;color:var(--primary-light);margin-bottom:12px;font-family:'JetBrains Mono',monospace;">Application Tier</div>
                    @foreach([
                        ['P1','Web Console / UI','User Interface','bi-display'],
                        ['P2','API Gateway','JWT Validation, SSL','bi-diagram-2'],
                        ['P3','RBAC Module','Access Control','bi-shield-lock'],
                        ['P4','Approval Engine','Workflow Management','bi-check2-all'],
                        ['P5','Audit & Logging','Non-Repudiation','bi-journal-text'],
                    ] as [$id,$name,$sub,$icon])
                    <div style="background:rgba(15,76,117,0.3);border:1px solid var(--primary);border-radius:8px;padding:10px 14px;margin-bottom:8px;display:flex;align-items:center;gap:12px;">
                        <i class="bi {{ $icon }}" style="color:var(--accent);font-size:1.1rem;"></i>
                        <div>
                            <div style="font-size:0.78rem;font-weight:600;color:#fff;"><span class="mono" style="color:var(--accent);">{{ $id }}</span> {{ $name }}</div>
                            <div style="font-size:0.65rem;color:var(--text-muted);">{{ $sub }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Data Layer -->
            <div class="col-md-4">
                <div style="border:2px solid var(--success);border-radius:10px;padding:16px;">
                    <div style="font-size:0.65rem;letter-spacing:0.12em;text-transform:uppercase;color:var(--success);margin-bottom:12px;font-family:'JetBrains Mono',monospace;">Data Layer</div>
                    @foreach([
                        ['D1','Config Repository','Git-Based Storage','bi-git','success'],
                        ['D2','Audit Logs','WORM Immutable Store','bi-journal-lock','accent'],
                        ['D3','Secrets Vault','HashiCorp Vault Ref.','bi-safe','warning'],
                    ] as [$id,$name,$sub,$icon,$c])
                    <div style="background:rgba(42,157,143,0.1);border:1px solid rgba(42,157,143,0.3);border-radius:8px;padding:12px 14px;margin-bottom:8px;display:flex;align-items:center;gap:12px;">
                        <i class="bi {{ $icon }}" style="color:var(--{{ $c }});font-size:1.2rem;"></i>
                        <div>
                            <div style="font-size:0.78rem;font-weight:600;color:#fff;"><span class="mono" style="color:var(--success);">{{ $id }}</span> {{ $name }}</div>
                            <div style="font-size:0.65rem;color:var(--text-muted);">{{ $sub }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="mt-3" style="background:rgba(230,57,70,0.08);border:1px solid rgba(230,57,70,0.3);border-radius:8px;padding:12px;">
                    <div style="font-size:0.68rem;color:var(--danger);font-weight:600;margin-bottom:6px;"><i class="bi bi-exclamation-diamond me-1"></i>Trust Boundary</div>
                    <div style="font-size:0.72rem;color:var(--text-muted);">All cross-boundary traffic must pass through P2 API Gateway with JWT validation and TLS encryption.</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CIA Non-Repudiation Matrix -->
<div class="card">
    <div class="card-header"><i class="bi bi-grid"></i> CIA + Non-Repudiation Matrix</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0" style="font-size:0.8rem;">
                <thead>
                    <tr>
                        <th>Feature</th>
                        <th style="color:#00b4d8;">Confidentiality</th>
                        <th style="color:#2a9d8f;">Integrity</th>
                        <th style="color:#f4a261;">Availability</th>
                        <th style="color:#e63946;">Non-Repudiation</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach([
                        ['Version-Controlled Repo', 'Secure access via encryption; restricted visibility', 'Prevent unauthorized edits; ensure rollback', 'Redundant storage and backup', 'Commit logs ensure traceability'],
                        ['RBAC', 'Role-based access ensures least privilege', 'Prevent privilege escalation', 'Controlled admin availability', 'Access logs verify user actions'],
                        ['Approval Workflow', 'Reviewer-only visibility during pending changes', 'Verified approvals prevent tampering', 'Workflow redundancy ensures no blocking', 'Audit trails confirm who approved what'],
                        ['Audit & Logging', 'WORM logs restrict unauthorized viewing', 'Hash chains prevent log tampering', 'Replicated logging service', 'Cryptographic proof of all actions'],
                    ] as [$feature, $conf, $integ, $avail, $nr])
                    <tr>
                        <td style="font-weight:600;white-space:nowrap;">{{ $feature }}</td>
                        <td style="font-size:0.75rem;color:var(--text-muted);">{{ $conf }}</td>
                        <td style="font-size:0.75rem;color:var(--text-muted);">{{ $integ }}</td>
                        <td style="font-size:0.75rem;color:var(--text-muted);">{{ $avail }}</td>
                        <td style="font-size:0.75rem;color:var(--text-muted);">{{ $nr }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
