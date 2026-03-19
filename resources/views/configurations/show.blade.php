@extends('layouts.app')
@section('title', $configuration->name)
@section('page-icon', 'file-code')
@section('page-title', $configuration->name)
@section('breadcrumb', 'Configurations / ' . $configuration->name)

@section('content')
<div class="row g-4">
    <div class="col-xl-8">
        <!-- Config Info -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-file-earmark-code"></i> Configuration Details
                <div class="ms-auto d-flex gap-2">
                    <span class="status-badge status-{{ $configuration->status === 'active' ? 'approved' : 'draft' }}">
                        {{ ucfirst($configuration->status) }}
                    </span>
                    @can('update', $configuration)
                    <a href="{{ route('configurations.edit', $configuration) }}" class="btn btn-sm btn-outline-accent">
                        <i class="bi bi-pencil me-1"></i>Edit
                    </a>
                    @endcan
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-4">
                    @foreach([
                        ['Environment', ucfirst($configuration->environment), 'globe'],
                        ['Type', strtoupper($configuration->type), 'filetype-yml'],
                        ['Version', 'v'.$configuration->version, 'tag'],
                        ['Last Modified', $configuration->updated_at->format('d M Y H:i'), 'clock'],
                    ] as [$label, $value, $icon])
                    <div class="col-6 col-sm-3">
                        <div style="background:var(--bg-3);border:1px solid var(--border);border-radius:8px;padding:12px;">
                            <div style="font-size:0.62rem;color:var(--text-muted);letter-spacing:0.1em;text-transform:uppercase;margin-bottom:4px;">
                                <i class="bi bi-{{ $icon }} me-1"></i>{{ $label }}
                            </div>
                            <div class="mono" style="font-size:0.9rem;color:var(--accent);font-weight:500;">{{ $value }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <div style="color:var(--text-muted);font-size:0.84rem;">{{ $configuration->description ?: '—' }}</div>
                </div>

                <div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label mb-0">Configuration Content</label>
                        <div class="d-flex gap-2">
                            <span style="font-size:0.65rem;color:var(--text-muted);" class="mono">
                                {{ str_word_count($configuration->content) }} tokens
                            </span>
                            @if(auth()->user()->role === 'Admin' || auth()->user()->role === 'Developer')
                            <button class="btn btn-sm btn-outline-accent" onclick="copyContent()">
                                <i class="bi bi-clipboard me-1"></i>Copy
                            </button>
                            @endif
                        </div>
                    </div>
                    <div class="code-block" id="config-content">{{ $configuration->content }}</div>
                </div>
            </div>
        </div>

        <!-- Version History -->
        <div class="card">
            <div class="card-header"><i class="bi bi-clock-history"></i> Version History — FR1.2</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead><tr><th>Version</th><th>Changed By</th><th>Change Reason</th><th>Date</th><th>Action</th></tr></thead>
                        <tbody>
                            @forelse($configuration->versions ?? [] as $version)
                            <tr>
                                <td><span class="mono text-accent">v{{ $version->version_number }}</span></td>
                                <td>{{ $version->user->name ?? '—' }}</td>
                                <td style="font-size:0.8rem;color:var(--text-muted);">{{ Str::limit($version->change_reason, 60) }}</td>
                                <td class="mono" style="font-size:0.7rem;color:var(--text-muted);">{{ $version->created_at->format('d M Y H:i') }}</td>
                                <td>
                                    <a href="{{ route('configurations.version', [$configuration, $version->id]) }}" class="btn btn-sm btn-outline-accent">
                                        <i class="bi bi-eye me-1"></i>View
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center py-3" style="color:var(--text-muted);">No version history yet</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Panel -->
    <div class="col-xl-4">
        <!-- Change Requests for this config -->
        <div class="card mb-3">
            <div class="card-header"><i class="bi bi-arrow-repeat"></i> Change Requests</div>
            <div class="card-body p-0">
                @forelse($configuration->changeRequests ?? [] as $req)
                <div class="d-flex align-items-center gap-3 p-3 border-top-cv" style="border-top:{{ $loop->first?'none':'1px solid var(--border)' }}!important;">
                    <div class="flex-grow-1">
                        <div style="font-size:0.8rem;font-weight:600;" class="mono">#{{ str_pad($req->id,4,'0',STR_PAD_LEFT) }}</div>
                        <div style="font-size:0.7rem;color:var(--text-muted);">{{ $req->submitter->name ?? '—' }} · {{ $req->created_at->diffForHumans() }}</div>
                    </div>
                    <span class="status-badge status-{{ $req->status }}">{{ ucfirst($req->status) }}</span>
                </div>
                @empty
                <div class="p-3 text-center" style="color:var(--text-muted);font-size:0.82rem;">No change requests</div>
                @endforelse
            </div>
        </div>

        <!-- Metadata -->
        <div class="card">
            <div class="card-header"><i class="bi bi-info-circle"></i> Metadata</div>
            <div class="card-body">
                @foreach([
                    ['Created By', $configuration->creator->name ?? '—'],
                    ['Created At', $configuration->created_at->format('d M Y H:i')],
                    ['Last Modified', $configuration->lastModifier->name ?? '—'],
                    ['Read-Only Roles', 'Auditor — FR1.3'],
                    ['Encryption', 'HTTPS/SSH — NFR1.1'],
                ] as [$k,$v])
                <div class="d-flex justify-content-between py-2 border-top-cv" style="{{ $loop->first?'border-top:none!important':'' }}">
                    <span style="font-size:0.75rem;color:var(--text-muted);">{{ $k }}</span>
                    <span style="font-size:0.78rem;color:var(--text);">{{ $v }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function copyContent() {
    navigator.clipboard.writeText(document.getElementById('config-content').innerText);
    const btn = event.target.closest('button');
    btn.innerHTML = '<i class="bi bi-check me-1"></i>Copied!';
    setTimeout(() => btn.innerHTML = '<i class="bi bi-clipboard me-1"></i>Copy', 2000);
}
</script>
@endpush
