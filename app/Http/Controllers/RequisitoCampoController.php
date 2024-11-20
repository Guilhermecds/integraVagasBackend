<?php

namespace App\Http\Controllers;

use App\Models\RequisitoCampo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class RequisitoCampoController extends Controller
{
    /**
     * Exibir todos os campos de requisito.
     */
    public function index(Request $request)
    {
        try {
            $idEmpresa = $request->query('idempresa');

            $requisitoCampos = RequisitoCampo::with('requisito')
                ->when($idEmpresa, function ($query) use ($idEmpresa) {
                    $query->whereHas('requisito', function ($query) use ($idEmpresa) {
                        $query->where('idempresa', $idEmpresa);
                    });
                })
                ->get();
                
            return response()->json($requisitoCampos);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erro ao buscar campos de requisito. ' . $e->getMessage()], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Cadastrar um novo campo de requisito.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'idrequisito' => 'required|exists:requisito,id',
                'nomecampo' => 'required|string|max:255',
                'score' => 'required|integer',
                'idsituacaocampo' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $requisitoCampo = RequisitoCampo::create([
                'idrequisito' => $request->idrequisito,
                'nomecampo' => $request->nomecampo,
                'score' => $request->score,
                'idsituacaocampo' => $request->has('idsituacaocampo') ? $request->idsituacaocampo : true
            ]);

            return response()->json($requisitoCampo, 201);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erro ao cadastrar o campo de requisito. ' . $e->getMessage()], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Editar um campo de requisito existente.
     */
    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'idrequisito' => 'required|exists:requisito,id',
                'nomecampo' => 'required|string|max:255',
                'score' => 'required|integer',
                'idsituacaocampo' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $requisitoCampo = RequisitoCampo::findOrFail($id);

            $requisitoCampo->update([
                'idrequisito' => $request->idrequisito,
                'nomecampo' => $request->nomecampo,
                'score' => $request->score,
                'idsituacaocampo' => $request->has('idsituacaocampo') ? $request->idsituacaocampo : true
            ]);

            return response()->json($requisitoCampo);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Campo de requisito não encontrado.'], HttpResponse::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erro ao atualizar o campo de requisito. ' . $e->getMessage()], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Inativa um campo de requisito.
     */
    public function inativar($id)
    {
        try {
            $requisitoCampo = RequisitoCampo::findOrFail($id);

            $requisitoCampo->idsituacaocampo = false;
            $requisitoCampo->save();

            return response()->json(['message' => 'Campo inativado com sucesso.'], HttpResponse::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Campo de requisito não encontrado.'], HttpResponse::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erro ao inativar o campo de requisito. ' . $e->getMessage()], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Ativa um campo de requisito.
     */
    public function ativar($id)
    {
        try {
            $requisitoCampo = RequisitoCampo::findOrFail($id);

            $requisitoCampo->idsituacaocampo = true;
            $requisitoCampo->save();

            return response()->json(['message' => 'Campo ativado com sucesso.'], HttpResponse::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Campo de requisito não encontrado.'], HttpResponse::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erro ao ativar o campo de requisito. ' . $e->getMessage()], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
