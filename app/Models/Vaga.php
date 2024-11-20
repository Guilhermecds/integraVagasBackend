<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vaga extends Model
{
    use HasFactory;

    protected $table = 'vaga';

    protected $fillable = [
        'nome',
        'vaga_apenas_deficiente',
        'cep',
        'logradouro',
        'numero',
        'complemento',
        'cidade',
        'descricao',
        'idempresa',
        'uf',
        'bairro',
        'idsituacaovaga',
        'idformacao',
        'requisitos_bonus',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'idempresa');
    }

    public function formacao()
    {
        return $this->belongsTo(Formacao::class, 'idformacao');
    }

    public function usuariovagas()
    {
        return $this->hasMany(UsuarioVaga::class, 'idvaga');
    }

    public function requisitosvaga()
    {
        return $this->hasMany(RequisitoVaga::class, 'idvaga');
    }
}
