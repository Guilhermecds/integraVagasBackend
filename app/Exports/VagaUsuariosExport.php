<?php

namespace App\Exports;

use App\Models\Vaga;
use App\Models\RequisitoCampoVagaUsuario;
use App\Models\RequisitoCampo;
use App\Models\Requisito; // Importando o modelo Requisito
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Facades\Excel;

class VagaUsuariosExport implements FromCollection, WithHeadings
{
    protected $vagaId;

    public function __construct($vagaId)
    {
        $this->vagaId = $vagaId;
    }

    public function collection()
    {
        $vaga = Vaga::with([
            'usuariovagas.usuario.requisitocampovagausuario', // Relacionamento com os requisitos do usuário para a vaga
            'usuariovagas.usuario.formacao' // Relacionamento com a formação do usuário
        ])->findOrFail($this->vagaId);

        // Mapeando os usuários que se candidataram à vaga
        $usuarios = $vaga->usuariovagas->map(function ($usuarioVaga) use ($vaga) {
            $usuario = $usuarioVaga->usuario;

            // Calculando o score baseado nos requisitos preenchidos pelo usuário para a vaga
            $score = 0;
            $requisitosUsuario = RequisitoCampoVagaUsuario::where('idusuario', $usuario->id)
                ->where('idvaga', $vaga->id)
                ->get();

            // Armazenar as descrições dos campos selecionados com seus valores
            $descricaoCampos = [];

            foreach ($requisitosUsuario as $requisitoUsuario) {
                $requisitoCampo = RequisitoCampo::find($requisitoUsuario->idrequisitocampo);
                if ($requisitoCampo) {
                    // Pegar o nome do requisito da tabela 'requisito' baseado no 'idrequisito' em 'requisitocampo'
                    $requisito = Requisito::find($requisitoCampo->idrequisito); // Pegando o requisito pelo ID

                    if ($requisito) {
                        // Somar o score do campo
                        $score += $requisitoCampo->score;

                        // Adicionar o nome do requisito e o valor (exemplo: descricao do requisito => valor)
                        $descricaoCampos[] = "{$requisito->descricao}: {$requisitoCampo->nomecampo}, "; // Supondo que "valorcampo" seja o valor selecionado pelo usuário
                    }
                }
            }

            // Verificando o match da formação
            $formacaoMatch = $usuario->formacao->id >= $vaga->formacao->id;

            // Retorna os dados do usuário com o score calculado e as descrições dos campos selecionados
            return [
                'nome' => $usuario->nome,
                'email' => $usuario->email,
                'telefone' => $usuario->telefone,
                'score' => $score, // Score baseado nos requisitos
                'formacao_match' => $formacaoMatch ? 'Sim' : 'Não',
                'descricao_campos' => implode("\n", $descricaoCampos), // Adiciona cada campo com descrição e valor em uma nova linha
                'usuario_url' => "http://localhost:3000/usuario/{$usuario->id}", // URL do usuário
            ];
        });

        // Ordenando os usuários pelo score de forma decrescente
        $usuariosOrdenados = $usuarios->sortByDesc('score');

        // Mapeando os dados para o formato necessário para exportação
        $data = $usuariosOrdenados->map(function ($usuario) {
            return [
                $usuario['nome'],
                $usuario['email'],
                $usuario['telefone'],
                $usuario['score'], // Score calculado
                $usuario['formacao_match'],
                $usuario['descricao_campos'], // As descrições dos campos selecionados com valores
                $usuario['usuario_url'], // Incluindo a URL do usuário
            ];
        });

        return collect($data);
    }

    public function headings(): array
    {
        return ['Nome', 'Email', 'Telefone', 'Score', 'Atende a Formação da vaga ?', 'Campos Selecionados', 'URL do Usuário'];
    }

    public function export()
    {
        $excelFile = Excel::raw($this, \Maatwebsite\Excel\Excel::XLSX); // Gera o arquivo XLSX no formato raw

        $base64File = base64_encode($excelFile);

        return response()->json([
            'base64' => $base64File,
        ]);
    }
}
