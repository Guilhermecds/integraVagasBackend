<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsuarioVaga extends Model
{
    use HasFactory;

    protected $table = 'usuariovaga';

    protected $fillable = [
        'idusuario',
        'idvaga',
        'idempresa',
    ];

    // O Eloquent já assume que a tabela possui um campo 'id' como chave primária

    // Definindo relacionamentos
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'idusuario');
    }

    public function vaga()
    {
        return $this->belongsTo(Vaga::class, 'idvaga');
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'idempresa');
    }
}
