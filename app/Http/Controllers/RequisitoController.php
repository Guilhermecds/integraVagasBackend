<?php

namespace App\Http\Controllers;

use App\Models\Requisito;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class RequisitoController extends Controller
{
    /**
     * Exibe todos os requisitos da empresa, se idempresa for passado.
     */
    public function index(Request $request)
    {
        try {
            $ativos = $request->query('ativos');

            $query = Requisito::when($request->idempresa, function ($query) use ($request) {
                return $query->where('idempresa', $request->idempresa)
                             ->whereHas('empresa', function ($query) {
                                 $query->where('idsituacaoempresa', true);
                             });
            });

            if (!is_null($ativos)) {
                $query->where('idsituacaorequisito', 1);
            }

            $requisitos = $query->get();

            return response()->json($requisitos);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erro ao buscar requisitos. ' . $e->getMessage()], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Cria um novo requisito.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'descricao' => 'required|string|max:255',
                'idempresa' => 'nullable|exists:empresa,id',
                'idsituacaorequisito' => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], HttpResponse::HTTP_UNPROCESSABLE_ENTITY);
            }

            $requisito = Requisito::create([
                'descricao' => $request->descricao,
                'idempresa' => $request->idempresa,
                'idsituacaorequisito' => $request->idsituacaorequisito ? $request->idsituacaorequisito : true,
            ]);

            return response()->json($requisito, HttpResponse::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erro ao cadastrar o requisito. ' . $e->getMessage()], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Atualiza os dados de um requisito existente.
     */
    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'descricao' => 'required|string|max:255',
                'idempresa' => 'nullable|exists:empresa,id',
                'idsituacaorequisito' => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], HttpResponse::HTTP_UNPROCESSABLE_ENTITY);
            }

            $requisito = Requisito::findOrFail($id);
            $requisito->update([
                'descricao' => $request->descricao,
                'idempresa' => $request->idempresa,
                'idsituacaorequisito' => $request->idsituacaorequisito ? $request->idsituacaorequisito : true,
            ]);

            return response()->json($requisito);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Requisito não encontrado.'], HttpResponse::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erro ao atualizar o requisito. ' . $e->getMessage()], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Inativa um  requisito.
     */
    public function inativar($id)
    {
        try {
            $requisito = Requisito::findOrFail($id);

            $requisito->idsituacaorequisito = false;
            $requisito->save();

            return response()->json(['message' => 'Requisito inativado com sucesso.'], HttpResponse::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Requisito não encontrado.'], HttpResponse::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erro ao inativar o requisito. ' . $e->getMessage()], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Ativa um requisito.
     */
    public function ativar($id)
    {
        try {
            $requisito = Requisito::findOrFail($id);

            $requisito->idsituacaorequisito = true;
            $requisito->save();

            return response()->json(['message' => 'Requisito ativado com sucesso.'], HttpResponse::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Requisito não encontrado.'], HttpResponse::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erro ao ativar o requisito. ' . $e->getMessage()], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
