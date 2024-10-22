<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Formacao extends Model
{
    use HasFactory;

    protected $table = 'formacao';

    protected $fillable = [
        'descricao',
    ];

    // O Eloquent já assume que a tabela possui um campo 'id' como chave primária
    // Caso a chave primária tenha um nome diferente, use a linha abaixo:
    // protected $primaryKey = 'id';

    // Adicione relacionamentos se necessário
    // public function usuarios()
    // {
    //     return $this->hasMany(Usuario::class);
    // }
}
