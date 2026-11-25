<?php
require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../includes/funcoes.php';
exigirUsuario();
$tabAtiva='perfil';
$u=usuario(); $pdo=conectar();
$st=$pdo->prepare('SELECT * FROM usuarios WHERE id_usuario=?');
$st->execute([$u['id']]); $dados=$st->fetch();
$msg=''; $erro='';

if($_SERVER['REQUEST_METHOD']==='POST'){
  if(!validarCSRF($_POST['csrf_token']??'')) $erro='Token inválido.';
  else{
    $nome=trim($_POST['nome']??'');
    $cidade=trim($_POST['cidade']??'');
    $estado=strtoupper(trim($_POST['estado']??''));
    $tem_pass=(int)($_POST['tem_passaporte']??0);
    $senha_nova=trim($_POST['senha_nova']??'');
    $senha_atual=$_POST['senha_atual']??'';
    if(!$nome) $erro='Nome é obrigatório.';
    elseif($estado&&strlen($estado)!==2) $erro='Estado deve ter 2 letras (ex: SP).';
    else{
      $pdo->prepare('UPDATE usuarios SET nome=?,cidade=?,estado=?,tem_passaporte=? WHERE id_usuario=?')
          ->execute([$nome,$cidade?:null,$estado?:null,$tem_pass,$u['id']]);
      $_SESSION['nome']=$nome;
      if($senha_nova){
        if(!password_verify($senha_atual,$dados['senha_hash'])) $erro='Senha atual incorreta.';
        elseif(strlen($senha_nova)<6) $erro='Nova senha deve ter pelo menos 6 caracteres.';
        else{
          $pdo->prepare('UPDATE usuarios SET senha_hash=? WHERE id_usuario=?')
              ->execute([password_hash($senha_nova,PASSWORD_BCRYPT,['cost'=>12]),$u['id']]);
          $msg='Perfil e senha atualizados com sucesso!';
        }
      }
      if(!$erro) { $msg=$msg?:' Perfil atualizado com sucesso!';
        $st=$pdo->prepare('SELECT * FROM usuarios WHERE id_usuario=?');
        $st->execute([$u['id']]); $dados=$st->fetch(); }
    }
  }
}

$totalProj=$pdo->prepare('SELECT COUNT(*) FROM projetos WHERE id_usuario=?');
$totalProj->execute([$u['id']]); $totalProj=$totalProj->fetchColumn();
$totalChk=$pdo->prepare('SELECT COUNT(*) FROM checklist c JOIN projetos p ON p.id_projeto=c.id_projeto WHERE p.id_usuario=? AND c.concluido=1');
$totalChk->execute([$u['id']]); $totalChk=$totalChk->fetchColumn();
?><!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Perfil — NextStep</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= ROOT_URL ?>/assets/css/app.css">
</head><body>
<?php include ROOT.'/includes/topbar.php'; ?>
<div class="content" style="max-width:700px;">
<a href="<?= ROOT_URL ?>/usuario/dashboard.php" class="voltar">← Dashboard</a>
<h2 style="font-size:20px;font-weight:800;margin-bottom:20px;">👤 Meu Perfil</h2>

<!-- STATS RÁPIDOS -->
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:22px;">
  <div class="card" style="text-align:center;">
    <div style="font-size:24px;font-weight:800;color:#BF9004"><?= $totalProj ?></div>
    <div style="font-size:12px;color:#9E9E9E">Projetos</div>
  </div>
  <div class="card" style="text-align:center;">
    <div style="font-size:24px;font-weight:800;color:#28a745"><?= $totalChk ?></div>
    <div style="font-size:12px;color:#9E9E9E">Tarefas feitas</div>
  </div>
  <div class="card" style="text-align:center;">
    <div style="font-size:24px;font-weight:800;color:#1a1208"><?= $dados['tem_passaporte']?'✅':'❌' ?></div>
    <div style="font-size:12px;color:#9E9E9E">Passaporte</div>
  </div>
</div>

<?php if($msg): ?><div class="alerta alerta-ok"><?= limpar(trim($msg)) ?></div><?php endif; ?>
<?php if($erro): ?><div class="alerta alerta-erro"><?= limpar($erro) ?></div><?php endif; ?>

<div class="card">
  <h3 class="card-titulo">Informações Pessoais</h3>
  <form method="POST">
    <input type="hidden" name="csrf_token" value="<?= tokenCSRF() ?>">
    <div class="form-grid">
      <div class="form-grupo">
        <label class="form-label">Nome completo</label>
        <input class="form-input" type="text" name="nome" value="<?= limpar($dados['nome']) ?>" required>
      </div>
      <div class="form-grupo">
        <label class="form-label">E-mail</label>
        <input class="form-input" type="email" value="<?= limpar($dados['email']) ?>" disabled style="opacity:.6">
      </div>
    </div>
    <div class="form-grid">
      <div class="form-grupo">
        <label class="form-label">Cidade</label>
        <input class="form-input" type="text" name="cidade" placeholder="Ex: Campinas" value="<?= limpar($dados['cidade']??'') ?>">
      </div>
      <div class="form-grupo">
        <label class="form-label">Estado (UF)</label>
        <input class="form-input" type="text" name="estado" placeholder="Ex: SP" maxlength="2" value="<?= limpar($dados['estado']??'') ?>">
      </div>
    </div>
    <div class="form-grupo">
      <label class="form-label">Você tem passaporte?</label>
      <div style="display:flex;gap:10px;">
        <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:14px;">
          <input type="radio" name="tem_passaporte" value="1" <?= $dados['tem_passaporte']?'checked':'' ?>> Sim, tenho passaporte
        </label>
        <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:14px;">
          <input type="radio" name="tem_passaporte" value="0" <?= !$dados['tem_passaporte']?'checked':'' ?>> Não, preciso tirar
        </label>
      </div>
    </div>

    <hr style="border:none;border-top:1px solid #F5F5F5;margin:20px 0;">
    <h4 style="font-size:14px;font-weight:700;color:#6B6B6B;margin-bottom:14px;">Alterar Senha (opcional)</h4>
    <div class="form-grid">
      <div class="form-grupo">
        <label class="form-label">Senha atual</label>
        <input class="form-input" type="password" name="senha_atual" placeholder="••••••••">
      </div>
      <div class="form-grupo">
        <label class="form-label">Nova senha</label>
        <input class="form-input" type="password" name="senha_nova" placeholder="Mínimo 6 caracteres">
      </div>
    </div>
    <button type="submit" class="btn btn-pr" style="width:100%;justify-content:center;padding:13px;">Salvar alterações</button>
  </form>
</div>

<div class="card" style="margin-top:14px;border-color:#FFF0F0;">
  <h3 style="font-size:15px;font-weight:700;color:#dc2626;margin-bottom:8px;">⚠️ Zona de Perigo</h3>
  <p style="font-size:13px;color:#6B6B6B;margin-bottom:14px;">Ao excluir sua conta, todos os seus projetos, checklists e dados serão permanentemente removidos.</p>
  <form method="POST" action="<?= ROOT_URL ?>/usuario/excluir_conta.php" onsubmit="return confirm('Tem certeza? Esta ação não pode ser desfeita.')"><input type="hidden" name="csrf" value="<?= tokenCSRF() ?>"><button class="btn" style="background:#dc2626;color:#fff;font-size:13px;padding:8px 16px;" type="submit">Excluir minha conta</button></form>
</div>
</div>
</body></html>
