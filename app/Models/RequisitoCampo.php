<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequisitoCampo extends Model
{
    use HasFactory;

    protected $table = 'requisitocampo';

    protected $fillable = [
        'idrequisito',
        'nomecampo',
        'score',
        'idsituacaocampo'
    ];

    public function requisito()
    {
        return $this->belongsTo(Requisito::class, 'idrequisito');
    }
}