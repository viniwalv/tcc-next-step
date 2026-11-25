<?php
require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../includes/funcoes.php';
exigirAdmin();
$tabAtiva='projetos_admin';
$u=usuario(); $pdo=conectar();

// Excluir projeto
if($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['excluir'])){
  if(validarCSRF($_POST['csrf']??'')){
    $pdo->prepare('DELETE FROM projetos WHERE id_projeto=?')->execute([(int)($_POST['id_projeto']??0)]);
  }
  header('Location:'.ROOT_URL.'/admin/projetos.php'); exit;
}

$projetos=$pdo->query('SELECT pr.*,u.nome AS usuario,pa.nome AS pais,pa.bandeira,c.nome AS cidade,o.descricao AS objetivo,(SELECT COUNT(*) FROM checklist WHERE id_projeto=pr.id_projeto) AS total_chk,(SELECT COALESCE(SUM(concluido),0) FROM checklist WHERE id_projeto=pr.id_projeto) AS feitos_chk FROM projetos pr JOIN usuarios u ON u.id_usuario=pr.id_usuario JOIN paises pa ON pa.id_pais=pr.id_pais JOIN cidades c ON c.id_cidade=pr.id_cidade JOIN objetivos o ON o.id_objetivo=pr.id_objetivo ORDER BY pr.criado_em DESC')->fetchAll();
?><!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Projetos — Admin NextStep</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= ROOT_URL ?>/assets/css/app.css">
</head><body>
<?php include ROOT.'/includes/topbar.php'; ?>
<div class="content">
<h2 style="font-size:20px;font-weight:800;margin-bottom:20px;">📋 Todos os Projetos (<?= count($projetos) ?>)</h2>
<div class="card">
<table class="ns-table">
  <thead><tr><th>#</th><th>Projeto</th><th>Usuário</th><th>Destino</th><th>Objetivo</th><th>Duração</th><th>Progresso</th><th>Criado em</th><th>Ação</th></tr></thead>
  <tbody>
  <?php foreach($projetos as $pj):
    $pct=$pj['total_chk']>0?round($pj['feitos_chk']/$pj['total_chk']*100):0;
  ?>
  <tr>
    <td style="color:#9E9E9E;font-size:12px;"><?= $pj['id_projeto'] ?></td>
    <td style="font-weight:600;"><?= limpar($pj['nome']) ?></td>
    <td style="font-size:13px;color:#9E9E9E;"><?= limpar($pj['usuario']) ?></td>
    <td><?= limpar($pj['bandeira']) ?> <?= limpar($pj['pais']) ?> — <?= limpar($pj['cidade']) ?></td>
    <td><span class="badge badge-muted"><?= limpar($pj['objetivo']) ?></span></td>
    <td><?= $pj['duracao_meses'] ?> meses</td>
    <td>
      <div style="display:flex;align-items:center;gap:6px;">
        <div class="prog-bg" style="width:60px;"><div class="prog-bar" style="width:<?= $pct ?>%"></div></div>
        <span style="font-size:12px;font-weight:700;color:#BF9004;"><?= $pct ?>%</span>
      </div>
    </td>
    <td style="font-size:12px;color:#9E9E9E;"><?= date('d/m/Y',strtotime($pj['criado_em'])) ?></td>
    <td>
      <form method="POST" style="display:inline;">
        <input type="hidden" name="csrf" value="<?= tokenCSRF() ?>">
        <input type="hidden" name="excluir" value="1">
        <input type="hidden" name="id_projeto" value="<?= $pj['id_projeto'] ?>">
        <button class="btn btn-sm" style="background:#dc2626;color:#fff;border:none;" type="submit" onclick="return confirm('Excluir este projeto permanentemente?')">Excluir</button>
      </form>
    </td>
  </tr>
  <?php endforeach; ?>
  <?php if(empty($projetos)): ?><tr><td colspan="9" style="text-align:center;color:#9E9E9E;padding:24px;">Nenhum projeto ainda.</td></tr><?php endif; ?>
  </tbody>
</table>
</div>
</div></body></html>
