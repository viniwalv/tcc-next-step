<?php
require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../includes/funcoes.php';
exigirAdmin();
$tabAtiva='usuarios';
$u=usuario(); $pdo=conectar();

// Ativar/desativar usuário
if($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['toggle_ativo'])){
  if(validarCSRF($_POST['csrf']??'')){
    $id=(int)($_POST['id_usuario']??0);
    $val=(int)($_POST['val']??0);
    if($id!==$u['id']) $pdo->prepare('UPDATE usuarios SET ativo=? WHERE id_usuario=?')->execute([$val,$id]);
  }
  header('Location:'.ROOT_URL.'/admin/usuarios.php'); exit;
}

$busca=trim($_GET['busca']??'');
if($busca){
  $st=$pdo->prepare('SELECT u.*,p.nome AS perfil,(SELECT COUNT(*) FROM projetos WHERE id_usuario=u.id_usuario) AS total_proj FROM usuarios u JOIN perfis p ON p.id_perfil=u.id_perfil WHERE u.nome LIKE ? OR u.email LIKE ? ORDER BY u.criado_em DESC');
  $st->execute(["%$busca%","%$busca%"]);
} else {
  $st=$pdo->query('SELECT u.*,p.nome AS perfil,(SELECT COUNT(*) FROM projetos WHERE id_usuario=u.id_usuario) AS total_proj FROM usuarios u JOIN perfis p ON p.id_perfil=u.id_perfil ORDER BY u.criado_em DESC');
}
$usuarios=$st->fetchAll();
?><!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Usuários — Admin NextStep</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= ROOT_URL ?>/assets/css/app.css">
</head><body>
<?php include ROOT.'/includes/topbar.php'; ?>
<div class="content">
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:10px;">
  <h2 style="font-size:20px;font-weight:800;">👥 Usuários (<?= count($usuarios) ?>)</h2>
  <form method="GET" style="display:flex;gap:8px;">
    <input class="form-input" type="text" name="busca" placeholder="Buscar por nome ou email..." value="<?= limpar($busca) ?>" style="width:260px;">
    <button class="btn btn-pr btn-sm" type="submit">Buscar</button>
    <?php if($busca): ?><a href="?" class="btn btn-outline btn-sm">Limpar</a><?php endif; ?>
  </form>
</div>
<div class="card">
<table class="ns-table">
  <thead><tr><th>#</th><th>Nome</th><th>Email</th><th>Idade</th><th>Cidade</th><th>Perfil</th><th>Passaporte</th><th>Projetos</th><th>Ativo</th><th>Cadastro</th><th>Ação</th></tr></thead>
  <tbody>
  <?php foreach($usuarios as $usr): ?>
  <tr>
    <td style="color:#9E9E9E;font-size:12px;"><?= $usr['id_usuario'] ?></td>
    <td style="font-weight:600;"><?= limpar($usr['nome']) ?></td>
    <td style="font-size:13px;color:#9E9E9E;"><?= limpar($usr['email']) ?></td>
    <td><?= $usr['idade'] ?></td>
    <td style="font-size:13px;"><?= limpar($usr['cidade']??'—') ?><?= $usr['estado']?', '.limpar($usr['estado']):'' ?></td>
    <td><span class="badge <?= $usr['perfil']==='admin'?'badge-pr':'badge-muted' ?>"><?= limpar($usr['perfil']) ?></span></td>
    <td style="text-align:center;"><?= $usr['tem_passaporte']?'✅':'❌' ?></td>
    <td style="text-align:center;font-weight:700;"><?= $usr['total_proj'] ?></td>
    <td style="text-align:center;"><?= $usr['ativo']?'<span style="color:#28a745;font-weight:700;">Sim</span>':'<span style="color:#dc2626;font-weight:700;">Não</span>' ?></td>
    <td style="font-size:12px;color:#9E9E9E;"><?= date('d/m/Y',strtotime($usr['criado_em'])) ?></td>
    <td>
      <?php if($usr['id_usuario']!==$u['id']): ?>
      <form method="POST" style="display:inline;">
        <input type="hidden" name="csrf" value="<?= tokenCSRF() ?>">
        <input type="hidden" name="toggle_ativo" value="1">
        <input type="hidden" name="id_usuario" value="<?= $usr['id_usuario'] ?>">
        <input type="hidden" name="val" value="<?= $usr['ativo']?0:1 ?>">
        <button class="btn btn-sm" style="background:<?= $usr['ativo']?'#dc2626':'#28a745' ?>;color:#fff;border:none;" type="submit" onclick="return confirm('<?= $usr['ativo']?'Desativar':'Ativar' ?> este usuário?')">
          <?= $usr['ativo']?'Desativar':'Ativar' ?>
        </button>
      </form>
      <?php else: ?><span style="font-size:12px;color:#9E9E9E;">você</span><?php endif; ?>
    </td>
  </tr>
  <?php endforeach; ?>
  <?php if(empty($usuarios)): ?><tr><td colspan="11" style="text-align:center;color:#9E9E9E;padding:24px;">Nenhum usuário encontrado.</td></tr><?php endif; ?>
  </tbody>
</table>
</div>
</div></body></html>
