@extends('layouts.app')
@section('title', 'Config Repository')
@section('page-icon', 'file-code')
@section('page-title', 'Configuration Repository')
@section('breadcrumb', 'Configurations')

@section('content')
<!-- Header Actions -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <p style="font-size:0.82rem;color:var(--text-muted);margin:0;">
            Version-controlled storage for all configuration items — FR1.1, FR1.2, FR1.3
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('configurations.create') }}" class="btn btn-accent">
            <i class="bi bi-plus-circle me-2"></i>New Configuration
        </a>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-sm-4">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Config name, key..." value="{{ request('search') }}">
            </div>
            <div class="col-sm-3">
                <label class="form-label">Environment</label>
                <select name="environment" class="form-select">
                    <option value="">All Environments</option>
                    <option value="production" {{ request('environment') === 'production' ? 'selected' : '' }}>Production</option>
                    <option value="staging"    {{ request('environment') === 'staging'    ? 'selected' : '' }}>Staging</option>
                    <option value="development"{{ request('environment') === 'development' ? 'selected' : '' }}>Development</option>
                    <option value="testing"    {{ request('environment') === 'testing'    ? 'selected' : '' }}>Testing</option>
                </select>
            </div>
            <div class="col-sm-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="active"   {{ request('status') === 'active'    ? 'selected' : '' }}>Active</option>
                    <option value="draft"    {{ request('status') === 'draft'     ? 'selected' : '' }}>Draft</option>
                    <option value="archived" {{ request('status') === 'archived'  ? 'selected' : '' }}>Archived</option>
                </select>
            </div>
            <div class="col-sm-2">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search me-1"></i>Filter</button>
            </div>
        </form>
    </div>
</div>

<!-- Config Table -->
<div class="card">
    <div class="card-header">
        <i class="bi bi-git"></i> Configuration Files
        <span class="ms-2 mono" style="font-size:0.7rem;color:var(--text-muted);">{{ $configurations->total() }} items</span>
        <div class="ms-auto d-flex gap-2">
            <span class="status-badge status-approved"><i class="bi bi-lock-fill me-1"></i>HTTPS/SSH Encrypted — NFR1.1</span>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Environment</th>
                        <th>Type</th>
                        <th>Version</th>
                        <th>Status</th>
                        <th>Last Modified By</th>
                        <th>Modified At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($configurations as $config)
                    <tr>
                        <td>
                            <div style="font-weight:600;color:#fff;">
                                <i class="bi bi-file-earmark-code me-2" style="color:var(--accent);"></i>{{ $config->name }}
                            </div>
                            <div class="mono" style="font-size:0.68rem;color:var(--text-muted);">{{ Str::limit($config->description, 50) }}</div>
                        </td>
                        <td>
                            @php $envColors = ['production'=>'danger','staging'=>'warning','development'=>'accent','testing'=>'success']; $ec = $envColors[$config->environment] ?? 'accent'; @endphp
                            <span class="status-badge status-{{ $ec === 'accent' ? 'deployed' : ($ec === 'danger' ? 'rejected' : ($ec === 'warning' ? 'pending' : 'approved')) }}">
                                {{ ucfirst($config->environment) }}
                            </span>
                        </td>
                        <td class="mono" style="font-size:0.75rem;color:var(--text-muted);">{{ strtoupper($config->type) }}</td>
                        <td>
                            <span class="mono" style="font-size:0.78rem;color:var(--accent);">v{{ $config->version }}</span>
                        </td>
                        <td><span class="status-badge status-{{ $config->status === 'active' ? 'approved' : ($config->status === 'draft' ? 'draft' : 'pending') }}">{{ ucfirst($config->status) }}</span></td>
                        <td style="font-size:0.82rem;">{{ $config->lastModifier->name ?? '—' }}</td>
                        <td class="mono" style="font-size:0.7rem;color:var(--text-muted);">{{ $config->updated_at->format('d M Y H:i') }}</td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('configurations.show', $config) }}" class="btn btn-sm btn-outline-accent" title="View"><i class="bi bi-eye"></i></a>
                                @can('update', $config)
                                <a href="{{ route('configurations.edit', $config) }}" class="btn btn-sm btn-outline-accent" title="Edit"><i class="bi bi-pencil"></i></a>
                                @endcan
                                <a href="{{ route('configurations.history', $config) }}" class="btn btn-sm btn-outline-accent" title="History"><i class="bi bi-clock-history"></i></a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5" style="color:var(--text-muted);">
                            <i class="bi bi-folder2-open" style="font-size:2rem;display:block;margin-bottom:12px;"></i>
                            No configurations found. <a href="{{ route('configurations.create') }}" class="text-accent">Create your first config</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($configurations->hasPages())
    <div class="card-body border-top-cv py-3">
        {{ $configurations->links() }}
    </div>
    @endif
</div>
@endsection
