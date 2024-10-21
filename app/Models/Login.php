<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Login extends Authenticatable
{
    use HasFactory;

    protected $table = 'login';

    protected $fillable = [
        'cpf',
        'password',
    ];

    protected $hidden = [
        'password',
    ];
}
