# Documentação sistêmica — NEON-77

## 1. Identificação

**Nome da universidade:** NEON-77 — Instituto Cibernético de Neo-Curitiba
**Slogan:** "Hackeie o futuro antes que ele hackeie você."
**Tema escolhido:** universidade cyberpunk

## 2. Visão geral do projeto

A NEON-77 é uma universidade fictícia ambientada em Neo-Curitiba, no ano de 2099. Numa cidade dominada por
megacorporações, o instituto forma netrunners (hackers, engenheiros de implantes, pilotos de drones e outros
especialistas) capazes de usar a tecnologia a favor das pessoas.

O website apresenta a instituição (página inicial e página institucional) e oferece um sistema de gestão
acadêmica com dois cadastros completos: os **protocolos** (cursos) e os **netrunners** (alunos).

## 3. Problema que o sistema resolve

A secretaria do instituto precisa controlar quais cursos são oferecidos e quais alunos estão matriculados em
cada um. Sem um sistema, essas informações ficariam espalhadas e seria difícil localizar um aluno, saber quantos
alunos cada curso possui ou evitar cadastros duplicados. O sistema centraliza esses dados no MySQL e permite
cadastrar, consultar, editar e excluir registros com segurança.

## 4. Principais funcionalidades

- Página inicial com nome, slogan, tema, principais protocolos (buscados no banco), diferenciais e atalhos.
- Página institucional com história, missão, visão, valores, mentores, unidades e curiosidades.
- Painel de dados (dashboard) com indicadores, gráficos e leituras automáticas sobre os netrunners e protocolos.
- CRUD completo de protocolos, com pesquisa por nome e paginação.
- CRUD completo de netrunners, com:
  - pesquisa por nome ou codinome usando `LIKE` (não precisa digitar o nome completo);
  - filtro por protocolo e filtro por nível de implante;
  - ordenação por nome A-Z, nome Z-A, mais recentes e mais antigos;
  - paginação com `LIMIT` e `OFFSET`, preservando pesquisa, filtros e ordenação ao trocar de página.
- Validação de todos os dados antes de gravar, com mensagens claras de erro.
- Confirmação antes de excluir qualquer registro.
- Bloqueio da exclusão de um protocolo que ainda possui netrunners matriculados.

## 5. Estrutura dos arquivos

```
neon77/
├── index.php                  Página inicial
├── sobre.php                  Página institucional
├── painel.php                 Painel de dados (dashboard)
├── config/
│   └── conexao.php            Conexão PDO com o MySQL
├── includes/
│   ├── header.php             Cabeçalho e menu (comum a todas as páginas)
│   ├── footer.php             Rodapé
│   ├── funcoes.php            Funções auxiliares (h(), mensagens, paginação, bind, consultas do painel)
│   └── validacoes.php         Regras de validação dos dois cadastros
├── netrunners/
│   ├── listar.php             Consulta com pesquisa, filtros, ordenação e paginação
│   ├── cadastrar.php          INSERT
│   ├── editar.php             SELECT por ID + UPDATE
│   ├── excluir.php            Confirmação + DELETE
│   └── form.php               Formulário usado no cadastro e na edição
├── protocolos/
│   ├── listar.php             Consulta com pesquisa e paginação
│   ├── cadastrar.php          INSERT
│   ├── editar.php             SELECT por ID + UPDATE
│   ├── excluir.php            Confirmação + DELETE
│   └── form.php               Formulário usado no cadastro e na edição
├── css/style.css              Identidade visual cyberpunk
├── img/logo.svg               Logotipo do instituto
├── sql/banco.sql              Criação do banco, tabelas e dados de exemplo
├── docs/documentacao.md       Este documento
└── README.md                  Instruções de instalação e execução
```

## 6. Estrutura do banco de dados

Banco: `neon77` (charset `utf8mb4`). Relacionamento: **um protocolo possui vários netrunners** (1:N).

### Tabela `protocolos`

| Campo | Tipo | Descrição |
|---|---|---|
| id | INT, PK, AUTO_INCREMENT | Identificador do protocolo |
| nome | VARCHAR(100), NOT NULL | Nome do curso |
| descricao | TEXT, NOT NULL | Descrição do curso |
| duracao_ciclos | INT, NOT NULL | Duração em ciclos (1 a 20) |
| mentor | VARCHAR(100), NOT NULL | Professor responsável |
| criado_em | DATETIME | Data de cadastro (automática) |

### Tabela `netrunners`

| Campo | Tipo | Descrição |
|---|---|---|
| id | INT, PK, AUTO_INCREMENT | Identificador do netrunner |
| nome | VARCHAR(100), NOT NULL | Nome completo |
| codinome | VARCHAR(50), NOT NULL | Apelido usado na rede |
| email | VARCHAR(150), NOT NULL, UNIQUE | E-mail (não pode repetir) |
| idade | INT, NOT NULL | Idade (14 a 120) |
| nivel_implante | TINYINT, NOT NULL | Nível do implante neural (1 a 5) |
| protocolo_id | INT, FK → protocolos.id | Curso em que está matriculado |
| criado_em | DATETIME | Data de matrícula (automática) |

A chave estrangeira usa `ON DELETE RESTRICT`, então o próprio banco também impede excluir um protocolo
com alunos.

## 7. CRUDs desenvolvidos

| Operação | Protocolos | Netrunners |
|---|---|---|
| Cadastrar (INSERT) | `protocolos/cadastrar.php` | `netrunners/cadastrar.php` |
| Consultar (SELECT) | `protocolos/listar.php` | `netrunners/listar.php` |
| Editar (UPDATE) | `protocolos/editar.php` | `netrunners/editar.php` |
| Excluir (DELETE) | `protocolos/excluir.php` | `netrunners/excluir.php` |

**Edição:** o sistema recebe o ID pela URL, valida que é um número inteiro positivo, busca o registro com
`SELECT ... WHERE id = :id`, preenche o formulário, valida os novos dados e executa o `UPDATE`.

**Exclusão:** o link de excluir abre uma tela de confirmação (GET). Só depois que o usuário clica no botão de
confirmação, o formulário envia um POST e o `DELETE` é executado.

## 8. Painel de dados (dashboard)

A tela `painel.php` (menu **Painel**) transforma os registros do banco em informação para a secretaria. Todos os
cálculos são feitos pelo próprio MySQL, com funções de agregação, e a página só formata o resultado.

| Elemento | O que mostra | Recurso SQL usado |
|---|---|---|
| Indicadores | Total de netrunners, protocolos com alunos, idade média (mínima e máxima), nível médio de implante e porcentagem de "cromados" (nível 4 ou 5) | `COUNT`, `AVG`, `MIN`, `MAX`, `SUM(CASE ...)` |
| Netrunners por protocolo | Barras com a quantidade e a porcentagem de alunos de cada curso; clicar no nome abre a listagem filtrada | `LEFT JOIN` + `GROUP BY` (inclui cursos sem alunos) |
| Níveis de implante | Colunas de 1 a 5; clicar em uma coluna abre a listagem filtrada por aquele nível | `GROUP BY nivel_implante` |
| Faixas etárias | Quantidade de alunos e nível médio de implante em cada faixa de idade | `CASE` para criar as faixas + `GROUP BY` |
| Matrículas por mês | Colunas com as matrículas de cada mês | `DATE_FORMAT(criado_em, '%Y-%m')` + `GROUP BY` |
| Destaques | Netrunner mais velho, mais jovem e os que têm implante nível 5 | `ORDER BY ... LIMIT 1` e `WHERE` |
| Perfil dos protocolos | Tabela com mentor, duração, alunos, idade média e implante médio de cada curso | `LEFT JOIN` + `AVG` |

**Leituras da rede:** o PHP compara os resultados e escreve frases automáticas, por exemplo qual protocolo é o
mais procurado (tratando empates), qual tem os alunos mais cromados, se a idade influencia o nível de implante,
qual foi o mês de pico de matrículas e quais protocolos ainda não têm alunos. Como as frases são geradas a partir
do banco, elas mudam sozinhas quando alguém cadastra, edita ou exclui um registro.

Os gráficos são feitos apenas com HTML e CSS (a largura ou a altura de cada barra é a porcentagem calculada),
sem bibliotecas externas. Quando ainda não há netrunners, o painel mostra um aviso em vez dos gráficos, e as
porcentagens usam a função `porcentagem()`, que evita divisão por zero.

## 9. Recursos de segurança

- **PDO** com `PDO::ERRMODE_EXCEPTION` em `config/conexao.php`.
- **Prepared statements** em todas as consultas que recebem dados externos, usando `prepare()`,
  `bindValue()` e `execute()`. Nenhum valor do usuário é concatenado no SQL.
- **LIMIT e OFFSET** também são passados por `bindValue()` com `PDO::PARAM_INT`.
- **Lista branca para ORDER BY:** como o nome de coluna não pode ser parâmetro, a ordenação escolhida é
  comparada com uma lista de opções permitidas; qualquer outro valor volta para o padrão.
- **htmlspecialchars()** (função `h()`) em todo dado exibido novamente no HTML, prevenindo XSS.
- **Validação de IDs** com `filter_var(..., FILTER_VALIDATE_INT)` antes de qualquer consulta.
- **Exclusão apenas por POST** com confirmação, evitando exclusões acidentais por link.
- **Painel:** os textos montados com dados do banco passam por `h()` antes de serem exibidos.

## 10. Validações

| Campo | Regra |
|---|---|
| Campos de texto | Obrigatórios, com limite de caracteres |
| E-mail | `FILTER_VALIDATE_EMAIL` e não pode estar em uso por outro netrunner |
| Idade | Número inteiro entre 14 e 120 |
| Nível de implante | Número inteiro entre 1 e 5 |
| Protocolo do netrunner | Precisa existir na tabela `protocolos` |
| Duração do protocolo | Número inteiro entre 1 e 20 |

Se houver qualquer erro, nada é gravado: o formulário volta preenchido com o que o usuário digitou e uma
lista de mensagens explica o que corrigir.

## 11. Fluxo geral da aplicação

```
[Usuário preenche o formulário]
        │  POST
        ▼
[PHP recebe os dados]  →  trim() em cada campo de $_POST
        │
        ▼
[Validação]  →  includes/validacoes.php
        │
   ┌────┴─────┐
 erros?      sem erros
   │            │
   ▼            ▼
[Formulário  [prepare() + bindValue() + execute()]
 volta com          │
 mensagens]         ▼
              [Dados gravados no MySQL]
                    │
                    ▼
              [Mensagem de sucesso na sessão + redirecionamento para a listagem]
```

1. **O usuário envia os dados:** preenche o formulário (`form.php`) e clica em salvar; o navegador envia um POST.
2. **O PHP processa:** `cadastrar.php` ou `editar.php` lê cada campo de `$_POST` e remove espaços extras.
3. **Os dados são validados:** `validar_netrunner()` ou `validar_protocolo()` devolve a lista de erros.
4. **Os dados são armazenados:** sem erros, o comando `INSERT` ou `UPDATE` é preparado, os valores são
   vinculados com `bindValue()` e o `execute()` grava no MySQL. Em seguida o usuário é redirecionado para a
   listagem, onde aparece a mensagem de sucesso.

Na consulta, o fluxo é parecido: os parâmetros de pesquisa, filtro, ordenação e página chegam por GET, viram
condições `WHERE` com parâmetros nomeados, e o resultado é exibido com `htmlspecialchars()`.
