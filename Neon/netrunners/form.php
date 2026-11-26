<?php
/**
 * Formulário compartilhado entre cadastrar.php e editar.php.
 * Espera: $dados, $erros, $protocolos, $acaoFormulario, $textoBotao
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
        Nome completo
        <input type="text" name="nome" maxlength="100" required value="<?= h($dados['nome']) ?>">
    </label>
    <label>
        Codinome
        <input type="text" name="codinome" maxlength="50" required value="<?= h($dados['codinome']) ?>">
    </label>
    <label>
        E-mail
        <input type="email" name="email" maxlength="150" required value="<?= h($dados['email']) ?>">
    </label>
    <div class="linha">
        <label>
            Idade
            <input type="number" name="idade" min="14" max="120" required value="<?= h($dados['idade']) ?>">
        </label>
        <label>
            Nível de implante
            <select name="nivel_implante" required>
                <option value="">Escolha</option>
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <option value="<?= $i ?>" <?= (string) $dados['nivel_implante'] === (string) $i ? 'selected' : '' ?>>Nível <?= $i ?></option>
                <?php endfor; ?>
            </select>
        </label>
    </div>
    <label>
        Protocolo
        <select name="protocolo_id" required>
            <option value="">Escolha um protocolo</option>
            <?php foreach ($protocolos as $p): ?>
                <option value="<?= $p['id'] ?>" <?= (string) $dados['protocolo_id'] === (string) $p['id'] ? 'selected' : '' ?>><?= h($p['nome']) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <div class="acoes-form">
        <button class="botao botao-primario" type="submit"><?= h($textoBotao) ?></button>
        <a class="botao" href="listar.php">Cancelar</a>
    </div>
</form>
