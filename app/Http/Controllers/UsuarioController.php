<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Usuario;
use App\Exceptions\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class UsuarioController extends Controller
{
    /**
     * Exibe todos os usuários.
     */
    public function index()
    {
        try {
            $usuarios = Usuario::all();

            return response()->json([
                'message' => 'Lista de usuários',
                'usuarios' => $usuarios
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
     * Cria um novo usuário.
     */
    public function store(Request $request)
    {
        try {
            // Criar um validador
            $validator = Validator::make($request->all(), [
                'nome' => 'required|string|max:255',
                'data_nascimento' => 'required|date',
                'sexo' => 'required|in:Masculino,Feminino,Outro',
                'cpf' => 'required|unique:usuario,cpf|max:11|min:11',
                'endereco' => 'nullable|string|max:255',
                'telefone' => 'nullable|string|max:11|min:10',
                'doencas_alergias' => 'nullable|string|max:500',
                'alerta' => 'nullable|boolean',
                'vacinas' => 'nullable|json',
                'remedios' => 'nullable|string|max:255',
            ], [
                'nome.required' => 'O campo nome é obrigatório.',
                'nome.string' => 'O campo nome deve ser uma string.',
                'nome.max' => 'O campo nome deve ter no máximo 255 caracteres.',
                'data_nascimento.required' => 'O campo data de nascimento é obrigatório.',
                'data_nascimento.date' => 'O campo data de nascimento deve ser uma data válida.',
                'sexo.required' => 'O campo sexo é obrigatório.',
                'sexo.in' => 'O campo sexo deve ser um dos seguintes valores: Masculino, Feminino, Outro.',
                'cpf.required' => 'O campo CPF é obrigatório.',
                'cpf.unique' => 'O CPF informado já está em uso.',
                'cpf.max' => 'O CPF não pode ter mais de 11 caracteres.',
                'cpf.min' => 'O CPF deve ter exatamente 11 caracteres.',
                'endereco.string' => 'O campo endereço deve ser uma string.',
                'endereco.max' => 'O campo endereço deve ter no máximo 255 caracteres.',
                'telefone.string' => 'O campo telefone deve ser uma string.',
                'telefone.max' => 'O campo telefone deve ter no máximo 11 caracteres.',
                'telefone.min' => 'O campo telefone deve ter no mínimo 10 caracteres.',
                'doencas_alergias.string' => 'O campo doenças e alergias deve ser uma string.',
                'doencas_alergias.max' => 'O campo doenças e alergias deve ter no máximo 500 caracteres.',
                'alerta.boolean' => 'O campo alerta deve ser verdadeiro ou falso.',
                'vacinas.json' => 'O campo vacinas deve ser um JSON válido.',
                'remedios.string' => 'O campo remédios deve ser uma string.',
                'remedios.max' => 'O campo remédios deve ter no máximo 255 caracteres.',
            ]);

            // Verificar se falhou a validação
            if ($validator->fails()) {
                throw new \Exception($validator->errors()->first(), HttpResponse::HTTP_BAD_REQUEST);
            }

            // Criar um novo usuário
            $usuario = Usuario::create($request->all());

            // Retornar uma resposta de sucesso
            return response()->json([
                'message' => 'Usuário criado com sucesso',
                'usuario' => $usuario
            ], HttpResponse::HTTP_CREATED);

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw new ValidationException($e->getMessage(), $e->errors(), $e->status);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Exibe um usuário específico.
     */
    public function show($id)
    {
        try {
            $usuario = Usuario::findOrFail($id);

            return response()->json([
                'message' => 'Usuário encontrado',
                'usuario' => $usuario
            ], HttpResponse::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Usuário não encontrado'
            ], HttpResponse::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Atualiza um usuário existente.
     */
    public function update(Request $request, $id)
    {
        try {
            // Encontrar o usuário pelo ID
            $usuario = Usuario::findOrFail($id);

            // Criar um validador
            $validator = Validator::make($request->all(), [
                'nome' => 'required|string|max:255',
                'data_nascimento' => 'required|date',
                'sexo' => 'required|in:Masculino,Feminino,Outro',
                'cpf' => [
                    'required',
                    'max:11',
                    'min:11',
                    Rule::unique('usuario')->ignore($usuario->id),
                ],
                'endereco' => 'nullable|string|max:255',
                'telefone' => 'nullable|string|max:11|min:10',
                'doencas_alergias' => 'nullable|string|max:500',
                'alerta' => 'nullable|boolean',
                'vacinas' => 'nullable|json',
                'remedios' => 'nullable|string|max:255',
            ], [
                'nome.required' => 'O campo nome é obrigatório.',
                'nome.string' => 'O campo nome deve ser uma string.',
                'nome.max' => 'O campo nome deve ter no máximo 255 caracteres.',
                'data_nascimento.required' => 'O campo data de nascimento é obrigatório.',
                'data_nascimento.date' => 'O campo data de nascimento deve ser uma data válida.',
                'sexo.required' => 'O campo sexo é obrigatório.',
                'sexo.in' => 'O campo sexo deve ser um dos seguintes valores: Masculino, Feminino, Outro.',
                'cpf.required' => 'O campo CPF é obrigatório.',
                'cpf.unique' => 'O CPF informado já está em uso.',
                'cpf.max' => 'O CPF não pode ter mais de 11 caracteres.',
                'cpf.min' => 'O CPF deve ter exatamente 11 caracteres.',
                'endereco.string' => 'O campo endereço deve ser uma string.',
                'endereco.max' => 'O campo endereço deve ter no máximo 255 caracteres.',
                'telefone.string' => 'O campo telefone deve ser uma string.',
                'telefone.max' => 'O campo telefone deve ter no máximo 11 caracteres.',
                'telefone.min' => 'O campo telefone deve ter no mínimo 10 caracteres.',
                'doencas_alergias.string' => 'O campo doenças e alergias deve ser uma string.',
                'doencas_alergias.max' => 'O campo doenças e alergias deve ter no máximo 500 caracteres.',
                'alerta.boolean' => 'O campo alerta deve ser verdadeiro ou falso.',
                'vacinas.json' => 'O campo vacinas deve ser um JSON válido.',
                'remedios.string' => 'O campo remédios deve ser uma string.',
                'remedios.max' => 'O campo remédios deve ter no máximo 255 caracteres.',
            ]);

            // Verificar se falhou a validação
            if ($validator->fails()) {
                throw new \Exception($validator->errors()->first(), HttpResponse::HTTP_BAD_REQUEST);
            }

            // Atualizar os campos do usuário
            $usuario->fill($request->all());
            $usuario->save();

            // Retornar uma resposta de sucesso
            return response()->json([
                'message' => 'Usuário atualizado com sucesso',
                'usuario' => $usuario
            ], HttpResponse::HTTP_OK);

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw new ValidationException($e->getMessage(), $e->errors(), $e->status);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Usuário não encontrado'
            ], HttpResponse::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove um usuário específico.
     */
    public function destroy($id)
    {
        try {
            $usuario = Usuario::findOrFail($id);
            $usuario->delete();

            // Retornar uma resposta de sucesso
            return response()->json([
                'message' => 'Usuário deletado com sucesso'
            ], HttpResponse::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Usuário não encontrado'
            ], HttpResponse::HTTP_NOT_FOUND);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'error' => 'Não é possível deletar o usúario. Ela está sendo referenciada por outras tabelas.'
            ], HttpResponse::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

     /**
     * Busca um usuário pelo CPF.
     */
    public function findByCpf($cpf)
    {
        try {
            // Validar o campo CPF no request
            $validator = Validator::make(['cpf' => $cpf], [
                'cpf' => 'required|string|max:11|min:11',
            ], [
                'cpf.required' => 'O campo CPF é obrigatório.',
                'cpf.string' => 'O campo CPF deve ser uma string.',
                'cpf.max' => 'O CPF não pode ter mais de 11 caracteres.',
                'cpf.min' => 'O CPF deve ter exatamente 11 caracteres.',
            ]);
            
            if ($validator->fails()) {
                throw new \Exception($validator->errors()->first(), HttpResponse::HTTP_BAD_REQUEST);
            }

            // Buscar o usuário pelo CPF
            $usuario = Usuario::where('cpf', $cpf)->first();

            // Retornar o usuário encontrado
            return response()->json([
                'message' => 'Usuário encontrado',
                'usuario' => $usuario
            ], HttpResponse::HTTP_OK);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Usuário não encontrado'
            ], HttpResponse::HTTP_NOT_FOUND);
        }  catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
