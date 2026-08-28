<?php
// ============================================================
//  NextStep — Configuração de Conexão com o Banco de Dados
// ============================================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'nextstep');
define('DB_USER', 'root');
define('DB_PASS', '');          // padrão XAMPP — altere se tiver senha
define('DB_CHARSET', 'utf8mb4');

function conectar(): PDO {
    static $pdo = null;

    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST
             . ';dbname='    . DB_NAME
             . ';charset='   . DB_CHARSET;

        $opcoes = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $opcoes);
        } catch (PDOException $e) {
            // Em produção, não exibir detalhes do erro
            die('<div style="font-family:sans-serif;padding:2rem;color:#c0392b;">
                    <strong>Erro de conexão com o banco de dados.</strong><br>
                    Verifique se o MySQL está rodando no XAMPP e se o banco "nextstep" foi criado.
                 </div>');
        }
    }

    return $pdo;
}