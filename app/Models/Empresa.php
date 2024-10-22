<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    use HasFactory;

    protected $table = 'empresa';

    protected $fillable = [
        'nome_fantasia',
        'razao_social',
        'cnpj',
        'telefone_corporativo',
        'email',
        'cep',
        'logradouro',
        'numero',
        'complemento',
        'cidade',
    ];
}