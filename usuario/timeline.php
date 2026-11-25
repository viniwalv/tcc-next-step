<?php
require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../includes/funcoes.php';
exigirUsuario();
$tabAtiva='timeline';
$u=usuario(); $pdo=conectar();

$id_proj=(int)($_GET['id']??0);
if(!$id_proj){
  $st=$pdo->prepare('SELECT id_projeto FROM projetos WHERE id_usuario=? ORDER BY criado_em DESC LIMIT 1');
  $st->execute([$u['id']]); $r=$st->fetch(); if($r) $id_proj=$r['id_projeto'];
}
$projeto=null;
if($id_proj){
  $st=$pdo->prepare('SELECT p.*,pa.nome AS pais_nome,pa.bandeira FROM projetos p JOIN paises pa ON pa.id_pais=p.id_pais WHERE p.id_projeto=? AND p.id_usuario=?');
  $st->execute([$id_proj,$u['id']]); $projeto=$st->fetch();
}
if(!$projeto){ header('Location:'.ROOT_URL.'/usuario/projetos.php'); exit; }

// AJAX: marcar evento
if($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['toggle_ev'])){
  header('Content-Type: application/json');
  if(!validarCSRF($_POST['csrf']??'')){echo json_encode(['ok'=>false]);exit;}
  $id_ev=(int)($_POST['id_ev']??0);
  $val=(int)($_POST['val']??0);
  $pdo->prepare('UPDATE timeline SET concluido=? WHERE id_evento=? AND id_projeto=?')->execute([$val,$id_ev,$id_proj]);
  echo json_encode(['ok'=>true]); exit;
}

$eventos=$pdo->prepare('SELECT t.*,cat.nome AS cat_nome FROM timeline t JOIN categorias_checklist cat ON cat.id_categoria=t.id_categoria WHERE t.id_projeto=? ORDER BY t.data_prevista ASC');
$eventos->execute([$id_proj]); $eventos=$eventos->fetchAll();
$todos=$pdo->prepare('SELECT id_projeto,nome FROM projetos WHERE id_usuario=? ORDER BY criado_em DESC');
$todos->execute([$u['id']]); $todos=$todos->fetchAll();
$hoje=date('Y-m-d');
?><!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Timeline — NextStep</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= ROOT_URL ?>/assets/css/app.css">
<style>
.tl-wrap{position:relative;padding-left:36px;}
.tl-wrap::before{content:'';position:absolute;left:10px;top:0;bottom:0;width:2px;background:linear-gradient(to bottom,#D9A404,#F5C542);}
.tl-item{position:relative;margin-bottom:20px;}
.tl-dot{position:absolute;left:-30px;top:16px;width:16px;height:16px;border-radius:50%;background:#E8E8E8;border:3px solid #fff;box-shadow:0 0 0 2px #E8E8E8;transition:all .2s;}
.tl-dot.done{background:#D9A404;box-shadow:0 0 0 2px rgba(217,164,4,.3);}
.tl-dot.hoje{background:#BF9004;box-shadow:0 0 0 4px rgba(217,164,4,.25);}
.tl-card{background:#fff;border:1px solid #E8E8E8;border-radius:12px;padding:16px 18px;transition:border-color .18s;}
.tl-card.done{background:#FFFDF5;border-color:rgba(217,164,4,.3);}
.tl-card.proximo{border-color:#D9A404;background:#FFFBF0;}
.tl-header{display:flex;align-items:flex-start;justify-content:space-between;gap:10px;margin-bottom:4px;}
.tl-titulo{font-size:15px;font-weight:700;color:#1a1208;}
.tl-card.done .tl-titulo{text-decoration:line-through;color:#9E9E9E;}
.tl-data{font-size:12px;font-weight:600;color:#BF9004;white-space:nowrap;}
.tl-desc{font-size:13px;color:#6B6B6B;margin-bottom:10px;}
.tl-badge{display:inline-block;padding:2px 8px;border-radius:99px;font-size:10px;font-weight:700;background:rgba(217,164,4,.1);color:#BF9004;margin-bottom:6px;}
.btn-tl{padding:6px 14px;font-size:12px;font-weight:700;border:1.5px solid #E8E8E8;border-radius:6px;background:transparent;cursor:pointer;transition:all .18s;color:#6B6B6B;}
.btn-tl.done{background:#FFF9E6;border-color:rgba(217,164,4,.4);color:#BF9004;}
.btn-tl:hover{border-color:#D9A404;color:#BF9004;}
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

<h2 style="font-size:20px;font-weight:800;margin-bottom:4px;">🗓️ Timeline — <?= limpar($projeto['bandeira']) ?> <?= limpar($projeto['nome']) ?></h2>
<p style="font-size:13px;color:#9E9E9E;margin-bottom:24px;">Acompanhe os prazos e marcos do seu intercâmbio. Marque conforme for concluindo.</p>

<?php if(empty($eventos)): ?>
<div class="card empty"><div class="empty-icon">🗓️</div><h3>Sem eventos</h3><p style="font-size:13px;color:#9E9E9E;margin-top:8px;">A timeline é gerada automaticamente quando você informa a data de início ao criar o projeto.</p></div>
<?php else: ?>
<div class="tl-wrap">
<?php
$proxEncontrado=false;
foreach($eventos as $ev):
  $isPast=$ev['data_prevista']<$hoje;
  $isHoje=$ev['data_prevista']===$hoje;
  $isProx=!$ev['concluido']&&!$proxEncontrado&&($isPast||$isHoje||$ev['data_prevista']>=$hoje);
  if($isProx&&!$ev['concluido']) $proxEncontrado=true;
  $cls=$ev['concluido']?'done':($isHoje?'hoje':($isProx?'proximo':''));
?>
<div class="tl-item">
  <div class="tl-dot <?= $cls ?>"></div>
  <div class="tl-card <?= $cls ?>">
    <div class="tl-badge"><?= limpar($ev['cat_nome']) ?></div>
    <div class="tl-header">
      <div>
        <div class="tl-titulo"><?= limpar($ev['titulo']) ?></div>
        <?php if($ev['descricao']): ?><div class="tl-desc"><?= limpar($ev['descricao']) ?></div><?php endif; ?>
      </div>
      <div style="display:flex;flex-direction:column;align-items:flex-end;gap:6px;">
        <div class="tl-data">📅 <?= date('d/m/Y',strtotime($ev['data_prevista'])) ?></div>
        <button class="btn-tl <?= $ev['concluido']?'done':'' ?>" data-id="<?= $ev['id_evento'] ?>" data-val="<?= $ev['concluido']?0:1 ?>">
          <?= $ev['concluido']?'✓ Concluído':'Marcar como feito' ?>
        </button>
      </div>
    </div>
  </div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>
</div>

<div class="toast" id="toast"></div>
<script>
const CSRF='<?= tokenCSRF() ?>';
const BASE='<?= ROOT_URL ?>/usuario/timeline.php?id=<?= $id_proj ?>';
document.querySelectorAll('.btn-tl').forEach(btn=>{
  btn.addEventListener('click',async function(){
    const id=this.dataset.id, val=parseInt(this.dataset.val);
    const fd=new FormData();fd.append('toggle_ev',1);fd.append('id_ev',id);fd.append('val',val);fd.append('csrf',CSRF);
    const r=await fetch(BASE,{method:'POST',body:fd});
    const d=await r.json();
    if(d.ok){
      const t=document.getElementById('toast');
      t.textContent=val?'✅ Evento concluído!':'↩️ Desmarcado';t.classList.add('show');
      setTimeout(()=>t.classList.remove('show'),2000);
      setTimeout(()=>location.reload(),600);
    }
  });
});
</script>
</body></html>
