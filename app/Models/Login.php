<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Login extends Authenticatable
{
    use HasFactory;

    protected $table = 'login';

    protected $fillable = [
        'cpf_cnpj',
        'senha',
        'idusuario',
        'idempresa'
    ];

    protected $hidden = [
        'senha',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'idusuario');
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'idempresa');
    }
}
