<?php
require_once '../config/conexao.php';
require_once '../includes/funcoes.php';
require_once '../includes/validacoes.php';

$dados = ['nome' => '', 'descricao' => '', 'duracao_ciclos' => '', 'mentor' => ''];
$erros = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($dados as $campo => $valor) {
        $dados[$campo] = trim($_POST[$campo] ?? '');
    }

    $erros = validar_protocolo($dados);

    if (!$erros) {
        $stmt = $pdo->prepare(
            'INSERT INTO protocolos (nome, descricao, duracao_ciclos, mentor)
             VALUES (:nome, :descricao, :duracao, :mentor)'
        );
        $stmt->bindValue(':nome', $dados['nome']);
        $stmt->bindValue(':descricao', $dados['descricao']);
        $stmt->bindValue(':duracao', (int) $dados['duracao_ciclos'], PDO::PARAM_INT);
        $stmt->bindValue(':mentor', $dados['mentor']);
        $stmt->execute();

        flash_definir('sucesso', 'Protocolo ' . $dados['nome'] . ' criado.');
        redirecionar('listar.php');
    }
}

$acaoFormulario = 'cadastrar.php';
$textoBotao = 'Criar protocolo';

$raiz = '../';
$tituloPagina = 'Criar protocolo';
$paginaAtual = 'protocolos';
require '../includes/header.php';
?>
<section class="cabecalho-secao"><h1>Criar protocolo</h1></section>
<?php require 'form.php'; ?>
<?php require '../includes/footer.php'; ?>
