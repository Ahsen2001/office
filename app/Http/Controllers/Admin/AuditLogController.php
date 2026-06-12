<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $this->filters($request);

        return view('admin.audit-logs.index', [
            'logs' => $this->query($filters)->paginate(20)->withQueryString(),
            'users' => User::orderBy('name')->get(['id', 'name', 'email']),
            'modules' => AuditLog::query()->select('module')->distinct()->orderBy('module')->pluck('module'),
            'filters' => $filters,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $logs = $this->query($this->filters($request))->with('user')->latest()->get();

        return response()->streamDownload(function () use ($logs) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Date', 'User', 'Action', 'Module', 'Description', 'IP Address', 'User Agent']);
            foreach ($logs as $log) {
                fputcsv($handle, [
                    $log->created_at?->format('Y-m-d H:i:s'),
                    $log->user?->name ?? 'System',
                    $log->action,
                    $log->module,
                    $log->description,
                    $log->ip_address,
                    $log->user_agent,
                ]);
            }
            fclose($handle);
        }, 'audit-logs.csv', ['Content-Type' => 'text/csv']);
    }

    private function query(array $filters)
    {
        return AuditLog::with('user')
            ->when($filters['user_id'], fn ($query) => $query->where('user_id', $filters['user_id']))
            ->when($filters['module'], fn ($query) => $query->where('module', $filters['module']))
            ->when($filters['date_from'], fn ($query) => $query->whereDate('created_at', '>=', $filters['date_from']))
            ->when($filters['date_to'], fn ($query) => $query->whereDate('created_at', '<=', $filters['date_to']))
            ->when($filters['search'], fn ($query) => $query->where(function ($query) use ($filters) {
                $term = $filters['search'];
                $query->where('action', 'like', "%{$term}%")
                    ->orWhere('module', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%")
                    ->orWhere('ip_address', 'like', "%{$term}%")
                    ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%"));
            }))
            ->latest();
    }

    private function filters(Request $request): array
    {
        return [
            'user_id' => $request->integer('user_id') ?: null,
            'module' => $request->string('module')->toString() ?: null,
            'date_from' => $request->date('date_from')?->format('Y-m-d'),
            'date_to' => $request->date('date_to')?->format('Y-m-d'),
            'search' => trim($request->string('search')->toString()),
        ];
    }
}
