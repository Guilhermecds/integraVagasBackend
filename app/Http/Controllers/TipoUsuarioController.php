<?php

namespace App\Http\Controllers;

use App\Models\TipoUsuario;

class TipoUsuarioController extends Controller
{
    /**
     * Retorna todos os registros de tipo de usuário, exceto o tipo com id 3.
     *
     * @return JsonResponse
     */
    public function index()
    {
        try {
            $tiposUsuario = TipoUsuario::where('id', '!=', 3)->get();
            return response()->json(['tiposUsuario' => $tiposUsuario], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao buscar tipos de usuário'], 500);
        }
    }
}
