<?php
require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../includes/funcoes.php';
exigirUsuario();
$tabAtiva='paises';
$u=usuario(); $pdo=conectar();

$paises=$pdo->query('SELECT p.*,(SELECT COUNT(*) FROM cidades c WHERE c.id_pais=p.id_pais) AS total_cidades,(SELECT MIN(custo_moradia+custo_alimentacao+custo_transporte+custo_lazer+custo_outros) FROM cidades c WHERE c.id_pais=p.id_pais) AS custo_min,(SELECT MAX(custo_moradia+custo_alimentacao+custo_transporte+custo_lazer+custo_outros) FROM cidades c WHERE c.id_pais=p.id_pais) AS custo_max FROM paises p ORDER BY p.nome')->fetchAll();

$filtro=$_GET['filtro']??'todos';
$comparar=array_filter(explode(',',$_GET['comparar']??''),fn($v)=>$v!=='');
?><!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Países — NextStep</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= ROOT_URL ?>/assets/css/app.css">
<style>
.pais-card{background:#fff;border:1.5px solid #E8E8E8;border-radius:12px;padding:20px;cursor:pointer;transition:all .18s;}
.pais-card:hover{border-color:#D9A404;box-shadow:0 4px 20px rgba(217,164,4,.15);}
.pais-card.selecionado{border-color:#D9A404;background:#FFFBF0;}
.pais-flag{font-size:36px;margin-bottom:8px;display:block;}
.pais-nome{font-size:16px;font-weight:800;color:#1a1208;margin-bottom:4px;}
.pais-idioma{font-size:12px;color:#9E9E9E;margin-bottom:12px;}
.pais-info{display:flex;flex-direction:column;gap:6px;}
.pais-row{display:flex;justify-content:space-between;font-size:13px;}
.pais-row span:first-child{color:#6B6B6B;}
.pais-row span:last-child{font-weight:600;color:#1a1208;}
.visto-sim{background:#FFF0F0;color:#dc2626;padding:2px 8px;border-radius:99px;font-size:11px;font-weight:700;}
.visto-nao{background:#d4edda;color:#155724;padding:2px 8px;border-radius:99px;font-size:11px;font-weight:700;}
.filtros{display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap;}
.filtro-btn{padding:7px 16px;border-radius:99px;font-size:13px;font-weight:600;border:1.5px solid #E8E8E8;background:#fff;cursor:pointer;transition:all .18s;text-decoration:none;color:#6B6B6B;}
.filtro-btn.active,.filtro-btn:hover{border-color:#D9A404;background:#FFF9E6;color:#BF9004;}
.comparar-bar{position:sticky;bottom:0;background:#1a1208;color:#fff;padding:14px 24px;border-radius:12px 12px 0 0;display:flex;align-items:center;justify-content:space-between;margin-top:24px;}
.comp-table th{background:#FFF9E6;}
</style></head><body>
<?php include ROOT.'/includes/topbar.php'; ?>
<div class="content">
<h2 style="font-size:20px;font-weight:800;margin-bottom:4px;">🌍 Países & Destinos</h2>
<p style="font-size:13px;color:#9E9E9E;margin-bottom:20px;">Explore os destinos disponíveis. Selecione até 3 para comparar lado a lado.</p>

<div class="filtros">
  <a href="?filtro=todos" class="filtro-btn <?= $filtro==='todos'?'active':'' ?>">Todos</a>
  <a href="?filtro=sem_visto" class="filtro-btn <?= $filtro==='sem_visto'?'active':'' ?>">Sem visto</a>
  <a href="?filtro=com_visto" class="filtro-btn <?= $filtro==='com_visto'?'active':'' ?>">Requer visto</a>
  <a href="?filtro=economico" class="filtro-btn <?= $filtro==='economico'?'active':'' ?>">Mais econômicos</a>
</div>

<?php
$lista=$paises;
if($filtro==='sem_visto') $lista=array_filter($lista,fn($p)=>!$p['requer_visto']);
elseif($filtro==='com_visto') $lista=array_filter($lista,fn($p)=>$p['requer_visto']);
elseif($filtro==='economico') { usort($lista,fn($a,$b)=>$a['custo_min']<=>$b['custo_min']); }
?>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:14px;margin-bottom:24px;">
<?php foreach($lista as $p): ?>
<div class="pais-card <?= in_array($p['id_pais'],$comparar)?'selecionado':'' ?>" onclick="toggleComparar(<?= $p['id_pais'] ?>,this)">
  <span class="pais-flag"><?= limpar($p['bandeira']) ?></span>
  <div class="pais-nome"><?= limpar($p['nome']) ?></div>
  <div class="pais-idioma"><?= limpar($p['idioma']) ?> · <?= limpar($p['moeda']) ?></div>
  <div class="pais-info">
    <div class="pais-row">
      <span>Custo mensal</span>
      <span>R$ <?= number_format($p['custo_min'],0,',','.') ?><?= $p['custo_max']!=$p['custo_min']?'–'.number_format($p['custo_max'],0,',','.'):'' ?></span>
    </div>
    <div class="pais-row">
      <span>Cidades</span>
      <span><?= $p['total_cidades'] ?> disponíveis</span>
    </div>
    <div class="pais-row">
      <span>Visto</span>
      <span><?= $p['requer_visto']?'<span class="visto-sim">Necessário</span>':'<span class="visto-nao">Não precisa</span>' ?></span>
    </div>
  </div>
</div>
<?php endforeach; ?>
</div>

<!-- TABELA DE COMPARAÇÃO -->
<?php if(count($comparar)>=2):
  $placeholders=implode(',',array_fill(0,count($comparar),'?'));
  $st=$pdo->prepare("SELECT p.*,(SELECT MIN(custo_moradia+custo_alimentacao+custo_transporte+custo_lazer+custo_outros) FROM cidades c WHERE c.id_pais=p.id_pais) AS custo_min,(SELECT MAX(passagem_brl) FROM cidades c WHERE c.id_pais=p.id_pais) AS pass_max FROM paises p WHERE p.id_pais IN ($placeholders)");
  $st->execute($comparar); $comp=$st->fetchAll();
?>
<div class="card">
  <h3 class="card-titulo">Comparação de Países</h3>
  <div style="overflow-x:auto;">
  <table class="ns-table">
    <thead><tr><th>Critério</th><?php foreach($comp as $c): ?><th style="text-align:center"><?= limpar($c['bandeira']) ?> <?= limpar($c['nome']) ?></th><?php endforeach; ?></tr></thead>
    <tbody>
      <tr><td>Moeda</td><?php foreach($comp as $c): ?><td style="text-align:center;font-weight:600"><?= limpar($c['moeda']) ?></td><?php endforeach; ?></tr>
      <tr><td>Idioma</td><?php foreach($comp as $c): ?><td style="text-align:center"><?= limpar($c['idioma']) ?></td><?php endforeach; ?></tr>
      <tr><td>Custo mensal mín.</td><?php foreach($comp as $c): ?><td style="text-align:center;font-weight:700;color:#BF9004">R$ <?= number_format($c['custo_min'],0,',','.') ?></td><?php endforeach; ?></tr>
      <tr><td>Passagem estimada</td><?php foreach($comp as $c): ?><td style="text-align:center">R$ <?= number_format($c['pass_max']*2,0,',','.') ?></td><?php endforeach; ?></tr>
      <tr><td>Visto necessário</td><?php foreach($comp as $c): ?><td style="text-align:center"><?= $c['requer_visto']?'<span class="visto-sim">Sim</span>':'<span class="visto-nao">Não</span>' ?></td><?php endforeach; ?></tr>
    </tbody>
  </table>
  </div>
</div>
<?php endif; ?>

<?php if(count($comparar)>0): ?>
<div class="comparar-bar">
  <span><?= count($comparar) ?> país(es) selecionado(s) para comparar</span>
  <a href="?comparar=<?= implode(',',$comparar) ?>" class="btn btn-pr btn-sm">Ver comparação</a>
</div>
<?php endif; ?>
</div>
<script>
let sel=[<?= implode(',',array_map('intval',$comparar)) ?>];
function toggleComparar(id,el){
  const idx=sel.indexOf(id);
  if(idx>=0){sel.splice(idx,1);el.classList.remove('selecionado');}
  else if(sel.length<3){sel.push(id);el.classList.add('selecionado');}
  else{alert('Selecione no máximo 3 países para comparar.');return;}
  const url=new URL(window.location);
  if(sel.length>0) url.searchParams.set('comparar',sel.join(','));
  else url.searchParams.delete('comparar');
  window.history.replaceState({},'',url);
  // Atualiza barra
  document.querySelector('.comparar-bar')&&document.querySelector('.comparar-bar').remove();
  if(sel.length>0){
    const bar=document.createElement('div');
    bar.className='comparar-bar';
    bar.innerHTML=`<span>${sel.length} país(es) selecionado(s)</span><a href="?comparar=${sel.join(',')}" class="btn btn-pr btn-sm">Ver comparação</a>`;
    document.querySelector('.content').appendChild(bar);
  }
}
</script>
</body></html>
