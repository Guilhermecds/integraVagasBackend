<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Funcionario;
use App\Models\Profissao;
use App\Exceptions\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class FuncionarioController extends Controller
{
    /**
     * Exibe todos os funcionários.
     */
    public function index()
    {
        try {
            $funcionarios = Funcionario::with('profissao')->get();

            return response()->json([
                'message' => 'Lista de funcionários',
                'funcionarios' => $funcionarios
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
     * Cria um novo funcionário.
     */
    public function store(Request $request)
    {
        try {
            // Criar um validador
            $validator = Validator::make($request->all(), [
                'nome' => 'required|string|max:255',
                'profissao_id' => 'required|exists:profissao,id',
                'data_admissao' => 'required|date',
                'data_nascimento' => 'required|date',
                'rg' => 'nullable|string|max:20',
                'cpf' => 'unique:funcionario,cpf|max:11|min:11|required',
                'endereco' => 'nullable|string|max:255',
                'telefone' => 'string|max:11|min:10',
                'sexo' => 'in:Masculino,Feminino,Outro',
            ], [
                'profissao_id.exists' => 'A profissão selecionada não existe.',
                'cpf.unique' => 'O CPF já está em uso.',
                'cpf.max' => 'O CPF não pode ter mais de 11 caracteres.',
                'cpf.min' => 'O CPF deve ter pelo menos 11 caracteres.',
                'telefone.max' => 'O telefone não pode ter mais de 11 caracteres.',
                'telefone.min' => 'O telefone deve ter pelo menos 10 caracteres.',
                'sexo.in' => 'O sexo deve ser Masculino, Feminino ou Outro.',
                'rg.max' => 'O RG não pode ter mais de 20 caracteres.',
                'endereco.max' => 'O endereço não pode ter mais de 255 caracteres.',
            ]);

            // Verificar se falhou a validação
            if ($validator->fails()) {
                throw new \Exception($validator->errors()->first(), HttpResponse::HTTP_BAD_REQUEST);
            }

            // Criar um novo funcionário
            $funcionario = Funcionario::create($request->all());

            // Retornar uma resposta de sucesso
            return response()->json([
                'message' => 'Funcionário criado com sucesso',
                'funcionario' => $funcionario
            ], HttpResponse::HTTP_CREATED);

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw new ValidationException($e->getMessage(), $e->errors(), $e->status);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Exibe um funcionário específico.
     */
    public function show($id)
    {
        try {
            $funcionario = Funcionario::with('profissao')->findOrFail($id);

            return response()->json([
                'message' => 'Funcionário encontrado',
                'funcionario' => $funcionario
            ], HttpResponse::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Funcionário não encontrado'
            ], HttpResponse::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Atualiza um funcionário existente.
     */
    public function update(Request $request, $id)
    {
        try {
            // Encontrar o funcionário pelo ID
            $funcionario = Funcionario::findOrFail($id);

            // Criar um validador
            $validator = Validator::make($request->all(), [
                'nome' => 'required|string|max:255',
                'profissao_id' => 'required|exists:profissao,id',
                'data_admissao' => 'required|date',
                'data_nascimento' => 'required|date',
                'rg' => 'nullable|string|max:20',
                'cpf' => [
                    'max:11',
                    'min:11',
                    Rule::unique('funcionario')->ignore($funcionario->id),
                ],
                'endereco' => 'nullable|string|max:255',
                'telefone' => 'string|max:11|min:10',
                'sexo' => 'in:Masculino,Feminino,Outro',
            ], [
                'profissao_id.exists' => 'A profissão selecionada não existe.',
                'cpf.unique' => 'O CPF já está em uso.',
                'cpf.max' => 'O CPF não pode ter mais de 11 caracteres.',
                'cpf.min' => 'O CPF deve ter pelo menos 11 caracteres.',
                'telefone.max' => 'O telefone não pode ter mais de 11 caracteres.',
                'telefone.min' => 'O telefone deve ter pelo menos 10 caracteres.',
                'sexo.in' => 'O sexo deve ser Masculino, Feminino ou Outro.',
                'rg.max' => 'O RG não pode ter mais de 20 caracteres.',
                'endereco.max' => 'O endereço não pode ter mais de 255 caracteres.',
            ]);

            // Verificar se falhou a validação
            if ($validator->fails()) {
                throw new \Exception($validator->errors()->first(), HttpResponse::HTTP_BAD_REQUEST);
            }

            // Atualizar os campos do funcionário
            $funcionario->fill($request->all());
            $funcionario->save();

            // Retornar uma resposta de sucesso
            return response()->json([
                'message' => 'Funcionário atualizado com sucesso',
                'funcionario' => $funcionario
            ], HttpResponse::HTTP_OK);

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw new ValidationException($e->getMessage(), $e->errors(), $e->status);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Funcionário não encontrado'
            ], HttpResponse::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove um funcionário específico.
     */
    public function destroy($id)
    {
        try {
            $funcionario = Funcionario::findOrFail($id);
            $funcionario->delete();

            // Retornar uma resposta de sucesso
            return response()->json([
                'message' => 'Funcionário deletado com sucesso'
            ], HttpResponse::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Funcionário não encontrado'
            ], HttpResponse::HTTP_NOT_FOUND);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'error' => 'Não é possível deletar o funcionário. Ela está sendo referenciada por outras tabelas.'
            ], HttpResponse::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
