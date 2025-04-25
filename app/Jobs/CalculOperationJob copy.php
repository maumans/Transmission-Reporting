<?php

namespace App\Jobs;

use App\Models\Operation;
use App\Models\OperationRubrique;
use App\Models\Rubrique;
use App\Models\RubriqueApi;
use App\Services\BanqueCentraleService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CalculOperationJob  implements ShouldQueue 
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $operationId;
    protected $rubriqueId;

    public function __construct($operationId, $rubriqueId)
    {
        $this->operationId = $operationId;
        $this->rubriqueId = $rubriqueId;
    }

    public function handle()
    {
        try {
           DB::beginTransaction();

            $operation = Operation::where('id', $this->operationId)->first();
            $rubrique = Rubrique::where('id', $this->rubriqueId)->first();
            $bcs = new BanqueCentraleService();
            $fichier_modele = $rubrique->fichier_modele ?? null;
            $results = collect();

            if (!$operation || !$rubrique) {
                $operation->notify(new \App\Notifications\CalculOperationCompleted('error', 'Opération ou rubrique non trouvée.'));
                throw new \Exception('Opération ou rubrique non trouvée.');
            }

            foreach (RubriqueApi::where('rubrique_id', $this->rubriqueId)->where('groupe', 'calcul')->where('actif', true)->get() as $api) {
                $results->push($bcs->calcul([
                    'rubrique' => $api->rubrique->nom,
                    'endpoint' => $api->endpoint,
                    'date_arretee' => $operation->date_arretee,
                    'statut' => $operation->statut,
                    'fichier_modele' => $fichier_modele,
                    'feuille' => $api->feuille,
                    'methode' => $api->methode
                ]));
            }

            /* foreach ($results as $result) {
                if (isset($result['statutCode'])) {
                    if ($result['statutCode'] != 'OK') {
                        throw new \Exception('Erreur lors du calcul de l\'opération.');
                    }
                }
                else {
                    throw new \Exception('Erreur lors du calcul de l\'opération.');
                }
            } */

            OperationRubrique::where('operation_id', $this->operationId)
                ->where('rubrique_id', $this->rubriqueId)
                ->update([
                    'execute' => true
                ]);

            DB::commit();

            // Notifier l'utilisateur du succès
            $operation->notify(new \App\Notifications\CalculOperationCompleted('success', 'Opération calculée avec succès.'));
        } catch (\Exception $e) {
           DB::rollBack();
            Log::error('Erreur lors du calcul de l\'opération: ' . $e->getMessage());
            
            // Notifier l'utilisateur de l'erreur
            $operation->notify(new \App\Notifications\CalculOperationCompleted('error', $e->getMessage()));
        }
    }
} 