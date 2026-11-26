<?php
/**
 * Painel de dados (dashboard) da NEON-77.
 * Todos os números são calculados no próprio MySQL com funções de agregação
 * (COUNT, AVG, MIN, MAX, SUM), GROUP BY, LEFT JOIN e CASE.
 * Nenhum dado vem do usuário, mas seguimos o padrão prepare() + execute() do sistema.
 */
require_once 'config/conexao.php';
require_once 'includes/funcoes.php';

// ---- 1. Indicadores gerais ----
$resumo = consultar($pdo,
    'SELECT COUNT(*)            AS total,
            AVG(idade)          AS idade_media,
            MIN(idade)          AS idade_min,
            MAX(idade)          AS idade_max,
            AVG(nivel_implante) AS nivel_medio,
            SUM(CASE WHEN nivel_implante >= 4 THEN 1 ELSE 0 END) AS cromados
     FROM netrunners'
)[0];
$totalNetrunners = (int) $resumo['total'];
$totalProtocolos = (int) consultar($pdo, 'SELECT COUNT(*) AS total FROM protocolos')[0]['total'];

// ---- 2. Netrunners por protocolo (LEFT JOIN para incluir protocolos sem alunos) ----
$porProtocolo = consultar($pdo,
    'SELECT p.id, p.nome, p.mentor, p.duracao_ciclos,
            COUNT(n.id)           AS total,
            AVG(n.idade)          AS idade_media,
            AVG(n.nivel_implante) AS nivel_medio
     FROM protocolos p
     LEFT JOIN netrunners n ON n.protocolo_id = p.id
     GROUP BY p.id, p.nome, p.mentor, p.duracao_ciclos
     ORDER BY total DESC, p.nome ASC'
);
$protocolosComAlunos = array_filter($porProtocolo, fn($p) => (int) $p['total'] > 0);
$protocolosVazios    = array_filter($porProtocolo, fn($p) => (int) $p['total'] === 0);
$maiorProtocolo      = $porProtocolo ? max(array_map(fn($p) => (int) $p['total'], $porProtocolo)) : 0;

// ---- 3. Distribuição por nível de implante (níveis 1 a 5, mesmo os sem ninguém) ----
$niveis = array_fill(1, 5, 0);
foreach (consultar($pdo, 'SELECT nivel_implante AS nivel, COUNT(*) AS total FROM netrunners GROUP BY nivel_implante') as $linha) {
    $niveis[(int) $linha['nivel']] = (int) $linha['total'];
}
$maiorNivel = max($niveis);

// ---- 4. Faixas etárias com o nível médio de implante de cada uma ----
$faixas = ['Até 19' => null, '20 a 24' => null, '25 a 29' => null, '30 ou mais' => null];
$linhasFaixa = consultar($pdo,
    "SELECT CASE
                WHEN idade < 20 THEN 'Até 19'
                WHEN idade < 25 THEN '20 a 24'
                WHEN idade < 30 THEN '25 a 29'
                ELSE '30 ou mais'
            END AS faixa,
            COUNT(*)            AS total,
            AVG(nivel_implante) AS nivel_medio
     FROM netrunners
     GROUP BY faixa"
);
foreach ($linhasFaixa as $linha) {
    $faixas[$linha['faixa']] = $linha;
}
$maiorFaixa = $linhasFaixa ? max(array_map(fn($f) => (int) $f['total'], $linhasFaixa)) : 0;

// ---- 5. Matrículas por mês ----
$porMes = consultar($pdo,
    "SELECT DATE_FORMAT(criado_em, '%Y-%m') AS mes, COUNT(*) AS total
     FROM netrunners
     GROUP BY mes
     ORDER BY mes ASC"
);
$maiorMes = $porMes ? max(array_map(fn($m) => (int) $m['total'], $porMes)) : 0;
$nomesMeses = ['jan', 'fev', 'mar', 'abr', 'mai', 'jun', 'jul', 'ago', 'set', 'out', 'nov', 'dez'];
function rotulo_mes(string $anoMes, array $nomes): string
{
    [$ano, $mes] = explode('-', $anoMes);
    return $nomes[(int) $mes - 1] . '/' . substr($ano, -2);
}

// ---- 6. Destaques individuais ----
$sqlDestaque = 'SELECT n.codinome, n.nome, n.idade, n.nivel_implante, p.nome AS protocolo
                FROM netrunners n
                INNER JOIN protocolos p ON p.id = n.protocolo_id';
$veterano   = consultar($pdo, "$sqlDestaque ORDER BY n.idade DESC, n.nome ASC LIMIT 1")[0] ?? null;
$maisJovem  = consultar($pdo, "$sqlDestaque ORDER BY n.idade ASC, n.nome ASC LIMIT 1")[0] ?? null;
$eliteNivel5 = consultar($pdo, "$sqlDestaque WHERE n.nivel_implante = 5 ORDER BY n.codinome ASC");

// ---- 7. Leituras automáticas (os "insights" em texto) ----
$leituras = [];

if ($protocolosComAlunos) {
    // Pode haver empate entre os protocolos mais procurados
    $lideres = array_filter($protocolosComAlunos, fn($p) => (int) $p['total'] === $maiorProtocolo);
    $nomesLideres = array_map(fn($p) => '<strong>' . h($p['nome']) . '</strong>', $lideres);
    if (count($lideres) === 1) {
        $leituras[] = sprintf(
            '%s é o protocolo mais procurado: concentra %s%% dos netrunners (%d de %d).',
            $nomesLideres[array_key_first($nomesLideres)], decimal(porcentagem($maiorProtocolo, $totalNetrunners), 0),
            $maiorProtocolo, $totalNetrunners
        );
    } else {
        $leituras[] = sprintf(
            'Empate no topo: %s têm %d netrunners cada e, juntos, reúnem %s%% da rede.',
            implode(' e ', $nomesLideres), $maiorProtocolo,
            decimal(porcentagem($maiorProtocolo * count($lideres), $totalNetrunners), 0)
        );
    }

    $maisCromado = $protocolosComAlunos;
    usort($maisCromado, fn($a, $b) => $b['nivel_medio'] <=> $a['nivel_medio']);
    $maisCromado = $maisCromado[0];
    $leituras[] = sprintf(
        'Os alunos mais cromados estão em <strong>%s</strong>, com nível médio de implante %s (a média do instituto é %s).',
        h($maisCromado['nome']), decimal($maisCromado['nivel_medio']), decimal($resumo['nivel_medio'])
    );
}

$faixasPreenchidas = array_values(array_filter($faixas));
if (count($faixasPreenchidas) >= 2) {
    $primeira = $faixasPreenchidas[0];
    $ultima   = end($faixasPreenchidas);
    $diferenca = $ultima['nivel_medio'] - $primeira['nivel_medio'];
    if (abs($diferenca) >= 0.5) {
        $leituras[] = sprintf(
            'Quanto mais velho, mais cromado: a faixa <strong>%s</strong> tem nível médio %s, contra %s na faixa <strong>%s</strong>.',
            h($ultima['faixa']), decimal($ultima['nivel_medio']), decimal($primeira['nivel_medio']), h($primeira['faixa'])
        );
    } else {
        $leituras[] = 'A idade quase não muda o nível de implante: as faixas etárias têm médias parecidas.';
    }
}

if ($porMes) {
    $pico = $porMes;
    usort($pico, fn($a, $b) => $b['total'] <=> $a['total']);
    $leituras[] = sprintf(
        'O pico de matrículas foi em <strong>%s</strong>, com %d novos netrunners.',
        rotulo_mes($pico[0]['mes'], $nomesMeses), $pico[0]['total']
    );
}

if ($protocolosVazios) {
    $leituras[] = sprintf(
        'Atenção: %s ainda %s nenhum netrunner matriculado.',
        implode(', ', array_map(fn($p) => '<strong>' . h($p['nome']) . '</strong>', $protocolosVazios)),
        count($protocolosVazios) === 1 ? 'não tem' : 'não têm'
    );
} elseif ($totalProtocolos > 0 && $totalNetrunners > 0) {
    $leituras[] = 'Todos os protocolos têm pelo menos um netrunner matriculado.';
}

$raiz = '';
$tituloPagina = 'Painel';
$paginaAtual = 'painel';
require 'includes/header.php';
?>

<section class="cabecalho-secao">
    <div>
        <h1>Painel da rede</h1>
        <p class="sub">Indicadores calculados em tempo real a partir do banco de dados.</p>
    </div>
    <a class="botao" href="netrunners/listar.php">Ver netrunners</a>
</section>

<?php if ($totalNetrunners === 0): ?>
    <div class="painel vazio">
        <p>Ainda não há netrunners matriculados, então não há dados para analisar.
           <a href="netrunners/cadastrar.php">Matricular o primeiro netrunner</a></p>
    </div>
<?php else: ?>

<!-- Indicadores principais -->
<section class="kpis" aria-label="Indicadores principais">
    <div class="painel kpi">
        <span class="kpi-rotulo">Netrunners</span>
        <span class="kpi-valor"><?= $totalNetrunners ?></span>
        <span class="kpi-detalhe">matriculados</span>
    </div>
    <div class="painel kpi">
        <span class="kpi-rotulo">Protocolos</span>
        <span class="kpi-valor"><?= count($protocolosComAlunos) ?><small>/<?= $totalProtocolos ?></small></span>
        <span class="kpi-detalhe">com alunos</span>
    </div>
    <div class="painel kpi">
        <span class="kpi-rotulo">Idade média</span>
        <span class="kpi-valor"><?= decimal($resumo['idade_media']) ?></span>
        <span class="kpi-detalhe">de <?= (int) $resumo['idade_min'] ?> a <?= (int) $resumo['idade_max'] ?> anos</span>
    </div>
    <div class="painel kpi">
        <span class="kpi-rotulo">Implante médio</span>
        <span class="kpi-valor"><?= decimal($resumo['nivel_medio']) ?><small>/5</small></span>
        <span class="kpi-detalhe">nível neural</span>
    </div>
    <div class="painel kpi kpi-alerta">
        <span class="kpi-rotulo">Cromados</span>
        <span class="kpi-valor"><?= decimal(porcentagem($resumo['cromados'], $totalNetrunners), 0) ?>%</span>
        <span class="kpi-detalhe"><?= (int) $resumo['cromados'] ?> com implante nível 4–5</span>
    </div>
</section>

<!-- Leituras automáticas -->
<?php if ($leituras): ?>
<section class="bloco">
    <h2>Leituras da rede</h2>
    <ul class="leituras">
        <?php foreach ($leituras as $leitura): ?>
            <li><?= $leitura /* já escapado com h() na montagem */ ?></li>
        <?php endforeach; ?>
    </ul>
</section>
<?php endif; ?>

<!-- Gráficos -->
<section class="bloco graficos">
    <article class="painel grafico">
        <h2>Netrunners por protocolo</h2>
        <ul class="barras">
            <?php foreach ($porProtocolo as $p): ?>
                <li>
                    <div class="barra-cabecalho">
                        <a href="netrunners/listar.php?protocolo=<?= (int) $p['id'] ?>"><?= h($p['nome']) ?></a>
                        <span><?= (int) $p['total'] ?> · <?= decimal(porcentagem($p['total'], $totalNetrunners), 0) ?>%</span>
                    </div>
                    <div class="barra-trilho"><div class="barra-preenchida" style="width: <?= round(porcentagem($p['total'], $maiorProtocolo), 1) ?>%"></div></div>
                </li>
            <?php endforeach; ?>
        </ul>
    </article>

    <article class="painel grafico">
        <h2>Níveis de implante</h2>
        <div class="colunas">
            <?php foreach ($niveis as $nivel => $qtd): ?>
                <a class="coluna" href="netrunners/listar.php?nivel=<?= $nivel ?>" title="Ver netrunners de nível <?= $nivel ?>">
                    <span class="coluna-area">
                        <span class="coluna-valor"><?= $qtd ?></span>
                        <span class="coluna-barra <?= $nivel >= 4 ? 'coluna-quente' : '' ?>" style="height: <?= round(porcentagem($qtd, $maiorNivel) * 0.8, 1) ?>%"></span>
                    </span>
                    <span class="coluna-rotulo">N<?= $nivel ?></span>
                </a>
            <?php endforeach; ?>
        </div>
        <p class="nota">Clique em uma coluna para ver os netrunners daquele nível.</p>
    </article>

    <article class="painel grafico">
        <h2>Faixas etárias</h2>
        <ul class="barras">
            <?php foreach ($faixas as $nome => $f): ?>
                <li>
                    <div class="barra-cabecalho">
                        <span><?= h($nome) ?> anos</span>
                        <span><?= $f ? (int) $f['total'] : 0 ?><?= $f ? ' · implante ' . decimal($f['nivel_medio']) : '' ?></span>
                    </div>
                    <div class="barra-trilho"><div class="barra-preenchida barra-amarela" style="width: <?= $f ? round(porcentagem($f['total'], $maiorFaixa), 1) : 0 ?>%"></div></div>
                </li>
            <?php endforeach; ?>
        </ul>
    </article>

    <article class="painel grafico">
        <h2>Matrículas por mês</h2>
        <div class="colunas colunas-rolagem">
            <?php foreach ($porMes as $m): ?>
                <div class="coluna">
                    <span class="coluna-area">
                        <span class="coluna-valor"><?= (int) $m['total'] ?></span>
                        <span class="coluna-barra" style="height: <?= round(porcentagem($m['total'], $maiorMes) * 0.8, 1) ?>%"></span>
                    </span>
                    <span class="coluna-rotulo"><?= h(rotulo_mes($m['mes'], $nomesMeses)) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
        <p class="nota">Apenas os meses que tiveram matrículas.</p>
    </article>
</section>

<!-- Destaques -->
<section class="bloco">
    <h2>Destaques</h2>
    <div class="destaques">
        <?php if ($veterano): ?>
        <div class="painel destaque">
            <span class="kpi-rotulo">Veterano da rede</span>
            <strong class="codinome"><?= h($veterano['codinome']) ?></strong>
            <span><?= h($veterano['nome']) ?>, <?= (int) $veterano['idade'] ?> anos — <?= h($veterano['protocolo']) ?></span>
        </div>
        <?php endif; ?>
        <?php if ($maisJovem): ?>
        <div class="painel destaque">
            <span class="kpi-rotulo">Mais jovem</span>
            <strong class="codinome"><?= h($maisJovem['codinome']) ?></strong>
            <span><?= h($maisJovem['nome']) ?>, <?= (int) $maisJovem['idade'] ?> anos — <?= h($maisJovem['protocolo']) ?></span>
        </div>
        <?php endif; ?>
        <div class="painel destaque">
            <span class="kpi-rotulo">Elite nível 5</span>
            <?php if ($eliteNivel5): ?>
                <strong class="codinome"><?= h(implode(', ', array_column($eliteNivel5, 'codinome'))) ?></strong>
                <span><?= count($eliteNivel5) ?> <?= count($eliteNivel5) === 1 ? 'netrunner com implante máximo' : 'netrunners com implante máximo' ?></span>
            <?php else: ?>
                <strong class="codinome">Ninguém ainda</strong>
                <span>Nenhum netrunner chegou ao nível 5.</span>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Perfil de cada protocolo -->
<section class="bloco">
    <h2>Perfil dos protocolos</h2>
    <div class="tabela-rolagem">
        <table class="tabela">
            <thead>
                <tr>
                    <th>Protocolo</th>
                    <th>Mentor</th>
                    <th>Ciclos</th>
                    <th>Netrunners</th>
                    <th>Idade média</th>
                    <th>Implante médio</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($porProtocolo as $p): ?>
                <tr>
                    <td><?= h($p['nome']) ?></td>
                    <td><?= h($p['mentor']) ?></td>
                    <td><?= (int) $p['duracao_ciclos'] ?></td>
                    <td><?= (int) $p['total'] ?></td>
                    <td><?= $p['total'] > 0 ? decimal($p['idade_media']) : '—' ?></td>
                    <td><?= $p['total'] > 0 ? decimal($p['nivel_medio']) : '—' ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php endif; ?>

<?php require 'includes/footer.php'; ?>
