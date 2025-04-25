<?php

namespace App\Services;

use App\Models\Operation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;

class TransmissionService
{
    protected $bcToken;

    public function __construct(BCTokenService $bcTokenService)
    {
        $this->bcToken = $bcTokenService->getToken(Auth::user());
    }

    public function readFile($filePath)
    {
        try {
            // Vérifier que le fichier est un Excel
            $extension = pathinfo($filePath, PATHINFO_EXTENSION);
            if (!in_array($extension, ['xlsx', 'xls'])) {
                throw new \Exception('Le fichier doit être au format Excel (.xlsx ou .xls)');
            }

            $spreadsheet = IOFactory::load(storage_path('app/public/' . $filePath));
            $worksheet = $spreadsheet->getActiveSheet();
            $data = [];

            foreach ($worksheet->getRowIterator() as $row) {
                $rowData = [];
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);

                foreach ($cellIterator as $cell) {
                    $rowData[] = $cell->getValue();
                }

                if ($row->getRowIndex() > 1) {
                    $data[] = [
                        'chapitre' => strval($rowData[0]) ?? '',
                        'intituleChapitre' => strval($rowData[1]) ?? '',
                        'numCompte' => strval($rowData[2]) ?? '',
                        'intituleCompte' => strval($rowData[3]) ?? '',
                        'numeroClient' => strval($rowData[4]) ?? '',
                        'nomClient' => strval($rowData[5]) ?? '',
                        'codeDevise' => strval($rowData[6]) ?? '',
                        'soldeDebit' => doubleval($rowData[7] ?? 0),
                        'soldeCredit' => doubleval($rowData[8] ?? 0),
                        'soldeNet' => doubleval($rowData[9] ?? 0),
                        'resident' => strval($rowData[10] ?? ''),
                        'codeAgentEconomique' => strval($rowData[11] ?? ''),
                        'codeSecteurActivite' => strval($rowData[12] ?? ''),
                    ];
                }
            }

            return $data;
        } catch (\Exception $e) {
            dd($e->getMessage());
            Log::error('Erreur lors de la lecture du fichier balance: ' . $e->getMessage());
            throw $e;
        }
    }


    public function transmit(Operation $operation, array $items)
    {
        try {
            if (!$this->bcToken) {
                return [
                    'success' => false,
                    'message' => 'Token BCT invalide ou expiré. Veuillez vous reconnecter.'
                ];
            }

            $payload = [
                'transmission' => [
                    'dateArrete' => $operation->date_arretee,
                    'statut' => $operation->statut,
                ],
                'versionAPI' => '1.0.0',
                'items' => $items,
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->bcToken,
                'Content-Type' => 'application/json',
            ])->post(env('BANQUE_CENTRALE_API_URL') . '/api/balance', $payload);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => $response->json(),
                ];
            }

            if ($response->status() === 401) {
                return [
                    'success' => false,
                    'message' => 'Session expirée. Veuillez vous reconnecter.',
                ];
            }

            return [
                'success' => false,
                'message' => 'Erreur lors de la transmission: ' . $response->body(),
            ];
        } catch (\Exception $e) {
            Log::error('Erreur lors de la transmission de la balance: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erreur lors de la transmission: ' . $e->getMessage(),
            ];
        }
    }
} 