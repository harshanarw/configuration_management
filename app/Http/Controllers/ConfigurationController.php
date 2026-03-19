<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Configuration, ConfigVersion, AuditLog};

class ConfigurationController extends Controller
{
    public function index(Request $request)
    {
        $query = Configuration::with(['creator','lastModifier']);

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                  ->orWhere('description', 'like', '%'.$request->search.'%');
            });
        }
        if ($request->environment) $query->where('environment', $request->environment);
        if ($request->status)      $query->where('status', $request->status);

        return view('configurations.index', [
            'configurations' => $query->latest()->paginate(15),
        ]);
    }

    public function create()
    {
        $this->authorize('create', Configuration::class);
        return view('configurations.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Configuration::class);

        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'type'          => 'required|in:env,yaml,json,ini,xml,toml,properties',
            'environment'   => 'required|in:production,staging,development,testing',
            'content'       => 'required|string',
            'description'   => 'nullable|string|max:500',
            'status'        => 'in:draft,active,archived',
            'change_reason' => 'required|string|min:10',
        ]);

        $config = Configuration::create([
            ...$validated,
            'version'          => '1.0.0',
            'created_by'       => auth()->id(),
            'last_modified_by' => auth()->id(),
        ]);

        // Save first version — FR1.1
        ConfigVersion::create([
            'configuration_id' => $config->id,
            'user_id'          => auth()->id(),
            'version_number'   => '1.0.0',
            'content'          => $validated['content'],
            'change_reason'    => $validated['change_reason'],
        ]);

        // Submit change request — FR3.1
        $config->changeRequests()->create([
            'submitted_by'  => auth()->id(),
            'change_reason' => $validated['change_reason'],
            'status'        => 'pending',
        ]);

        // Audit log — FR2.3
        AuditLog::record(
            'Created configuration: '.$config->name,
            'config', 'medium', $config->name, $config->id
        );

        return redirect()->route('configurations.show', $config)
                         ->with('success', 'Configuration created and submitted for review.');
    }

    public function show(Configuration $configuration)
    {
        $configuration->load(['creator','lastModifier','changeRequests.submitter','versions.user']);
        return view('configurations.show', compact('configuration'));
    }

    public function edit(Configuration $configuration)
    {
        $this->authorize('update', $configuration);
        return view('configurations.create', compact('configuration'));
    }

    public function update(Request $request, Configuration $configuration)
    {
        $this->authorize('update', $configuration);

        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'type'          => 'required|in:env,yaml,json,ini,xml,toml,properties',
            'environment'   => 'required|in:production,staging,development,testing',
            'content'       => 'required|string',
            'description'   => 'nullable|string|max:500',
            'status'        => 'in:draft,active,archived',
            'change_reason' => 'required|string|min:10',
        ]);

        // Increment version — FR1.1
        $configuration->incrementVersion();
        $newVersion = $configuration->version;

        $configuration->update([
            ...$validated,
            'version'          => $newVersion,
            'last_modified_by' => auth()->id(),
        ]);

        // Snapshot version history — FR1.2
        ConfigVersion::create([
            'configuration_id' => $configuration->id,
            'user_id'          => auth()->id(),
            'version_number'   => $newVersion,
            'content'          => $validated['content'],
            'change_reason'    => $validated['change_reason'],
        ]);

        // New change request for updated config
        $configuration->changeRequests()->create([
            'submitted_by'  => auth()->id(),
            'change_reason' => $validated['change_reason'],
            'status'        => 'pending',
        ]);

        AuditLog::record(
            'Updated configuration: '.$configuration->name.' to v'.$newVersion,
            'config', 'medium', $configuration->name, $configuration->id
        );

        return redirect()->route('configurations.show', $configuration)
                         ->with('success', 'Configuration updated and submitted for review.');
    }

    public function history(Configuration $configuration)
    {
        $configuration->load(['versions.user']);
        return view('configurations.show', compact('configuration'));
    }

    public function version(Configuration $configuration, $versionId)
    {
        $version = $configuration->versions()->findOrFail($versionId);
        return view('configurations.show', compact('configuration', 'version'));
    }
}
