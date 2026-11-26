<?php
/**
 * Conexão com o banco de dados MySQL usando PDO.
 * Ajuste as variáveis abaixo de acordo com o seu ambiente (XAMPP, WAMP, etc.).
 */
$host    = 'localhost';
$banco   = 'neon77';
$usuario = 'root';
$senha   = '';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$banco;charset=utf8mb4",
        $usuario,
        $senha,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die('<h1 style="font-family:monospace;color:#ff2bd6;background:#0a0a12;padding:2rem">
        Falha ao conectar no banco de dados. Verifique config/conexao.php e se o banco neon77 foi criado.</h1>');
}
