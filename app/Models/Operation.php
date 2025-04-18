<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Operation extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function rubrique(): BelongsTo
    {
        return $this->belongsTo(Rubrique::class);
    }

    public function declarant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'declarant_id');
    }

    public function valideur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'valideur_id');
    }

    public function executions(): HasMany
    {
        return $this->hasMany(OperationExecution::class);
    }

    public function getStatutLabelAttribute(): string
    {
        return match($this->statut) {
            'en_attente' => 'En attente',
            'en_cours' => 'En cours',
            'termine' => 'Terminé',
            'erreur' => 'Erreur',
            default => $this->statut,
        };
    }

    public function getFichierBalanceUrlAttribute(): ?string
    {
        return $this->fichier_balance ? Storage::url($this->fichier_balance) : null;
    }

    public function operationRubriques(): HasMany
    {
        return $this->hasMany(OperationRubrique::class);
    }
} 