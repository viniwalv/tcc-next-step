<?php
require_once __DIR__.'/config/config.php';
require_once __DIR__.'/includes/funcoes.php';
if(usuarioLogado()) redirecionarPorPerfil();
$erro=''; $idadeRecusada=false;
if($_SERVER['REQUEST_METHOD']==='POST'){
  if(!validarCSRF($_POST['csrf_token']??'')) $erro='Requisição inválida.';
  else{
    $nome=trim($_POST['nome']??'');
    $email=trim($_POST['email']??'');
    $senha=$_POST['senha']??'';
    $idade=(int)($_POST['idade']??0);
    if(!$nome||!$email||!$senha||!$idade) $erro='Preencha todos os campos.';
    elseif(strlen($nome)>100||strlen($email)>150) $erro='Nome ou e-mail muito longo.';
    elseif(!filter_var($email,FILTER_VALIDATE_EMAIL)) $erro='E-mail inválido.';
    elseif($idade<14||$idade>60) $idadeRecusada=true;
    elseif(strlen($senha)<6) $erro='Senha deve ter pelo menos 6 caracteres.';
    else{
      $pdo=conectar();
      $chk=$pdo->prepare('SELECT id_usuario FROM usuarios WHERE email=? LIMIT 1');
      $chk->execute([$email]);
      if($chk->fetch()) $erro='Este e-mail já está cadastrado.';
      else{
        $hash=password_hash($senha,PASSWORD_BCRYPT,['cost'=>12]);
        $ins=$pdo->prepare('INSERT INTO usuarios(nome,email,senha_hash,idade,id_perfil)VALUES(?,?,?,?,2)');
        $ins->execute([$nome,$email,$hash,$idade]);
        $id=$pdo->lastInsertId();
        session_regenerate_id(true);
        $_SESSION['id_usuario']=$id; $_SESSION['nome']=$nome;
        $_SESSION['email']=$email; $_SESSION['perfil']='usuario';
        setMsg('sucesso','Conta criada com sucesso! Bem-vindo ao NextStep 🎉');
        header('Location:'.ROOT_URL.'/usuario/dashboard.php'); exit;
      }
    }
  }
}
?><!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Cadastro — NextStep</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:#F5F5F5;min-height:100vh;display:flex;align-items:center;justify-content:center;}
.card{background:#fff;border-radius:16px;padding:44px 40px;width:440px;max-width:100%;box-shadow:0 8px 40px rgba(0,0,0,.1);}
.logo{display:flex;align-items:center;gap:8px;font-size:20px;font-weight:800;color:#1a1208;margin-bottom:24px;}
h1{font-size:22px;font-weight:800;color:#1a1208;margin-bottom:6px;}
.sub{font-size:14px;color:#9E9E9E;margin-bottom:24px;}
.label{display:block;font-size:13px;font-weight:600;color:#BF9004;margin-bottom:6px;}
.inp{width:100%;padding:11px 14px;border:1.5px solid #E8E8E8;border-radius:8px;font-family:'Inter',sans-serif;font-size:14px;color:#1a1208;background:#fafafa;outline:none;margin-bottom:16px;}
.inp:focus{border-color:#D9A404;background:#fff;}
.policy{background:#FFF9E6;border-radius:8px;padding:10px 14px;font-size:12px;color:#BF9004;margin-bottom:18px;}
.btn{width:100%;padding:13px;background:#D9A404;color:#fff;border:none;border-radius:8px;font-size:15px;font-weight:700;cursor:pointer;}
.btn:hover{background:#BF9004;}
.erro{background:#fff0f0;border-left:4px solid #dc2626;color:#7f1d1d;padding:10px 14px;border-radius:6px;font-size:13px;margin-bottom:16px;}
.foot{text-align:center;font-size:13px;color:#9E9E9E;margin-top:20px;}
.foot a{color:#D9A404;font-weight:600;}
.recusa-icon{font-size:48px;margin-bottom:16px;display:block;text-align:center;}
</style></head><body>
<div class="card">
  <div class="logo">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="#D9A404"><path d="M22 16.5v-2l-8.5-5V4c0-1.1-.9-2-1.5-2s-1.5.9-1.5 2v5.5L2 14.5v2l8.5-2.6V19l-2.5 1.8V22l3.5-1 3.5 1v-1.2L12.5 19v-5.1L22 16.5z"/></svg>
    NextStep
  </div>
  <?php if($idadeRecusada): ?>
    <span class="recusa-icon">🚫</span>
    <h1>Acesso Restrito</h1>
    <p class="sub">O NextStep é destinado a usuários entre <b>14 e 60 anos</b>.</p>
    <div class="policy">Nossos recursos foram desenvolvidos para estudantes e profissionais nessa faixa etária.</div>
    <a href="<?= ROOT_URL ?>/cadastro.php" class="btn" style="display:block;text-align:center;">Tentar novamente</a>
  <?php else: ?>
    <h1>Criar sua conta</h1>
    <p class="sub">Comece a planejar seu intercâmbio gratuitamente</p>
    <?php if($erro): ?><div class="erro">⚠️ <?= limpar($erro) ?></div><?php endif; ?>
    <form method="POST">
      <input type="hidden" name="csrf_token" value="<?= tokenCSRF() ?>">
      <label class="label">Nome completo</label>
      <input class="inp" type="text" name="nome" placeholder="João Silva" value="<?= limpar($_POST['nome']??'') ?>" required>
      <label class="label">E-mail</label>
      <input class="inp" type="email" name="email" placeholder="seu@email.com" value="<?= limpar($_POST['email']??'') ?>" required>
      <label class="label">Senha</label>
      <input class="inp" type="password" name="senha" placeholder="Mínimo 6 caracteres" required>
      <label class="label">Idade</label>
      <input class="inp" type="number" name="idade" placeholder="18" min="1" max="120" value="<?= limpar($_POST['idade']??'') ?>" required>
      <div class="policy">🛡 Aceitamos usuários entre 14 e 60 anos.</div>
      <button class="btn" type="submit">Criar conta</button>
    </form>
    <div class="foot">Já tem conta? <a href="<?= ROOT_URL ?>/login.php">Fazer login</a></div>
  <?php endif; ?>
</div>
<script src="<?= ROOT_URL ?>/assets/js/acessibilidade.js" defer></script>
</body></html>
