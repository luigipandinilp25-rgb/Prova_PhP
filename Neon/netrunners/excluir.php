<?php
require_once '../config/conexao.php';
require_once '../includes/funcoes.php';

// 1. Identifica o registro (GET para mostrar a confirmação, POST para excluir)
$id = id_valido($_POST['id'] ?? $_GET['id'] ?? null);
if ($id === null) {
    flash_definir('erro', 'Netrunner inválido.');
    redirecionar('listar.php');
}

$stmt = $pdo->prepare('SELECT n.nome, n.codinome, p.nome AS protocolo_nome
                       FROM netrunners n INNER JOIN protocolos p ON p.id = n.protocolo_id
                       WHERE n.id = :id');
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$netrunner = $stmt->fetch();

if (!$netrunner) {
    flash_definir('erro', 'Esse netrunner não existe mais no sistema.');
    redirecionar('listar.php');
}

// 3. Executa o DELETE somente após a confirmação
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['confirmar'] ?? '') === 'sim') {
    $stmt = $pdo->prepare('DELETE FROM netrunners WHERE id = :id');
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    flash_definir('sucesso', 'Netrunner ' . $netrunner['codinome'] . ' desconectado do sistema.');
    redirecionar('listar.php');
}

$raiz = '../';
$tituloPagina = 'Excluir netrunner';
$paginaAtual = 'netrunners';
require '../includes/header.php';
?>
<section class="painel confirmacao">
    <h1>Excluir netrunner?</h1>
    <!-- 2. Solicita confirmação -->
    <p>Você vai remover <strong><?= h($netrunner['nome']) ?></strong> (<?= h($netrunner['codinome']) ?>),
       do protocolo <?= h($netrunner['protocolo_nome']) ?>. Essa ação não pode ser desfeita.</p>
    <form method="post" action="excluir.php">
        <input type="hidden" name="id" value="<?= $id ?>">
        <input type="hidden" name="confirmar" value="sim">
        <div class="acoes-form">
            <button class="botao botao-perigo" type="submit">Excluir netrunner</button>
            <a class="botao" href="listar.php">Cancelar</a>
        </div>
    </form>
</section>
<?php require '../includes/footer.php'; ?>
