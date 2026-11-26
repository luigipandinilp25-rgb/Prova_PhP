<?php
require_once 'config/conexao.php';
require_once 'includes/funcoes.php';

// Principais cursos: os protocolos com mais netrunners matriculados
$stmt = $pdo->prepare(
    'SELECT p.id, p.nome, p.descricao, p.mentor,
            (SELECT COUNT(*) FROM netrunners n WHERE n.protocolo_id = p.id) AS total
     FROM protocolos p
     ORDER BY total DESC, p.nome ASC
     LIMIT 3'
);
$stmt->execute();
$destaques = $stmt->fetchAll();

$raiz = '';
$tituloPagina = 'Início';
$paginaAtual = 'inicio';
require 'includes/header.php';
?>

<section class="hero">
    <p class="hero-local">Neo-Curitiba, 2099</p>
    <h1 class="glitch" data-text="NEON-77">NEON-77</h1>
    <p class="hero-nome">Instituto Cibernético de Neo-Curitiba</p>
    <p class="hero-slogan">Hackeie o futuro antes que ele hackeie você.</p>
    <div class="hero-acoes">
        <a class="botao botao-primario" href="netrunners/cadastrar.php">Matricular netrunner</a>
        <a class="botao" href="sobre.php">Conhecer o instituto</a>
    </div>
</section>

<section class="bloco tema">
    <h2>O tema</h2>
    <p>Uma universidade cyberpunk. Em 2099, as megacorporações controlam a cidade e a rede. A NEON-77 é a única
       instituição independente que ainda forma netrunners livres: gente capaz de entrar na rede, construir
       implantes e enfrentar as corporações com conhecimento em vez de armas.</p>
</section>

<section class="bloco">
    <div class="titulo-bloco">
        <h2>Principais protocolos</h2>
        <a href="protocolos/listar.php">Ver todos os protocolos</a>
    </div>
    <?php if ($destaques): ?>
    <div class="grade-protocolos">
        <?php foreach ($destaques as $p): ?>
            <article class="chip">
                <h3><?= h($p['nome']) ?></h3>
                <p><?= h($p['descricao']) ?></p>
                <p class="chip-rodape">Mentor: <?= h($p['mentor']) ?> — <?= (int) $p['total'] ?> netrunners</p>
            </article>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
        <p>Nenhum protocolo cadastrado ainda. <a href="protocolos/cadastrar.php">Criar o primeiro protocolo</a></p>
    <?php endif; ?>
</section>

<section class="bloco">
    <h2>Por que a NEON-77</h2>
    <ul class="diferenciais">
        <li><strong>Laboratórios de imersão total.</strong> Os netrunners treinam dentro de simulações da rede corporativa, sem risco de queimar o córtex.</li>
        <li><strong>Implantes inclusos na matrícula.</strong> Todo ingressante recebe um implante neural nível 1, atualizado conforme avança no protocolo.</li>
        <li><strong>Mentores que já invadiram corporações.</strong> O corpo docente reúne ex-agentes, engenheiros renegados e uma IA com doutorado.</li>
        <li><strong>Rede independente.</strong> O campus roda em servidores próprios, fora do alcance das megacorporações.</li>
    </ul>
</section>

<section class="bloco atalhos">
    <a href="sobre.php"><strong>O instituto</strong><span>História, missão, mentores e unidades.</span></a>
    <a href="painel.php"><strong>Painel</strong><span>Indicadores e gráficos da rede em tempo real.</span></a>
    <a href="netrunners/listar.php"><strong>Netrunners</strong><span>Consultar, matricular, editar e excluir alunos.</span></a>
    <a href="protocolos/listar.php"><strong>Protocolos</strong><span>Gerenciar os cursos oferecidos.</span></a>
</section>

<?php require 'includes/footer.php'; ?>
