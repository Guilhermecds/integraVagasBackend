<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Formacao;
use App\Models\Usuario;
use App\Models\Vaga;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class FormacaoController extends Controller
{
    /**
     * Exibe todas as formações.
     */
    public function index()
    {
        $formacoes = Formacao::all();
        return response()->json(['formacoes' => $formacoes], HttpResponse::HTTP_OK);
    }

    /**
     * Cria uma nova formação.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'descricao' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], HttpResponse::HTTP_BAD_REQUEST);
        }

        $formacao = Formacao::create($request->all());

        return response()->json(['message' => 'Formação criada com sucesso.', 'formacao' => $formacao], HttpResponse::HTTP_CREATED);
    }

    /**
     * Exibe uma formação específica.
     */
    public function show($id)
    {
        $formacao = Formacao::find($id);

        if (!$formacao) {
            return response()->json(['error' => 'Formação não encontrada.'], HttpResponse::HTTP_NOT_FOUND);
        }

        return response()->json(['formacao' => $formacao], HttpResponse::HTTP_OK);
    }

    /**
     * Atualiza uma formação existente.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'descricao' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], HttpResponse::HTTP_BAD_REQUEST);
        }

        $formacao = Formacao::find($id);

        if (!$formacao) {
            return response()->json(['error' => 'Formação não encontrada.'], HttpResponse::HTTP_NOT_FOUND);
        }

        $formacao->update($request->all());

        return response()->json(['message' => 'Formação atualizada com sucesso.', 'formacao' => $formacao], HttpResponse::HTTP_OK);
    }

    /**
     * Deleta uma formação existente.
     */
    public function destroy($id)
    {
        $formacao = Formacao::find($id);

        if (!$formacao) {
            return response()->json(['error' => 'Formação não encontrada.'], HttpResponse::HTTP_NOT_FOUND);
        }

        $usuarioComFormacao = Usuario::where('idformacao', $id)->exists();
        $vagaComFormacao = Vaga::where('idformacao', $id)->exists();

        if ($usuarioComFormacao || $vagaComFormacao) {
            return response()->json(['error' => 'Não é possível deletar esta formação, pois ela está vinculada a usuários ou vagas.'], HttpResponse::HTTP_BAD_REQUEST);
        }

        $formacao->delete();

        return response()->json(['message' => 'Formação deletada com sucesso.'], HttpResponse::HTTP_OK);
    }
}
