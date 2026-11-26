<?php
/**
 * Cabeçalho comum a todas as páginas.
 * Antes de incluir, a página define:
 *   $raiz         => '' para páginas na raiz, '../' para páginas em subpastas
 *   $tituloPagina => título exibido na aba do navegador
 *   $paginaAtual  => 'inicio', 'sobre', 'painel', 'netrunners' ou 'protocolos'
 */
$raiz = $raiz ?? '';
$tituloPagina = $tituloPagina ?? 'NEON-77';
$paginaAtual = $paginaAtual ?? '';
$flash = flash_obter();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($tituloPagina) ?> | NEON-77</title>
    <link rel="icon" href="<?= $raiz ?>img/logo.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700;900&family=Share+Tech+Mono&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $raiz ?>css/style.css">
</head>
<body>
<header class="topo">
    <a class="marca" href="<?= $raiz ?>index.php">
        <img src="<?= $raiz ?>img/logo.svg" alt="" width="40" height="40">
        <span>NEON-77</span>
    </a>
    <nav class="menu" aria-label="Menu principal">
        <a href="<?= $raiz ?>index.php" class="<?= $paginaAtual === 'inicio' ? 'ativo' : '' ?>">Início</a>
        <a href="<?= $raiz ?>sobre.php" class="<?= $paginaAtual === 'sobre' ? 'ativo' : '' ?>">O instituto</a>
        <a href="<?= $raiz ?>painel.php" class="<?= $paginaAtual === 'painel' ? 'ativo' : '' ?>">Painel</a>
        <a href="<?= $raiz ?>netrunners/listar.php" class="<?= $paginaAtual === 'netrunners' ? 'ativo' : '' ?>">Netrunners</a>
        <a href="<?= $raiz ?>protocolos/listar.php" class="<?= $paginaAtual === 'protocolos' ? 'ativo' : '' ?>">Protocolos</a>
    </nav>
</header>
<main class="conteudo">
<?php if ($flash): ?>
    <div class="alerta alerta-<?= h($flash['tipo']) ?>" role="status"><?= h($flash['mensagem']) ?></div>
<?php endif; ?>
