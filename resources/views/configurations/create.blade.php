@extends('layouts.app')
@section('title', isset($configuration) ? 'Edit Configuration' : 'New Configuration')
@section('page-icon', 'plus-square')
@section('page-title', isset($configuration) ? 'Edit Configuration' : 'New Configuration')
@section('breadcrumb', 'Configurations / ' . (isset($configuration) ? 'Edit' : 'Create'))

@section('content')
<div class="row g-4">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-file-earmark-plus"></i>
                {{ isset($configuration) ? 'Edit Configuration File' : 'Create New Configuration' }}
            </div>
            <div class="card-body">
                <form method="POST" action="{{ isset($configuration) ? route('configurations.update', $configuration) : route('configurations.store') }}">
                    @csrf
                    @if(isset($configuration)) @method('PUT') @endif

                    <div class="row g-3 mb-3">
                        <div class="col-sm-8">
                            <label class="form-label">Configuration Name *</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   placeholder="e.g. database.production.env"
                                   value="{{ old('name', $configuration->name ?? '') }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label">Type *</label>
                            <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                                <option value="">Select type...</option>
                                @foreach(['env','yaml','json','ini','xml','toml','properties'] as $t)
                                <option value="{{ $t }}" {{ old('type', $configuration->type ?? '') === $t ? 'selected' : '' }}>{{ strtoupper($t) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-sm-6">
                            <label class="form-label">Environment *</label>
                            <select name="environment" class="form-select @error('environment') is-invalid @enderror" required>
                                <option value="">Select environment...</option>
                                @foreach(['production','staging','development','testing'] as $env)
                                <option value="{{ $env }}" {{ old('environment', $configuration->environment ?? '') === $env ? 'selected' : '' }}>
                                    {{ ucfirst($env) }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                @foreach(['draft','active','archived'] as $s)
                                <option value="{{ $s }}" {{ old('status', $configuration->status ?? 'draft') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <input type="text" name="description" class="form-control"
                               placeholder="Brief description of this configuration..."
                               value="{{ old('description', $configuration->description ?? '') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Configuration Content *</label>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span style="font-size:0.7rem;color:var(--text-muted);">
                                <i class="bi bi-shield-exclamation me-1" style="color:var(--warning);"></i>
                                Do NOT store plaintext secrets — use a secrets manager reference (NFR / Secrets Management)
                            </span>
                        </div>
                        <textarea name="content" rows="14" class="form-control @error('content') is-invalid @enderror"
                                  placeholder="# Configuration content&#10;# Use secret references like: DB_PASSWORD=${vault:database/prod#password}&#10;&#10;APP_ENV=production&#10;APP_DEBUG=false&#10;DB_HOST=db.internal&#10;DB_PORT=5432&#10;DB_DATABASE=app_prod&#10;DB_USERNAME=app_user&#10;DB_PASSWORD=${vault:database/prod#password}">{{ old('content', $configuration->content ?? '') }}</textarea>
                        @error('content')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Change Reason *</label>
                        <textarea name="change_reason" rows="3" class="form-control @error('change_reason') is-invalid @enderror"
                                  placeholder="Describe the reason for this change, expected impact, and rollback plan (FR3.1)..." required>{{ old('change_reason') }}</textarea>
                        <div style="font-size:0.7rem;color:var(--text-muted);margin-top:4px;">
                            <i class="bi bi-info-circle me-1"></i>FR3.1: All change requests must include reason, impact, and rollback plan.
                        </div>
                        @error('change_reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-accent">
                            <i class="bi bi-send me-2"></i>
                            {{ isset($configuration) ? 'Submit Change Request' : 'Create & Submit for Review' }}
                        </button>
                        <a href="{{ route('configurations.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Policy Sidebar -->
    <div class="col-xl-4">
        <div class="card mb-3">
            <div class="card-header"><i class="bi bi-info-circle"></i> Policy Requirements</div>
            <div class="card-body" style="font-size:0.8rem;">
                <div class="mb-3 pb-3 border-top-cv" style="border-top:none!important;">
                    <div class="text-accent fw-bold mb-1">FR1.1 — Version Control</div>
                    <div style="color:var(--text-muted);">Every change is committed to the version-controlled repository with full history.</div>
                </div>
                <div class="mb-3 pb-3 border-top-cv">
                    <div class="text-accent fw-bold mb-1">FR1.2 — History Tracking</div>
                    <div style="color:var(--text-muted);">User ID and timestamp recorded for every commit automatically.</div>
                </div>
                <div class="mb-3 pb-3 border-top-cv">
                    <div class="text-accent fw-bold mb-1">FR3.1 — Change Reason</div>
                    <div style="color:var(--text-muted);">All requests must include reason, impact assessment, and rollback plan.</div>
                </div>
                <div class="border-top-cv pt-3">
                    <div style="color:var(--warning);" class="fw-bold mb-1"><i class="bi bi-shield-exclamation me-1"></i>Secrets Policy</div>
                    <div style="color:var(--text-muted);">Never store credentials in plain text. Use <code class="text-accent">${'{'}vault:path#key{'}'}</code> references.</div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><i class="bi bi-diagram-3"></i> Approval Flow</div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item success">
                        <div class="timeline-time">Step 1</div>
                        <div class="timeline-text"><strong>Submit</strong> — Developer creates change request</div>
                    </div>
                    <div class="timeline-item warning">
                        <div class="timeline-time">Step 2 — FR3.2</div>
                        <div class="timeline-text"><strong>Reviewer</strong> reviews and approves/rejects</div>
                    </div>
                    <div class="timeline-item warning">
                        <div class="timeline-time">Step 3 — FR3.2</div>
                        <div class="timeline-text"><strong>Approver</strong> gives final authorization</div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-time">Step 4 — FR3.3</div>
                        <div class="timeline-text"><strong>Auto-Deploy</strong> triggered upon full approval</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
