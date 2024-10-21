<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Usuario extends Model
{
    protected $table = 'usuario';

    protected $fillable = [
        'nome',
        'data_nascimento',
        'sexo',
        'rg',
        'cpf',
        'endereco',
        'telefone',
        'doencas_alergias',
        'alerta',
        'vacinas',
        'remedios',
    ];
}
