<?php
require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../includes/funcoes.php';
exigirUsuario();
$tabAtiva='checklist';
$u=usuario(); $pdo=conectar();

// Pega o primeiro projeto do usuário se não especificado
$id_proj=(int)($_GET['id']??0);
if(!$id_proj){
  $st=$pdo->prepare('SELECT id_projeto FROM projetos WHERE id_usuario=? ORDER BY criado_em DESC LIMIT 1');
  $st->execute([$u['id']]); $r=$st->fetch();
  if($r) $id_proj=$r['id_projeto'];
}

$projeto=null;
if($id_proj){
  $st=$pdo->prepare('SELECT p.*,pa.nome AS pais_nome,pa.bandeira,o.descricao AS objetivo,c.nome AS cidade_nome FROM projetos p JOIN paises pa ON pa.id_pais=p.id_pais JOIN cidades c ON c.id_cidade=p.id_cidade JOIN objetivos o ON o.id_objetivo=p.id_objetivo WHERE p.id_projeto=? AND p.id_usuario=?');
  $st->execute([$id_proj,$u['id']]); $projeto=$st->fetch();
}
if(!$projeto){ header('Location:'.ROOT_URL.'/usuario/projetos.php?novo=1'); exit; }

// AJAX: marcar/desmarcar
if($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['toggle'])){
  header('Content-Type: application/json');
  if(!validarCSRF($_POST['csrf']??'')){echo json_encode(['ok'=>false]);exit;}
  $id_item=(int)($_POST['id_item']??0);
  $val=(int)($_POST['val']??0);
  $chk=$pdo->prepare('SELECT c.id_item FROM checklist c JOIN projetos p ON p.id_projeto=c.id_projeto WHERE c.id_item=? AND p.id_usuario=?');
  $chk->execute([$id_item,$u['id']]);
  if(!$chk->fetch()){echo json_encode(['ok'=>false]);exit;}
  $pdo->prepare('UPDATE checklist SET concluido=? WHERE id_item=?')->execute([$val,$id_item]);
  $st=$pdo->prepare('SELECT COUNT(*) AS total,COALESCE(SUM(concluido),0) AS feitos FROM checklist WHERE id_projeto=?');
  $st->execute([$id_proj]); $pg=$st->fetch();
  $pct=$pg['total']>0?round($pg['feitos']/$pg['total']*100):0;
  echo json_encode(['ok'=>true,'pct'=>$pct,'feitos'=>(int)$pg['feitos'],'total'=>(int)$pg['total']]); exit;
}

// AJAX: salvar nota
if($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['nota_item'])){
  header('Content-Type: application/json');
  if(!validarCSRF($_POST['csrf']??'')){echo json_encode(['ok'=>false]);exit;}
  $id_item=(int)($_POST['id_item']??0);
  $nota=trim($_POST['nota']??'');
  $chk=$pdo->prepare('SELECT c.id_item FROM checklist c JOIN projetos p ON p.id_projeto=c.id_projeto WHERE c.id_item=? AND p.id_usuario=?');
  $chk->execute([$id_item,$u['id']]);
  if(!$chk->fetch()){echo json_encode(['ok'=>false]);exit;}
  $pdo->prepare('UPDATE checklist SET notas=? WHERE id_item=?')->execute([$nota,$id_item]);
  echo json_encode(['ok'=>true]); exit;
}

// Itens agrupados por categoria
$itens=$pdo->prepare('SELECT c.*,cat.nome AS cat_nome FROM checklist c JOIN categorias_checklist cat ON cat.id_categoria=c.id_categoria WHERE c.id_projeto=? ORDER BY cat.id_categoria,c.id_item');
$itens->execute([$id_proj]); $itens=$itens->fetchAll();
$por_cat=[];
foreach($itens as $i) $por_cat[$i['cat_nome']][]=$i;
$total=count($itens);
$feitos=array_sum(array_column($itens,'concluido'));
$pct=$total>0?round($feitos/$total*100):0;

// Projetos do usuário para o seletor
$todos=$pdo->prepare('SELECT id_projeto,nome FROM projetos WHERE id_usuario=? ORDER BY criado_em DESC');
$todos->execute([$u['id']]); $todos=$todos->fetchAll();
?><!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Checklist — NextStep</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= ROOT_URL ?>/assets/css/app.css">
<style>
.chk-card{background:#fff;border:1px solid #E8E8E8;border-radius:12px;padding:18px 20px;margin-bottom:10px;transition:border-color .18s;}
.chk-card.done{border-color:rgba(217,164,4,.3);background:#FFFDF5;}
.chk-row{display:flex;align-items:flex-start;gap:14px;}
.chk-box{width:20px;height:20px;border-radius:5px;border:2px solid #E8E8E8;background:#fafafa;cursor:pointer;flex-shrink:0;appearance:none;-webkit-appearance:none;margin-top:2px;transition:all .18s;position:relative;}
.chk-box:checked{background:#D9A404;border-color:#D9A404;}
.chk-box:checked::after{content:'';position:absolute;left:4px;top:1px;width:8px;height:11px;border:2px solid #fff;border-top:none;border-left:none;transform:rotate(45deg);}
.chk-titulo{font-size:15px;font-weight:700;color:#1a1208;margin-bottom:3px;}
.chk-card.done .chk-titulo{text-decoration:line-through;color:#9E9E9E;}
.chk-desc{font-size:13px;color:#6B6B6B;margin-bottom:10px;line-height:1.5;}
.nota-field{width:100%;background:#FFF9E6;border:none;border-radius:8px;padding:9px 12px;font-family:'Inter',sans-serif;font-size:13px;color:#BF9004;resize:none;min-height:44px;outline:none;}
.nota-field::placeholder{color:#D9A404;opacity:.6;}
.nota-st{font-size:11px;color:#9E9E9E;margin-top:3px;text-align:right;min-height:14px;}
.nota-st.ok{color:#28a745;}
.cat-titulo{font-size:16px;font-weight:700;color:#1a1208;margin:22px 0 10px;}
.prog-wrap{background:#fff;border:1px solid rgba(217,164,4,.25);border-radius:12px;padding:18px 22px;margin-bottom:22px;}
.proj-sel{padding:8px 14px;border:1.5px solid rgba(217,164,4,.4);border-radius:8px;font-family:'Inter',sans-serif;font-size:14px;color:#1a1208;background:#FFF9E6;outline:none;cursor:pointer;}
</style></head><body>
<?php include ROOT.'/includes/topbar.php'; ?>
<div class="content">
<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:6px;">
  <a href="<?= ROOT_URL ?>/usuario/dashboard.php" class="voltar" style="margin-bottom:0;">← Dashboard</a>
  <?php if(count($todos)>1): ?>
  <select class="proj-sel" onchange="location='?id='+this.value">
    <?php foreach($todos as $t): ?><option value="<?= $t['id_projeto'] ?>" <?= $t['id_projeto']==$id_proj?'selected':'' ?>><?= limpar($t['nome']) ?></option><?php endforeach; ?>
  </select>
  <?php endif; ?>
</div>

<!-- Progresso -->
<div class="prog-wrap">
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
    <div>
      <div style="font-size:15px;font-weight:800;color:#1a1208;"><?= limpar($projeto['bandeira']) ?> <?= limpar($projeto['nome']) ?></div>
      <div id="prog-sub" style="font-size:13px;color:#9E9E9E;margin-top:2px;"><?= $feitos ?> de <?= $total ?> tarefas concluídas</div>
    </div>
    <div id="prog-pct" style="font-size:26px;font-weight:800;color:#BF9004;"><?= $pct ?>%</div>
  </div>
  <div class="prog-bg"><div class="prog-bar" id="prog-bar" style="width:<?= $pct ?>%"></div></div>
</div>

<h2 style="font-size:20px;font-weight:800;color:#BF9004;margin-bottom:4px;">Checklist de Tarefas</h2>
<p style="font-size:13px;color:#9E9E9E;margin-bottom:20px;">Marque as tarefas conforme for concluindo. Suas notas são salvas automaticamente.</p>

<?php if(empty($itens)): ?>
<div class="card empty"><div class="empty-icon">📋</div><h3>Nenhuma tarefa encontrada</h3></div>
<?php else: ?>
<?php foreach($por_cat as $cat=>$items): ?>
<div class="cat-titulo"><?= limpar($cat) ?></div>
<?php foreach($items as $item): ?>
<div class="chk-card <?= $item['concluido']?'done':'' ?>" id="card-<?= $item['id_item'] ?>">
  <div class="chk-row">
    <input type="checkbox" class="chk-box" data-id="<?= $item['id_item'] ?>" <?= $item['concluido']?'checked':'' ?>>
    <div style="flex:1;">
      <div class="chk-titulo"><?= limpar($item['titulo']) ?></div>
      <div class="chk-desc"><?= limpar($item['descricao']??'') ?></div>
      <textarea class="nota-field" data-id="<?= $item['id_item'] ?>" placeholder="Adicionar notas..." rows="2"><?= limpar($item['notas']??'') ?></textarea>
      <div class="nota-st" id="ns-<?= $item['id_item'] ?>"></div>
    </div>
  </div>
</div>
<?php endforeach; endforeach; endif; ?>
</div>

<div class="toast" id="toast"></div>
<script>
const CSRF='<?= tokenCSRF() ?>';
const BASE='<?= ROOT_URL ?>/usuario/checklist.php?id=<?= $id_proj ?>';

document.querySelectorAll('.chk-box').forEach(c=>{
  c.addEventListener('change',async function(){
    const id=this.dataset.id, val=this.checked?1:0;
    document.getElementById('card-'+id).classList.toggle('done',!!val);
    const fd=new FormData();fd.append('toggle',1);fd.append('id_item',id);fd.append('val',val);fd.append('csrf',CSRF);
    const r=await fetch(BASE,{method:'POST',body:fd});
    const d=await r.json();
    if(d.ok){
      document.getElementById('prog-bar').style.width=d.pct+'%';
      document.getElementById('prog-pct').textContent=d.pct+'%';
      document.getElementById('prog-sub').textContent=d.feitos+' de '+d.total+' tarefas concluídas';
      toast(val?'✅ Tarefa concluída!':'↩️ Tarefa desmarcada');
    }
  });
});

const timers={};
document.querySelectorAll('.nota-field').forEach(t=>{
  t.addEventListener('input',function(){
    const id=this.dataset.id;
    const st=document.getElementById('ns-'+id);
    st.textContent='Digitando...'; st.className='nota-st';
    clearTimeout(timers[id]);
    timers[id]=setTimeout(async()=>{
      const fd=new FormData();fd.append('nota_item',1);fd.append('id_item',id);fd.append('nota',this.value);fd.append('csrf',CSRF);
      const r=await fetch(BASE,{method:'POST',body:fd});
      const d=await r.json();
      st.textContent=d.ok?'Salvo ✓':'Erro'; st.className='nota-st'+(d.ok?' ok':'');
      if(d.ok) setTimeout(()=>st.textContent='',2000);
    },800);
  });
});

function toast(msg){const t=document.getElementById('toast');t.textContent=msg;t.classList.add('show');setTimeout(()=>t.classList.remove('show'),2200);}
</script>
</body></html>
