<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleRedirect {
    public function handle(Request $request, Closure $next): Response {
        if (!auth()->check()) {
            return redirect('/login');
        }

        $role = auth()->user()->role;

        return match ($role) {
            'developer' => redirect('/developer'),
            'kantor' => redirect('/kantor'),
            'pabrik' => redirect('/pabrik'),
            'owner' => redirect('/owner'),
            default => redirect('/login')
        };
    }
}
