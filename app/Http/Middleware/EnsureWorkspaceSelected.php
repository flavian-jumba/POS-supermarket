<?php

namespace App\Http\Middleware;

use App\Support\Workspace;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureWorkspaceSelected
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! app(Workspace::class)->currentOrganization($request->user())) {
            return redirect()->route('workspace.index');
        }

        return $next($request);
    }
}
