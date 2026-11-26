<?php
/**
 * Regras de validação dos dados antes de INSERT e UPDATE.
 * Cada função devolve uma lista de erros; lista vazia = dados válidos.
 */

function validar_protocolo(array $d): array
{
    $erros = [];

    if ($d['nome'] === '') {
        $erros[] = 'Informe o nome do protocolo.';
    } elseif (mb_strlen($d['nome']) > 100) {
        $erros[] = 'O nome do protocolo pode ter no máximo 100 caracteres.';
    }

    if ($d['descricao'] === '') {
        $erros[] = 'Escreva uma descrição para o protocolo.';
    }

    $duracao = filter_var($d['duracao_ciclos'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 20]]);
    if ($d['duracao_ciclos'] === '') {
        $erros[] = 'Informe a duração em ciclos.';
    } elseif ($duracao === false) {
        $erros[] = 'A duração deve ser um número inteiro entre 1 e 20 ciclos.';
    }

    if ($d['mentor'] === '') {
        $erros[] = 'Informe o mentor responsável.';
    } elseif (mb_strlen($d['mentor']) > 100) {
        $erros[] = 'O nome do mentor pode ter no máximo 100 caracteres.';
    }

    return $erros;
}

function validar_netrunner(array $d, PDO $pdo, ?int $idAtual = null): array
{
    $erros = [];

    if ($d['nome'] === '') {
        $erros[] = 'Informe o nome do netrunner.';
    } elseif (mb_strlen($d['nome']) > 100) {
        $erros[] = 'O nome pode ter no máximo 100 caracteres.';
    }

    if ($d['codinome'] === '') {
        $erros[] = 'Informe o codinome usado na rede.';
    } elseif (mb_strlen($d['codinome']) > 50) {
        $erros[] = 'O codinome pode ter no máximo 50 caracteres.';
    }

    if ($d['email'] === '') {
        $erros[] = 'Informe o e-mail.';
    } elseif (!filter_var($d['email'], FILTER_VALIDATE_EMAIL)) {
        $erros[] = 'O e-mail informado não é válido. Exemplo: ghost@neon77.net';
    } else {
        // O e-mail não pode estar em uso por outro netrunner
        $sql = 'SELECT COUNT(*) FROM netrunners WHERE email = :email';
        if ($idAtual !== null) {
            $sql .= ' AND id <> :id';
        }
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':email', $d['email']);
        if ($idAtual !== null) {
            $stmt->bindValue(':id', $idAtual, PDO::PARAM_INT);
        }
        $stmt->execute();
        if ((int) $stmt->fetchColumn() > 0) {
            $erros[] = 'Este e-mail já pertence a outro netrunner.';
        }
    }

    if ($d['idade'] === '') {
        $erros[] = 'Informe a idade.';
    } elseif (filter_var($d['idade'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 14, 'max_range' => 120]]) === false) {
        $erros[] = 'A idade deve ser um número inteiro entre 14 e 120.';
    }

    if (filter_var($d['nivel_implante'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 5]]) === false) {
        $erros[] = 'Escolha um nível de implante entre 1 e 5.';
    }

    $protocoloId = filter_var($d['protocolo_id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($protocoloId === false) {
        $erros[] = 'Escolha um protocolo.';
    } else {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM protocolos WHERE id = :id');
        $stmt->bindValue(':id', $protocoloId, PDO::PARAM_INT);
        $stmt->execute();
        if ((int) $stmt->fetchColumn() === 0) {
            $erros[] = 'O protocolo escolhido não existe.';
        }
    }

    return $erros;
}
