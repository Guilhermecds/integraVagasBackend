<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vacina extends Model
{
    use HasFactory;

    protected $table = 'vacina';

    protected $fillable = [
        'nome_vacina',
        'quantidade_disponivel',
        'data_limite_vacinacao',
        'descricao',
    ];

    protected $dates = [
        'data_limite_vacinacao',
    ];
}
