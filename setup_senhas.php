<?php
require_once __DIR__.'/config/config.php';
require_once __DIR__.'/config/db.php';
$senhas=['admin@nextstep.com'=>'admin123','ingridy@nextstep.com'=>'teste123'];
$pdo=conectar();
echo '<style>body{font-family:sans-serif;padding:2rem;background:#FFF8E7}</style><h2>Setup NextStep</h2>';
foreach($senhas as $email=>$senha){
  $hash=password_hash($senha,PASSWORD_BCRYPT,['cost'=>12]);
  $pdo->prepare('UPDATE usuarios SET senha_hash=? WHERE email=?')->execute([$hash,$email]);
  echo "<p>✅ <b>$email</b> → senha: <code>$senha</code></p>";
}
echo '<p><a href="'.ROOT_URL.'/login.php">Ir para o login</a></p>';
echo '<p style="color:red"><b>⚠️ Apague este arquivo após usar!</b></p>';
