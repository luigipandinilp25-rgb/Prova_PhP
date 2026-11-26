<?php
/**
 * Formulário compartilhado entre cadastrar.php e editar.php.
 * Espera: $dados, $erros, $acaoFormulario, $textoBotao
 */
?>
<?php if ($erros): ?>
    <div class="alerta alerta-erro" role="alert">
        <strong>Corrija os campos abaixo antes de salvar:</strong>
        <ul>
            <?php foreach ($erros as $erro): ?>
                <li><?= h($erro) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form class="painel formulario" method="post" action="<?= h($acaoFormulario) ?>" novalidate>
    <label>
        Nome do protocolo
        <input type="text" name="nome" maxlength="100" required value="<?= h($dados['nome']) ?>">
    </label>
    <label>
        Descrição
        <textarea name="descricao" rows="4" required><?= h($dados['descricao']) ?></textarea>
    </label>
    <div class="linha">
        <label>
            Duração (ciclos)
            <input type="number" name="duracao_ciclos" min="1" max="20" required value="<?= h($dados['duracao_ciclos']) ?>">
        </label>
        <label>
            Mentor responsável
            <input type="text" name="mentor" maxlength="100" required value="<?= h($dados['mentor']) ?>">
        </label>
    </div>
    <div class="acoes-form">
        <button class="botao botao-primario" type="submit"><?= h($textoBotao) ?></button>
        <a class="botao" href="listar.php">Cancelar</a>
    </div>
</form>
