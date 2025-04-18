<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Balance extends Model
{
    use HasFactory;

    protected $fillable = [
        'date_arretee',
        'statut',
        'etat',
        'declarant_id',
        'valideur_id',
        'fichier_balance',
        'date_transmission',
        'reponse_api',
    ];

    protected $casts = [
        'date_arretee' => 'datetime',
        'date_transmission' => 'datetime',
    ];

    public function declarant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'declarant_id');
    }

    public function valideur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'valideur_id');
    }

    public function getFichierBalanceUrlAttribute(): ?string
    {
        return $this->fichier_balance ? Storage::url($this->fichier_balance) : null;
    }

    public function getStatutLabelAttribute(): string
    {
        return match($this->statut) {
            'CREATION' => 'Création',
            'MODIFICATION' => 'Modification',
            'ANNULATION' => 'Annulation',
            default => $this->statut,
        };
    }

    public function getEtatLabelAttribute(): string
    {
        return match($this->etat) {
            'en_attente' => 'En attente',
            'en_cours' => 'En cours',
            'transmise' => 'Transmise',
            'erreur' => 'Erreur',
            default => $this->etat,
        };
    }
}
