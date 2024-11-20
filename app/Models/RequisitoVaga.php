<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequisitoVaga extends Model
{
    use HasFactory;

    protected $table = 'requisitovaga';

    protected $fillable = ['idvaga', 'idrequisito'];

    /**
     * Relacionamento com a tabela Vaga.
     */
    public function vaga()
    {
        return $this->belongsTo(Vaga::class, 'idvaga');
    }

    /**
     * Relacionamento com a tabela Requisito.
     */
    public function requisito()
    {
        return $this->belongsTo(Requisito::class, 'idrequisito');
    }
}
