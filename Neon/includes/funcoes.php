<?php
/**
 * Funções auxiliares usadas em todo o sistema.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** Escapa um valor antes de exibi-lo no HTML (proteção contra XSS). */
function h($valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

/** Redireciona para outra página e encerra o script. */
function redirecionar(string $url): void
{
    header("Location: $url");
    exit;
}

/** Guarda uma mensagem para ser exibida na próxima página. */
function flash_definir(string $tipo, string $mensagem): void
{
    $_SESSION['flash'] = ['tipo' => $tipo, 'mensagem' => $mensagem];
}

/** Recupera (e apaga) a mensagem guardada. */
function flash_obter(): ?array
{
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Monta o link de uma página da paginação preservando
 * pesquisa, filtros e ordenação que estão na URL.
 */
function link_pagina(int $pagina): string
{
    $parametros = $_GET;
    $parametros['pagina'] = $pagina;
    return '?' . http_build_query($parametros);
}

/** Faz o bindValue de uma lista de parâmetros, usando o tipo correto. */
function vincular_parametros(PDOStatement $stmt, array $parametros): void
{
    foreach ($parametros as $chave => $valor) {
        $tipo = is_int($valor) ? PDO::PARAM_INT : PDO::PARAM_STR;
        $stmt->bindValue($chave, $valor, $tipo);
    }
}

/** Valida um ID recebido pela URL ou formulário. Retorna o inteiro ou null. */
function id_valido($valor): ?int
{
    $id = filter_var($valor, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    return $id === false ? null : $id;
}

/** Executa uma consulta sem parâmetros externos e devolve todas as linhas. */
function consultar(PDO $pdo, string $sql): array
{
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll();
}

/** Formata um número decimal no padrão brasileiro (vírgula). */
function decimal($valor, int $casas = 1): string
{
    return number_format((float) $valor, $casas, ',', '.');
}

/** Calcula a porcentagem de uma parte sobre o total, sem dividir por zero. */
function porcentagem($parte, $total): float
{
    return $total > 0 ? ($parte / $total) * 100 : 0.0;
}
