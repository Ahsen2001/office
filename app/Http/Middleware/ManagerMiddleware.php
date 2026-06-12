<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ManagerMiddleware extends RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        return parent::handle($request, $next, 'manager');
    }
}
