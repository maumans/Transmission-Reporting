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

class CalculOperationJob  implements ShouldQueue 
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $bcs;
    protected $data;

    public function __construct(array $data, $bcs)
    {
        $this->bcs = $bcs;
        $this->data = $data;
    }

    public function handle()
    {
    

        try {
            $this->bcs->calcul($this->data);
        } catch (\Exception $e) {
            \Log::error('Erreur dans CalculOperationJob: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'data'=>$this->data
            ]);
    
            throw $e; // Optionnel : relancer si tu veux laisser Laravel gérer l’échec
        }
    }
} 