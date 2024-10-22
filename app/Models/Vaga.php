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
        'requisitos',
        'vaga_apenas_deficiente',
        'idade_minima',
        'cep',
        'logradouro',
        'numero',
        'complemento',
        'cidade',
        'descricao',
        'idsituacaovaga',
        'bonus',
    ];

}
