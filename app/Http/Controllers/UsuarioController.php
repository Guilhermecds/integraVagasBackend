<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;
use App\Models\RequisitoCampo;
use App\Models\RequisitoCampoVagaUsuario;
use App\Models\UsuarioVaga;
use App\Models\Vaga;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Illuminate\Support\Facades\Mail;

class UsuarioController extends Controller
{
    /**
     * Exibe todos os usuários.
     */
    public function index(Request $request)
    {
        $ativos = $request->query('ativos');

        $query = Usuario::with('tipoUsuario');
        
        if (!is_null($ativos)) {
            $query->where('idsituacaousuario', 1);
        }

        $usuarios = $query->get();

        foreach ($usuarios as $usuario) {
            if ($usuario->curriculo && file_exists($usuario->curriculo)) {
                $usuario->curriculo_base64 = base64_encode(file_get_contents($usuario->curriculo));
            } else {
                $usuario->curriculo_base64 = null;
            }
        }

        return response()->json([
            'message' => 'Lista de usuários',
            'usuarios' => $usuarios,
        ]);
    }

    /**
     * Cria um novo usuário e o vincula ao login.
     */
    public function store(Request $request)
    {

        $camposObrigatorios = [
            'nome' => 'required|string|max:255',
            'idtipousuario' => 'required|exists:tipousuario,id',
            'email' => 'required|email|unique:usuario,email',
            'telefone' => 'nullable|string|max:15',
            'cpf' => 'required|string|unique:usuario,cpf',
            'senha' => 'required|string|min:8',
            'bairro' => 'nullable|string|max:255',
            'uf' => 'required|string|min:2|max:2',
        ];

        if ($request->idtipousuario == 1) {
            $camposObrigatorios = array_merge($camposObrigatorios, [
                'data_nascimento' => 'required|date',
                'idformacao' => 'required|exists:formacao,id',
                'curriculo' => 'required|string',
            ]);
        }

        $validator = Validator::make($request->all(), $camposObrigatorios);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], HttpResponse::HTTP_BAD_REQUEST);
        }


        try {
            DB::beginTransaction();
            $curriculoCaminho = '';

            if ($request->curriculo) {
                $curriculoBase64 = $request->curriculo;
                $fileData = base64_decode($curriculoBase64);

                $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
                $fileMimeType = finfo_buffer($fileInfo, $fileData);
                finfo_close($fileInfo);

                if (!in_array($fileMimeType, ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])) {
                    return response()->json(['error' => 'O arquivo não é um tipo válido. Apenas PDF, DOC e DOCX são permitidos.'], HttpResponse::HTTP_BAD_REQUEST);
                }

                $curriculoNome = 'curriculo_' . uniqid() . '.' . ($fileMimeType === 'application/pdf' ? 'pdf' : 'docx');
                $curriculoCaminho = storage_path('app/public/curriculos/' . $curriculoNome);
                file_put_contents($curriculoCaminho, $fileData);
            }

            $usuario = Usuario::create([
                'nome' => $request->nome,
                'sou_deficiente' => $request->sou_deficiente ?? false,
                'data_nascimento' => $request->data_nascimento ?? null,
                'idtipousuario' => $request->idtipousuario,
                'email' => $request->email,
                'telefone' => $request->telefone,
                'cpf' => $request->cpf,
                'cep' => $request->cep,
                'logradouro' => $request->logradouro,
                'numero' => $request->numero,
                'complemento' => $request->complemento,
                'cidade' => $request->cidade,
                'bairro' => $request->bairro,
                'uf' => $request->uf,
                'curriculo' => $curriculoCaminho,
                'idsituacaousuario' => true,
                'idformacao' => $request->idformacao ?? null,
            ]);

            $loginRequest = new Request([
                'cpf_cnpj' => $request->cpf,
                'senha' => $request->senha,
                'idusuario' => $usuario->id,
            ]);

            $loginController = new LoginController();
            $loginResponse = $loginController->store($loginRequest);

            if ($loginResponse->getStatusCode() !== HttpResponse::HTTP_CREATED) {
                DB::rollBack();
                $errorContent = $loginResponse->getOriginalContent();
                $errorMessage = $errorContent['error'] ?? 'Erro desconhecido';
                return response()->json(['error' => $errorMessage], HttpResponse::HTTP_BAD_REQUEST);
            }

            DB::commit();

            return response()->json([
                'message' => 'Usuário criado com sucesso.',
                'usuario' => $usuario,
            ], HttpResponse::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Exibe um usuário específico.
     */
    public function show($id)
    {
        $usuario = Usuario::with('tipoUsuario')->find($id);

        if (!$usuario) {
            return response()->json(['error' => 'Usuário não encontrado.'], HttpResponse::HTTP_NOT_FOUND);
        }

        if ($usuario->curriculo && file_exists($usuario->curriculo)) {
            $usuario->curriculo_base64 = base64_encode(file_get_contents($usuario->curriculo));
        } else {
            $usuario->curriculo_base64 = null;
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
            'bairro' => 'nullable|string|max:255',
        ]);


        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], HttpResponse::HTTP_BAD_REQUEST);
        }

        $usuario = Usuario::find($id);

        if (!$usuario) {
            return response()->json(['error' => 'Usuário não encontrado.'], HttpResponse::HTTP_NOT_FOUND);
        }

        if ($request->has('curriculo')) {
            $curriculoBase64 = $request->curriculo;
            $fileData = base64_decode($curriculoBase64);

            $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
            $fileMimeType = finfo_buffer($fileInfo, $fileData);
            finfo_close($fileInfo);

            if (!in_array($fileMimeType, ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])) {
                return response()->json(['error' => 'O arquivo não é um tipo válido. Apenas PDF, DOC e DOCX são permitidos.'], HttpResponse::HTTP_BAD_REQUEST);
            }

            $curriculoNome = 'curriculo_' . uniqid() . '.' . ($fileMimeType === 'application/pdf' ? 'pdf' : 'docx');

            $curriculoCaminho = storage_path('app/public/curriculos/' . $curriculoNome);

            file_put_contents($curriculoCaminho, $fileData);

            $request->merge(['curriculo' => $curriculoCaminho]);
        }

        $usuario->fill($request->only(['nome', 'sou_deficiente', 'data_nascimento', 'idtipousuario', 'email', 'telefone', 'cpf', 'idformacao', 'cep', 'logradouro', 'numero', 'complemento', 'cidade', 'bairro', 'uf', 'curriculo']));
        $usuario->save();

        $loginRequest = new Request([
            'cpf_cnpj' => $request->cpf ?? $usuario->cpf,
            'senha' => $request->senha ?? null,
            'idusuario' => $usuario->id,
        ]);

        $loginController = new LoginController();

        $loginResponse = $loginController->update($loginRequest, $usuario->id);

        if ($loginResponse->getStatusCode() !== HttpResponse::HTTP_OK) {
            return response()->json(['error' => 'Erro ao atualizar os dados de login.'], HttpResponse::HTTP_BAD_REQUEST);
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

        $usuario->idsituacaousuario = false;
        $usuario->save();

        return response()->json([
            'message' => 'Usuário inativado com sucesso.',
            'usuario' => $usuario,
        ], HttpResponse::HTTP_OK);
    }

    /**
     * Ativa um usuário.
     */
    public function ativar($id)
    {
        $usuario = Usuario::find($id);

        if (!$usuario) {
            return response()->json(['error' => 'Usuário não encontrado.'], HttpResponse::HTTP_NOT_FOUND);
        }

        $usuario->idsituacaousuario = true;
        $usuario->save();

        return response()->json([
            'message' => 'Usuário inativado com sucesso.',
            'usuario' => $usuario,
        ], HttpResponse::HTTP_OK);
    }

    /**
     * Realiza a candidatura de um usuário em uma vaga.
     */
    public function candidatar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'idusuario' => 'required|exists:usuario,id',
            'idvaga' => 'required|exists:vaga,id',
            'requisitos' => 'required|array',
            'requisitos.*' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], HttpResponse::HTTP_BAD_REQUEST);
        }

        $usuario = Usuario::find($request->idusuario);
        $vaga = Vaga::find($request->idvaga);

        $candidaturaExistente = UsuarioVaga::where('idusuario', $usuario->id)
            ->where('idvaga', $vaga->id)
            ->exists();

        if ($candidaturaExistente) {
            return response()->json(['error' => 'Usuário já se candidatou a esta vaga.'], HttpResponse::HTTP_CONFLICT);
        }

        if ($vaga->vaga_apenas_deficiente && !$usuario->sou_deficiente) {
            return response()->json(['error' => 'Esta vaga é destinada apenas a pessoas com deficiência.'], HttpResponse::HTTP_FORBIDDEN);
        }

        try {
            DB::beginTransaction();

            UsuarioVaga::create([
                'idusuario' => $usuario->id,
                'idvaga' => $vaga->id,
                'idempresa' => $vaga->idempresa,
            ]);

            if (isset($request->requisitos) && is_array($request->requisitos)) {
                foreach ($request->requisitos as $idRequisito => $idRequisitoCampo) {
                    $requisitoCampo = RequisitoCampo::find($idRequisitoCampo);

                    if ($requisitoCampo) {
                        RequisitoCampoVagaUsuario::create([
                            'idusuario' => $usuario->id,
                            'idvaga' => $vaga->id,
                            'idrequisitocampo' => $idRequisitoCampo,
                        ]);
                    } else {
                        return response()->json(['error' => "Requisito com ID $idRequisitoCampo não encontrado."], HttpResponse::HTTP_NOT_FOUND);
                    }
                }
            }

            DB::commit();

            $this->enviarEmailCandidatura($usuario, $vaga);

            return response()->json(['message' => 'Candidatura realizada com sucesso.'], HttpResponse::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Erro ao realizar candidatura: ' . $e->getMessage()], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Envia um e-mail de notificação para o usuário e a empresa.
     */
    protected function enviarEmailCandidatura($usuario, $vaga)
    {
        $detalhesEmailEmpresa = [
            'titulo' => 'Nova Candidatura para a Vaga ' . $vaga->nome,
            'mensagem' => "Olá, {$usuario->nome}. Sua candidatura para a vaga '{$vaga->nome}' foi enviada com sucesso. Boa sorte!.",
        ];

        $detalhesEmailUsuario = [
            'titulo' => 'Nova Candidatura Recebida',
            'mensagem' => "O candidato {$usuario->nome} se candidatou à vaga '{$vaga->nome}' de sua empresa.",
        ];

        Mail::to($usuario->email)->send(new \App\Mail\NotificacaoCandidatura($detalhesEmailUsuario));

        if ($vaga->empresa) {
            Mail::to($vaga->empresa->email)->send(new \App\Mail\NotificacaoCandidatura($detalhesEmailEmpresa));
        }
    }

    /**
     * Salva a foto do usuário.
     */
    public function salvarFoto(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'foto' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], HttpResponse::HTTP_BAD_REQUEST);
        }

        $usuario = Usuario::find($id);

        if (!$usuario) {
            return response()->json(['error' => 'Usuário não encontrado.'], HttpResponse::HTTP_NOT_FOUND);
        }

        $fotoBase64 = $request->foto;

        if (!preg_match('/^data:image\/(jpeg|png|jpg);base64,/', $fotoBase64)) {
            return response()->json(['error' => 'O formato da foto é inválido.'], HttpResponse::HTTP_BAD_REQUEST);
        }

        $usuario->foto = $fotoBase64;
        $usuario->save();

        return response()->json([
            'message' => 'Foto salva com sucesso.',
            'usuario' => $usuario,
        ], HttpResponse::HTTP_OK);
    }
}
