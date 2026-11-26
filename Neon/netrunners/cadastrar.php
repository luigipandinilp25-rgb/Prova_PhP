<?php
require_once '../config/conexao.php';
require_once '../includes/funcoes.php';
require_once '../includes/validacoes.php';

$dados = ['nome' => '', 'codinome' => '', 'email' => '', 'idade' => '', 'nivel_implante' => '', 'protocolo_id' => ''];
$erros = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Recebe os dados do formulário
    foreach ($dados as $campo => $valor) {
        $dados[$campo] = trim($_POST[$campo] ?? '');
    }

    // 2. Valida
    $erros = validar_netrunner($dados, $pdo);

    // 3. Grava no MySQL somente se não houver erros
    if (!$erros) {
        $stmt = $pdo->prepare(
            'INSERT INTO netrunners (nome, codinome, email, idade, nivel_implante, protocolo_id)
             VALUES (:nome, :codinome, :email, :idade, :nivel, :protocolo)'
        );
        $stmt->bindValue(':nome', $dados['nome']);
        $stmt->bindValue(':codinome', $dados['codinome']);
        $stmt->bindValue(':email', $dados['email']);
        $stmt->bindValue(':idade', (int) $dados['idade'], PDO::PARAM_INT);
        $stmt->bindValue(':nivel', (int) $dados['nivel_implante'], PDO::PARAM_INT);
        $stmt->bindValue(':protocolo', (int) $dados['protocolo_id'], PDO::PARAM_INT);
        $stmt->execute();

        flash_definir('sucesso', 'Netrunner ' . $dados['codinome'] . ' matriculado.');
        redirecionar('listar.php');
    }
}

$stmt = $pdo->prepare('SELECT id, nome FROM protocolos ORDER BY nome');
$stmt->execute();
$protocolos = $stmt->fetchAll();

$acaoFormulario = 'cadastrar.php';
$textoBotao = 'Matricular';

$raiz = '../';
$tituloPagina = 'Matricular netrunner';
$paginaAtual = 'netrunners';
require '../includes/header.php';
?>
<section class="cabecalho-secao">
    <h1>Matricular netrunner</h1>
</section>
<?php if (!$protocolos): ?>
    <div class="painel vazio"><p>Cadastre pelo menos um protocolo antes de matricular netrunners. <a href="../protocolos/cadastrar.php">Criar protocolo</a></p></div>
<?php else: ?>
    <?php require 'form.php'; ?>
<?php endif; ?>
<?php require '../includes/footer.php'; ?>
