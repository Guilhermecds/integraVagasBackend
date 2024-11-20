<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class TokenLogin extends Model
{
    use HasFactory;

    protected $table = 'tokenlogin';

    protected $fillable = [
        'idlogin',
        'token',
    ];

    public function login()
    {
        return $this->belongsTo(Login::class, 'idlogin');
    }

}
