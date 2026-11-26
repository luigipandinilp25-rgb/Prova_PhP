<?php
require_once '../config/conexao.php';
require_once '../includes/funcoes.php';
require_once '../includes/validacoes.php';

$id = id_valido($_GET['id'] ?? null);
if ($id === null) {
    flash_definir('erro', 'Protocolo inválido.');
    redirecionar('listar.php');
}

$stmt = $pdo->prepare('SELECT nome, descricao, duracao_ciclos, mentor FROM protocolos WHERE id = :id');
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$registro = $stmt->fetch();

if (!$registro) {
    flash_definir('erro', 'Esse protocolo não existe mais no sistema.');
    redirecionar('listar.php');
}

$dados = $registro;
$erros = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($dados as $campo => $valor) {
        $dados[$campo] = trim($_POST[$campo] ?? '');
    }

    $erros = validar_protocolo($dados);

    if (!$erros) {
        $stmt = $pdo->prepare(
            'UPDATE protocolos
             SET nome = :nome, descricao = :descricao, duracao_ciclos = :duracao, mentor = :mentor
             WHERE id = :id'
        );
        $stmt->bindValue(':nome', $dados['nome']);
        $stmt->bindValue(':descricao', $dados['descricao']);
        $stmt->bindValue(':duracao', (int) $dados['duracao_ciclos'], PDO::PARAM_INT);
        $stmt->bindValue(':mentor', $dados['mentor']);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        flash_definir('sucesso', 'Protocolo ' . $dados['nome'] . ' atualizado.');
        redirecionar('listar.php');
    }
}

$acaoFormulario = 'editar.php?id=' . $id;
$textoBotao = 'Salvar alterações';

$raiz = '../';
$tituloPagina = 'Editar protocolo';
$paginaAtual = 'protocolos';
require '../includes/header.php';
?>
<section class="cabecalho-secao"><h1>Editar protocolo</h1></section>
<?php require 'form.php'; ?>
<?php require '../includes/footer.php'; ?>
