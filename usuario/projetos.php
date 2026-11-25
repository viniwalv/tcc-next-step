<?php
require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../includes/funcoes.php';
exigirUsuario();
$tabAtiva='projetos';
$u=usuario(); $pdo=conectar();
$erro=''; $novo=isset($_GET['novo']);

if($_SERVER['REQUEST_METHOD']==='POST'){
  if(!validarCSRF($_POST['csrf_token']??'')) $erro='Requisição inválida.';
  else{
    $nome=trim($_POST['nome']??'');
    $id_pais=(int)($_POST['id_pais']??0);
    $id_cidade=(int)($_POST['id_cidade']??0);
    $id_objetivo=(int)($_POST['id_objetivo']??0);
    $duracao=(int)($_POST['duracao']??0);
    $data_inicio=trim($_POST['data_inicio']??'');
    $notas=trim($_POST['notas']??'');
    if(!$nome||!$id_pais||!$id_cidade||!$id_objetivo||!$duracao){$erro='Preencha todos os campos.';$novo=true;}
    elseif($duracao<1||$duracao>60||strlen($nome)>150||($data_inicio&&!DateTime::createFromFormat('Y-m-d',$data_inicio))){$erro='Confira os dados: duração de 1 a 60 meses, nome de até 150 caracteres e data válida.';$novo=true;}
    else{
      $chkC=$pdo->prepare('SELECT *,(custo_moradia+custo_alimentacao+custo_transporte+custo_lazer+custo_outros) AS custo_mensal FROM cidades WHERE id_cidade=? AND id_pais=?');
      $chkC->execute([$id_cidade,$id_pais]); $cid=$chkC->fetch();
      if(!$cid){$erro='Cidade inválida para este país.';$novo=true;}
      else{
        $meta=($cid['custo_mensal']*$duracao)+($cid['passagem_brl']*2);
        $ins=$pdo->prepare('INSERT INTO projetos(id_usuario,id_pais,id_cidade,id_objetivo,nome,duracao_meses,data_inicio,notas)VALUES(?,?,?,?,?,?,?,?)');
        $ins->execute([$u['id'],$id_pais,$id_cidade,$id_objetivo,$nome,$duracao,$data_inicio?:null,$notas?:null]);
        $id_proj=$pdo->lastInsertId();
        $pdo->prepare('INSERT INTO plano_financeiro(id_projeto,custo_mensal,valor_guardado,meta_total,passagem_brl)VALUES(?,?,0,?,?)')->execute([$id_proj,$cid['custo_mensal'],$meta,$cid['passagem_brl']]);
        gerarChecklist($pdo,$id_proj,$id_objetivo);
        if($data_inicio) gerarTimeline($pdo,$id_proj,$data_inicio);
        setMsg('sucesso','Projeto criado! Checklist e timeline gerados automaticamente. 🎉');
        header('Location:'.ROOT_URL.'/usuario/dashboard.php');exit;
      }
    }
  }
}

$paises=$pdo->query('SELECT id_pais,nome,bandeira FROM paises ORDER BY nome')->fetchAll();
$objetivos=$pdo->query('SELECT id_objetivo,descricao FROM objetivos')->fetchAll();
$all_cid=$pdo->query('SELECT id_cidade,id_pais,nome,(custo_moradia+custo_alimentacao+custo_transporte+custo_lazer+custo_outros) AS custo_mensal,passagem_brl FROM cidades ORDER BY id_pais,nome')->fetchAll();
$cid_por_pais=[];
foreach($all_cid as $c) $cid_por_pais[$c['id_pais']][]=$c;
$msg=getMsg();
?><!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Projetos — NextStep</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= ROOT_URL ?>/assets/css/app.css">
</head><body>
<?php include ROOT.'/includes/topbar.php'; ?>
<div class="content">
<?php if($msg): ?><div class="alerta alerta-<?= $msg['tipo'] ?>"><?= limpar($msg['texto']) ?></div><?php endif; ?>
<a href="<?= ROOT_URL ?>/usuario/dashboard.php" class="voltar">← Voltar</a>

<?php if($novo): ?>
<div class="card" style="max-width:700px;margin:0 auto;">
  <h2 style="font-size:22px;font-weight:800;margin-bottom:24px;">Criar Novo Projeto de Intercâmbio</h2>
  <?php if($erro): ?><div class="alerta alerta-erro">⚠️ <?= limpar($erro) ?></div><?php endif; ?>
  <form method="POST">
    <input type="hidden" name="csrf_token" value="<?= tokenCSRF() ?>">
    <div class="form-grupo">
      <label class="form-label">Nome do Projeto</label>
      <input class="form-input" type="text" name="nome" placeholder="Ex: Mestrado em Toronto" value="<?= limpar($_POST['nome']??'') ?>" required>
      <span class="form-hint">Dê um nome para identificar seu projeto</span>
    </div>
    <div class="form-grid">
      <div class="form-grupo">
        <label class="form-label">País</label>
        <div class="select-wrap">
          <select class="form-select" id="id_pais" name="id_pais" required>
            <option value="">Selecione o país</option>
            <?php foreach($paises as $p): ?><option value="<?= $p['id_pais'] ?>" <?= ($_POST['id_pais']??'')==$p['id_pais']?'selected':'' ?>><?= limpar($p['bandeira']) ?> <?= limpar($p['nome']) ?></option><?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="form-grupo">
        <label class="form-label">Cidade</label>
        <div class="select-wrap">
          <select class="form-select" id="id_cidade" name="id_cidade" required disabled>
            <option value="">Selecione o país primeiro</option>
          </select>
        </div>
      </div>
    </div>
    <div class="form-grupo">
      <label class="form-label">Objetivo do Intercâmbio</label>
      <div class="select-wrap">
        <select class="form-select" name="id_objetivo" required>
          <?php foreach($objetivos as $o): ?><option value="<?= $o['id_objetivo'] ?>" <?= ($_POST['id_objetivo']??1)==$o['id_objetivo']?'selected':'' ?>><?= limpar($o['descricao']) ?></option><?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="form-grid">
      <div class="form-grupo">
        <label class="form-label">Tempo de Permanência (meses)</label>
        <input class="form-input" type="number" id="duracao" name="duracao" placeholder="Ex: 6" min="1" max="60" value="<?= limpar($_POST['duracao']??'') ?>" required>
      </div>
      <div class="form-grupo">
        <label class="form-label">Data Prevista de Início</label>
        <input class="form-input" type="date" name="data_inicio" value="<?= limpar($_POST['data_inicio']??'') ?>">
        <span class="form-hint">Opcional — gera a timeline automaticamente</span>
      </div>
    </div>
    <!-- Preview meta em tempo real -->
    <div id="meta-preview" class="muted-box" style="display:none;margin-bottom:18px;">
      <h4 style="font-size:13px;font-weight:700;color:#BF9004;margin-bottom:10px;text-transform:uppercase;letter-spacing:.5px;">💰 Estimativa de Meta</h4>
      <div style="display:flex;justify-content:space-between;font-size:13px;color:#6B6B6B;margin-bottom:5px;"><span>Custo mensal em <span id="prev-cid">—</span></span><span id="prev-cm">R$ 0</span></div>
      <div style="display:flex;justify-content:space-between;font-size:13px;color:#6B6B6B;margin-bottom:5px;"><span>Subtotal (<span id="prev-dur">—</span> meses)</span><span id="prev-sub">R$ 0</span></div>
      <div style="display:flex;justify-content:space-between;font-size:13px;color:#6B6B6B;margin-bottom:8px;"><span>Passagem ida + volta</span><span id="prev-pass">R$ 0</span></div>
      <div style="display:flex;justify-content:space-between;font-size:15px;font-weight:800;color:#1a1208;border-top:1px solid rgba(217,164,4,.25);padding-top:8px;"><span>Meta total recomendada</span><span id="prev-tot">R$ 0</span></div>
    </div>
    <div class="form-grupo">
      <label class="form-label">Notas Adicionais</label>
      <textarea class="form-textarea" name="notas" placeholder="Observações, motivações..."><?= limpar($_POST['notas']??'') ?></textarea>
    </div>
    <div class="muted-box" style="margin-bottom:20px;">
      <h4 style="font-size:14px;font-weight:700;color:#BF9004;margin-bottom:8px;">O que será criado automaticamente:</h4>
      <p style="font-size:13px;color:#6B6B6B;">✓ Checklist personalizado por país e objetivo<br>✓ Plano financeiro com custos reais da cidade<br>✓ Timeline com prazos calculados pela data de início<br>✓ Meta incluindo passagem de ida e volta</p>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-pr" style="justify-content:center;">Criar Projeto</button>
      <a href="<?= ROOT_URL ?>/usuario/dashboard.php" class="btn btn-outline" style="justify-content:center;">Cancelar</a>
    </div>
  </form>
</div>
<?php else:
  $projs=$pdo->prepare('SELECT p.*,pa.nome AS pais_nome,pa.bandeira,o.descricao AS objetivo,c.nome AS cidade_nome FROM projetos p JOIN paises pa ON pa.id_pais=p.id_pais JOIN cidades c ON c.id_cidade=p.id_cidade JOIN objetivos o ON o.id_objetivo=p.id_objetivo WHERE p.id_usuario=? ORDER BY p.criado_em DESC');
  $projs->execute([$u['id']]); $projs=$projs->fetchAll();
?>
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;">
  <h2 style="font-size:20px;font-weight:800;">Meus Projetos</h2>
  <a href="?novo=1" class="btn btn-pr btn-sm">+ Novo Projeto</a>
</div>
<?php if(empty($projs)): ?>
<div class="card empty"><div class="empty-icon">🌍</div><h3 style="margin-bottom:10px;">Nenhum projeto ainda</h3><a href="?novo=1" class="btn btn-pr" style="margin-top:14px;">Criar projeto</a></div>
<?php else: ?>
<div style="display:flex;flex-direction:column;gap:12px;">
  <?php foreach($projs as $pj): ?>
  <div class="card" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
    <div>
      <div style="font-size:11px;font-weight:700;color:#BF9004;text-transform:uppercase;margin-bottom:2px;"><?= limpar($pj['bandeira']) ?> <?= limpar($pj['pais_nome']) ?> — <?= limpar($pj['cidade_nome']) ?></div>
      <div style="font-size:16px;font-weight:800;color:#1a1208;"><?= limpar($pj['nome']) ?></div>
      <div style="font-size:13px;color:#9E9E9E;margin-top:2px;"><?= limpar($pj['objetivo']) ?> · <?= $pj['duracao_meses'] ?> meses</div>
    </div>
    <div style="display:flex;gap:8px;">
      <a href="<?= ROOT_URL ?>/usuario/checklist.php?id=<?= $pj['id_projeto'] ?>" class="btn btn-outline btn-sm">Checklist</a>
      <a href="<?= ROOT_URL ?>/usuario/timeline.php?id=<?= $pj['id_projeto'] ?>" class="btn btn-outline btn-sm">Timeline</a>
      <a href="<?= ROOT_URL ?>/usuario/financeiro.php?id=<?= $pj['id_projeto'] ?>" class="btn btn-pr btn-sm">Financeiro</a>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; endif; ?>
</div>
<script>
const cids=<?= json_encode($cid_por_pais) ?>;
const selP=document.getElementById('id_pais');
const selC=document.getElementById('id_cidade');
const selD=document.getElementById('duracao');
if(selP){
  selP.addEventListener('change',function(){
    const id=parseInt(this.value);
    selC.innerHTML='<option value="">Selecione a cidade</option>';
    if(id&&cids[id]){cids[id].forEach(c=>{const o=document.createElement('option');o.value=c.id_cidade;o.textContent=c.nome;o.dataset.cm=c.custo_mensal;o.dataset.pass=c.passagem_brl;selC.appendChild(o);});selC.disabled=false;}
    else{selC.disabled=true;selC.innerHTML='<option value="">Selecione o país primeiro</option>';}
    calcMeta();
  });
  selC.addEventListener('change',calcMeta);
  if(selD) selD.addEventListener('input',calcMeta);
}
function fmt(v){return'R$ '+Math.round(v).toLocaleString('pt-BR');}
function calcMeta(){
  const opt=selC.options[selC.selectedIndex];
  const dur=parseInt(selD?selD.value:0)||0;
  const prev=document.getElementById('meta-preview');
  if(!prev||!opt||!opt.dataset.cm||!dur){if(prev)prev.style.display='none';return;}
  const cm=parseFloat(opt.dataset.cm),pa=parseFloat(opt.dataset.pass);
  document.getElementById('prev-cid').textContent=opt.textContent;
  document.getElementById('prev-cm').textContent=fmt(cm);
  document.getElementById('prev-dur').textContent=dur;
  document.getElementById('prev-sub').textContent=fmt(cm*dur);
  document.getElementById('prev-pass').textContent=fmt(pa*2);
  document.getElementById('prev-tot').textContent=fmt((cm*dur)+(pa*2));
  prev.style.display='block';
}
</script>
</body></html>
