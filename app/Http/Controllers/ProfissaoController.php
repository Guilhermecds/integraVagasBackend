<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Profissao;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class ProfissaoController extends Controller
{
    public function index()
    {
        try {
            $profissoes = Profissao::all();

            return response()->json([
                'message' => 'Lista de profissões',
                'profissoes' => $profissoes
            ], HttpResponse::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Nenhuma profissão encontrada'
            ], HttpResponse::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nome' => 'required|string|max:255',
            ], [
                'nome.required' => 'O campo nome é obrigatório.',
                'nome.string' => 'O campo nome deve ser uma string.',
                'nome.max' => 'O campo nome deve ter no máximo 255 caracteres.',
            ]);

            if ($validator->fails()) {
                throw new \Exception($validator->errors()->first(), HttpResponse::HTTP_BAD_REQUEST);
            }

            $profissao = Profissao::create($request->all());

            return response()->json([
                'message' => 'Profissão criada com sucesso',
                'profissao' => $profissao
            ], HttpResponse::HTTP_CREATED);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    public function show($id)
    {
        try {
            $profissao = Profissao::findOrFail($id);

            return response()->json([
                'message' => 'Profissão encontrada',
                'profissao' => $profissao
            ], HttpResponse::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Profissão não encontrada'], HttpResponse::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $profissao = Profissao::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'nome' => 'required|string|max:255' . $profissao->id,
            ], [
                'nome.required' => 'O campo nome é obrigatório.',
                'nome.string' => 'O campo nome deve ser uma string.',
                'nome.max' => 'O campo nome deve ter no máximo 255 caracteres.',
            ]);

            if ($validator->fails()) {
                throw new \Exception($validator->errors()->first(), HttpResponse::HTTP_BAD_REQUEST);
            }

            $profissao->update($request->all());

            return response()->json([
                'message' => 'Profissão atualizada com sucesso',
                'profissao' => $profissao
            ], HttpResponse::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Profissão não encontrada'], HttpResponse::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy($id)
    {
        try {
            $profissao = Profissao::findOrFail($id);
            $profissao->delete();

            return response()->json(['message' => 'Profissão deletada com sucesso'], HttpResponse::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Profissão não encontrada'], HttpResponse::HTTP_NOT_FOUND);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'error' => 'Não é possível deletar a profissão. Ela está sendo referenciada por outras tabelas.'
            ], HttpResponse::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
