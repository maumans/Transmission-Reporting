<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        if ($request->user()) {
            $user = $request->user();
            $isAdmin = $user->hasAnyRole(['admin']);
            $isDeclarant = $user->hasAnyRole(['declarant']);
            $isValideur = $user->hasAnyRole(['valideur']);
        }

        return [
            ...parent::share($request),
            'success' => session('success'),
            'error' => session('error'),        
            'info' => session('info'),
            'operationId' => session('operationId'),
            'auth' => [
                'user' => $request->user(),
                'isAdmin' => $isAdmin ?? false,
                'isDeclarant' => $isDeclarant ?? false,
                'isValideur' => $isValideur ?? false,
            ],
        ];
    }
}
