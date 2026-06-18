<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\ServiceApplication;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('dashboards.manager', [
            'applicationsCount' => ServiceApplication::count(),
            'pendingCount' => ServiceApplication::whereHas('status', fn ($query) => $query->whereIn('code', [
                'submitted',
                'pending',
                'under_review',
                'processing',
                'waiting_for_documents',
            ]))->count(),
        ]);
    }
}
