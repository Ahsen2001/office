<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(): View
    {
        $defaults = [
            ['group' => 'general', 'key' => 'office_name', 'value' => config('app.name'), 'type' => 'string', 'description' => 'Name shown across reports and public pages.'],
            ['group' => 'general', 'key' => 'office_email', 'value' => '', 'type' => 'string', 'description' => 'Primary public contact email.'],
            ['group' => 'general', 'key' => 'office_phone', 'value' => '', 'type' => 'string', 'description' => 'Primary public contact number.'],
            ['group' => 'workflow', 'key' => 'default_processing_days', 'value' => '7', 'type' => 'integer', 'description' => 'Default service deadline in working days.'],
            ['group' => 'security', 'key' => 'public_status_enabled', 'value' => '1', 'type' => 'boolean', 'description' => 'Allow citizens to check limited application status publicly.'],
            ['group' => 'uploads', 'key' => 'max_upload_kb', 'value' => (string) config('office.uploads.max_kilobytes'), 'type' => 'integer', 'description' => 'Maximum document upload size in kilobytes.'],
        ];

        foreach ($defaults as $setting) {
            SystemSetting::firstOrCreate(['key' => $setting['key']], $setting);
        }

        return view('admin.settings.index', [
            'settings' => SystemSetting::orderBy('group')->orderBy('key')->get()->groupBy('group'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'settings' => ['required', 'array'],
            'settings.*' => ['nullable', 'string', 'max:2000'],
        ]);

        foreach ($data['settings'] as $key => $value) {
            $setting = SystemSetting::where('key', $key)->first();

            if (! $setting) {
                continue;
            }

            $oldValue = $setting->value;
            $setting->update(['value' => $this->normalizeValue($setting->type, $value)]);

            AuditLogger::log(
                'update',
                'settings',
                "Updated setting {$setting->key}.",
                $setting,
                ['value' => $oldValue],
                ['value' => $setting->value],
                $request
            );
        }

        return back()->with('success', 'Settings updated successfully.');
    }

    private function normalizeValue(string $type, ?string $value): ?string
    {
        return match ($type) {
            'boolean' => $value ? '1' : '0',
            'integer' => (string) max(0, (int) $value),
            'decimal' => (string) (float) $value,
            default => $value,
        };
    }
}
