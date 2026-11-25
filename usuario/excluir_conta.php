<?php
require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../includes/funcoes.php';
exigirUsuario();
if($_SERVER['REQUEST_METHOD']!=='POST'||!validarCSRF($_POST['csrf']??'')||isPerfil('admin')){
  header('Location:'.ROOT_URL.'/usuario/perfil.php'); exit;
}
$u=usuario(); $pdo=conectar();
$pdo->prepare('DELETE FROM usuarios WHERE id_usuario=?')->execute([$u['id']]);
$_SESSION=[]; session_destroy();
header('Location:'.ROOT_URL.'/login.php'); exit;
