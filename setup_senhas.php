<?php
// ============================================================
//  NextStep — Setup: gerar hashes das senhas de teste
//  Execute UMA VEZ: http://localhost/vini/tcc/setup_senhas.php
//  Depois APAGUE este arquivo!
// ============================================================

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';

$senhas = [
    'admin@nextstep.com'   => 'admin123',
    'ingridy@nextstep.com' => 'teste123',
];

$pdo = conectar();
echo '<style>body{font-family:sans-serif;padding:2rem;background:#FDF8EE;}</style>';
echo '<h2>🔐 Setup de Senhas — NextStep</h2><hr><br>';

foreach ($senhas as $email => $senha) {
    $hash = password_hash($senha, PASSWORD_BCRYPT, ['cost' => 12]);
    $stmt = $pdo->prepare('UPDATE usuarios SET senha_hash = ? WHERE email = ?');
    $stmt->execute([$hash, $email]);
    echo "<p>✅ <strong>$email</strong> → senha: <code>$senha</code></p>";
}

echo '<br><hr>';
echo '<p>✅ Pronto! <a href="' . ROOT_URL . '/login.php">Ir para o Login</a></p>';
echo '<p style="color:red;"><strong>⚠️ Apague este arquivo após usar!</strong></p>';
