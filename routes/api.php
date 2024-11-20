<?php

use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\FormacaoController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RequisitoCampoController;
use App\Http\Controllers\RequisitoController;
use App\Http\Controllers\TipoUsuarioController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\VagaController;
use Illuminate\Support\Facades\Route;

//login
Route::post('/login/register', [LoginController::class, 'register']);
Route::post('/login', [LoginController::class, 'login']);
Route::get('/login', [LoginController::class, 'index']);
Route::get('/login/{id}', [LoginController::class, 'show']);
Route::put('/login/{id}', [LoginController::class, 'update']);

// Rotas de recuperação e redefinição de senha
Route::post('/recuperar-senha', [LoginController::class, 'recuperarSenha']);
Route::post('/redefinir-senha/token', [LoginController::class, 'redefinirSenhaComToken']);

//usuarios
Route::get('usuarios', [UsuarioController::class, 'index']);
Route::post('usuarios', [UsuarioController::class, 'store']);
Route::get('usuarios/{id}', [UsuarioController::class, 'show']);
Route::put('usuarios/{id}', [UsuarioController::class, 'update']);
Route::put('usuarios/{id}/inativar', [UsuarioController::class, 'inativar']);
Route::put('usuarios/{id}/ativar', [UsuarioController::class, 'ativar']);
Route::post('usuarios/candidatar', [UsuarioController::class, 'candidatar']);
Route::post('/usuarios/{id}/salvar-foto', [UsuarioController::class, 'salvarFoto']);

Route::get('tipousuario', [TipoUsuarioController::class, 'index']);

Route::get('formacoes', [FormacaoController::class, 'index']);
Route::post('formacoes', [FormacaoController::class, 'store']);
Route::get('formacoes/{id}', [FormacaoController::class, 'show']);
Route::put('formacoes/{id}', [FormacaoController::class, 'update']);
Route::delete('formacoes/{id}', [FormacaoController::class, 'destroy']);

Route::get('requisitos', [RequisitoController::class, 'index']);
Route::post('requisitos', [RequisitoController::class, 'store']);
Route::get('requisitos/{id}', [RequisitoController::class, 'show']);
Route::put('requisitos/{id}', [RequisitoController::class, 'update']);
Route::put('requisitos/{id}/inativar', [RequisitoController::class, 'inativar']);
Route::put('requisitos/{id}/inativar', [RequisitoController::class, 'ativar']);

Route::get('empresas', [EmpresaController::class, 'index']);
Route::post('empresas', [EmpresaController::class, 'store']);
Route::get('empresas/{id}', [EmpresaController::class, 'show']);
Route::put('empresas/{id}', [EmpresaController::class, 'update']);
Route::put('empresas/{id}/inativar', [EmpresaController::class, 'inativar']);
Route::put('empresas/{id}/ativar', [EmpresaController::class, 'ativar']);
Route::post('/empresas/{id}/salvar-foto', [EmpresaController::class, 'salvarFoto']);

Route::prefix('vagas')->group(function () {
    Route::get('/', [VagaController::class, 'index']);
    Route::post('/', [VagaController::class, 'store']);
    Route::get('/{id}', [VagaController::class, 'show']);
    Route::put('/{id}', [VagaController::class, 'update']);
    Route::get('/usuario/{id}', [VagaController::class, 'buscarVagaPorIdUsuario']);
    Route::get('/empresa/{id}', [VagaController::class, 'buscarEmpresaPorIdUsuario']);
    Route::put('/{id}/inativar', [VagaController::class, 'inativar']);
    Route::put('/{id}/ativar', [VagaController::class, 'ativar']);
    Route::get('/empresa/{id}/ordenar-usuarios', [VagaController::class, 'ordenarUsuariosPorVaga']);
    Route::get('/{id}/exportar', [VagaController::class, 'exportVagaUsuarios']);
});

Route::prefix('requisitocampos')->group(function () {
    Route::get('/', [RequisitoCampoController::class, 'index']);
    Route::post('/', [RequisitoCampoController::class, 'store']);
    Route::put('/{id}', [RequisitoCampoController::class, 'update']);
    Route::put('/{id}/inativar', [RequisitoCampoController::class, 'inativar']);
    Route::put('/{id}/ativar', [RequisitoCampoController::class, 'ativar']);
});