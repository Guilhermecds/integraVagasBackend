<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agendamento extends Model
{
    use HasFactory;

    protected $table = 'agendamento';

    protected $fillable = [
        'data_horario_visita',
        'funcionario_id',
        'usuario_id',
        'vacinas',
    ];

    protected $casts = [
        'vacinas' => 'array',
        'data_horario_visita' => 'datetime',
    ];

    public function funcionario()
    {
        return $this->belongsTo(Funcionario::class);
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }
}
