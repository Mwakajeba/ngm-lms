<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RequireBranchSelection
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            $branchId = session('branch_id') ?: $user->branch_id;

            // Fall back to first assigned branch (many-to-many) if none selected
            if (! $branchId && method_exists($user, 'branches')) {
                $branchId = $user->branches()->value('branches.id');
                if ($branchId) {
                    session(['branch_id' => $branchId]);
                }
            }

            if (! $branchId) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Branch selection is required. Please select a branch first.',
                        'redirect' => route('change-branch'),
                    ], 403);
                }

                return redirect()->route('change-branch');
            }

            if (! session('branch_id')) {
                session(['branch_id' => $branchId]);
            }

            config(['app.current_branch_id' => $branchId]);
        }

        return $next($request);
    }
}
