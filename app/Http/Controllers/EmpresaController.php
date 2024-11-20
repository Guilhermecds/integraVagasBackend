<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Empresa;
use App\Models\Login;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class EmpresaController extends Controller
{
    /**
     * Exibe todas as empresas.
     */
    public function index(Request $request)
    {
        $ativos = $request->query('ativos');
        $query = Empresa::query();

        if (!is_null($ativos)) {
            $query->where('idsituacaoempresa', 1);
        }

        $empresas = $query->get();

        return response()->json([
            'message' => 'Lista de empresas',
            'empresas' => $empresas
        ], HttpResponse::HTTP_OK);
    }

    /**
     * Cria uma nova empresa e a vincula ao login.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nome_fantasia' => 'required|string|max:255',
            'razao_social' => 'required|string|max:255',
            'cnpj' => 'required|string|unique:empresa,cnpj',
            'telefone_corporativo' => 'nullable|string|max:15',
            'email' => 'required|email|unique:empresa,email',
            'cep' => 'nullable|string|max:10',
            'logradouro' => 'nullable|string|max:255',
            'numero' => 'nullable|string|max:10',
            'complemento' => 'nullable|string|max:255',
            'cidade' => 'nullable|string|max:255',
            'uf' => 'nullable|string|min:2|max:2',
            'senha' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], HttpResponse::HTTP_BAD_REQUEST);
        }

        // Criar uma nova empresa
        $empresa = Empresa::create([
            'nome_fantasia' => $request->nome_fantasia,
            'razao_social' => $request->razao_social,
            'cnpj' => $request->cnpj,
            'telefone_corporativo' => $request->telefone_corporativo,
            'email' => $request->email,
            'cep' => $request->cep,
            'logradouro' => $request->logradouro,
            'numero' => $request->numero,
            'complemento' => $request->complemento,
            'cidade' => $request->cidade,
            'uf' => $request->uf,
            'bairro' => $request->bairro,
            'idtipousuario' => 3,
            'idsituacaoempresa' => true
        ]);

        // Criar um login associado à empresa
        $login = Login::create([
            'cpf_cnpj' => $request->cnpj, // Ajuste conforme necessário
            'senha' => Hash::make($request->senha),
            'idempresa' => $empresa->id,
            // 'idusuario' => $request->idusuario, // Se aplicável
        ]);

        return response()->json([
            'message' => 'Empresa criada com sucesso.',
            'empresa' => $empresa,
            'login' => $login,
        ], HttpResponse::HTTP_CREATED);
    }

    /**
     * Exibe uma empresa específica.
     */
    public function show($id)
    {
        $empresa = Empresa::find($id);

        if (!$empresa) {
            return response()->json(['error' => 'Empresa não encontrada.'], HttpResponse::HTTP_NOT_FOUND);
        }

        return response()->json([
            'message' => 'Empresa encontrada.',
            'empresa' => $empresa,
        ], HttpResponse::HTTP_OK);
    }

    /**
     * Atualiza uma empresa existente.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nome_fantasia' => 'required|string|max:255',
            'razao_social' => 'required|string|max:255',
            'cnpj' => 'required|string|unique:empresa,cnpj,' . $id,
            'telefone_corporativo' => 'required|string|max:15',
            'email' => 'required|email|unique:empresa,email,' . $id,
            'cep' => 'required|string|max:10',
            'logradouro' => 'required|string|max:255',
            'numero' => 'required|string|max:10',
            'complemento' => 'nullable|string|max:255',
            'bairro' => 'required|string|max:255',
            'cidade' => 'required|string|max:255',
            'uf' => 'required|string|min:2|max:2',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], HttpResponse::HTTP_BAD_REQUEST);
        }

        $empresa = Empresa::find($id);

        if (!$empresa) {
            return response()->json(['error' => 'Empresa não encontrada.'], HttpResponse::HTTP_NOT_FOUND);
        }

        // Atualiza os campos da empresa
        $empresa->fill($request->only(['nome_fantasia', 'razao_social', 'cnpj', 'telefone_corporativo', 'email', 'cep', 'logradouro', 'numero', 'complemento', 'cidade', 'bairro', 'uf']));
        $empresa->save();

        return response()->json([
            'message' => 'Empresa atualizada com sucesso.',
            'empresa' => $empresa,
        ], HttpResponse::HTTP_OK);
    }

    /**
     * Inativa uma empresa.
     */
    public function inativar($id)
    {
        $empresa = Empresa::find($id);

        if (!$empresa) {
            return response()->json(['error' => 'Empresa não encontrada.'], HttpResponse::HTTP_NOT_FOUND);
        }

        $empresa->idsituacaoempresa = false;
        $empresa->save();

        return response()->json([
            'message' => 'Empresa inativada com sucesso.',
            'empresa' => $empresa,
        ], HttpResponse::HTTP_OK);
    }

    /**
     * Ativa uma empresa.
     */
    public function ativar($id)
    {
        $empresa = Empresa::find($id);

        if (!$empresa) {
            return response()->json(['error' => 'Empresa não encontrada.'], HttpResponse::HTTP_NOT_FOUND);
        }

        $empresa->idsituacaoempresa = true;
        $empresa->save();

        return response()->json([
            'message' => 'Empresa inativada com sucesso.',
            'empresa' => $empresa,
        ], HttpResponse::HTTP_OK);
    }

    /**
     * Salva a foto do Empresa.
     */
    public function salvarFoto(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'foto' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], HttpResponse::HTTP_BAD_REQUEST);
        }

        $empresa = Empresa::find($id);

        if (!$empresa) {
            return response()->json(['error' => 'Empresa não encontrada.'], HttpResponse::HTTP_NOT_FOUND);
        }

        $fotoBase64 = $request->foto;

        if (!preg_match('/^data:image\/(jpeg|png|jpg);base64,/', $fotoBase64)) {
            return response()->json(['error' => 'O formato da foto é inválido.'], HttpResponse::HTTP_BAD_REQUEST);
        }

        $empresa->foto = $fotoBase64;
        $empresa->save();

        return response()->json([
            'message' => 'Foto salva com sucesso.',
            'empresa' => $empresa,
        ], HttpResponse::HTTP_OK);
    }
}
