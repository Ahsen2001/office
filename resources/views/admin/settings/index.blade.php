@extends('layouts.admin')

@section('title', 'System Settings')
@section('page-title', 'System Settings')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">System settings</h1>
            <p class="text-muted mb-0">Manage office profile, workflow defaults, upload limits, and public status options.</p>
        </div>
        <a href="{{ route('admin.audit-logs.index', ['module' => 'settings']) }}" class="btn btn-outline-primary">
            <i class="fa-solid fa-shield-halved me-1"></i> Setting Audit
        </a>
    </div>

    <form method="POST" action="{{ route('admin.settings.update') }}">
        @csrf
        @method('PUT')

        <div class="row g-4">
            @foreach($settings as $group => $items)
                <div class="col-xl-6">
                    <div class="card soft-card h-100">
                        <div class="card-header p-4">
                            <h2 class="h5 mb-0 text-capitalize">{{ str_replace('_', ' ', $group) }}</h2>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                @foreach($items as $setting)
                                    <div class="col-12">
                                        <label class="form-label fw-semibold" for="setting-{{ $setting->key }}">
                                            {{ ucwords(str_replace('_', ' ', $setting->key)) }}
                                        </label>

                                        @if($setting->type === 'boolean')
                                            <div class="form-check form-switch">
                                                <input type="hidden" name="settings[{{ $setting->key }}]" value="0">
                                                <input id="setting-{{ $setting->key }}" class="form-check-input" type="checkbox" name="settings[{{ $setting->key }}]" value="1" @checked((bool) $setting->value)>
                                                <label class="form-check-label" for="setting-{{ $setting->key }}">Enabled</label>
                                            </div>
                                        @elseif($setting->type === 'integer')
                                            <input id="setting-{{ $setting->key }}" type="number" min="0" name="settings[{{ $setting->key }}]" value="{{ old('settings.'.$setting->key, $setting->value) }}" class="form-control">
                                        @else
                                            <input id="setting-{{ $setting->key }}" name="settings[{{ $setting->key }}]" value="{{ old('settings.'.$setting->key, $setting->value) }}" class="form-control">
                                        @endif

                                        @if($setting->description)
                                            <div class="form-text">{{ $setting->description }}</div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="d-flex flex-wrap justify-content-end gap-2 mt-4">
            <button type="reset" class="btn btn-light">Reset</button>
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i> Save Settings</button>
        </div>
    </form>
@endsection
