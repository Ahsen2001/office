<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StaffMiddleware extends RoleMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        return parent::handle($request, $next, 'staff');
    }
}
