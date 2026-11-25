<?php
require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../includes/funcoes.php';
exigirAdmin();
$tabAtiva='dashboard';
$u=usuario(); $pdo=conectar();
$totalUsu=$pdo->query('SELECT COUNT(*) FROM usuarios WHERE id_perfil=2')->fetchColumn();
$totalProj=$pdo->query('SELECT COUNT(*) FROM projetos')->fetchColumn();
$totalChk=$pdo->query('SELECT COUNT(*) FROM checklist WHERE concluido=1')->fetchColumn();
$totalPaises=$pdo->query('SELECT COUNT(DISTINCT id_pais) FROM projetos')->fetchColumn();
$ultUsu=$pdo->query('SELECT u.*,p.nome AS perfil FROM usuarios u JOIN perfis p ON p.id_perfil=u.id_perfil ORDER BY u.criado_em DESC LIMIT 8')->fetchAll();
$ultProj=$pdo->query('SELECT pr.nome,pr.duracao_meses,pr.criado_em,u.nome AS usuario,pa.nome AS pais,pa.bandeira,c.nome AS cidade FROM projetos pr JOIN usuarios u ON u.id_usuario=pr.id_usuario JOIN paises pa ON pa.id_pais=pr.id_pais JOIN cidades c ON c.id_cidade=pr.id_cidade ORDER BY pr.criado_em DESC LIMIT 6')->fetchAll();
?><!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Admin — NextStep</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= ROOT_URL ?>/assets/css/app.css">
</head><body>
<?php include ROOT.'/includes/topbar.php'; ?>
<div class="content">
<h2 style="font-size:20px;font-weight:800;margin-bottom:20px;">⚙️ Painel Administrativo</h2>
<div class="stats">
  <div class="stat"><div><div class="stat-label">Usuários</div><div class="stat-valor"><?= $totalUsu ?></div></div><div class="stat-icon"><svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-5-3.87M9 20H4v-2a4 4 0 015-3.87M12 12a4 4 0 100-8 4 4 0 000 8z"/></svg></div></div>
  <div class="stat"><div><div class="stat-label">Projetos</div><div class="stat-valor"><?= $totalProj ?></div></div><div class="stat-icon"><svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></div></div>
  <div class="stat"><div><div class="stat-label">Tarefas Concluídas</div><div class="stat-valor"><?= $totalChk ?></div></div><div class="stat-icon"><svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div></div>
  <div class="stat"><div><div class="stat-label">Países Escolhidos</div><div class="stat-valor"><?= $totalPaises ?></div></div><div class="stat-icon"><svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z"/></svg></div></div>
</div>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;">
  <div class="card">
    <h3 class="card-titulo">Últimos Usuários</h3>
    <table class="ns-table">
      <thead><tr><th>Nome</th><th>Email</th><th>Perfil</th><th>Cadastro</th></tr></thead>
      <tbody>
      <?php foreach($ultUsu as $usr): ?>
      <tr>
        <td style="font-weight:600"><?= limpar($usr['nome']) ?></td>
        <td style="font-size:13px;color:#9E9E9E"><?= limpar($usr['email']) ?></td>
        <td><span class="badge <?= $usr['perfil']==='admin'?'badge-pr':'badge-muted' ?>"><?= limpar($usr['perfil']) ?></span></td>
        <td style="font-size:12px;color:#9E9E9E"><?= date('d/m/Y',strtotime($usr['criado_em'])) ?></td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <div style="margin-top:12px;"><a href="<?= ROOT_URL ?>/admin/usuarios.php" class="btn btn-outline btn-sm">Ver todos</a></div>
  </div>
  <div class="card">
    <h3 class="card-titulo">Projetos Recentes</h3>
    <table class="ns-table">
      <thead><tr><th>Projeto</th><th>Usuário</th><th>Destino</th></tr></thead>
      <tbody>
      <?php foreach($ultProj as $pj): ?>
      <tr>
        <td style="font-weight:600"><?= limpar($pj['nome']) ?></td>
        <td style="font-size:13px;color:#9E9E9E"><?= limpar($pj['usuario']) ?></td>
        <td><?= limpar($pj['bandeira']) ?> <?= limpar($pj['pais']) ?></td>
      </tr>
      <?php endforeach; ?>
      <?php if(empty($ultProj)): ?><tr><td colspan="3" style="text-align:center;color:#9E9E9E;padding:20px;">Nenhum projeto ainda.</td></tr><?php endif; ?>
      </tbody>
    </table>
    <div style="margin-top:12px;"><a href="<?= ROOT_URL ?>/admin/projetos.php" class="btn btn-outline btn-sm">Ver todos</a></div>
  </div>
</div>
</div></body></html>
