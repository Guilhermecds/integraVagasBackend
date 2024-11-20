<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Requisito extends Model
{
    use HasFactory;

    protected $table = 'requisito';

    protected $fillable = ['descricao', 'idempresa', 'idsituacaorequisito'];

    /**
     * Relacionamento com a tabela requisitovaga.
     */
    public function requisitovagas()
    {
        return $this->hasMany(RequisitoVaga::class, 'idrequisito');
    }

    /**
     * Relacionamento com a tabela requisitocampo.
     */
    public function requisitocampos()
    {
        return $this->hasMany(RequisitoCampo::class, 'idrequisito');
    }

    /**
     * Relacionamento com a tabela empresa.
     */
    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'idempresa');
    }

    /**
     * Relacionamento com a tabela requisitocampovagausuario.
     */
    public function requisitocampovagausuarios()
    {
        return $this->hasMany(RequisitoCampoVagaUsuario::class, 'idrequisitocampo');
    }
}
