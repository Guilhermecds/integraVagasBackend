<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Usuario extends Model
{
    use HasFactory;

    protected $table = 'usuario';

    protected $fillable = [
        'nome',
        'senha',
        'sou_deficiente',
        'data_nascimento',
        'idtipousuario',
        'email',
        'telefone',
        'cpf',
        'cep',
        'logradouro',
        'numero',
        'complemento',
        'cidade',
        'curriculo',
        'idsituacaousuario',
        'idformacao',
    ];

    public function tipoUsuario()
    {
        return $this->belongsTo(TipoUsuario::class, 'idtipousuario');
    }

    public function formacao()
    {
        return $this->belongsTo(Formacao::class, 'idformacao');
    }
}