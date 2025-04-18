<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RubriqueApi extends Model
{
    use HasFactory;

    protected $fillable = [
        'rubrique_id',
        'nom',
        'endpoint',
        'methode',
        'groupe',
        'actif'
    ];

    protected $casts = [
        'actif' => 'boolean',
    ];

    public function rubrique(): BelongsTo
    {
        return $this->belongsTo(Rubrique::class);
    }

    public function executions(): HasMany
    {
        return $this->hasMany(OperationExecution::class);
    }

    public function execute($data)
    {
        // Implémentation de l'exécution de l'API
        // À adapter selon vos besoins
        return true;
    }
} 