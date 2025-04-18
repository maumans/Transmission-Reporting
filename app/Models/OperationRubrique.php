<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperationRubrique extends Model
{
    protected $table = 'operation_rubriques';

    protected $fillable = [
        'operation_id',
        'rubrique_id'
    ];

    public function operation()
    {
        return $this->belongsTo(Operation::class);
    }

    public function rubrique()
    {
        return $this->belongsTo(Rubrique::class);
    }
}
