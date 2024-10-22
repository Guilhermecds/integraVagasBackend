<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExperienciaUsuario extends Model
{
    use HasFactory;

    protected $table = 'experienciausuario';

    protected $fillable = [
        'idusuario',
        'idexperiencia',
    ];

    // O Eloquent já assume que a tabela possui um campo 'id' como chave primária

    // Definindo relacionamentos
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'idusuario');
    }

    public function experiencia()
    {
        return $this->belongsTo(Experiencia::class, 'idexperiencia');
    }
}
