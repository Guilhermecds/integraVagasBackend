<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Experiencia;
use App\Models\ExperienciaUsuario;
use App\Models\ExperienciaVaga;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Illuminate\Support\Facades\DB;

class ExperienciaController extends Controller
{
    /**
     * Exibe todas as experiências.
     */
    public function index()
    {
        $experiencias = Experiencia::all();
        return response()->json(['experiencias' => $experiencias], HttpResponse::HTTP_OK);
    }

    /**
     * Cria uma nova experiência.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'descricao' => 'required|string|max:255',
            // Adicione outras validações conforme necessário
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], HttpResponse::HTTP_BAD_REQUEST);
        }

        $experiencia = Experiencia::create($request->all());

        return response()->json(['message' => 'Experiência criada com sucesso.', 'experiencia' => $experiencia], HttpResponse::HTTP_CREATED);
    }

    /**
     * Exibe uma experiência específica.
     */
    public function show($id)
    {
        $experiencia = Experiencia::find($id);

        if (!$experiencia) {
            return response()->json(['error' => 'Experiência não encontrada.'], HttpResponse::HTTP_NOT_FOUND);
        }

        return response()->json(['experiencia' => $experiencia], HttpResponse::HTTP_OK);
    }

    /**
     * Atualiza uma experiência existente.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'descricao' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], HttpResponse::HTTP_BAD_REQUEST);
        }

        $experiencia = Experiencia::find($id);

        if (!$experiencia) {
            return response()->json(['error' => 'Experiência não encontrada.'], HttpResponse::HTTP_NOT_FOUND);
        }

        $experiencia->update($request->all());

        return response()->json(['message' => 'Experiência atualizada com sucesso.', 'experiencia' => $experiencia], HttpResponse::HTTP_OK);
    }


    public function destroy($id)
    {
        $experiencia = Experiencia::find($id);

        if (!$experiencia) {
            return response()->json(['error' => 'Experiência não encontrada.'], HttpResponse::HTTP_NOT_FOUND);
        }

        $usuarioComExperiencia = ExperienciaUsuario::where('idexperiencia', $id)->exists();
        $vagaComExperiencia = ExperienciaVaga::where('idexperiencia', $id)->exists();

        if ($usuarioComExperiencia || $vagaComExperiencia) {
            return response()->json(['error' => 'Não é possível excluir a experiência, pois ela está vinculada a um usuário ou vaga.'], HttpResponse::HTTP_BAD_REQUEST);
        }

        $experiencia->delete();

        return response()->json(['message' => 'Experiência deletada com sucesso.'], HttpResponse::HTTP_OK);
    }
}
