# 📚 BookShell

Sistema de gerenciamento de livros via terminal, desenvolvido em PHP puro (sem frameworks).

## Funcionalidades

- **Cadastrar** livros com título, autor, número de páginas e status de leitura
- **Listar** todos os livros ordenados alfabeticamente
- **Buscar** livros por título (busca parcial e case-insensitive)
- **Editar** registros existentes (Enter mantém o valor atual)
- **Remover** livros com confirmação antes de excluir
- **Estatísticas** do acervo (total, lidos/não lidos, média de páginas, maior e menor livro)

## Requisitos

- PHP 8.0 ou superior

## Como rodar

```bash
git clone https://github.com/seu-usuario/BookShell.git
cd BookShell
php index.php
```

## Estrutura

```
BookShell/
├── index.php       # Ponto de entrada e menu interativo
├── functions.php   # Lógica de negócio e validações
└── livros.json     # Gerado automaticamente ao cadastrar o primeiro livro
```

## Campos de cada livro

| Campo   | Tipo    | Descrição                  |
|---------|---------|----------------------------|
| id      | inteiro | Gerado automaticamente     |
| titulo  | string  | Título do livro            |
| autor   | string  | Nome do autor              |
| paginas | inteiro | Número de páginas          |
| lido    | boolean | Se o livro já foi lido     |
