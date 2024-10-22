<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vacina;
use App\Exceptions\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class VacinaController extends Controller
{
    /**
     * Exibe todas as vacinas.
     */
    public function index()
    {
        try {
            $vacinas = Vacina::all();

            return response()->json([
                'message' => 'Lista de vacinas',
                'vacinas' => $vacinas
            ], HttpResponse::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Nenhum login encontrado'
            ], HttpResponse::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Cria uma nova vacina.
     */
    public function store(Request $request)
    {
        try {
            // Criar um validador
            $validator = Validator::make($request->all(), [
                'nome_vacina' => 'required|string|max:255',
                'quantidade_disponivel' => 'required|integer|min:0',
                'data_limite_vacinacao' => 'required|date',
                'descricao' => 'nullable|string'
            ], [
                'required' => 'O campo :attribute é obrigatório.',
                'string' => 'O campo :attribute deve ser uma string.',
                'max' => 'O campo :attribute deve ter no máximo :max caracteres.',
                'integer' => 'O campo :attribute deve ser um número inteiro.',
                'min' => 'O campo :attribute deve ter um valor mínimo de :min.',
                'date' => 'O campo :attribute deve ser uma data válida.'
            ]);

            // Verificar se falhou a validação
            if ($validator->fails()) {
                throw new \Exception($validator->errors()->first(), HttpResponse::HTTP_BAD_REQUEST);
            }

            // Criar uma nova vacina
            $vacina = Vacina::create($request->all());

            // Retornar uma resposta de sucesso
            return response()->json([
                'message' => 'Vacina criada com sucesso',
                'vacina' => $vacina
            ], HttpResponse::HTTP_CREATED);

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw new ValidationException($e->getMessage(), $e->errors(), $e->status);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Exibe uma vacina específica.
     */
    public function show($id)
    {
        try {
            $vacina = Vacina::findOrFail($id);

            return response()->json([
                'message' => 'Vacina encontrada',
                'vacina' => $vacina
            ], HttpResponse::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Vacina não encontrada'
            ], HttpResponse::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Atualiza uma vacina existente.
     */
    public function update(Request $request, $id)
    {
        try {
            // Encontrar a vacina pelo ID
            $vacina = Vacina::findOrFail($id);

            // Criar um validador
            $validator = Validator::make($request->all(), [
                'nome_vacina' => 'required|string|max:255',
                'quantidade_disponivel' => 'required|integer|min:0',
                'data_limite_vacinacao' => 'required|date',
                'descricao' => 'nullable|string'
            ], [
                'required' => 'O campo :attribute é obrigatório.',
                'string' => 'O campo :attribute deve ser uma string.',
                'max' => 'O campo :attribute deve ter no máximo :max caracteres.',
                'integer' => 'O campo :attribute deve ser um número inteiro.',
                'min' => 'O campo :attribute deve ter um valor mínimo de :min.',
                'date' => 'O campo :attribute deve ser uma data válida.'
            ]);

            // Verificar se falhou a validação
            if ($validator->fails()) {
                throw new \Exception($validator->errors()->first(), HttpResponse::HTTP_BAD_REQUEST);
            }

            // Atualizar os campos da vacina
            $vacina->fill($request->all());
            $vacina->save();

            // Retornar uma resposta de sucesso
            return response()->json([
                'message' => 'Vacina atualizada com sucesso',
                'vacina' => $vacina
            ], HttpResponse::HTTP_OK);

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw new ValidationException($e->getMessage(), $e->errors(), $e->status);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Vacina não encontrada'
            ], HttpResponse::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove uma vacina específica.
     */
    public function destroy($id)
    {
        try {
            $vacina = Vacina::findOrFail($id);
            $vacina->delete();

            // Retornar uma resposta de sucesso
            return response()->json([
                'message' => 'Vacina deletada com sucesso'
            ], HttpResponse::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Vacina não encontrada'
            ], HttpResponse::HTTP_NOT_FOUND);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'error' => 'Não é possível deletar a vacina. Ela está sendo referenciada por outras tabelas.'
            ], HttpResponse::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
