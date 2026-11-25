<?php
require_once __DIR__.'/config/config.php';
require_once __DIR__.'/includes/funcoes.php';
if(usuarioLogado()) redirecionarPorPerfil();
$erro='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  if(!validarCSRF($_POST['csrf_token']??'')) $erro='Requisição inválida.';
  else{
    $email=trim($_POST['email']??'');
    $senha=$_POST['senha']??'';
    if(!$email||!$senha) $erro='Preencha todos os campos.';
    elseif(!filter_var($email,FILTER_VALIDATE_EMAIL)) $erro='E-mail inválido.';
    else{
      $pdo=conectar();
      $st=$pdo->prepare('SELECT u.*,p.nome AS perfil FROM usuarios u JOIN perfis p ON p.id_perfil=u.id_perfil WHERE u.email=? LIMIT 1');
      $st->execute([$email]);
      $u=$st->fetch();
      if(!$u||!password_verify($senha,$u['senha_hash'])) $erro='E-mail ou senha incorretos.';
      elseif(!$u['ativo']) $erro='Conta desativada.';
      else{
        session_regenerate_id(true);
        $_SESSION['id_usuario']=$u['id_usuario'];
        $_SESSION['nome']=$u['nome'];
        $_SESSION['email']=$u['email'];
        $_SESSION['perfil']=$u['perfil'];
        redirecionarPorPerfil();
      }
    }
  }
}
?><!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Login — NextStep</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:#F5F5F5;min-height:100vh;display:flex;align-items:center;justify-content:center;}
.wrap{display:flex;width:900px;max-width:100%;min-height:520px;border-radius:16px;overflow:hidden;box-shadow:0 8px 40px rgba(0,0,0,.12);}
.left{flex:1;background:linear-gradient(135deg,#1a1208,#3a2c10);display:flex;flex-direction:column;align-items:center;justify-content:center;padding:48px 36px;color:#fff;}
.left-logo{display:flex;align-items:center;gap:10px;font-size:26px;font-weight:800;margin-bottom:24px;}
.left h2{font-size:22px;font-weight:700;margin-bottom:12px;text-align:center;}
.left p{font-size:14px;opacity:.65;text-align:center;line-height:1.7;}
.feat{display:flex;flex-direction:column;gap:10px;margin-top:28px;width:100%;}
.feat-item{display:flex;align-items:center;gap:10px;font-size:13px;opacity:.8;}
.feat-icon{width:32px;height:32px;background:rgba(217,164,4,.2);border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.right{width:420px;background:#fff;padding:48px 40px;display:flex;flex-direction:column;justify-content:center;}
.right h1{font-size:24px;font-weight:800;color:#1a1208;margin-bottom:6px;}
.right p{font-size:14px;color:#9E9E9E;margin-bottom:28px;}
.teste-box{background:#FFF9E6;border-radius:8px;padding:12px 14px;font-size:12px;color:#BF9004;margin-bottom:20px;line-height:1.7;}
.label{display:block;font-size:13px;font-weight:600;color:#BF9004;margin-bottom:6px;}
.inp{width:100%;padding:11px 14px;border:1.5px solid #E8E8E8;border-radius:8px;font-family:'Inter',sans-serif;font-size:14px;color:#1a1208;background:#fafafa;outline:none;margin-bottom:16px;}
.inp:focus{border-color:#D9A404;box-shadow:0 0 0 3px rgba(217,164,4,.1);background:#fff;}
.btn{width:100%;padding:13px;background:#D9A404;color:#fff;border:none;border-radius:8px;font-size:15px;font-weight:700;cursor:pointer;}
.btn:hover{background:#BF9004;}
.erro{background:#fff0f0;border-left:4px solid #dc2626;color:#7f1d1d;padding:10px 14px;border-radius:6px;font-size:13px;margin-bottom:16px;}
.foot{text-align:center;font-size:13px;color:#9E9E9E;margin-top:20px;}
.foot a{color:#D9A404;font-weight:600;}
@media(max-width:700px){.left{display:none;}.right{width:100%;padding:32px 24px;}.wrap{border-radius:0;min-height:100vh;}}
</style></head><body>
<div class="wrap">
  <div class="left">
    <div class="left-logo">
      <svg width="28" height="28" viewBox="0 0 24 24" fill="#D9A404"><path d="M22 16.5v-2l-8.5-5V4c0-1.1-.9-2-1.5-2s-1.5.9-1.5 2v5.5L2 14.5v2l8.5-2.6V19l-2.5 1.8V22l3.5-1 3.5 1v-1.2L12.5 19v-5.1L22 16.5z"/></svg>
      NextStep
    </div>
    <h2>Planeje seu intercâmbio do zero ao embarque</h2>
    <p>Organize documentos, finanças e prazos — tudo em um só lugar.</p>
    <div class="feat">
      <div class="feat-item"><div class="feat-icon">📋</div><span>Checklist personalizado por país</span></div>
      <div class="feat-item"><div class="feat-icon">💰</div><span>Simulador de custos reais</span></div>
      <div class="feat-item"><div class="feat-icon">🗓️</div><span>Timeline com prazos automáticos</span></div>
      <div class="feat-item"><div class="feat-icon">🌍</div><span>Comparador de países e destinos</span></div>
    </div>
  </div>
  <div class="right">
    <h1>Bem-vindo de volta!</h1>
    <p>Entre para continuar seu planejamento</p>
    <div class="teste-box">🔑 <b>Usuário:</b> ingridy@nextstep.com | <b>Senha:</b> teste123<br>🔑 <b>Admin:</b> admin@nextstep.com | <b>Senha:</b> admin123</div>
    <?php if($erro): ?><div class="erro">⚠️ <?= limpar($erro) ?></div><?php endif; ?>
    <form method="POST">
      <input type="hidden" name="csrf_token" value="<?= tokenCSRF() ?>">
      <label class="label">E-mail</label>
      <input class="inp" type="email" name="email" placeholder="seu@email.com" value="<?= limpar($_POST['email']??'') ?>" required>
      <label class="label">Senha</label>
      <input class="inp" type="password" name="senha" placeholder="••••••••" required>
      <button class="btn" type="submit">Entrar</button>
    </form>
    <div class="foot">Não tem conta? <a href="<?= ROOT_URL ?>/cadastro.php">Criar conta</a></div>
  </div>
</div>
<script src="<?= ROOT_URL ?>/assets/js/acessibilidade.js" defer></script>
</body></html>
