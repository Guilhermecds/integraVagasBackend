<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Funcionario extends Model
{
    use HasFactory;

    protected $table = 'funcionario';

    protected $fillable = [
        'nome',
        'profissao_id',
        'data_admissao',
        'data_nascimento',
        'rg',
        'cpf',
        'endereco',
        'telefone',
        'sexo',
    ];

    protected $dates = [
        'data_admissao',
        'data_nascimento',
    ];

    public function profissao()
    {
        return $this->belongsTo(Profissao::class);
    }
}
