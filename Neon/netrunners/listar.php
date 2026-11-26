<?php
/**
 * Consulta completa de netrunners:
 * pesquisa com LIKE, filtros (protocolo e nível), ordenação e paginação.
 */
require_once '../config/conexao.php';
require_once '../includes/funcoes.php';

// ---- Leitura dos parâmetros da URL ----
$busca     = trim($_GET['busca'] ?? '');
$protocolo = $_GET['protocolo'] ?? '';
$nivel     = $_GET['nivel'] ?? '';
$ordem     = $_GET['ordem'] ?? 'nome_asc';

// ORDER BY não aceita parâmetro em prepared statement,
// por isso usamos uma lista branca de ordenações permitidas.
$ordens = [
    'nome_asc'  => ['n.nome ASC', 'Nome A-Z'],
    'nome_desc' => ['n.nome DESC', 'Nome Z-A'],
    'recente'   => ['n.criado_em DESC, n.id DESC', 'Mais recentes'],
    'antigo'    => ['n.criado_em ASC, n.id ASC', 'Mais antigos'],
];
if (!isset($ordens[$ordem])) {
    $ordem = 'nome_asc';
}

// ---- Montagem do WHERE com parâmetros nomeados ----
$condicoes  = [];
$parametros = [];

if ($busca !== '') {
    $condicoes[] = '(n.nome LIKE :busca_nome OR n.codinome LIKE :busca_codinome)';
    $parametros[':busca_nome']     = '%' . $busca . '%';
    $parametros[':busca_codinome'] = '%' . $busca . '%';
}

$protocoloId = id_valido($protocolo);
if ($protocoloId !== null) {
    $condicoes[] = 'n.protocolo_id = :protocolo';
    $parametros[':protocolo'] = $protocoloId;
}

$nivelInt = filter_var($nivel, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 5]]);
if ($nivelInt !== false) {
    $condicoes[] = 'n.nivel_implante = :nivel';
    $parametros[':nivel'] = $nivelInt;
}

$where = $condicoes ? 'WHERE ' . implode(' AND ', $condicoes) : '';

// ---- Paginação ----
$porPagina = 6;
$pagina = id_valido($_GET['pagina'] ?? 1) ?? 1;

$stmt = $pdo->prepare("SELECT COUNT(*) FROM netrunners n $where");
vincular_parametros($stmt, $parametros);
$stmt->execute();
$total = (int) $stmt->fetchColumn();

$totalPaginas = max(1, (int) ceil($total / $porPagina));
$pagina = min($pagina, $totalPaginas);
$offset = ($pagina - 1) * $porPagina;

// ---- Consulta principal ----
$sql = "SELECT n.id, n.nome, n.codinome, n.email, n.idade, n.nivel_implante, n.criado_em,
               p.nome AS protocolo_nome
        FROM netrunners n
        INNER JOIN protocolos p ON p.id = n.protocolo_id
        $where
        ORDER BY {$ordens[$ordem][0]}
        LIMIT :limite OFFSET :offset";

$stmt = $pdo->prepare($sql);
vincular_parametros($stmt, $parametros);
$stmt->bindValue(':limite', $porPagina, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$netrunners = $stmt->fetchAll();

// Protocolos para o filtro
$stmt = $pdo->prepare('SELECT id, nome FROM protocolos ORDER BY nome');
$stmt->execute();
$protocolos = $stmt->fetchAll();

$raiz = '../';
$tituloPagina = 'Netrunners';
$paginaAtual = 'netrunners';
require '../includes/header.php';
?>

<section class="cabecalho-secao">
    <div>
        <h1>Netrunners</h1>
        <p class="sub"><?= $total ?> <?= $total === 1 ? 'netrunner encontrado' : 'netrunners encontrados' ?> na rede.</p>
    </div>
    <a class="botao botao-primario" href="cadastrar.php">Matricular netrunner</a>
</section>

<form class="painel filtros" method="get" action="listar.php">
    <label>
        Pesquisar
        <input type="search" name="busca" value="<?= h($busca) ?>" placeholder="Nome ou codinome">
    </label>
    <label>
        Protocolo
        <select name="protocolo">
            <option value="">Todos</option>
            <?php foreach ($protocolos as $p): ?>
                <option value="<?= $p['id'] ?>" <?= $protocoloId === (int) $p['id'] ? 'selected' : '' ?>><?= h($p['nome']) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>
        Nível de implante
        <select name="nivel">
            <option value="">Todos</option>
            <?php for ($i = 1; $i <= 5; $i++): ?>
                <option value="<?= $i ?>" <?= $nivelInt === $i ? 'selected' : '' ?>>Nível <?= $i ?></option>
            <?php endfor; ?>
        </select>
    </label>
    <label>
        Ordenar por
        <select name="ordem">
            <?php foreach ($ordens as $chave => $info): ?>
                <option value="<?= $chave ?>" <?= $ordem === $chave ? 'selected' : '' ?>><?= $info[1] ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <div class="acoes-filtro">
        <button class="botao botao-primario" type="submit">Aplicar</button>
        <a class="botao" href="listar.php">Limpar</a>
    </div>
</form>

<?php if (!$netrunners): ?>
    <div class="painel vazio">
        <p>Nenhum netrunner corresponde a essa busca. Tente outro nome ou limpe os filtros.</p>
    </div>
<?php else: ?>
    <div class="tabela-rolagem">
        <table class="tabela">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Codinome</th>
                    <th>E-mail</th>
                    <th>Idade</th>
                    <th>Implante</th>
                    <th>Protocolo</th>
                    <th>Matrícula</th>
                    <th><span class="sr">Ações</span></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($netrunners as $n): ?>
                <tr>
                    <td><?= h($n['nome']) ?></td>
                    <td class="codinome"><?= h($n['codinome']) ?></td>
                    <td><?= h($n['email']) ?></td>
                    <td><?= (int) $n['idade'] ?></td>
                    <td><span class="nivel nivel-<?= (int) $n['nivel_implante'] ?>" title="Nível <?= (int) $n['nivel_implante'] ?>"><?= str_repeat('▮', (int) $n['nivel_implante']) . str_repeat('▯', 5 - (int) $n['nivel_implante']) ?></span></td>
                    <td><?= h($n['protocolo_nome']) ?></td>
                    <td><?= date('d/m/Y', strtotime($n['criado_em'])) ?></td>
                    <td class="acoes">
                        <a href="editar.php?id=<?= (int) $n['id'] ?>">Editar</a>
                        <a class="perigo" href="excluir.php?id=<?= (int) $n['id'] ?>">Excluir</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPaginas > 1): ?>
    <nav class="paginacao" aria-label="Paginação">
        <?php if ($pagina > 1): ?>
            <a href="<?= h(link_pagina($pagina - 1)) ?>">Anterior</a>
        <?php endif; ?>
        <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
            <?php if ($i === $pagina): ?>
                <span class="atual" aria-current="page"><?= $i ?></span>
            <?php else: ?>
                <a href="<?= h(link_pagina($i)) ?>"><?= $i ?></a>
            <?php endif; ?>
        <?php endfor; ?>
        <?php if ($pagina < $totalPaginas): ?>
            <a href="<?= h(link_pagina($pagina + 1)) ?>">Próxima</a>
        <?php endif; ?>
    </nav>
    <?php endif; ?>
<?php endif; ?>

<?php require '../includes/footer.php'; ?>
