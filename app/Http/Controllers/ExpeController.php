<?php

namespace App\Http\Controllers;

use App\Exceptions\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Login;
use App\Models\Sessao;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class LoginController extends Controller
{
    /**
     * Registrar um novo usuário.
     */
    public function register(Request $request)
    {
        try {
            // Validar a entrada do usuário
            $request->validate([
                'cpf' => ['required', 'unique:login', 'max:11', 'min:11'],
                'password' => ['required', 'min:8'],
            ], [
                'cpf.unique' => 'Este CPF já está em uso.',
                'cpf.max' => 'O CPF não pode ter mais de 11 caracteres.',
                'cpf.min' => 'O CPF não pode ter menos de 11 caracteres.',
                'password.min' => 'A senha deve ter pelo menos 8 caracteres.',
            ]);
    
            // Criar um novo usuário
            $user = Login::create([
                'cpf' => $request->cpf,
                'password' => Hash::make($request->password),
            ]);
    
            // Retornar uma resposta de sucesso
            return response()->json([
                'message' => 'Usuário registrado com sucesso',
                'user' => $user
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw new ValidationException($e->getMessage(), $e->errors(), $e->status);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Realizar o login do usuário.
     */
    public function login(Request $request)
    {
        try {
            // Validar a entrada do usuário
            $request->validate([
                'cpf' => 'required',
                'password' => 'required',
            ]);

            // Encontrar o usuário pelo CPF
            $user = Login::where('cpf', $request->cpf)->first();

            // Verificar se o usuário existe e se a senha está correta
            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'message' => 'Credenciais inválidas'
                ], 401);
            }

            // Verificar se já existe uma sessão ativa para o usuário
            $sessao = Sessao::where('user_id', $user->id)->first();

            if (!$sessao) {
                $sessao = new Sessao();
                $sessao->id = $user->id;
                $sessao->user_id = $user->id;
                $sessao->ip_address = $request->ip();
                $sessao->user_agent = $request->userAgent();
                $sessao->payload = $user->cpf; 
                $sessao->last_activity = now()->timestamp; 
                $sessao->save();
            }

            // Retornar uma resposta de sucesso com a sessão
            return response()->json([
                'message' => 'Login realizado com sucesso',
                'user' => $user,
                'sessao' => $sessao
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw new ValidationException($e->getMessage(), $e->errors(), $e->status);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Listar todos os logins.
     */
    public function index()
    {
        try {
            $logins = Login::all();

            return response()->json([
                'message' => 'Lista de logins',
                'logins' => $logins
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
     * Exibe um login específico.
     */
    public function show($id)
    {
        try {
            $login = Login::findOrFail($id);

            return response()->json([
                'message' => 'Login encontrado',
                'login' => $login
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
     * Atualizar um login específico.
     */
    public function update(Request $request, $id)
    {
        try {
            // Encontrar o login pelo ID
            $login = Login::findOrFail($id);
    
            // Validar a entrada do usuário
            $request->validate([
                'cpf' => ['required', 'unique:login,cpf,' . $login->id, 'max:11', 'min:11'],
                'password' => ['required', 'min:8'],
            ], [
                'cpf.max' => 'O CPF não pode ter mais de 11 caracteres.',
                'cpf.min' => 'O CPF não pode ter menos de 11 caracteres.',
                'password.min' => 'A senha deve ter pelo menos 8 caracteres.',
            ]);
    
            // Atualizar os campos do login
            $login->cpf = $request->cpf;
            $login->password = Hash::make($request->password);
            $login->save();
    
            // Retornar uma resposta de sucesso
            return response()->json([
                'message' => 'Login atualizado com sucesso',
                'login' => $login
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw new ValidationException($e->getMessage(), $e->errors(), $e->status);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Deletar um login específico.
     */
    public function destroy($id)
    {
        try {
            $login = Login::findOrFail($id);
            $login->delete();

            return response()->json(['message' => 'Login deletado com sucesso'], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Login não encontrado'], 404);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'error' => 'Não é possível deletar o login. Ela está sendo referenciada por outras tabelas.'
            ], HttpResponse::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
