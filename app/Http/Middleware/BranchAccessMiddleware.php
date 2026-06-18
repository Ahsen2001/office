<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BranchAccessMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        abort_unless($user, 403);

        if ($user->hasRole('admin', 'management', 'reception')) {
            return $next($request);
        }

        abort_unless($user->hasRole('branch_head', 'branch_staff') && $user->branch_id, 403);

        foreach (['branch', 'service', 'application', 'appointment'] as $parameter) {
            $model = $request->route($parameter);

            if ($model instanceof Model) {
                $branchId = $parameter === 'branch' ? $model->getKey() : $model->getAttribute('branch_id');
                abort_unless((int) $branchId === (int) $user->branch_id, 403);
            }
        }

        return $next($request);
    }
}
