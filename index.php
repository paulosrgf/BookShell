#!/usr/bin/env php
<?php
 
// Ponto de entrada do BookShell
// Inclui todas as funções de negócio definidas em functions.php
require_once __DIR__ . '/functions.php';
 
// ============================================================
//  CONFIGURAÇÃO
// ============================================================
 
// Caminho do arquivo JSON que persiste os dados entre execuções
define('ARQUIVO_DADOS', __DIR__ . '/livros.json');
 
// ============================================================
//  INICIALIZAÇÃO
// ============================================================
 
// Carrega os livros salvos (retorna [] se o arquivo não existir)
$livros = carregarLivros(ARQUIVO_DADOS);
 
// ============================================================
//  LOOP PRINCIPAL DO MENU
// ============================================================
 
while (true) {
    exibirMenu();
 
    echo "Escolha uma opção: ";
    $opcao = trim(fgets(STDIN));   // lê a entrada do teclado
 
    switch ($opcao) {
        case '1':
            cadastrarLivro($livros, ARQUIVO_DADOS);
            break;
 
        case '2':
            listarLivros($livros);
            break;
 
        case '3':
            buscarLivros($livros);
            break;
 
        case '4':
            editarLivro($livros, ARQUIVO_DADOS);
            break;
 
        case '5':
            removerLivro($livros, ARQUIVO_DADOS);
            break;
 
        case '6':
            exibirEstatisticas($livros);
            break;
 
        case '0':
            echo "\n  Até logo! 📚\n\n";
            exit(0);   // encerra o processo com código 0 (sucesso)
 
        default:
            echo "\n  ✗ Opção inválida. Tente novamente.\n";
    }
 
    // Pausa antes de redesenhar o menu
    echo "\nPressione Enter para continuar...";
    fgets(STDIN);
}
 
// ============================================================
//  FUNÇÕES DE INTERFACE (UI)
// ============================================================
 
/**
 * Desenha o menu principal no terminal.
 * Separada aqui no index para deixar claro que é responsabilidade
 * de apresentação, não de regra de negócio.
 */
function exibirMenu(): void
{
    // Limpa a tela: 'clear' no Linux/Mac, 'cls' no Windows
    $cmd = (PHP_OS_FAMILY === 'Windows') ? 'cls' : 'clear';
    system($cmd);
 
    echo "╔══════════════════════════════════════╗\n";
    echo "║          📚  B O O K S H E L L        ║\n";
    echo "║     Sistema de Gerenciamento de       ║\n";
    echo "║              Livros                   ║\n";
    echo "╠══════════════════════════════════════╣\n";
    echo "║  1 » Cadastrar livro                  ║\n";
    echo "║  2 » Listar livros                    ║\n";
    echo "║  3 » Buscar livro                     ║\n";
    echo "║  4 » Editar livro                     ║\n";
    echo "║  5 » Remover livro                    ║\n";
    echo "║  6 » Estatísticas                     ║\n";
    echo "║  0 » Sair                             ║\n";
    echo "╚══════════════════════════════════════╝\n\n";
}