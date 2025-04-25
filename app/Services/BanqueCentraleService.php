<?php

namespace App\Services;

use App\Models\Balance;
use App\Models\Operation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Storage;

class BanqueCentraleService
{
    protected $baseUrl;
    protected $apiKey;
    protected $apiSecret;
    protected $timeout;
    protected $retryAttempts;
    protected $retryDelay;
    protected $token;

    private const SITU_SHEETS = ['SITU_01', 'SITU_02', 'SITU_03'];
    private const FINS_SHEETS = ['FINS_01', 'FINS_02', 'FINS_03'];

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
        $user = Auth::user();
        $result = $this->signin($user->apiEmail, $user->apiPassword);

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

    public function calculSitu($route, $date_arretee, $statut, $fichier_modele, $numeroFeuille)
    {

        try {

            if (!$fichier_modele) {
                throw new \Exception('Le fichier modèle de l\'opération n\'existe pas');
            }

            $items = $this->readSituFile($date_arretee, $statut, $fichier_modele, $numeroFeuille);

            $endpoint = env('BANQUE_CENTRALE_API_URL') . $route;

            $payload = [
                'transmission' => [
                    'dateArrete' => $date_arretee,
                    'statut' => $statut,
                ],
                'versionAPI' => '1.0.0',
                'items' => $items,
            ];

            $response = Http::timeout($this->timeout)
                ->retry($this->retryAttempts, $this->retryDelay)
                ->withHeaders($this->getHeaders())
                ->post($endpoint, $payload);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Échec du calcul du fichier SITU', [
                'route' => $route,
                'status' => $response->status(),
                'response' => $response->json(),
            ]);

            return null;
        } catch (\Exception $e) {
            dd($e->getMessage());
            Log::error('Erreur lors du calcul du fichier SITU', [
                'route' => $route,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function calcul($data)
    {
        $response = null;
        try {
            $type = $data['rubrique'];
            $route = $data['endpoint'];
            $date_arretee = $data['date_arretee'];
            $statut = $data['statut'];
            $fichier_modele = $data['fichier_modele'];
            $feuille = $data['feuille'];
            $methode = $data['methode'];
           

            if (!$fichier_modele) {
                throw new \Exception('Le fichier modèle de l\'opération n\'existe pas');
            }
            $payload = [];

            switch ($type) {
                case 'SITU':
                    $payload = $this->readSituFile($date_arretee, $statut, $fichier_modele, $feuille);
                    break;
                case 'FINS':
                    $payload = $this->readFinsFile($date_arretee, $statut, $fichier_modele, $feuille, $route);
                    break;
                default:
                    throw new \Exception('Type de fichier non reconnu');
            }

            $endpoint = env('BANQUE_CENTRALE_API_URL') . $route;
            $response = Http::timeout($this->timeout)
                ->retry($this->retryAttempts, $this->retryDelay)
                ->withHeaders($this->getHeaders())
                ->post($endpoint, $payload);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Échec du calcul du fichier', [
                'route' => $route,
                'status' => $response->status(),
                'response' => $response->json(),
            ]);

            return null;
        } catch(\Exception $e) {
            Log::error('Erreur lors du calcul du fichier', [
                'route' => $route,
                'error' => $e->getMessage(),
                'data'=>$data,
                'payload'=>$payload,
                'endpoint'=>$endpoint,
                'response'=>$response
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

    public function readSituFile($date_arretee, $statut, string $filePath, string $feuille): array
    {
        try {
            if (!Storage::disk('public')->exists($filePath)) {
                throw new \Exception("Le fichier SITU n'existe pas dans le chemin spécifié " . $filePath);
            }

            $spreadsheet = IOFactory::load(Storage::disk('public')->path($filePath));
            $data = [
                'transmission' => [
                    'dateArrete' => $date_arretee,
                    'statut' => $statut
                ],
                'versionAPI' => '1.0.0',
                'items' => [],
                //'items2' => []
            ];

            if (!$spreadsheet->sheetNameExists($feuille)) {
                Log::warning("La feuille {$feuille} n'existe pas dans le fichier SITU");
                return $data;
            }

            $worksheet = $spreadsheet->getSheetByName($feuille);

            $data['items'] = $this->readWorksheetSituData($worksheet);

            return $data;
        } catch (\Exception $e) {
            Log::error('Erreur lors de la lecture du fichier SITU: ' . $e->getMessage());
            throw $e;
        }
    }

    private function readWorksheetSituData($sheet): array
    {
        $row = 12; // Les données commencent à la ligne 13
        $data = [];

        while (true) {
            $code = $sheet->getCell("A$row")->getValue();
            $libelle = $sheet->getCell("E$row")->getValue();

            if ($row === 125) {
                break; // Arrêter si ligne vide
            }

            if ($code !== null && $libelle !== null && $code !== "CODE") {
                $data[] = [
                    "code" => strval($code) ?: "string",
                    "libelle" => strval($libelle) ?: "string",
                    "provisionAmortissement" => 0,
                    "residentGnf" => 0,
                    "nonResidentGnf" => 0,
                    "residentDevise" => 0,
                    "nonResidentDevise" => 0,
                    "total" => 0,
                    "valeurTotalLigne" => 0
                ];
            }

            $row++;
        }

        return $data;
    }

    public function readFinsFile($date_arretee, $statut, string $filePath, string $feuille, string $endPoint): array
    {

        try {
            if (!Storage::disk('public')->exists($filePath)) {
                dd("Le fichier FINS n'existe pas dans le chemin spécifié " . $filePath);
                throw new \Exception("Le fichier FINS n'existe pas dans le chemin spécifié " . $filePath);
            }

            $spreadsheet = IOFactory::load(Storage::disk('public')->path($filePath));

            $data = [
                'transmission' => [
                    'dateArrete' => $date_arretee,
                    'statut' => $statut
                ],
                'versionAPI' => '1.0.0',
                'items' => [],
                'items2' => []
            ];


            if (!$spreadsheet->sheetNameExists($feuille)) {
                dd("La feuille {$feuille} n'existe pas dans le fichier FINS");
                Log::warning("La feuille {$feuille} n'existe pas dans le fichier FINS");
                return $data;
            }

            $worksheet = $spreadsheet->getSheetByName($feuille);

            $data['items'] = $this->readWorksheetFinsData($worksheet, $endPoint)['items'];
            $data['items2'] = $this->readWorksheetFinsData($worksheet, $endPoint)['items2'];

            return $data;
        } catch (\Exception $e) {
            dd("Erreur lors de la lecture du fichier FINS: " . $e->getMessage());
            Log::error('Erreur lors de la lecture du fichier FINS: ' . $e->getMessage());
            throw $e;
        }
    }

    private function readWorksheetFinsData($sheet, string $endPoint): array
    {
        $row = 9; // Les données commencent à la ligne 9
        $data = [
            'items' => [],
            'items2' => []
        ];

        while (true) {
            $code = $sheet->getCell("A$row")->getValue();
            $libelle = $sheet->getCell("D$row")->getValue();

            if ($row === 190) {
                break; // Arrêter si ligne vide
            }


            if ($code !== null && $libelle !== null && $code !== "CODE") {
                switch ($endPoint) {
                    case "/api/calcul/fins/fins01DEV":
                        $data['items'] = [
                            "code" => strval($code) ?: "string",
                            "libelle" => strval($libelle) ?: "string",
                            "adminCentrale" => 0,
                            "adminLocaleRegionale" => 0,
                            "administrationSecuriteSociale" => 0,
                            "snfPublique" => 0,
                            "autreSnf" => 0,
                            "entrepriseIndividuelle" => 0,
                            "particulier" => 0,
                            "institutionSansButLucratif" => 0,
                            "assuranceCaisseRetraite" => 0,
                            "autresIntermediaresFinancier" => 0,
                            "nonResident" => 0,
                            "total" => 0,
                            "resident" => 0,
                            "etatOrganismeAssimiles" => 0,
                            "societeNonFinancier" => 0,
                            "menage" => 0,
                            "clienteleFinancier" => 0
                        ];
                        $data['items2'] = [];
                        break;

                    case "/api/calcul/fins/fins01GNF":
                        $data['items'] = [
                            "code" => strval($code) ?: "string",
                            "libelle" => strval($libelle) ?: "string",
                            "adminCentrale" => 0,
                            "adminLocaleRegionale" => 0,
                            "administrationSecuriteSociale" => 0,
                            "snfPublique" => 0,
                            "autreSnf" => 0,
                            "entrepriseIndividuelle" => 0,
                            "particulier" => 0,
                            "institutionSansButLucratif" => 0,
                            "assuranceCaisseRetraite" => 0,
                            "autresIntermediaresFinancier" => 0,
                            "nonResident" => 0,
                            "total" => 0,
                            "resident" => 0,
                            "etatOrganismeAssimiles" => 0,
                            "societeNonFinancier" => 0,
                            "menage" => 0,
                            "clienteleFinancier" => 0
                        ];
                        $data['items2'] = [];
                        break;
                    case "/api/calcul/fins/fins02DEV":
                        $data['items'] = [
                            "code" => strval($code) ?: "string",
                            "libelle" => strval($libelle) ?: "string",
                            "adminCentrale" => 0,
                            "adminLocaleRegionale" => 0,
                            "administrationSecuriteSociale" => 0,
                            "snfPublique" => 0,
                            "autreSnf" => 0,
                            "entrepriseIndividuelle" => 0,
                            "particulier" => 0,
                            "institutionSansButLucratif" => 0,
                            "assuranceCaisseRetraite" => 0,
                            "autresIntermediaresFinancier" => 0,
                            "nonResident" => 0,
                            "total" => 0,
                            "resident" => 0,
                            "etatOrganismeAssimiles" => 0,
                            "societeNonFinancier" => 0,
                            "menage" => 0,
                            "clienteleFinancier" => 0
                        ];
                        $data['items2'] = [];
                        break;
                    case "/api/calcul/fins/fins02GNF":
                        $data['items'] = [
                            "code" => strval($code) ?: "string",
                            "libelle" => strval($libelle) ?: "string",
                            "adminCentrale" => 0,
                            "adminLocaleRegionale" => 0,
                            "administrationSecuriteSociale" => 0,
                            "snfPublique" => 0,
                            "autreSnf" => 0,
                            "entrepriseIndividuelle" => 0,
                            "particulier" => 0,
                            "institutionSansButLucratif" => 0,
                            "assuranceCaisseRetraite" => 0,
                            "autresIntermediaresFinancier" => 0,
                            "nonResident" => 0,
                            "total" => 0,
                            "resident" => 0,
                            "etatOrganismeAssimiles" => 0,
                            "societeNonFinancier" => 0,
                            "menage" => 0,
                            "clienteleFinancier" => 0
                        ];
                        $data['items2'] = [];
                        break;
                    case "/api/calcul/fins/fins03":
                        $data['items'] = [
                            "code" => strval($code) ?: "string",
                            "libelle" => strval($libelle) ?: "string",
                            "gnfBilan" => 0,
                            "deviseBilan" => 0,
                            "gnfHorsBilan" => 0,
                            "deviseHorsBilan" => 0,
                            "total" => 0,
                            "valeurTotalLigne" => 0,
                            "totalBilan" => 0,
                            "totalHorsBilan" => 0
                        ];
                        $data['items2'] = [];
                        break;
                    case "/api/calcul/fins/fins04":
                        $data['items'] = [
                            "code" => strval($code) ?: "string",
                            "libelle" => strval($libelle) ?: "string",
                            "gnfBilan" => 0,
                            "deviseBilan" => 0,
                            "gnfHorsBilan" => 0,
                            "deviseHorsBilan" => 0,
                            "total" => 0,
                            "valeurTotalLigne" => 0,
                            "totalBilan" => 0,
                            "totalHorsBilan" => 0
                        ];
                        $data['items2'] = [];
                        break;
                    case "/api/calcul/fins/fins05":
                        $data['items'] = [
                            "code" => strval($code) ?: "string",
                            "libelle" => strval($libelle) ?: "string",
                            "provisionAmortissement" => 0,
                            "residentGnf" => 0,
                            "nonResidentGnf" => 0,
                            "residentDevise" => 0,
                            "nonResidentDevise" => 0,
                            "total" => 0,
                            "valeurTotalLigne" => 0
                        ];
                        $data['items2'] = [];
                        break;
                    case "/api/calcul/fins/fins06":
                        $data['items'] = [
                            "code" => strval($code) ?: "string",
                            "libelle" => strval($libelle) ?: "string",
                            "provisionAmortissement" => 0,
                            "residentGnf" => 0,
                            "nonResidentGnf" => 0,
                            "residentDevise" => 0,
                            "nonResidentDevise" => 0,
                            "total" => 0,
                            "valeurTotalLigne" => 0
                        ];
                        $data['items2'] = [];
                        break;
                    case "/api/calcul/fins/fins07DEV":
                        $data['items'] = [
                            "code" => strval($code) ?: "string",
                            "libelle" => strval($libelle) ?: "string",
                            "bcrg" => 0,
                            "ccp" => 0,
                            "tp" => 0,
                            "banque" => 0,
                            "etablissementsFinanciers" => 0,
                            "institutionMicrofinance" => 0,
                            "institutionFinanciereSpecialise" => 0,
                            "institutionFinanciereNonResident" => 0,
                            "total" => 0,
                            "valeurTotalLigne" => 0
                        ];
                        $data['items2'] = [
                            "code" => strval($code) ?: "string",
                            "libelle" => strval($libelle) ?: "string",
                            "adminCentrale" => 0,
                            "adminLocaleRegionale" => 0,
                            "administrationSecuriteSociale" => 0,
                            "snfPublique" => 0,
                            "autreSnf" => 0,
                            "entrepriseIndividuelle" => 0,
                            "particulier" => 0,
                            "institutionSansButLucratif" => 0,
                            "assuranceCaisseRetraite" => 0,
                            "autresIntermediaresFinancier" => 0,
                            "nonResident" => 0,
                            "total" => 0,
                            "resident" => 0,
                            "etatOrganismeAssimiles" => 0,
                            "societeNonFinancier" => 0,
                            "menage" => 0,
                            "clienteleFinancier" => 0
                        ];
                        break;
                    case "/api/calcul/fins/fins07GNF":
                        $data['items'] = [
                            "code" => strval($code) ?: "string",
                            "libelle" => strval($libelle) ?: "string",
                            "bcrg" => 0,
                            "ccp" => 0,
                            "tp" => 0,
                            "banque" => 0,
                            "etablissementsFinanciers" => 0,
                            "institutionMicrofinance" => 0,
                            "institutionFinanciereSpecialise" => 0,
                            "institutionFinanciereNonResident" => 0,
                            "total" => 0,
                            "valeurTotalLigne" => 0
                        ];
                        $data['items2'] = [
                            "code" => strval($code) ?: "string",
                            "libelle" => strval($libelle) ?: "string",
                            "adminCentrale" => 0,
                            "adminLocaleRegionale" => 0,
                            "administrationSecuriteSociale" => 0,
                            "snfPublique" => 0,
                            "autreSnf" => 0,
                            "entrepriseIndividuelle" => 0,
                            "particulier" => 0,
                            "institutionSansButLucratif" => 0,
                            "assuranceCaisseRetraite" => 0,
                            "autresIntermediaresFinancier" => 0,
                            "nonResident" => 0,
                            "total" => 0,
                            "resident" => 0,
                            "etatOrganismeAssimiles" => 0,
                            "societeNonFinancier" => 0,
                            "menage" => 0,
                            "clienteleFinancier" => 0
                        ];
                        break;
                    case "/api/calcul/fins/fins08":
                        $data['items'] = [
                            "code" => strval($code) ?: "string",
                            "libelle" => strval($libelle) ?: "string",
                            "bcrg" => 0,
                            "ccp" => 0,
                            "tp" => 0,
                            "banque" => 0,
                            "etablissementsFinanciers" => 0,
                            "institutionMicrofinance" => 0,
                            "institutionFinanciereSpecialise" => 0,
                            "institutionFinanciereNonResident" => 0,
                            "total" => 0,
                            "valeurTotalLigne" => 0
                        ];
                        $data['items2'] = [];
                        break;
                    case "/api/calcul/fins/fins09DEV":
                        $data['items'] = [
                            "code" => strval($code) ?: "string",
                            "libelle" => strval($libelle) ?: "string",
                            "bcrg" => 0,
                            "ccp" => 0,
                            "tp" => 0,
                            "banque" => 0,
                            "etablissementsFinanciers" => 0,
                            "institutionMicrofinance" => 0,
                            "institutionFinanciereSpecialise" => 0,
                            "institutionFinanciereNonResident" => 0,
                            "total" => 0,
                            "valeurTotalLigne" => 0
                        ];
                        $data['items2'] = [
                            "code" => strval($code) ?: "string",
                            "libelle" => strval($libelle) ?: "string",
                            "adminCentrale" => 0,
                            "adminLocaleRegionale" => 0,
                            "administrationSecuriteSociale" => 0,
                            "snfPublique" => 0,
                            "autreSnf" => 0,
                            "entrepriseIndividuelle" => 0,
                            "particulier" => 0,
                            "institutionSansButLucratif" => 0,
                            "assuranceCaisseRetraite" => 0,
                            "autresIntermediaresFinancier" => 0,
                            "nonResident" => 0,
                            "total" => 0,
                            "resident" => 0,
                            "etatOrganismeAssimiles" => 0,
                            "societeNonFinancier" => 0,
                            "menage" => 0,
                            "clienteleFinancier" => 0
                        ];
                        break;
                    case "/api/calcul/fins/fins09GNF":
                        $data['items'] = [
                            "code" => strval($code) ?: "string",
                            "libelle" => strval($libelle) ?: "string",
                            "bcrg" => 0,
                            "ccp" => 0,
                            "tp" => 0,
                            "banque" => 0,
                            "etablissementsFinanciers" => 0,
                            "institutionMicrofinance" => 0,
                            "institutionFinanciereSpecialise" => 0,
                            "institutionFinanciereNonResident" => 0,
                            "total" => 0,
                            "valeurTotalLigne" => 0
                        ];
                        $data['items2'] = [
                            "code" => strval($code) ?: "string",
                            "libelle" => strval($libelle) ?: "string",
                            "adminCentrale" => 0,
                            "adminLocaleRegionale" => 0,
                            "administrationSecuriteSociale" => 0,
                            "snfPublique" => 0,
                            "autreSnf" => 0,
                            "entrepriseIndividuelle" => 0,
                            "particulier" => 0,
                            "institutionSansButLucratif" => 0,
                            "assuranceCaisseRetraite" => 0,
                            "autresIntermediaresFinancier" => 0,
                            "nonResident" => 0,
                            "total" => 0,
                            "resident" => 0,
                            "etatOrganismeAssimiles" => 0,
                            "societeNonFinancier" => 0,
                            "menage" => 0,
                            "clienteleFinancier" => 0
                        ];
                        break;

                    case "/api/calcul/fins/fins10":
                        $data['items'] = [
                            "code" => strval($code) ?: "string",
                            "libelle" => strval($libelle) ?: "string",
                            "provisionAmortissement" => 0,
                            "residentGnf" => 0,
                            "nonResidentGnf" => 0,
                            "residentDevise" => 0,
                            "nonResidentDevise" => 0,
                            "total" => 0,
                            "valeurTotalLigne" => 0
                        ];
                        $data['items2'] = [];
                        break;
                    case "/api/calcul/fins/fins11":
                        $data['items'] = [
                            "code" => strval($code) ?: "string",
                            "libelle" => strval($libelle) ?: "string",
                            "totalExpositionSouffranceBrutes" => 0,
                            "provisionDeplacementActifBilan" => 0,
                            "provisionDeplacementPassifBilan" => 0,
                            "totalExpositionsSouffrancesNettes" => 0,
                            "valeurTotalLigne" => 0
                        ];
                        $data['items2'] = [];
                        break;
                    case "/api/calcul/fins/fins12":
                        $data['items'] = [
                            "code" => strval($code) ?: "string",
                            "libelle" => strval($libelle) ?: "string",
                            "inferieurTroisMois" => 0,
                            "intervalleTroisSixMois" => 0,
                            "intervalleSixDouzeMois" => 0,
                            "intervalleDouzeDixHuitMois" => 0,
                            "intervalleDixHuitVingtQuatreMois" => 0,
                            "superieurVingtQuatreMois" => 0
                        ];
                        $data['items2'] = [];
                        break;
                    case "/api/calcul/fins/fins13":
                        $data['items'] = [
                            "code" => strval($code) ?: "string",
                            "natureProvision" => "string",
                            "soldeDebutExercice" => 0,
                            "repriseExercice" => 0,
                            "dotationExercice" => 0,
                            "soldeFinExercice" => 0,
                            "provisionExigeBCRG" => 0,
                            "total" => 0,
                            "valeurTotalLigne" => 0
                        ];
                        $data['items2'] = [];
                        break;
                    case "/api/calcul/fins/fins14":
                        $data['items'] = [
                            "code" => strval($code) ?: "string",
                            "libelle" => strval($libelle) ?: "string",
                            "cadreSuperieur" => 0,
                            "cadreMoyen" => 0,
                            "cadreSubalterne" => 0,
                            "contratuelOccasionnel" => 0,
                            "total" => 0,
                            "valeurTotalLigne" => 0
                        ];
                        $data['items2'] = [
                            "code" => strval($code) ?: "string",
                            "libelle" => strval($libelle) ?: "string",
                            "femmeSiege" => 0,
                            "hommeSiege" => 0,
                            "femmeAgence" => 0,
                            "hommeAgence" => 0,
                            "hommeGenre" => 0,
                            "femmeGenre" => 0,
                            "valeurTotalLigne" => 0,
                            "effectifTotal" => 0
                        ];
                        break;
                    case "/api/calcul/fins/fins15":
                        $data['items'] = [
                            "code" => strval($code) ?: "string",
                            "libelle" => strval($libelle) ?: "string",
                            "valeurFinPeriodePrecedente" => 0,
                            "acquisitionsAjustements" => 0,
                            "immobilistApporteParTiers" => 0,
                            "immoSortiesActif" => 0,
                            "cumulPeriodePrecedente" => 0,
                            "dotationPeriodeAjustement" => 0,
                            "amtImmobSortieActif" => 0,
                            "valeurTotalLigne" => 0,
                            "valeurBrutesFinPeriode" => 0,
                            "totalMouvementPeriode" => 0,
                            "totalAmortissement" => 0,
                            "valeurNetteComptable" => 0
                        ];
                        $data['items2'] = [];
                        break;

                    case "/api/calcul/fins/fins16":
                        $data['items'] = [
                            "code" => strval($code) ?: "string",
                            "libelle" => strval($libelle) ?: "string",
                            "mars" => 0,
                            "juin" => 0,
                            "sept" => 0,
                            "dec" => 0
                        ];
                        $data['items2'] = [];
                        break;
                    case "/api/calcul/fins/fins17":
                        $data['items'] = [
                            "code" => strval($code) ?: "string",
                            "libelle" => strval($libelle) ?: "string",
                            "montantJan" => 0,
                            "montantFev" => 0,
                            "montantMars" => 0,
                            "montantAvril" => 0,
                            "montantMai" => 0,
                            "montantJuin" => 0,
                            "montantJuil" => 0,
                            "montantAout" => 0,
                            "montantSept" => 0,
                            "montantOct" => 0,
                            "montantNov" => 0,
                            "montantDec" => 0,
                            "montantTotal" => 0
                        ];
                        $data['items2'] = [];
                        break;
                    default:
                        break;
                }
            }

            $row++;
        }

        return $data;
    }

    public function validateSituData(array $data): bool
    {
        try {
            // Vérification de la présence de toutes les feuilles requises
            foreach (self::SITU_SHEETS as $sheetName) {
                if (!isset($data[$sheetName])) {
                    throw new \Exception("La feuille {$sheetName} est manquante");
                }
            }

            // Validation des données de chaque feuille
            foreach ($data as $sheetName => $sheetData) {
                if (empty($sheetData)) {
                    throw new \Exception("La feuille {$sheetName} est vide");
                }

                // Ajoutez ici d'autres validations spécifiques selon vos besoins
                // Par exemple, vérification des colonnes requises, des types de données, etc.
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Erreur de validation des données SITU: ' . $e->getMessage());
            throw $e;
        }
    }
}
