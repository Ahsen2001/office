<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Service;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicPageController extends Controller
{
    public function home(): View
    {
        return view('public.home', [
            'featuredServices' => Service::query()
                ->with('branch')
                ->where('is_active', '=', true)
                ->orderBy('name', 'asc')
                ->limit(6)
                ->get(),
            'serviceCount' => Service::query()->where('is_active', '=', true)->count(),
            'departmentCount' => Branch::query()->where('is_active', '=', true)->count(),
            'office' => $this->officeDetails(),
        ]);
    }

    public function about(): View
    {
        return view('public.about', [
            'office' => $this->officeDetails(),
        ]);
    }

    public function services(Request $request): View
    {
        $search = trim($request->string('search')->toString());
        $departmentId = $request->integer('branch_id') ?: null;

        $services = Service::query()
            ->with('branch')
            ->where('is_active', '=', true)
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('branch', fn ($departmentQuery) => $departmentQuery
                            ->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($departmentId, fn ($query) => $query->where('branch_id', '=', $departmentId))
            ->orderBy('name', 'asc')
            ->paginate(9)
            ->withQueryString();

        return view('public.services', [
            'services' => $services,
            'departments' => Branch::query()
                ->where('is_active', '=', true)
                ->orderBy('name', 'asc')
                ->get(),
            'search' => $search,
            'departmentId' => $departmentId,
            'office' => $this->officeDetails(),
        ]);
    }

    public function contact(): View
    {
        return view('public.contact', [
            'office' => $this->officeDetails(),
        ]);
    }

    private function officeDetails(): array
    {
        $settings = SystemSetting::query()
            ->whereIn('key', [
                'office_name',
                'office_email',
                'office_phone',
                'office_address',
                'working_hours',
            ])
            ->pluck('value', 'key');

        return [
            'name' => $settings->get('office_name', config('app.name', 'Office Service Management System')),
            'email' => $settings->get('office_email', 'info@office.example'),
            'phone' => $settings->get('office_phone', '+94 11 000 0000'),
            'address' => $settings->get('office_address', 'Public Services Office, Main Administrative Complex, Colombo'),
            'hours' => $settings->get('working_hours', 'Monday to Friday, 8:30 AM - 4:30 PM'),
        ];
    }
}
