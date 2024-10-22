<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;
use App\Models\Login;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class UsuarioController extends Controller
{
    /**
     * Exibe todos os usuários.
     */
    public function index()
    {
        $usuarios = Usuario::all();

        return response()->json([
            'message' => 'Lista de usuários',
            'usuarios' => $usuarios
        ], HttpResponse::HTTP_OK);
    }

    /**
     * Cria um novo usuário e o vincula ao login.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:255',
            'data_nascimento' => 'required|date',
            'idtipousuario' => 'required|exists:tipousuario,id',
            'email' => 'required|email|unique:usuario,email',
            'telefone' => 'nullable|string|max:15',
            'cpf' => 'required|string|unique:usuario,cpf',
            'idformacao' => 'required|exists:formacao,id',
            'senha' => 'required|string|min:8', // Adicione a validação da senha conforme necessário
            'experiencias' => 'nullable|array', // Adiciona uma validação para um array de experiências
            'experiencias.*' => 'exists:experiencia,id', // Cada experiência deve existir na tabela experiencia
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], HttpResponse::HTTP_BAD_REQUEST);
        }

        // Criar um novo usuário
        $usuario = Usuario::create([
            'nome' => $request->nome,
            'sou_deficiente' => $request->sou_deficiente ?? false,
            'data_nascimento' => $request->data_nascimento,
            'idtipousuario' => $request->idtipousuario,
            'email' => $request->email,
            'telefone' => $request->telefone,
            'cpf' => $request->cpf,
            'cep' => $request->cep,
            'logradouro' => $request->logradouro,
            'numero' => $request->numero,
            'complemento' => $request->complemento,
            'cidade' => $request->cidade,
            'curriculo' => $request->curriculo,
            'idsituacaousuario' => true, // Considerando que o usuário está ativo ao ser criado
            'idformacao' => $request->idformacao,
        ]);

        // Criar um login associado ao usuário
        $login = Login::create([
            'cpf_cnpj' => $request->cpf, // Ajuste conforme necessário
            'senha' => Hash::make($request->senha),
            'idusuario' => $usuario->id,
            // 'idempresa' => $request->idempresa, // Se aplicável
        ]);

        // Vincular experiências, se fornecidas
        if ($request->has('experiencias')) {
            foreach ($request->experiencias as $experienciaId) {
                ExperienciaUsuario::create([
                    'idusuario' => $usuario->id,
                    'idexperiencia' => $experienciaId,
                ]);
            }
        }

        return response()->json([
            'message' => 'Usuário criado com sucesso.',
            'usuario' => $usuario,
            'login' => $login,
        ], HttpResponse::HTTP_CREATED);
    }

    /**
     * Exibe um usuário específico.
     */
    public function show($id)
    {
        $usuario = Usuario::find($id);

        if (!$usuario) {
            return response()->json(['error' => 'Usuário não encontrado.'], HttpResponse::HTTP_NOT_FOUND);
        }

        return response()->json([
            'message' => 'Usuário encontrado.',
            'usuario' => $usuario,
        ], HttpResponse::HTTP_OK);
    }

    /**
     * Atualiza um usuário existente.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nome' => 'nullable|string|max:255',
            'data_nascimento' => 'nullable|date',
            'idtipousuario' => 'nullable|exists:tipousuario,id',
            'email' => 'nullable|email|unique:usuario,email,' . $id,
            'telefone' => 'nullable|string|max:15',
            'cpf' => 'nullable|string|unique:usuario,cpf,' . $id,
            'idformacao' => 'nullable|exists:formacao,id',
            'experiencias' => 'nullable|array', // Adiciona uma validação para um array de experiências
            'experiencias.*' => 'exists:experiencia,id', // Cada experiência deve existir na tabela experiencia
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], HttpResponse::HTTP_BAD_REQUEST);
        }

        $usuario = Usuario::find($id);

        if (!$usuario) {
            return response()->json(['error' => 'Usuário não encontrado.'], HttpResponse::HTTP_NOT_FOUND);
        }

        // Atualiza os campos do usuário
        $usuario->fill($request->only(['nome', 'data_nascimento', 'idtipousuario', 'email', 'telefone', 'cpf', 'idformacao']));
        $usuario->save();

        // Atualizar experiências, se fornecidas
        if ($request->has('experiencias')) {
            // Remove experiências existentes
            ExperienciaUsuario::where('idusuario', $usuario->id)->delete();

            // Adiciona as novas experiências
            foreach ($request->experiencias as $experienciaId) {
                ExperienciaUsuario::create([
                    'idusuario' => $usuario->id,
                    'idexperiencia' => $experienciaId,
                ]);
            }
        }

        return response()->json([
            'message' => 'Usuário atualizado com sucesso.',
            'usuario' => $usuario,
        ], HttpResponse::HTTP_OK);
    }

    /**
     * Inativa um usuário.
     */
    public function inativar($id)
    {
        $usuario = Usuario::find($id);

        if (!$usuario) {
            return response()->json(['error' => 'Usuário não encontrado.'], HttpResponse::HTTP_NOT_FOUND);
        }

        // Atualiza o status do usuário para inativo
        $usuario->idsituacaousuario = false; // Alterando a situação para inativo
        $usuario->save();

        return response()->json([
            'message' => 'Usuário inativado com sucesso.',
            'usuario' => $usuario,
        ], HttpResponse::HTTP_OK);
    }
}
