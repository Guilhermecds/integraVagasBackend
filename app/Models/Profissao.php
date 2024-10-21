<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profissao extends Model
{
    use HasFactory;

    protected $table = 'profissao'; // nome da tabela no banco de dados

    protected $fillable = [
        'nome',
    ];

    public $timestamps = true; // se a tabela possui timestamps (created_at e updated_at)

    // Relacionamentos, acessores, mutadores e outros métodos podem ser adicionados aqui conforme necessário
}
