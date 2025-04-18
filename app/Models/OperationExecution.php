<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OperationExecution extends Model
{
    use HasFactory;

    protected $fillable = [
        'operation_id',
        'rubrique_api_id',
        'statut',
        'date_execution',
        'erreur',
    ];

    protected $casts = [
        'date_execution' => 'datetime',
    ];

    const STATUT_EN_ATTENTE = 'en_attente';
    const STATUT_EN_COURS = 'en_cours';
    const STATUT_REUSSIE = 'reussie';
    const STATUT_ECHOUEE = 'echouee';

    public function operation(): BelongsTo
    {
        return $this->belongsTo(Operation::class);
    }

    public function rubriqueApi(): BelongsTo
    {
        return $this->belongsTo(RubriqueApi::class);
    }
} 