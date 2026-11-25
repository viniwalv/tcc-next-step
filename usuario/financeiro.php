<?php
require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../includes/funcoes.php';
exigirUsuario();
$tabAtiva='financeiro';
$u=usuario(); $pdo=conectar();

$id_proj=(int)($_GET['id']??0);
if(!$id_proj){
  $st=$pdo->prepare('SELECT id_projeto FROM projetos WHERE id_usuario=? ORDER BY criado_em DESC LIMIT 1');
  $st->execute([$u['id']]); $r=$st->fetch(); if($r) $id_proj=$r['id_projeto'];
}
$projeto=null;
if($id_proj){
  $st=$pdo->prepare('SELECT p.*,pa.nome AS pais_nome,pa.bandeira,pa.moeda,c.nome AS cidade_nome,o.descricao AS objetivo FROM projetos p JOIN paises pa ON pa.id_pais=p.id_pais JOIN cidades c ON c.id_cidade=p.id_cidade JOIN objetivos o ON o.id_objetivo=p.id_objetivo WHERE p.id_projeto=? AND p.id_usuario=?');
  $st->execute([$id_proj,$u['id']]); $projeto=$st->fetch();
}
if(!$projeto){ header('Location:'.ROOT_URL.'/usuario/projetos.php'); exit; }

// Plano financeiro
$pf=$pdo->prepare('SELECT pf.*,c.custo_moradia,c.custo_alimentacao,c.custo_transporte,c.custo_lazer,c.custo_outros FROM plano_financeiro pf JOIN projetos p ON p.id_projeto=pf.id_projeto JOIN cidades c ON c.id_cidade=p.id_cidade WHERE pf.id_projeto=?');
$pf->execute([$id_proj]); $pf=$pf->fetch();

$msg=''; $erro='';

// Atualizar valor guardado
if($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['atualizar'])){
  if(!validarCSRF($_POST['csrf_token']??'')) $erro='Token inválido.';
  else{
    $vg=floatval(str_replace(',','.',$_POST['valor_guardado']??0));
    if($vg<0||$vg>99999999) $erro='Informe um valor entre 0 e 99.999.999.';
    else{
      $pdo->prepare('UPDATE plano_financeiro SET valor_guardado=? WHERE id_projeto=?')->execute([$vg,$id_proj]);
      $msg='Valor atualizado com sucesso!';
      $pf['valor_guardado']=$vg;
    }
  }
}

$meta=$pf['meta_total']??0;
$guardado=$pf['valor_guardado']??0;
$falta=max(0,$meta-$guardado);
$pct=$meta>0?min(100,round($guardado/$meta*100)):0;
$cm=$pf['custo_mensal']??0;
$pass=$pf['passagem_brl']??0;
$dur=$projeto['duracao_meses'];
$meses_guardando=($cm>0&&$falta>0)?ceil($falta/$cm):0;

$todos=$pdo->prepare('SELECT id_projeto,nome FROM projetos WHERE id_usuario=? ORDER BY criado_em DESC');
$todos->execute([$u['id']]); $todos=$todos->fetchAll();
?><!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Financeiro — NextStep</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= ROOT_URL ?>/assets/css/app.css">
<style>
.fin-stat{background:#fff;border:1px solid #E8E8E8;border-radius:12px;padding:20px;text-align:center;}
.fin-stat-label{font-size:12px;color:#9E9E9E;font-weight:600;text-transform:uppercase;letter-spacing:.6px;margin-bottom:8px;}
.fin-stat-val{font-size:24px;font-weight:800;color:#1a1208;}
.fin-stat-val.gold{color:#BF9004;}
.fin-stat-val.verde{color:#28a745;}
.breakdown-row{display:flex;align-items:center;justify-content:space-between;padding:12px 0;border-bottom:1px solid #F5F5F5;font-size:14px;}
.breakdown-row:last-child{border-bottom:none;}
.breakdown-label{display:flex;align-items:center;gap:8px;color:#6B6B6B;}
.breakdown-dot{width:10px;height:10px;border-radius:50%;flex-shrink:0;}
.breakdown-val{font-weight:700;color:#1a1208;}
.breakdown-pct{font-size:12px;color:#9E9E9E;margin-left:4px;}
.proj-sel{padding:8px 14px;border:1.5px solid rgba(217,164,4,.4);border-radius:8px;font-family:'Inter',sans-serif;font-size:14px;color:#1a1208;background:#FFF9E6;outline:none;cursor:pointer;}
</style></head><body>
<?php include ROOT.'/includes/topbar.php'; ?>
<div class="content">
<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:18px;">
  <a href="<?= ROOT_URL ?>/usuario/dashboard.php" class="voltar" style="margin-bottom:0;">← Dashboard</a>
  <?php if(count($todos)>1): ?>
  <select class="proj-sel" onchange="location='?id='+this.value">
    <?php foreach($todos as $t): ?><option value="<?= $t['id_projeto'] ?>" <?= $t['id_projeto']==$id_proj?'selected':'' ?>><?= limpar($t['nome']) ?></option><?php endforeach; ?>
  </select>
  <?php endif; ?>
</div>

<h2 style="font-size:20px;font-weight:800;margin-bottom:4px;">💰 Planejamento Financeiro</h2>
<p style="font-size:13px;color:#9E9E9E;margin-bottom:22px;"><?= limpar($projeto['bandeira']) ?> <?= limpar($projeto['nome']) ?> · <?= limpar($projeto['cidade_nome']) ?> · <?= $dur ?> meses</p>

<?php if($msg): ?><div class="alerta alerta-ok"><?= limpar($msg) ?></div><?php endif; ?>
<?php if($erro): ?><div class="alerta alerta-erro"><?= limpar($erro) ?></div><?php endif; ?>

<!-- STATS FINANCEIROS -->
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:22px;">
  <div class="fin-stat">
    <div class="fin-stat-label">Meta Total</div>
    <div class="fin-stat-val">R$ <?= number_format($meta,0,',','.') ?></div>
    <div style="font-size:12px;color:#9E9E9E;margin-top:4px;">custo + passagem</div>
  </div>
  <div class="fin-stat">
    <div class="fin-stat-label">Já Guardado</div>
    <div class="fin-stat-val verde">R$ <?= number_format($guardado,0,',','.') ?></div>
    <div style="font-size:12px;color:#9E9E9E;margin-top:4px;"><?= $pct ?>% da meta</div>
  </div>
  <div class="fin-stat">
    <div class="fin-stat-label">Ainda Falta</div>
    <div class="fin-stat-val gold">R$ <?= number_format($falta,0,',','.') ?></div>
    <div style="font-size:12px;color:#9E9E9E;margin-top:4px;"><?= $meses_guardando>0?"~$meses_guardando meses poupando R$ ".number_format($cm,0,',','.'):'meta atingida! 🎉' ?></div>
  </div>
</div>

<!-- BARRA DE PROGRESSO -->
<div class="card" style="margin-bottom:18px;">
  <div style="display:flex;justify-content:space-between;font-size:14px;margin-bottom:8px;">
    <span style="color:#6B6B6B;font-weight:600;">Progresso da Meta</span>
    <span style="font-weight:800;color:#BF9004;"><?= $pct ?>%</span>
  </div>
  <div class="prog-bg" style="height:14px;"><div class="prog-bar" style="width:<?= $pct ?>%"></div></div>
  <div style="display:flex;justify-content:space-between;font-size:12px;color:#9E9E9E;margin-top:6px;">
    <span>R$ <?= number_format($guardado,0,',','.') ?> guardado</span>
    <span>Meta: R$ <?= number_format($meta,0,',','.') ?></span>
  </div>
</div>

<!-- ATUALIZAR VALOR -->
<div class="card" style="margin-bottom:18px;">
  <h3 class="card-titulo">Atualizar valor guardado</h3>
  <form method="POST" style="display:flex;gap:10px;align-items:flex-end;">
    <input type="hidden" name="csrf_token" value="<?= tokenCSRF() ?>">
    <input type="hidden" name="atualizar" value="1">
    <div style="flex:1;">
      <label class="form-label">Quanto você já guardou? (R$)</label>
      <input class="form-input" type="number" name="valor_guardado" min="0" step="0.01" value="<?= number_format($guardado,2,'.','') ?>" required>
    </div>
    <button type="submit" class="btn btn-pr">Salvar</button>
  </form>
</div>

<!-- DETALHAMENTO DE CUSTOS -->
<?php if($pf): ?>
<div class="card" style="margin-bottom:18px;">
  <h3 class="card-titulo">Detalhamento de Custos Mensais</h3>
  <?php
  $cols=['custo_moradia'=>['Moradia','#3B82F6'],'custo_alimentacao'=>['Alimentação','#10B981'],'custo_transporte'=>['Transporte','#F59E0B'],'custo_lazer'=>['Lazer','#8B5CF6'],'custo_outros'=>['Outros','#6B7280']];
  foreach($cols as $key=>[$label,$cor]):
    $v=$pf[$key]??0;
    $pp=$cm>0?round($v/$cm*100):0;
  ?>
  <div class="breakdown-row">
    <div class="breakdown-label"><div class="breakdown-dot" style="background:<?= $cor ?>"></div><?= $label ?></div>
    <div><span class="breakdown-val">R$ <?= number_format($v,0,',','.') ?>/mês</span><span class="breakdown-pct"><?= $pp ?>%</span></div>
  </div>
  <?php endforeach; ?>
  <div class="breakdown-row" style="border-top:2px solid #E8E8E8;margin-top:4px;">
    <div style="font-weight:800;color:#1a1208;">Total mensal</div>
    <div style="font-weight:800;color:#BF9004;">R$ <?= number_format($cm,0,',','.') ?>/mês</div>
  </div>
</div>

<!-- RESUMO FINAL -->
<div class="muted-box">
  <h4 style="font-size:14px;font-weight:800;color:#BF9004;margin-bottom:12px;">📊 Resumo do Investimento</h4>
  <div style="display:flex;flex-direction:column;gap:8px;font-size:14px;">
    <div style="display:flex;justify-content:space-between;"><span style="color:#6B6B6B;">Custo mensal em <?= limpar($projeto['cidade_nome']) ?></span><span style="font-weight:700;">R$ <?= number_format($cm,0,',','.') ?></span></div>
    <div style="display:flex;justify-content:space-between;"><span style="color:#6B6B6B;">Duração (<?= $dur ?> meses)</span><span style="font-weight:700;">R$ <?= number_format($cm*$dur,0,',','.') ?></span></div>
    <div style="display:flex;justify-content:space-between;"><span style="color:#6B6B6B;">Passagem ida + volta</span><span style="font-weight:700;">R$ <?= number_format($pass*2,0,',','.') ?></span></div>
    <div style="display:flex;justify-content:space-between;border-top:1px solid rgba(217,164,4,.3);padding-top:8px;margin-top:4px;">
      <span style="font-weight:800;color:#1a1208;">Meta total recomendada</span>
      <span style="font-weight:800;color:#BF9004;">R$ <?= number_format($meta,0,',','.') ?></span>
    </div>
  </div>
</div>
<?php endif; ?>
</div>
</body></html>
