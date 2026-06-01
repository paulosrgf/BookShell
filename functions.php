<?php

// ============================================================
//  FUNÇÕES DE PERSISTÊNCIA
// ============================================================

/**
 * Carrega os livros salvos no arquivo JSON.
 * Retorna um array vazio se o arquivo não existir.
 */
function carregarLivros(string $arquivo): array
{
    if (!file_exists($arquivo)) {
        return [];
    }

    $json = file_get_contents($arquivo);
    $dados = json_decode($json, true);

    return is_array($dados) ? $dados : [];
}

/**
 * Salva o array de livros no arquivo JSON com formatação legível.
 */
function salvarLivros(array $livros, string $arquivo): void
{
    file_put_contents($arquivo, json_encode($livros, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// ============================================================
//  GERAÇÃO DE ID
// ============================================================

/**
 * Gera um ID único incrementando o maior ID existente.
 * Se não houver livros, começa do 1.
 */
function gerarId(array $livros): int
{
    if (empty($livros)) {
        return 1;
    }

    $ids = array_column($livros, 'id');   // extrai só os IDs
    return max($ids) + 1;                 // próximo = maior + 1
}

// ============================================================
//  VALIDAÇÕES
// ============================================================

/**
 * Valida o título: não pode ser vazio e deve ter no máximo 100 chars.
 * Retorna a mensagem de erro ou string vazia se estiver ok.
 */
function validarTitulo(string $titulo): string
{
    if (trim($titulo) === '') {
        return 'O título não pode ser vazio.';
    }
    if (mb_strlen($titulo) > 100) {
        return 'O título deve ter no máximo 100 caracteres.';
    }
    return '';
}

/**
 * Valida o autor: não pode ser vazio e deve ter no máximo 80 chars.
 */
function validarAutor(string $autor): string
{
    if (trim($autor) === '') {
        return 'O autor não pode ser vazio.';
    }
    if (mb_strlen($autor) > 80) {
        return 'O autor deve ter no máximo 80 caracteres.';
    }
    return '';
}

/**
 * Valida o número de páginas: deve ser inteiro positivo entre 1 e 99999.
 */
function validarPaginas(string $entrada): string
{
    if (!ctype_digit(trim($entrada))) {
        return 'Informe um número inteiro positivo para páginas.';
    }
    $paginas = (int) $entrada;
    if ($paginas < 1 || $paginas > 99999) {
        return 'O número de páginas deve estar entre 1 e 99999.';
    }
    return '';
}

/**
 * Valida o campo "lido": aceita s/n (case-insensitive).
 */
function validarLido(string $entrada): string
{
    $val = strtolower(trim($entrada));
    if ($val !== 's' && $val !== 'n') {
        return 'Digite S para sim ou N para não.';
    }
    return '';
}

// ============================================================
//  CRUD
// ============================================================

/**
 * Cadastra um novo livro no array e persiste no arquivo.
 * Campos: id (int), titulo (string), autor (string),
 *         paginas (int), lido (bool).
 */
function cadastrarLivro(array &$livros, string $arquivo): void
{
    echo "\n=== CADASTRAR LIVRO ===\n";

    // --- título ---
    do {
        echo "Título: ";
        $titulo = trim(fgets(STDIN));
        $erro   = validarTitulo($titulo);
        if ($erro) echo "  ✗ $erro\n";
    } while ($erro);

    // --- autor ---
    do {
        echo "Autor: ";
        $autor = trim(fgets(STDIN));
        $erro  = validarAutor($autor);
        if ($erro) echo "  ✗ $erro\n";
    } while ($erro);

    // --- páginas ---
    do {
        echo "Número de páginas: ";
        $entradaPag = trim(fgets(STDIN));
        $erro       = validarPaginas($entradaPag);
        if ($erro) echo "  ✗ $erro\n";
    } while ($erro);

    // --- lido ---
    do {
        echo "Já leu este livro? (S/N): ";
        $entradaLido = trim(fgets(STDIN));
        $erro        = validarLido($entradaLido);
        if ($erro) echo "  ✗ $erro\n";
    } while ($erro);

    $livro = [
        'id'     => gerarId($livros),
        'titulo' => $titulo,
        'autor'  => $autor,
        'paginas'=> (int) $entradaPag,
        'lido'   => strtolower($entradaLido) === 's',   // boolean
    ];

    $livros[] = $livro;
    salvarLivros($livros, $arquivo);

    echo "\n  ✔ Livro cadastrado com ID #{$livro['id']}!\n";
}

/**
 * Lista todos os livros ordenados alfabeticamente pelo título.
 * Se não houver livros, exibe aviso.
 */
function listarLivros(array $livros): void
{
    echo "\n=== LISTA DE LIVROS ===\n";

    if (empty($livros)) {
        echo "  Nenhum livro cadastrado.\n";
        return;
    }

    // cria cópia para não alterar o array original
    $ordenados = $livros;
    usort($ordenados, fn($a, $b) => mb_strtolower($a['titulo']) <=> mb_strtolower($b['titulo']));

    echo str_repeat('-', 72) . "\n";
    printf("%-4s %-32s %-22s %6s  %s\n", 'ID', 'TÍTULO', 'AUTOR', 'PÁGS', 'LIDO');
    echo str_repeat('-', 72) . "\n";

    foreach ($ordenados as $l) {
        printf(
            "%-4d %-32s %-22s %6d  %s\n",
            $l['id'],
            mb_substr($l['titulo'], 0, 32),
            mb_substr($l['autor'],  0, 22),
            $l['paginas'],
            $l['lido'] ? '✔' : '✘'
        );
    }

    echo str_repeat('-', 72) . "\n";
    echo '  Total: ' . count($livros) . " livro(s)\n";
}

/**
 * Busca livros cujo título contenha o termo informado (case-insensitive).
 */
function buscarLivros(array $livros): void
{
    echo "\n=== BUSCAR LIVRO ===\n";
    echo "Termo de busca: ";
    $termo = trim(fgets(STDIN));

    if ($termo === '') {
        echo "  ✗ Informe um termo para buscar.\n";
        return;
    }

    // mb_stripos retorna false se não encontrar
    $resultado = array_filter(
        $livros,
        fn($l) => mb_stripos($l['titulo'], $termo) !== false
    );

    if (empty($resultado)) {
        echo "  Nenhum livro encontrado para \"$termo\".\n";
        return;
    }

    echo "\n  " . count($resultado) . " resultado(s) para \"$termo\":\n";
    listarLivros(array_values($resultado));   // reusa a função de listagem
}

/**
 * Edita um livro existente.
 * Ao pressionar Enter sem digitar nada, o valor atual é mantido.
 */
function editarLivro(array &$livros, string $arquivo): void
{
    echo "\n=== EDITAR LIVRO ===\n";

    if (empty($livros)) {
        echo "  Nenhum livro cadastrado.\n";
        return;
    }

    echo "ID do livro a editar: ";
    $inputId = trim(fgets(STDIN));

    $indice = encontrarIndicePorId($livros, (int) $inputId);

    if ($indice === -1) {
        echo "  ✗ Livro não encontrado.\n";
        return;
    }

    $livro = $livros[$indice];
    echo "\n  Editando: {$livro['titulo']} (pressione Enter para manter o valor atual)\n\n";

    // --- título ---
    do {
        echo "  Título [{$livro['titulo']}]: ";
        $novoTitulo = fgets(STDIN);               // não aplica trim ainda
        $novoTitulo = rtrim($novoTitulo, "\n\r"); // remove só quebra de linha
        if ($novoTitulo === '') {
            $novoTitulo = $livro['titulo'];        // mantém valor atual
            $erro = '';
        } else {
            $novoTitulo = trim($novoTitulo);
            $erro = validarTitulo($novoTitulo);
            if ($erro) echo "    ✗ $erro\n";
        }
    } while ($erro);

    // --- autor ---
    do {
        echo "  Autor [{$livro['autor']}]: ";
        $novoAutor = fgets(STDIN);
        $novoAutor = rtrim($novoAutor, "\n\r");
        if ($novoAutor === '') {
            $novoAutor = $livro['autor'];
            $erro = '';
        } else {
            $novoAutor = trim($novoAutor);
            $erro = validarAutor($novoAutor);
            if ($erro) echo "    ✗ $erro\n";
        }
    } while ($erro);

    // --- páginas ---
    do {
        echo "  Páginas [{$livro['paginas']}]: ";
        $novaPag = fgets(STDIN);
        $novaPag = rtrim($novaPag, "\n\r");
        if ($novaPag === '') {
            $novaPag = (string) $livro['paginas'];
            $erro = '';
        } else {
            $novaPag = trim($novaPag);
            $erro = validarPaginas($novaPag);
            if ($erro) echo "    ✗ $erro\n";
        }
    } while ($erro);

    // --- lido ---
    $lidoAtual = $livro['lido'] ? 'S' : 'N';
    do {
        echo "  Lido? [{$lidoAtual}] (S/N): ";
        $novoLido = fgets(STDIN);
        $novoLido = rtrim($novoLido, "\n\r");
        if ($novoLido === '') {
            $novoLido = $lidoAtual;
            $erro = '';
        } else {
            $novoLido = trim($novoLido);
            $erro = validarLido($novoLido);
            if ($erro) echo "    ✗ $erro\n";
        }
    } while ($erro);

    // aplica as alterações no array original
    $livros[$indice]['titulo']  = $novoTitulo;
    $livros[$indice]['autor']   = $novoAutor;
    $livros[$indice]['paginas'] = (int) $novaPag;
    $livros[$indice]['lido']    = strtolower($novoLido) === 's';

    salvarLivros($livros, $arquivo);
    echo "\n  ✔ Livro atualizado com sucesso!\n";
}

/**
 * Remove um livro após confirmação do usuário.
 */
function removerLivro(array &$livros, string $arquivo): void
{
    echo "\n=== REMOVER LIVRO ===\n";

    if (empty($livros)) {
        echo "  Nenhum livro cadastrado.\n";
        return;
    }

    echo "ID do livro a remover: ";
    $inputId = trim(fgets(STDIN));

    $indice = encontrarIndicePorId($livros, (int) $inputId);

    if ($indice === -1) {
        echo "  ✗ Livro não encontrado.\n";
        return;
    }

    $livro = $livros[$indice];
    echo "\n  Livro: \"{$livro['titulo']}\" — {$livro['autor']}\n";
    echo "  Confirmar remoção? (S/N): ";
    $confirm = strtolower(trim(fgets(STDIN)));

    if ($confirm !== 's') {
        echo "  Remoção cancelada.\n";
        return;
    }

    array_splice($livros, $indice, 1);   // remove sem deixar buraco de índice
    salvarLivros($livros, $arquivo);
    echo "\n  ✔ Livro removido com sucesso!\n";
}

/**
 * Exibe estatísticas calculadas a partir do acervo.
 * 1. Total de livros
 * 2. Livros lidos vs não lidos
 * 3. Média de páginas
 * 4. Livro com mais páginas
 * 5. Livro com menos páginas
 */
function exibirEstatisticas(array $livros): void
{
    echo "\n=== ESTATÍSTICAS ===\n";

    if (empty($livros)) {
        echo "  Nenhum dado disponível.\n";
        return;
    }

    $total      = count($livros);
    $lidos      = count(array_filter($livros, fn($l) => $l['lido']));
    $naoLidos   = $total - $lidos;
    $paginas    = array_column($livros, 'paginas');
    $mediaPag   = array_sum($paginas) / $total;
    $maxPag     = max($paginas);
    $minPag     = min($paginas);

    // encontra os livros correspondentes ao max e min
    $maisGrande = array_filter($livros, fn($l) => $l['paginas'] === $maxPag);
    $menorLivro = array_filter($livros, fn($l) => $l['paginas'] === $minPag);

    $tituloMaior = array_values($maisGrande)[0]['titulo'];
    $tituloMenor = array_values($menorLivro)[0]['titulo'];

    echo str_repeat('-', 50) . "\n";
    echo "  📚 Total de livros cadastrados : $total\n";
    echo "  ✔  Livros lidos                : $lidos\n";
    echo "  ✘  Livros não lidos            : $naoLidos\n";
    printf("  📄 Média de páginas            : %.1f\n", $mediaPag);
    echo "  🔝 Livro com mais páginas      : \"$tituloMaior\" ($maxPag págs)\n";
    echo "  🔻 Livro com menos páginas     : \"$tituloMenor\" ($minPag págs)\n";
    echo str_repeat('-', 50) . "\n";
}

// ============================================================
//  UTILITÁRIOS
// ============================================================

/**
 * Percorre o array e retorna o índice (posição) do livro com o ID dado.
 * Retorna -1 se não encontrar.
 */
function encontrarIndicePorId(array $livros, int $id): int
{
    foreach ($livros as $indice => $livro) {
        if ($livro['id'] === $id) {
            return $indice;
        }
    }
    return -1;
}