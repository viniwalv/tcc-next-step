<?php
require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../includes/funcoes.php';
exigirUsuario();
$tabAtiva='dashboard';
$u=usuario(); $pdo=conectar();

$dados=$pdo->prepare('SELECT * FROM usuarios WHERE id_usuario=?');
$dados->execute([$u['id']]); $dados=$dados->fetch();

$projetos=$pdo->prepare('SELECT p.*,pa.nome AS pais_nome,pa.bandeira,o.descricao AS objetivo,c.nome AS cidade_nome,pf.valor_guardado,pf.meta_total,pf.custo_mensal FROM projetos p JOIN paises pa ON pa.id_pais=p.id_pais JOIN cidades c ON c.id_cidade=p.id_cidade JOIN objetivos o ON o.id_objetivo=p.id_objetivo LEFT JOIN plano_financeiro pf ON pf.id_projeto=p.id_projeto WHERE p.id_usuario=? ORDER BY p.criado_em DESC');
$projetos->execute([$u['id']]); $projetos=$projetos->fetchAll();
$totalProj=count($projetos);

$ck=$pdo->prepare('SELECT COUNT(*) AS total,COALESCE(SUM(c.concluido),0) AS feitos FROM checklist c JOIN projetos p ON p.id_projeto=c.id_projeto WHERE p.id_usuario=?');
$ck->execute([$u['id']]); $ck=$ck->fetch();

$eco=$pdo->prepare('SELECT COALESCE(SUM(pf.valor_guardado),0) AS total FROM plano_financeiro pf JOIN projetos p ON p.id_projeto=pf.id_projeto WHERE p.id_usuario=?');
$eco->execute([$u['id']]); $eco=$eco->fetch();

$prox=$pdo->prepare('SELECT COUNT(*) AS total FROM timeline t JOIN projetos p ON p.id_projeto=t.id_projeto WHERE p.id_usuario=? AND t.concluido=0 AND t.data_prevista>=CURDATE()');
$prox->execute([$u['id']]); $prox=$prox->fetch();

$stmtProg=$pdo->prepare('SELECT COUNT(*) AS total,COALESCE(SUM(concluido),0) AS feitos FROM checklist WHERE id_projeto=?');
$msg=getMsg();
?><!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Dashboard — NextStep</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= ROOT_URL ?>/assets/css/app.css">
</head><body>
<?php include ROOT.'/includes/topbar.php'; ?>
<div class="content">
<?php if($msg): ?><div class="alerta alerta-<?= $msg['tipo'] ?>"><?= limpar($msg['texto']) ?></div><?php endif; ?>

<div style="margin-bottom:20px;">
  <h2 style="font-size:20px;font-weight:800;color:#1a1208;">Olá, <?= limpar(explode(' ',$u['nome'])[0]) ?>! ✈️</h2>
  <p style="font-size:14px;color:#9E9E9E;">Continue planejando seu intercâmbio</p>
</div>

<!-- STATS -->
<div class="stats">
  <div class="stat">
    <div><div class="stat-label">Projetos</div><div class="stat-valor"><?= $totalProj ?></div></div>
    <div class="stat-icon"><svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg></div>
  </div>
  <div class="stat">
    <div><div class="stat-label">Tarefas Concluídas</div><div class="stat-valor"><?= (int)$ck['feitos'] ?></div></div>
    <div class="stat-icon"><svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
  </div>
  <div class="stat">
    <div><div class="stat-label">Economia Total</div><div class="stat-valor" style="font-size:18px;">R$ <?= number_format($eco['total'],0,',','.') ?></div></div>
    <div class="stat-icon"><svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
  </div>
  <div class="stat">
    <div><div class="stat-label">Próximos Eventos</div><div class="stat-valor"><?= (int)$prox['total'] ?></div></div>
    <div class="stat-icon"><svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></div>
  </div>
</div>

<!-- PROJETOS -->
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
  <h3 style="font-size:18px;font-weight:800;">Meus Projetos</h3>
  <a href="<?= ROOT_URL ?>/usuario/projetos.php?novo=1" class="btn btn-pr btn-sm">+ Novo Projeto</a>
</div>

<?php if(empty($projetos)): ?>
<div class="card empty">
  <div class="empty-icon">🌍</div>
  <h3 style="margin-bottom:10px;">Nenhum projeto ainda</h3>
  <p style="font-size:14px;color:#9E9E9E;margin-bottom:20px;">Crie seu primeiro projeto e comece a planejar!</p>
  <a href="<?= ROOT_URL ?>/usuario/projetos.php?novo=1" class="btn btn-pr">Criar projeto</a>
</div>
<?php else: ?>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px;">
  <?php foreach($projetos as $proj):
    $stmtProg->execute([$proj['id_projeto']]);
    $pg=$stmtProg->fetch();
    $pct=$pg['total']>0?round($pg['feitos']/$pg['total']*100):0;
    $dur=$proj['duracao_meses']>=12&&$proj['duracao_meses']%12===0
      ?($proj['duracao_meses']/12).' '.($proj['duracao_meses']/12===1?'ano':'anos')
      :$proj['duracao_meses'].' meses';
  ?>
  <a href="<?= ROOT_URL ?>/usuario/checklist.php?id=<?= $proj['id_projeto'] ?>" style="text-decoration:none;">
  <div class="card" style="cursor:pointer;transition:border-color .2s,box-shadow .2s;border:1.5px solid #E8E8E8;" onmouseover="this.style.borderColor='#D9A404';this.style.boxShadow='0 4px 20px rgba(217,164,4,.15)'" onmouseout="this.style.borderColor='#E8E8E8';this.style.boxShadow=''">
    <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:4px;">
      <div style="font-size:11px;font-weight:700;color:#BF9004;text-transform:uppercase;letter-spacing:.6px;"><?= limpar($proj['bandeira']) ?> <?= limpar($proj['pais_nome']) ?></div>
      <span class="badge badge-pr"><?= limpar($proj['objetivo']) ?></span>
    </div>
    <div style="font-size:17px;font-weight:800;color:#1a1208;margin-bottom:4px;"><?= limpar($proj['nome']) ?></div>
    <div style="font-size:13px;color:#9E9E9E;margin-bottom:14px;"><?= limpar($proj['cidade_nome']) ?></div>
    <div style="display:flex;justify-content:space-between;font-size:12px;color:#6B6B6B;margin-bottom:5px;">
      <span>Progresso</span><span><?= $pct ?>%</span>
    </div>
    <div class="prog-bg" style="margin-bottom:12px;"><div class="prog-bar" style="width:<?= $pct ?>%"></div></div>
    <div style="display:flex;justify-content:space-between;font-size:13px;">
      <span style="color:#6B6B6B;">Duração</span><span style="font-weight:700;color:#BF9004;"><?= $dur ?></span>
    </div>
    <div style="display:flex;justify-content:space-between;font-size:13px;margin-top:6px;">
      <span style="color:#6B6B6B;">Meta</span><span style="font-weight:700;color:#BF9004;">R$ <?= number_format($proj['meta_total']??0,0,',','.') ?></span>
    </div>
  </div></a>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- PERFIL INCOMPLETO -->
<?php if(!$dados['cidade']||!$dados['estado']): ?>
<div class="muted-box" style="margin-top:20px;text-align:center;padding:28px;">
  <div style="font-size:36px;margin-bottom:10px;">📍</div>
  <h4 style="font-size:15px;font-weight:800;margin-bottom:6px;">Complete seu perfil</h4>
  <p style="font-size:13px;color:#6B6B6B;margin-bottom:14px;">Adicione sua localização para ver serviços próximos de você</p>
  <a href="<?= ROOT_URL ?>/usuario/perfil.php" class="btn btn-pr btn-sm">Completar perfil</a>
</div>
<?php endif; ?>
</div>
</body></html>
