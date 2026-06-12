<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Person;
use App\Models\ServiceApplication;
use App\Models\User;

class DashboardController extends Controller
{
    public function __invoke()
    {
        return response()->json([
            'users' => User::count(),
            'departments' => Department::count(),
            'people' => Person::count(),
            'applications' => ServiceApplication::count(),
        ]);
    }
}
