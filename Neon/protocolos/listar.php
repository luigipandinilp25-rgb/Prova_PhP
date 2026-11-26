<?php
/**
 * Listagem de protocolos (cursos) com pesquisa por nome e paginação.
 */
require_once '../config/conexao.php';
require_once '../includes/funcoes.php';

$busca = trim($_GET['busca'] ?? '');

$where = '';
$parametros = [];
if ($busca !== '') {
    $where = 'WHERE p.nome LIKE :busca';
    $parametros[':busca'] = '%' . $busca . '%';
}

$porPagina = 6;
$pagina = id_valido($_GET['pagina'] ?? 1) ?? 1;

$stmt = $pdo->prepare("SELECT COUNT(*) FROM protocolos p $where");
vincular_parametros($stmt, $parametros);
$stmt->execute();
$total = (int) $stmt->fetchColumn();

$totalPaginas = max(1, (int) ceil($total / $porPagina));
$pagina = min($pagina, $totalPaginas);
$offset = ($pagina - 1) * $porPagina;

$stmt = $pdo->prepare(
    "SELECT p.id, p.nome, p.descricao, p.duracao_ciclos, p.mentor,
            (SELECT COUNT(*) FROM netrunners n WHERE n.protocolo_id = p.id) AS total_netrunners
     FROM protocolos p
     $where
     ORDER BY p.nome ASC
     LIMIT :limite OFFSET :offset"
);
vincular_parametros($stmt, $parametros);
$stmt->bindValue(':limite', $porPagina, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$protocolos = $stmt->fetchAll();

$raiz = '../';
$tituloPagina = 'Protocolos';
$paginaAtual = 'protocolos';
require '../includes/header.php';
?>

<section class="cabecalho-secao">
    <div>
        <h1>Protocolos</h1>
        <p class="sub">Os cursos do instituto. Cada netrunner segue um protocolo.</p>
    </div>
    <a class="botao botao-primario" href="cadastrar.php">Criar protocolo</a>
</section>

<form class="painel filtros filtros-simples" method="get" action="listar.php">
    <label>
        Pesquisar
        <input type="search" name="busca" value="<?= h($busca) ?>" placeholder="Nome do protocolo">
    </label>
    <div class="acoes-filtro">
        <button class="botao botao-primario" type="submit">Pesquisar</button>
        <a class="botao" href="listar.php">Limpar</a>
    </div>
</form>

<?php if (!$protocolos): ?>
    <div class="painel vazio"><p>Nenhum protocolo encontrado. Tente outro nome ou crie um novo protocolo.</p></div>
<?php else: ?>
    <div class="grade-protocolos">
        <?php foreach ($protocolos as $p): ?>
            <article class="chip">
                <h2><?= h($p['nome']) ?></h2>
                <p><?= h($p['descricao']) ?></p>
                <dl>
                    <div><dt>Mentor</dt><dd><?= h($p['mentor']) ?></dd></div>
                    <div><dt>Duração</dt><dd><?= (int) $p['duracao_ciclos'] ?> ciclos</dd></div>
                    <div><dt>Netrunners</dt><dd><?= (int) $p['total_netrunners'] ?></dd></div>
                </dl>
                <div class="acoes">
                    <a href="../netrunners/listar.php?protocolo=<?= (int) $p['id'] ?>">Ver netrunners</a>
                    <a href="editar.php?id=<?= (int) $p['id'] ?>">Editar</a>
                    <a class="perigo" href="excluir.php?id=<?= (int) $p['id'] ?>">Excluir</a>
                </div>
            </article>
        <?php endforeach; ?>
    </div>

    <?php if ($totalPaginas > 1): ?>
    <nav class="paginacao" aria-label="Paginação">
        <?php if ($pagina > 1): ?><a href="<?= h(link_pagina($pagina - 1)) ?>">Anterior</a><?php endif; ?>
        <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
            <?php if ($i === $pagina): ?>
                <span class="atual" aria-current="page"><?= $i ?></span>
            <?php else: ?>
                <a href="<?= h(link_pagina($i)) ?>"><?= $i ?></a>
            <?php endif; ?>
        <?php endfor; ?>
        <?php if ($pagina < $totalPaginas): ?><a href="<?= h(link_pagina($pagina + 1)) ?>">Próxima</a><?php endif; ?>
    </nav>
    <?php endif; ?>
<?php endif; ?>

<?php require '../includes/footer.php'; ?>
