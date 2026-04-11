<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetActiveCompany
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && !$user->active_company_id) {
            $firstCompany = $user->companies()->first();
            if ($firstCompany) {
                $user->update(['active_company_id' => $firstCompany->id]);
            }
        }

        return $next($request);
    }
}
