<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class BanqueCentraleService
{
    protected $baseUrl;
    protected $apiKey;
    protected $apiSecret;
    protected $timeout;
    protected $retryAttempts;
    protected $retryDelay;
    protected $token;

    public function __construct()
    {
        $this->baseUrl = config('banque-centrale.base_url');
        $this->apiKey = env('BANQUE_CENTRALE_API_KEY');
        $this->apiSecret = env('BANQUE_CENTRALE_API_SECRET');
        $this->timeout = config('banque-centrale.timeout');
        $this->retryAttempts = config('banque-centrale.retry_attempts');
        $this->retryDelay = config('banque-centrale.retry_delay');
    }

    protected function getHeaders()
    {
        return [
            'Authorization' => 'Bearer ' . $this->getToken(),
            'Accept' => 'application/json',
        ];
    }

    protected function getToken()
    {
        $user=Auth::user();
        $result=$this->signin($user->email,$user->email);

        return $result['token'];
    }

    public function signin($username, $password)
    {
        try {
            $response = Http::timeout($this->timeout)
                ->retry($this->retryAttempts, $this->retryDelay)
                ->post($this->baseUrl . config('banque-centrale.endpoints.auth.signin'), [
                    'username' => $username,
                    'password' => $password,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $this->token = $data['token'];
                Cache::put('banque_centrale_token', $this->token, now()->addHours(1));
                return $data;
            }

            Log::error('Échec de la connexion', [
                'status' => $response->status(),
                'response' => $response->json(),
            ]);

            return null;
        } catch (\Exception $e) {
            dd($e->getMessage());
            Log::error('Erreur lors de la connexion', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function refreshToken()
    {
        try {
            $response = Http::timeout($this->timeout)
                ->retry($this->retryAttempts, $this->retryDelay)
                ->withHeaders($this->getHeaders())
                ->post($this->baseUrl . config('banque-centrale.endpoints.auth.refresh'));

            if ($response->successful()) {
                $data = $response->json();
                $this->token = $data['token'];
                Cache::put('banque_centrale_token', $this->token, now()->addHours(1));
                return $data;
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Erreur lors du rafraîchissement du token', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function logout()
    {
        try {
            $response = Http::timeout($this->timeout)
                ->retry($this->retryAttempts, $this->retryDelay)
                ->withHeaders($this->getHeaders())
                ->post($this->baseUrl . config('banque-centrale.endpoints.auth.logout'));

            Cache::forget('banque_centrale_token');
            $this->token = null;

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Erreur lors de la déconnexion', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function resetPassword($currentPassword, $newPassword)
    {
        try {
            $response = Http::timeout($this->timeout)
                ->retry($this->retryAttempts, $this->retryDelay)
                ->withHeaders($this->getHeaders())
                ->post($this->baseUrl . config('banque-centrale.endpoints.util.reset_password'), [
                    'current_password' => $currentPassword,
                    'new_password' => $newPassword,
                ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Erreur lors du changement de mot de passe', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function forgotPassword($email)
    {
        try {
            $response = Http::timeout($this->timeout)
                ->retry($this->retryAttempts, $this->retryDelay)
                ->post($this->baseUrl . config('banque-centrale.endpoints.util.forgot_password'), [
                    'email' => $email,
                ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Erreur lors de la demande de réinitialisation du mot de passe', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function importSitu($file, $type)
    {
        try {
            $endpoint = config("banque-centrale.endpoints.situ.import.situ{$type}");
            $response = Http::timeout($this->timeout)
                ->retry($this->retryAttempts, $this->retryDelay)
                ->withHeaders($this->getHeaders())
                ->attach('file', $file->get(), $file->getClientOriginalName())
                ->post($endpoint);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Échec de l\'import du fichier SITU', [
                'type' => $type,
                'status' => $response->status(),
                'response' => $response->json(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'import du fichier SITU', [
                'type' => $type,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function calculSitu($route)
    {
        try {
            $endpoint = env('BANQUE_CENTRALE_API_URL') . $route;

            dd($this->getHeaders(),$endpoint);

            $response = Http::timeout($this->timeout)
                ->retry($this->retryAttempts, $this->retryDelay)
                ->withHeaders($this->getHeaders())
                ->post($endpoint);

            if ($response->successful()) {
                dd($response->json(),'MAU');
                return $response->json();
            }

            Log::error('Échec du calcul du fichier SITU', [
                'route' => $route,
                'status' => $response->status(),
                'response' => $response->json(),
            ]);

            return null;
        } catch (\Exception $e) {
            dd($e->getMessage(),'MAURIUVE');
            Log::error('Erreur lors du calcul du fichier SITU', [
                'route' => $route,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function getSituRapport()
    {
        try {
            $response = Http::timeout($this->timeout)
                ->retry($this->retryAttempts, $this->retryDelay)
                ->withHeaders($this->getHeaders())
                ->post($this->baseUrl . config('banque-centrale.endpoints.situ.rapport'));

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Échec de la récupération du rapport SITU', [
                'status' => $response->status(),
                'response' => $response->json(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération du rapport SITU', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
