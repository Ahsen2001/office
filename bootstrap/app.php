<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append([
            \App\Http\Middleware\SecurityHeaders::class,
        ]);

        $middleware->alias([
            'active' => \App\Http\Middleware\EnsureUserIsActive::class,
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'staff' => \App\Http\Middleware\StaffMiddleware::class,
            'department.officer' => \App\Http\Middleware\DepartmentOfficerMiddleware::class,
            'manager' => \App\Http\Middleware\ManagerMiddleware::class,
            'branch.access' => \App\Http\Middleware\BranchAccessMiddleware::class,
            'management' => \App\Http\Middleware\ManagementMiddleware::class,
            'reception' => \App\Http\Middleware\ReceptionMiddleware::class,
            'branch.head' => \App\Http\Middleware\BranchHeadMiddleware::class,
            'branch.staff' => \App\Http\Middleware\BranchStaffMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
