<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequisitoCampoVagaUsuario extends Model
{
    use HasFactory;

    protected $table = 'requisitocampovagausuario';

    protected $fillable = ['idvaga', 'idrequisitocampo', 'idusuario'];

    /**
     * Relacionamento com a tabela Vaga.
     */
    public function vaga()
    {
        return $this->belongsTo(Vaga::class, 'idvaga');
    }

    /**
     * Relacionamento com a tabela Requisitocampo.
     */
    public function requisitocampo()
    {
        return $this->belongsTo(Requisitocampo::class, 'idrequisitocampo');
    }

    /**
     * Relacionamento com a tabela Usuario.
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'idusuario');
    }
}
