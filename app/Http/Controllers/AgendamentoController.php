<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Agendamento;
use App\Models\Funcionario;
use App\Models\Usuario;
use App\Exceptions\ValidationException;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class AgendamentoController extends Controller
{
    /**
     * Exibe todos os agendamentos.
     */
    public function index()
    {
        try {
            $agendamentos = Agendamento::with(['funcionario', 'usuario'])->get();

            return response()->json([
                'message' => 'Lista de agendamentos',
                'agendamentos' => $agendamentos
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
     * Cria um novo agendamento.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'data_horario_visita' => 'required|date',
                'funcionario_id' => 'required|exists:funcionario,id',
                'usuario_id' => 'required|exists:usuario,id',
                'vacinas' => 'nullable|json',
            ], [
                'data_horario_visita.required' => 'A data e horário da visita são obrigatórios.',
                'data_horario_visita.date' => 'A data e horário da visita devem ser uma data válida.',
                'funcionario_id.required' => 'O ID do funcionário é obrigatório.',
                'funcionario_id.exists' => 'O funcionário selecionado não existe.',
                'usuario_id.required' => 'O ID do usuário é obrigatório.',
                'usuario_id.exists' => 'O usuário selecionado não existe.',
                'vacinas.json' => 'O campo de vacinas deve ser um JSON válido.',
            ]);

            if ($validator->fails()) {
                throw new \Exception($validator->errors()->first(), HttpResponse::HTTP_BAD_REQUEST);
            }

            $agendamento = Agendamento::create($request->all());

            return response()->json([
                'message' => 'Agendamento criado com sucesso',
                'agendamento' => $agendamento
            ], HttpResponse::HTTP_CREATED);

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw new ValidationException($e->getMessage(), $e->errors(), $e->status);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Exibe um agendamento específico.
     */
    public function show($id)
    {
        try {
            $agendamento = Agendamento::with(['funcionario', 'usuario'])->findOrFail($id);

            return response()->json([
                'message' => 'Agendamento encontrado',
                'agendamento' => $agendamento
            ], HttpResponse::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Agendamento não encontrado'
            ], HttpResponse::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Atualiza um agendamento existente.
     */
    public function update(Request $request, $id)
    {
        try {
            $agendamento = Agendamento::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'data_horario_visita' => 'required|date',
                'funcionario_id' => 'required|exists:funcionario,id',
                'usuario_id' => 'required|exists:usuario,id',
                'vacinas' => 'nullable|json',
            ], [
                'data_horario_visita.required' => 'A data e horário da visita são obrigatórios.',
                'data_horario_visita.date' => 'A data e horário da visita devem ser uma data válida.',
                'funcionario_id.required' => 'O ID do funcionário é obrigatório.',
                'funcionario_id.exists' => 'O funcionário selecionado não existe.',
                'usuario_id.required' => 'O ID do usuário é obrigatório.',
                'usuario_id.exists' => 'O usuário selecionado não existe.',
                'vacinas.json' => 'O campo de vacinas deve ser um JSON válido.',
            ]);

            if ($validator->fails()) {
                throw new \Exception($validator->errors()->first(), HttpResponse::HTTP_BAD_REQUEST);
            }

            $agendamento->update($request->all());

            return response()->json([
                'message' => 'Agendamento atualizado com sucesso',
                'agendamento' => $agendamento
            ], HttpResponse::HTTP_OK);

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw new ValidationException($e->getMessage(), $e->errors(), $e->status);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Agendamento não encontrado'
            ], HttpResponse::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove um agendamento específico.
     */
    public function destroy($id)
    {
        try {
            $agendamento = Agendamento::findOrFail($id);
            $agendamento->delete();

            return response()->json([
                'message' => 'Agendamento deletado com sucesso'
            ], HttpResponse::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Agendamento não encontrado'
            ], HttpResponse::HTTP_NOT_FOUND);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'error' => 'Não é possível deletar o agendamento. Ela está sendo referenciada por outras tabelas.'
            ], HttpResponse::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

   /**
     * Retorna todos os agendamentos futuros para um usuário específico a partir da data/hora atual.
     */
    public function agendamentosFuturos($usuario_id)
    {
        try {
            $agendamentos = Agendamento::where('data_horario_visita', '>=', Carbon::now('America/Sao_Paulo'))
                                    ->where('usuario_id', $usuario_id)
                                    ->with(['funcionario', 'usuario'])
                                    ->get();

            return response()->json([
                'message' => 'Lista de agendamentos futuros',
                'agendamentos' => $agendamentos
            ], HttpResponse::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Retorna todos os agendamentos passados para um usuário específico até a data/hora atual.
     */
    public function agendamentosPassados($usuario_id)
    {
        try {
            $agendamentos = Agendamento::where('data_horario_visita', '<', Carbon::now('America/Sao_Paulo'))
                                    ->where('usuario_id', $usuario_id)
                                    ->with(['funcionario', 'usuario'])
                                    ->get();

            return response()->json([
                'message' => 'Lista de agendamentos passados',
                'agendamentos' => $agendamentos
            ], HttpResponse::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
