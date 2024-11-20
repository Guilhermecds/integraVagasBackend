<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Login;
use App\Models\Usuario;
use App\Models\Empresa;
use App\Models\TokenLogin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Illuminate\Support\Str;

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

        $userOrCompany = null;

        if ($login->idusuario) {
            $userOrCompany = Usuario::find($login->idusuario);
            if ($userOrCompany && $userOrCompany->idsituacaousuario !== 1) {
                return response()->json(['error' => 'Usuário inativo.'], HttpResponse::HTTP_FORBIDDEN);
            }
        }

        if ($login->idempresa) {
            $userOrCompany = Empresa::find($login->idempresa);
            if ($userOrCompany && $userOrCompany->idsituacaoempresa !== 1) {
                return response()->json(['error' => 'Empresa inativa.'], HttpResponse::HTTP_FORBIDDEN);
            }
        }

        if (!$userOrCompany) {
            return response()->json(['error' => 'Usuário ou empresa não encontrados.'], HttpResponse::HTTP_NOT_FOUND);
        }

        return response()->json([
            'message' => 'Login realizado com sucesso.',
            'data' => $userOrCompany
        ], HttpResponse::HTTP_OK);
    }

    /**
     * Cria um novo login.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cpf_cnpj' => 'required|string|max:14|unique:login',
            'senha' => [
                'required',
                'string',
                'min:8',
                'regex:/[A-Z]/',
                'regex:/[a-z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*?&]/',
            ],
            'idusuario' => 'nullable|exists:usuario,id',
            'idempresa' => 'nullable|exists:empresa,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], HttpResponse::HTTP_BAD_REQUEST);
        }

        try {
            $senhaCriptografada = Hash::make($request->senha);

            $login = Login::create([
                'cpf_cnpj' => $request->cpf_cnpj,
                'senha' => $senhaCriptografada,
                'idusuario' => $request->idusuario ?? null,
                'idempresa' => $request->idempresa ?? null
            ]);

            return response()->json(['message' => 'Login criado com sucesso.'], HttpResponse::HTTP_CREATED);
        } catch (\Exception $e) {

            return response()->json(['error' => $e->getMessage()], HttpResponse::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Atualiza um login existente.
     */
    public function update(Request $request, $id)
    {
        // Validação dos dados
        $validator = Validator::make($request->all(), [
            'cpf_cnpj' => 'required|string|max:14|unique:login,cpf_cnpj,' . $id,
            'senha' => [
                'nullable',
                'string',
                'min:8',
                'regex:/[A-Z]/',
                'regex:/[a-z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*?&]/',
            ],
            'idusuario' => 'nullable|exists:usuario,id',
            'idempresa' => 'nullable|exists:empresa,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], HttpResponse::HTTP_BAD_REQUEST);
        }

        $login = Login::find($id);

        if (!$login) {
            return response()->json(['error' => 'Login não encontrado.'], HttpResponse::HTTP_NOT_FOUND);
        }

        $login->cpf_cnpj = $request->cpf_cnpj;
        if ($request->has('senha') && $request->senha) {
            $login->senha = Hash::make($request->senha);
        }
        $login->idusuario = $request->idusuario ?? $login->idusuario;
        $login->idempresa = $request->idempresa ?? $login->idempresa;

        try {
            $login->save();

            return response()->json(['message' => 'Login atualizado com sucesso.'], HttpResponse::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], HttpResponse::HTTP_BAD_REQUEST);
        }
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

        $usuario = Usuario::where('email', $request->email)->first();

        if (!$usuario) {
            return response()->json(['error' => 'E-mail não encontrado.'], HttpResponse::HTTP_NOT_FOUND);
        }

        $login = Login::where('idusuario', $usuario->id)
            ->where('cpf_cnpj', $usuario->cpf)
            ->first();

        if (!$login) {
            return response()->json(['error' => 'Login não encontrado para o usuário com o Email fornecido.'], HttpResponse::HTTP_NOT_FOUND);
        }

        $token = Str::random(60);

        TokenLogin::updateOrCreate(
            ['idlogin' => $login->id],
            ['token' => $token]
        );

        Mail::to($usuario->email)->send(new \App\Mail\ResetPasswordMail($token));

        return response()->json(['message' => 'E-mail de redefinição de senha enviado.'], HttpResponse::HTTP_OK);
    }

    /**
     * Redefine a senha do usuário.
     */
    public function redefinirSenhaComToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'nova_senha' => [
                'required',
                'string',
                'min:8',
                'regex:/[A-Z]/',
                'regex:/[a-z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*?&]/',
            ],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], HttpResponse::HTTP_BAD_REQUEST);
        }

        $tokenLogin = TokenLogin::where('token', $request->token)->first();

        if (!$tokenLogin) {
            return response()->json(['error' => 'Token inválido.'], HttpResponse::HTTP_UNAUTHORIZED);
        }

        $login = Login::find($tokenLogin->idlogin);
        if ($login) {
            $login->senha = Hash::make($request->nova_senha);
            $login->save();
        }

        $tokenLogin->delete();

        return response()->json(['message' => 'Senha redefinida com sucesso.'], HttpResponse::HTTP_OK);
    }
}
