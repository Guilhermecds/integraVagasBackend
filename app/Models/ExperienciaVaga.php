<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExperienciaVaga extends Model
{
    use HasFactory;

    protected $table = 'experienciavaga';

    protected $fillable = [
        'idvaga',
        'idexperiencia',
    ];

    public function vaga()
    {
        return $this->belongsTo(Vaga::class, 'idvaga');
    }

    public function experiencia()
    {
        return $this->belongsTo(Experiencia::class, 'idexperiencia');
    }
}
