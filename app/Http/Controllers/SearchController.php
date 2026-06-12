<?php

namespace App\Http\Controllers;

use App\Models\ApplicationStatus;
use App\Models\Department;
use App\Models\Person;
use App\Models\Service;
use App\Models\ServiceApplication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $this->filters($request);

        return view('search.index', [
            'filters' => $filters,
            'departments' => Department::orderBy('name')->get(),
            'statuses' => ApplicationStatus::orderBy('sort_order')->get(),
            'services' => Service::orderBy('name')->get(),
            'people' => $this->peopleQuery($filters)->paginate(10, ['*'], 'people_page')->withQueryString(),
            'applications' => $this->applicationsQuery($filters)->paginate(10, ['*'], 'applications_page')->withQueryString(),
        ]);
    }

    public function suggestions(Request $request): JsonResponse
    {
        $term = trim($request->string('q')->toString());

        if (mb_strlen($term) < 2) {
            return response()->json([]);
        }

        $people = Person::query()
            ->select(['id', 'full_name', 'person_code', 'national_id', 'phone'])
            ->where(function ($query) use ($term) {
                $query->where('full_name', 'like', "{$term}%")
                    ->orWhere('person_code', 'like', "{$term}%")
                    ->orWhere('national_id', 'like', "{$term}%")
                    ->orWhere('passport_no', 'like', "{$term}%")
                    ->orWhere('phone', 'like', "{$term}%");
            })
            ->limit(5)
            ->get()
            ->map(fn (Person $person) => [
                'type' => 'Person',
                'label' => $person->full_name,
                'meta' => $person->person_code.' | '.$person->phone,
                'url' => route('staff.people.show', $person),
            ]);

        $applications = ServiceApplication::query()
            ->with(['person:id,full_name', 'status:id,name'])
            ->select(['id', 'application_no', 'person_id', 'status_id'])
            ->where('application_no', 'like', "{$term}%")
            ->limit(5)
            ->get()
            ->map(fn (ServiceApplication $application) => [
                'type' => 'Application',
                'label' => $application->application_no,
                'meta' => ($application->person?->full_name ?? 'Unknown').' | '.($application->status?->name ?? ''),
                'url' => route('staff.applications.show', $application),
            ]);

        return response()->json($people->merge($applications)->values());
    }

    private function peopleQuery(array $filters)
    {
        $term = $filters['q'];

        return Person::query()
            ->when($term, fn ($query) => $query->where(function ($query) use ($term) {
                $query->where('full_name', 'like', "%{$term}%")
                    ->orWhere('person_code', 'like', "%{$term}%")
                    ->orWhere('national_id', 'like', "%{$term}%")
                    ->orWhere('passport_no', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%");
            }))
            ->when($filters['date_from'], fn ($query) => $query->whereDate('registered_at', '>=', $filters['date_from']))
            ->when($filters['date_to'], fn ($query) => $query->whereDate('registered_at', '<=', $filters['date_to']))
            ->latest('registered_at');
    }

    private function applicationsQuery(array $filters)
    {
        $term = $filters['q'];

        return ServiceApplication::with(['person', 'department', 'service', 'status'])
            ->when($term, fn ($query) => $query->where(function ($query) use ($term) {
                $query->where('application_no', 'like', "%{$term}%")
                    ->orWhereHas('person', fn ($personQuery) => $personQuery
                        ->where('full_name', 'like', "%{$term}%")
                        ->orWhere('person_code', 'like', "%{$term}%")
                        ->orWhere('national_id', 'like', "%{$term}%")
                        ->orWhere('passport_no', 'like', "%{$term}%")
                        ->orWhere('phone', 'like', "%{$term}%"))
                    ->orWhereHas('department', fn ($departmentQuery) => $departmentQuery->where('name', 'like', "%{$term}%"))
                    ->orWhereHas('service', fn ($serviceQuery) => $serviceQuery->where('name', 'like', "%{$term}%"))
                    ->orWhereHas('status', fn ($statusQuery) => $statusQuery->where('name', 'like', "%{$term}%"));
            }))
            ->when($filters['department_id'], fn ($query) => $query->where('department_id', $filters['department_id']))
            ->when($filters['service_id'], fn ($query) => $query->where('service_id', $filters['service_id']))
            ->when($filters['status_id'], fn ($query) => $query->where('status_id', $filters['status_id']))
            ->when($filters['date_from'], fn ($query) => $query->whereDate('submitted_at', '>=', $filters['date_from']))
            ->when($filters['date_to'], fn ($query) => $query->whereDate('submitted_at', '<=', $filters['date_to']))
            ->latest('submitted_at');
    }

    private function filters(Request $request): array
    {
        return [
            'q' => trim($request->string('q')->toString()),
            'department_id' => $request->integer('department_id') ?: null,
            'service_id' => $request->integer('service_id') ?: null,
            'status_id' => $request->integer('status_id') ?: null,
            'date_from' => $request->date('date_from')?->format('Y-m-d'),
            'date_to' => $request->date('date_to')?->format('Y-m-d'),
        ];
    }
}
