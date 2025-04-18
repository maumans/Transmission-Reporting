<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rubrique extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'code',
        'description',
        'fichier_modele',
        'actif',
        'parent_id'
    ];

    protected $casts = [
        'actif' => 'boolean',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Rubrique::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Rubrique::class, 'parent_id');
    }

    public function apis(): HasMany
    {
        return $this->hasMany(RubriqueApi::class);
    }

    public function transmissions(): HasMany
    {
        return $this->hasMany(Operation::class);
    }

    public function operations(): HasMany
    {
        return $this->hasMany(Operation::class);
    }

    public function operationRubriques(): HasMany
    {
        return $this->hasMany(OperationRubrique::class);
    }
} 