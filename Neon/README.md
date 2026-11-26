# NEON-77 — Instituto Cibernético de Neo-Curitiba

> Hackeie o futuro antes que ele hackeie você.

Projeto Final **My Crazy University**: website de uma universidade fictícia com tema **cyberpunk**,
desenvolvido em PHP e MySQL.

## Autor

Projeto individual desenvolvido por Luigi Pandini Campos.

## Tema da universidade

Neo-Curitiba, 2099. Megacorporações controlam a cidade e a rede. A NEON-77 é a última universidade
independente, formando netrunners: hackers éticos, engenheiros de implantes, designers de hologramas,
pilotos de drones e advogados contra as corporações.

## Descrição do sistema

- Página inicial (`index.php`) e página institucional (`sobre.php`).
- **Painel de dados** (`painel.php`) com indicadores, gráficos e leituras automáticas calculados no MySQL.
- CRUD de **Protocolos** (cursos) com pesquisa e paginação.
- CRUD de **Netrunners** (alunos) com pesquisa por nome/codinome (`LIKE`), filtros por protocolo e nível de
  implante, ordenação e paginação (`LIMIT`/`OFFSET`) que preserva os filtros.
- Validação de dados, prepared statements e `htmlspecialchars()` em toda saída.

A documentação completa está em [`docs/documentacao.md`](docs/documentacao.md).

## Tecnologias utilizadas

- PHP 8 (PDO)
- MySQL 8 ou MariaDB
- HTML5 e CSS3
- Google Fonts (Orbitron e Share Tech Mono)

## Como executar

### 1. Pré-requisitos

Instale o [XAMPP](https://www.apachefriends.org/) (ou WAMP/Laragon) com Apache e MySQL.

### 2. Baixar o projeto

Clone o repositório dentro da pasta pública do servidor (no XAMPP, `C:\xampp\htdocs`):

```bash
cd C:\xampp\htdocs
git clone https://github.com/SEU-USUARIO/neon77.git
```

### 3. Criar o banco de dados

1. Inicie o Apache e o MySQL no painel do XAMPP.
2. Acesse `http://localhost/phpmyadmin`.
3. Clique em **Importar**, escolha o arquivo `sql/banco.sql` e clique em **Executar**.

O script cria o banco `neon77`, as tabelas `protocolos` e `netrunners` e insere dados de exemplo.

Pelo terminal, também funciona:

```bash
mysql -u root -p < sql/banco.sql
```

### 4. Configurar a conexão

Abra `config/conexao.php` e ajuste os dados, se necessário:

```php
$host    = 'localhost';
$banco   = 'neon77';
$usuario = 'root';
$senha   = '';   // no XAMPP a senha padrão do root é vazia
```

### 5. Acessar o sistema

Abra no navegador:

```
http://localhost/neon77/
```

Sem XAMPP, com PHP instalado, também é possível rodar o servidor embutido na pasta do projeto:

```bash
php -S localhost:8000
```

e acessar `http://localhost:8000`.
