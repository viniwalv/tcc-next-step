<?php
// ============================================================
//  NextStep — Conexão com o banco de dados
//  Arquivo: config/db.php
// ============================================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'nextstep');
define('DB_USER', 'root');
define('DB_PASS', '');           // padrão XAMPP — sem senha
define('DB_CHARSET', 'utf8mb4');

function conectar(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            die('<p style="font-family:sans-serif;padding:2rem;color:red;">
                <strong>Erro ao conectar no banco.</strong><br>
                Verifique se o MySQL está ligado no XAMPP e se o banco "nextstep" foi criado.<br>
                Detalhe: ' . $e->getMessage() . '
                </p>');
        }
    }
    return $pdo;
}
