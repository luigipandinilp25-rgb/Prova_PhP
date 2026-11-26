<?php
require_once '../config/conexao.php';
require_once '../includes/funcoes.php';

$id = id_valido($_POST['id'] ?? $_GET['id'] ?? null);
if ($id === null) {
    flash_definir('erro', 'Protocolo inválido.');
    redirecionar('listar.php');
}

$stmt = $pdo->prepare('SELECT p.nome,
                              (SELECT COUNT(*) FROM netrunners n WHERE n.protocolo_id = p.id) AS total
                       FROM protocolos p WHERE p.id = :id');
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$protocolo = $stmt->fetch();

if (!$protocolo) {
    flash_definir('erro', 'Esse protocolo não existe mais no sistema.');
    redirecionar('listar.php');
}

$bloqueado = (int) $protocolo['total'] > 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['confirmar'] ?? '') === 'sim') {
    if ($bloqueado) {
        flash_definir('erro', 'Não é possível excluir um protocolo que ainda tem netrunners matriculados.');
        redirecionar('listar.php');
    }
    $stmt = $pdo->prepare('DELETE FROM protocolos WHERE id = :id');
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    flash_definir('sucesso', 'Protocolo ' . $protocolo['nome'] . ' excluído.');
    redirecionar('listar.php');
}

$raiz = '../';
$tituloPagina = 'Excluir protocolo';
$paginaAtual = 'protocolos';
require '../includes/header.php';
?>
<section class="painel confirmacao">
    <h1>Excluir protocolo?</h1>
    <?php if ($bloqueado): ?>
        <p><strong><?= h($protocolo['nome']) ?></strong> ainda tem <?= (int) $protocolo['total'] ?> netrunner(s) matriculado(s).
           Transfira ou exclua esses netrunners antes de excluir o protocolo.</p>
        <div class="acoes-form">
            <a class="botao botao-primario" href="../netrunners/listar.php?protocolo=<?= $id ?>">Ver netrunners do protocolo</a>
            <a class="botao" href="listar.php">Voltar</a>
        </div>
    <?php else: ?>
        <p>Você vai remover o protocolo <strong><?= h($protocolo['nome']) ?></strong>. Essa ação não pode ser desfeita.</p>
        <form method="post" action="excluir.php">
            <input type="hidden" name="id" value="<?= $id ?>">
            <input type="hidden" name="confirmar" value="sim">
            <div class="acoes-form">
                <button class="botao botao-perigo" type="submit">Excluir protocolo</button>
                <a class="botao" href="listar.php">Cancelar</a>
            </div>
        </form>
    <?php endif; ?>
</section>
<?php require '../includes/footer.php'; ?>
