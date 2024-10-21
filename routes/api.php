<?php

use App\Http\Controllers\AgendamentoController;
use App\Http\Controllers\FuncionarioController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ProfissaoController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\VacinaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//login
Route::post('/login/register', [LoginController::class, 'register']);
Route::post('/login', [LoginController::class, 'login']);
Route::get('/login', [LoginController::class, 'index']);
Route::get('/login/{id}', [LoginController::class, 'show']);
Route::put('/login/{id}', [LoginController::class, 'update']);
Route::delete('/login/{id}', [LoginController::class, 'destroy']);

//usuarios
Route::get('usuarios', [UsuarioController::class, 'index']);
Route::post('usuarios', [UsuarioController::class, 'store']);
Route::get('usuarios/{id}', [UsuarioController::class, 'show']);
Route::put('usuarios/{id}', [UsuarioController::class, 'update']);
Route::delete('usuarios/{id}', [UsuarioController::class, 'destroy']);
Route::get('/usuario/{cpf}', [UsuarioController::class, 'findByCpf']);

//profissoes
Route::get('/profissoes', [ProfissaoController::class, 'index']);
Route::post('/profissoes', [ProfissaoController::class, 'store']);
Route::get('/profissoes/{id}', [ProfissaoController::class, 'show']);
Route::put('/profissoes/{id}', [ProfissaoController::class, 'update']);
Route::delete('/profissoes/{id}', [ProfissaoController::class, 'destroy']);

//funcionarios
Route::get('/funcionarios', [FuncionarioController::class, 'index']);
Route::post('/funcionarios', [FuncionarioController::class, 'store']);
Route::get('/funcionarios/{id}', [FuncionarioController::class, 'show']);
Route::put('/funcionarios/{id}', [FuncionarioController::class, 'update']);
Route::delete('/funcionarios/{id}', [FuncionarioController::class, 'destroy']);

//vacinas
Route::get('/vacinas', [VacinaController::class, 'index']);
Route::post('/vacinas', [VacinaController::class, 'store']);
Route::get('/vacinas/{id}', [VacinaController::class, 'show']);
Route::put('/vacinas/{id}', [VacinaController::class, 'update']);
Route::delete('/vacinas/{id}', [VacinaController::class, 'destroy']);

//agendamentos
Route::get('/agendamentos', [AgendamentoController::class, 'index']);
Route::post('/agendamentos', [AgendamentoController::class, 'store']);
Route::get('/agendamentos/{id}', [AgendamentoController::class, 'show']);
Route::put('/agendamentos/{id}', [AgendamentoController::class, 'update']);
Route::delete('/agendamentos/{id}', [AgendamentoController::class, 'destroy']);
Route::get('/agendamentos/futuros/{usuario_id}', [AgendamentoController::class, 'agendamentosFuturos']);
Route::get('/agendamentos/passados/{usuario_id}', [AgendamentoController::class, 'agendamentosPassados']);