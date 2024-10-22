<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Login;
use App\Models\Usuario;
use App\Models\Empresa;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class LoginController extends Controller
{
    /**
     * Realiza o login.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cpf_cnpj' => 'required|string|max:14',
            'senha' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], HttpResponse::HTTP_BAD_REQUEST);
        }

        $login = Login::where('cpf_cnpj', $request->cpf_cnpj)->first();

        if (!$login || !Hash::check($request->senha, $login->senha)) {
            return response()->json(['error' => 'Credenciais inválidas.'], HttpResponse::HTTP_UNAUTHORIZED);
        }

        return response()->json(['message' => 'Login realizado com sucesso.'], HttpResponse::HTTP_OK);
    }

    /**
     * Cria um novo login.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cpf_cnpj' => 'required|string|max:14|unique:logins',
            'senha' => [
                'required',
                'string',
                'min:8',
                'regex:/[A-Z]/', // pelo menos uma letra maiúscula
                'regex:/[a-z]/', // pelo menos uma letra minúscula
                'regex:/[0-9]/', // pelo menos um número
                'regex:/[@$!%*?&]/', // pelo menos um caractere especial
            ],
            'tipo' => 'required|string|in:usuario,empresa',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], HttpResponse::HTTP_BAD_REQUEST);
        }

        $senhaCriptografada = Hash::make($request->senha);

        $login = Login::create([
            'cpf_cnpj' => $request->cpf_cnpj,
            'senha' => $senhaCriptografada,
            'tipo' => $request->tipo,
        ]);

        if ($request->tipo === 'usuario') {
            Usuario::create(['login_id' => $login->id, /* outros campos do usuário */]);
        } else {
            Empresa::create(['login_id' => $login->id, /* outros campos da empresa */]);
        }

        return response()->json(['message' => 'Login criado com sucesso.'], HttpResponse::HTTP_CREATED);
    }

    /**
     * Recupera a senha do usuário.
     */
    public function recuperarSenha(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], HttpResponse::HTTP_BAD_REQUEST);
        }

        // Verificar se o e-mail existe na tabela de usuários
        $usuario = Usuario::where('email', $request->email)->first();

        if (!$usuario) {
            return response()->json(['error' => 'E-mail não encontrado.'], HttpResponse::HTTP_NOT_FOUND);
        }

        // Gerar um token para redefinir a senha
        $token = str_random(60);

        // Salvar o token na tabela de tokenlogin
        TokenLogin::updateOrCreate(
            ['idlogin' => $usuario->login_id], // vincular ao login
            ['token' => $token]
        );

        // Envie o e-mail para redefinir a senha
        Mail::to($usuario->email)->send(new \App\Mail\ResetPasswordMail($token));

        return response()->json(['message' => 'E-mail de redefinição de senha enviado.'], HttpResponse::HTTP_OK);
    }

    /**
     * Redefine a senha do usuário.
     */
    public function redefinirSenha(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'nova_senha' => [
                'required',
                'string',
                'min:8',
                'regex:/[A-Z]/', // pelo menos uma letra maiúscula
                'regex:/[a-z]/', // pelo menos uma letra minúscula
                'regex:/[0-9]/', // pelo menos um número
                'regex:/[@$!%*?&]/', // pelo menos um caractere especial
            ],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], HttpResponse::HTTP_BAD_REQUEST);
        }

        // Verificar se o token é válido
        $tokenLogin = TokenLogin::where('token', $request->token)->first();

        if (!$tokenLogin) {
            return response()->json(['error' => 'Token inválido.'], HttpResponse::HTTP_UNAUTHORIZED);
        }

        // Atualizar a senha do login correspondente
        $login = Login::find($tokenLogin->idlogin);
        if ($login) {
            $login->senha = Hash::make($request->nova_senha);
            $login->save();
        }

        // Remover o token após a redefinição
        $tokenLogin->delete(); // O token é deletado aqui

        return response()->json(['message' => 'Senha redefinida com sucesso.'], HttpResponse::HTTP_OK);
    }
}
