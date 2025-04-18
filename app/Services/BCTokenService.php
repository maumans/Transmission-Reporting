<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class BCTokenService
{
    public function getToken(User $user): ?string
    {
        try {

            $banqueCentraleService = new BanqueCentraleService();

            $response = $banqueCentraleService->signin(
                $user->email,
                $user->email
            );

            //dd($user,$response);

            return $response['token'] ?? null;
        } catch (\Exception $e) {
            dd($e->getMessage());
            Log::error('Exception lors de la récupération du token BCT: ' . $e->getMessage());
            return null;
        }
    }

    public function isTokenValid(User $user): bool
    {
        if (!$user->bctoken) {
            return false;
        }

        try {
            // Vérifier si le token est expiré (par exemple, après 24 heures)
            $tokenCreatedAt = Carbon::parse($user->bctoken_created_at);
            return $tokenCreatedAt->addHours(24)->isFuture();
        } catch (\Exception $e) {
            Log::error('Erreur lors de la vérification du token BCT: ' . $e->getMessage());
            return false;
        }
    }

    public function updateToken(User $user, string $token): void
    {
        $user->update([
            'bctoken' => $token,
            'bctoken_created_at' => now()
        ]);
    }

    public function clearToken(User $user): void
    {
        $user->update([
            'bctoken' => null,
            'bctoken_created_at' => null
        ]);
    }
}
