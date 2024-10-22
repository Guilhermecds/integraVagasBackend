<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vaga;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class VagaController extends Controller
{
    /**
     * Exibe todas as vagas.
     */
    public function index()
    {
        $vagas = Vaga::all();
        return response()->json(['vagas' => $vagas], HttpResponse::HTTP_OK);
    }

    /**
     * Cria uma nova vaga.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:255',
            'requisitos' => 'required|string',
            'vaga_apenas_deficiente' => 'boolean',
            'idade_minima' => 'nullable|integer',
            'cep' => 'nullable|string|max:10',
            'logradouro' => 'nullable|string|max:255',
            'numero' => 'nullable|string|max:10',
            'complemento' => 'nullable|string|max:255',
            'cidade' => 'nullable|string|max:255',
            'descricao' => 'nullable|string',
            'idsituacaovaga' => 'required|boolean',
            'bonus' => 'nullable|string|max:255',
            // Adicione outras validações conforme necessário
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], HttpResponse::HTTP_BAD_REQUEST);
        }

        $vaga = Vaga::create($request->all());

        return response()->json(['message' => 'Vaga criada com sucesso.', 'vaga' => $vaga], HttpResponse::HTTP_CREATED);
    }

    /**
     * Exibe uma vaga específica.
     */
    public function show($id)
    {
        $vaga = Vaga::find($id);

        if (!$vaga) {
            return response()->json(['error' => 'Vaga não encontrada.'], HttpResponse::HTTP_NOT_FOUND);
        }

        return response()->json(['vaga' => $vaga], HttpResponse::HTTP_OK);
    }

    /**
     * Atualiza uma vaga existente.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:255',
            'requisitos' => 'required|string',
            'vaga_apenas_deficiente' => 'boolean',
            'idade_minima' => 'nullable|integer',
            'cep' => 'nullable|string|max:10',
            'logradouro' => 'nullable|string|max:255',
            'numero' => 'nullable|string|max:10',
            'complemento' => 'nullable|string|max:255',
            'cidade' => 'nullable|string|max:255',
            'descricao' => 'nullable|string',
            'idsituacaovaga' => 'required|boolean',
            'bonus' => 'nullable|string|max:255',
            // Adicione outras validações conforme necessário
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], HttpResponse::HTTP_BAD_REQUEST);
        }

        $vaga = Vaga::find($id);

        if (!$vaga) {
            return response()->json(['error' => 'Vaga não encontrada.'], HttpResponse::HTTP_NOT_FOUND);
        }

        $vaga->update($request->all());

        return response()->json(['message' => 'Vaga atualizada com sucesso.', 'vaga' => $vaga], HttpResponse::HTTP_OK);
    }

    /**
     * Inativa uma vaga.
     */
    public function inativar($id)
    {
        $vaga = Vaga::find($id);

        if (!$vaga) {
            return response()->json(['error' => 'Vaga não encontrada.'], HttpResponse::HTTP_NOT_FOUND);
        }

        // Aqui você pode implementar a lógica de inativação, por exemplo, removendo ou atualizando o estado
        $vaga->delete(); // Ou uma lógica de inativação

        return response()->json(['message' => 'Vaga inativada com sucesso.'], HttpResponse::HTTP_OK);
    }
}
