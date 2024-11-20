<?php

namespace App\Http\Controllers;

use App\Exports\VagaUsuariosExport;
use App\Models\RequisitoCampo;
use App\Models\RequisitoCampoVagaUsuario;
use App\Models\RequisitoVaga;
use Illuminate\Http\Request;
use App\Models\Vaga;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class VagaController extends Controller
{
    /**
     * Exibe todas as vagas.
     */
    public function index(Request $request)
    {
        $vagaApenasDeficiente = $request->query('vaga_apenas_deficiente');
        $nome = $request->query('nome');
        $nomeEmpresa = $request->query('nome_empresa');
        $ativos = $request->query('ativos');

        $query = Vaga::with([
            'empresa',
            'requisitosvaga' => function ($q) {
                $q->with(['requisito' => function ($q) {
                    $q->where('idsituacaorequisito', 1)
                        ->with(['requisitocampos' => function ($q) {
                            $q->where('idsituacaocampo', 1);
                        }]);
                }]);
            }
        ]);

        if (!is_null($ativos)) {
            $query->where('idsituacaovaga', 1)
                ->whereHas('empresa', function ($q) {
                    $q->where('idsituacaoempresa', 1);
                });
        }

        if (!is_null($vagaApenasDeficiente)) {
            $query->where('vaga_apenas_deficiente', $vagaApenasDeficiente);
        }

        if (!is_null($nome)) {
            $query->where('nome', 'like', "%$nome%");
        }

        if (!is_null($nomeEmpresa)) {
            $query->whereHas('empresa', function ($q) use ($nomeEmpresa) {
                $q->where('razao_social', 'like', "%$nomeEmpresa%");
            });
        }

        $vagas = $query->get();

        return response()->json(['vagas' => $vagas], HttpResponse::HTTP_OK);
    }

    /**
     * Cria uma nova vaga.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:255',
            'vaga_apenas_deficiente' => 'boolean',
            'cep' => 'required|string|max:10',
            'logradouro' => 'required|string|max:255',
            'numero' => 'required|string|max:10',
            'complemento' => 'nullable|string|max:255',
            'cidade' => 'required|string|max:255',
            'descricao' => 'required|string',
            'idsituacaovaga' => 'nullable|boolean',
            'idformacao' => 'required|exists:formacao,id',
            'idempresa' => 'required|exists:empresa,id',
            'requisitos' => 'nullable|array',
            'requisitos.*' => 'exists:requisito,id',
            'uf' => 'required|string|min:2|max:2',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], HttpResponse::HTTP_BAD_REQUEST);
        }

        $vaga = Vaga::create($request->all());

        if ($request->has('requisitos') && !empty($request->requisitos)) {
            foreach ($request->requisitos as $requisitoId) {
                RequisitoVaga::create([
                    'idvaga' => $vaga->id,
                    'idrequisito' => $requisitoId,
                ]);
            }
        }

        return response()->json(['message' => 'Vaga criada com sucesso.', 'vaga' => $vaga], HttpResponse::HTTP_CREATED);
    }

    /**
     * Exibe uma vaga específica.
     */
    public function show($id)
    {
        $query = Vaga::with([
            'empresa',
            'formacao',
            'requisitosvaga' => function ($q) {
                $q->with(['requisito' => function ($q) {
                    $q->where('idsituacaorequisito', 1)
                        ->with(['requisitocampos' => function ($q) {
                            $q->where('idsituacaocampo', 1);
                        }]);
                }]);
            }
        ]);

        $vaga = $query->find($id);

        if (!$vaga) {
            return response()->json(['error' => 'Vaga não encontrada.'], HttpResponse::HTTP_NOT_FOUND);
        }

        return response()->json(['vaga' => $vaga], HttpResponse::HTTP_OK);
    }

    /**
     * Atualiza uma vaga existente.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:255',
            'vaga_apenas_deficiente' => 'boolean',
            'cep' => 'nullable|string|max:10',
            'logradouro' => 'nullable|string|max:255',
            'numero' => 'nullable|string|max:10',
            'complemento' => 'nullable|string|max:255',
            'cidade' => 'nullable|string|max:255',
            'descricao' => 'nullable|string',
            'idsituacaovaga' => 'required|boolean',
            'idformacao' => 'required|exists:formacao,id',
            'idempresa' => 'required|exists:empresa,id',
            'requisitos' => 'nullable|array',
            'requisitos.*' => 'exists:requisito,id',
            'uf' => 'required|string|min:2|max:2',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], HttpResponse::HTTP_BAD_REQUEST);
        }

        $vaga = Vaga::find($id);

        if (!$vaga) {
            return response()->json(['error' => 'Vaga não encontrada.'], HttpResponse::HTTP_NOT_FOUND);
        }

        $vaga->update($request->all());

        if ($request->has('requisitos') && !empty($request->requisitos)) {
            RequisitoVaga::where('idvaga', $id)->delete();

            foreach ($request->requisitos as $requisitoId) {
                RequisitoVaga::create([
                    'idvaga' => $vaga->id,
                    'idrequisito' => $requisitoId,
                ]);
            }
        }

        return response()->json(['message' => 'Vaga atualizada com sucesso.', 'vaga' => $vaga], HttpResponse::HTTP_OK);
    }

    /**
     * Inativa uma vaga.
     */
    public function inativar($id)
    {
        $vaga = Vaga::find($id);

        if (!$vaga) {
            return response()->json(['error' => 'Vaga não encontrada.'], HttpResponse::HTTP_NOT_FOUND);
        }

        $vaga->idsituacaovaga = false;
        $vaga->save();

        return response()->json(['message' => 'Vaga inativada com sucesso.'], HttpResponse::HTTP_OK);
    }

    /**
     * Ativa uma vaga.
     */
    public function ativar($id)
    {
        $vaga = Vaga::find($id);

        if (!$vaga) {
            return response()->json(['error' => 'Vaga não encontrada.'], HttpResponse::HTTP_NOT_FOUND);
        }

        $vaga->idsituacaovaga = true;
        $vaga->save();

        return response()->json(['message' => 'Vaga inativada com sucesso.'], HttpResponse::HTTP_OK);
    }

    /**
     * Busca vaga por id usuario.
     */
    public function buscarVagaPorIdUsuario(Request $request, $id)
    {
        $ativos = $request->query('ativos');

        $query = Vaga::whereHas('usuariovagas', function ($query) use ($id) {
            $query->where('idusuario', $id);
        })
            ->with([
                'empresa',
                'formacao',
                'usuariovagas' => function ($query) use ($id) {
                    $query->where('idusuario', $id);
                }
            ]);

        if (!is_null($ativos)) {
            $query->where('idsituacaovaga', 1)
                ->whereHas('empresa', function ($q) {
                    $q->where('idsituacaoempresa', 1);
                });
        }

        $vagas = $query->get();

        foreach ($vagas as $vaga) {
            $vaga->usuariovagas = $vaga->usuariovagas->first();
        }

        if ($vagas->isEmpty()) {
            return response()->json(['error' => 'Nenhuma vaga encontrada para este usuário.'], HttpResponse::HTTP_NOT_FOUND);
        }

        return response()->json(['vagas' => $vagas], HttpResponse::HTTP_OK);
    }

    /**
     * Busca vagas por id da empresa associada ao id do usuário.
     */
    public function buscarEmpresaPorIdUsuario($id)
    {
        $vagas = Vaga::where('idempresa', $id)
            ->with([
                'empresa',
                'formacao'
            ])
            ->get();

        if ($vagas->isEmpty()) {
            return response()->json(['error' => 'Nenhuma vaga encontrada para esta empresa.'], HttpResponse::HTTP_NOT_FOUND);
        }

        return response()->json(['vagas' => $vagas], HttpResponse::HTTP_OK);
    }

    /**
     * Ordena usuários por vaga com base no score.
     */
    public function ordenarUsuariosPorVaga(Request $request, $idempresa)
    {
        $query = Vaga::with([
            'empresa',
            'formacao',
            'requisitosvaga',
            'usuariovagas',
        ]);

        $vagaApenasDeficiente = $request->query('vaga_apenas_deficiente');
        $nome = $request->query('nome');

        $query->where('idempresa', $idempresa);

        if (!is_null($vagaApenasDeficiente)) {
            $query->where('vaga_apenas_deficiente', $vagaApenasDeficiente);
        }

        if (!is_null($nome)) {
            $query->where('nome', 'like', "%$nome%");
        }

        $vagas = $query->get();

        $vagasOrdenadas = [];

        foreach ($vagas as $vaga) {
            $usuarios = $vaga->usuariovagas->filter(function ($usuarioVaga) {
                return $usuarioVaga->usuario && $usuarioVaga->usuario->idsituacaousuario == 1;
            })->map(function ($usuarioVaga) use ($vaga) {
                $usuario = $usuarioVaga->usuario;

                $score = 0;

                $requisitosUsuario = RequisitoCampoVagaUsuario::where('idusuario', $usuario->id)
                    ->where('idvaga', $vaga->id)
                    ->get();

                foreach ($requisitosUsuario as $requisitoUsuario) {
                    $requisitoCampo = RequisitoCampo::find($requisitoUsuario->idrequisitocampo);

                    if ($requisitoCampo) {
                        $score += $requisitoCampo->score;
                    }
                }

                return [
                    'usuario' => $usuario,
                    'score' => $score,
                ];
            });

            $usuariosOrdenados = $usuarios->sortByDesc('score');

            $vaga->usuarios_ordenados = $usuariosOrdenados;

            $vagasOrdenadas[] = $vaga;
        }

        return response()->json([
            'vagas' => $vagasOrdenadas
        ], HttpResponse::HTTP_OK);
    }

    public function exportVagaUsuarios($idvaga)
    {
        $export = new VagaUsuariosExport($idvaga);

        return $export->export();
    }
}
