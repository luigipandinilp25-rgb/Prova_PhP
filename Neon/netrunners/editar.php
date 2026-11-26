<?php
require_once '../config/conexao.php';
require_once '../includes/funcoes.php';
require_once '../includes/validacoes.php';

// 1. Identifica o registro pelo ID
$id = id_valido($_GET['id'] ?? null);
if ($id === null) {
    flash_definir('erro', 'Netrunner inválido.');
    redirecionar('listar.php');
}

// 2. Busca os dados no banco
$stmt = $pdo->prepare('SELECT nome, codinome, email, idade, nivel_implante, protocolo_id FROM netrunners WHERE id = :id');
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$registro = $stmt->fetch();

if (!$registro) {
    flash_definir('erro', 'Esse netrunner não existe mais no sistema.');
    redirecionar('listar.php');
}

// 3. Preenche o formulário com os dados atuais
$dados = $registro;
$erros = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($dados as $campo => $valor) {
        $dados[$campo] = trim($_POST[$campo] ?? '');
    }

    // 4. Valida os novos dados
    $erros = validar_netrunner($dados, $pdo, $id);

    // 5. Executa o UPDATE
    if (!$erros) {
        $stmt = $pdo->prepare(
            'UPDATE netrunners
             SET nome = :nome, codinome = :codinome, email = :email,
                 idade = :idade, nivel_implante = :nivel, protocolo_id = :protocolo
             WHERE id = :id'
        );
        $stmt->bindValue(':nome', $dados['nome']);
        $stmt->bindValue(':codinome', $dados['codinome']);
        $stmt->bindValue(':email', $dados['email']);
        $stmt->bindValue(':idade', (int) $dados['idade'], PDO::PARAM_INT);
        $stmt->bindValue(':nivel', (int) $dados['nivel_implante'], PDO::PARAM_INT);
        $stmt->bindValue(':protocolo', (int) $dados['protocolo_id'], PDO::PARAM_INT);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        flash_definir('sucesso', 'Dados de ' . $dados['codinome'] . ' atualizados.');
        redirecionar('listar.php');
    }
}

$stmt = $pdo->prepare('SELECT id, nome FROM protocolos ORDER BY nome');
$stmt->execute();
$protocolos = $stmt->fetchAll();

$acaoFormulario = 'editar.php?id=' . $id;
$textoBotao = 'Salvar alterações';

$raiz = '../';
$tituloPagina = 'Editar netrunner';
$paginaAtual = 'netrunners';
require '../includes/header.php';
?>
<section class="cabecalho-secao">
    <h1>Editar netrunner</h1>
</section>
<?php require 'form.php'; ?>
<?php require '../includes/footer.php'; ?>
