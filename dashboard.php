<?php
// ============================================================
//  NextStep — Dashboard do Usuário
// ============================================================

require_once __DIR__ . '/../includes/funcoes.php';
exigirUsuario();

$paginaAtiva = 'home';
$u    = dadosUsuario();
$pdo  = conectar();

// --- Dados do usuário completo
$stmtUser = $pdo->prepare('SELECT * FROM usuarios WHERE id_usuario = ?');
$stmtUser->execute([$u['id']]);
$usuario = $stmtUser->fetch();

// --- Projetos do usuário
$stmtProj = $pdo->prepare(
    'SELECT p.*, pa.nome AS pais_nome, pa.moeda, o.descricao AS objetivo
       FROM projetos p
       JOIN paises   pa ON pa.id_pais     = p.id_pais
       JOIN objetivos o  ON o.id_objetivo = p.id_objetivo
      WHERE p.id_usuario = ?
      ORDER BY p.criado_em DESC'
);
$stmtProj->execute([$u['id']]);
$projetos = $stmtProj->fetchAll();
$totalProjetos = count($projetos);

// --- Itens de checklist pendentes (todos os projetos do usuário)
$stmtCheck = $pdo->prepare(
    'SELECT COUNT(*) AS total, SUM(c.concluido) AS concluidos
       FROM checklist c
       JOIN projetos  p ON p.id_projeto = c.id_projeto
      WHERE p.id_usuario = ?'
);
$stmtCheck->execute([$u['id']]);
$checkStats = $stmtCheck->fetch();
$totalItens     = (int)($checkStats['total'] ?? 0);
$totalConcluidos= (int)($checkStats['concluidos'] ?? 0);
$pctChecklist   = $totalItens > 0 ? round($totalConcluidos / $totalItens * 100) : 0;

// --- Próximo evento da timeline
$stmtTimeline = $pdo->prepare(
    'SELECT t.titulo, t.data_prevista, pa.nome AS pais
       FROM timeline t
       JOIN projetos  p  ON p.id_projeto = t.id_projeto
       JOIN paises    pa ON pa.id_pais   = p.id_pais
      WHERE p.id_usuario = ?
        AND t.concluido = 0
        AND t.data_prevista >= CURDATE()
      ORDER BY t.data_prevista ASC
      LIMIT 1'
);
$stmtTimeline->execute([$u['id']]);
$proximoEvento = $stmtTimeline->fetch();

// --- Plano financeiro do projeto mais recente
$stmtFin = $pdo->prepare(
    'SELECT pf.*, pr.nome AS projeto_nome
       FROM plano_financeiro pf
       JOIN projetos pr ON pr.id_projeto = pf.id_projeto
      WHERE pr.id_usuario = ?
      ORDER BY pr.criado_em DESC
      LIMIT 1'
);
$stmtFin->execute([$u['id']]);
$financeiro = $stmtFin->fetch();

$msg = getMensagem();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard — NextStep</title>
  <link rel="stylesheet" href="/nextstep/assets/css/style.css">
  <style>
    .boas-vindas {
      background: linear-gradient(135deg, var(--azul) 0%, var(--azul-medio) 60%, #364fc7 100%);
      border-radius: var(--radius);
      padding: 28px 32px;
      color: #fff;
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 28px;
      position: relative;
      overflow: hidden;
    }
    .boas-vindas::after {
      content: '✈️';
      position: absolute;
      right: 28px;
      top: 50%;
      transform: translateY(-50%);
      font-size: 72px;
      opacity: .12;
    }
    .projeto-card {
      border: 1.5px solid var(--cinza-borda);
      border-radius: var(--radius);
      padding: 20px;
      background: var(--branco);
      transition: box-shadow var(--transicao), border-color var(--transicao);
    }
    .projeto-card:hover {
      box-shadow: var(--sombra-lg);
      border-color: var(--azul-claro);
    }
    .projeto-card__pais {
      font-size: 11px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: .8px;
      color: var(--azul-claro);
      margin-bottom: 4px;
    }
    .projeto-card__nome {
      font-family: 'Sora', sans-serif;
      font-size: 16px;
      font-weight: 700;
      color: var(--azul);
      margin-bottom: 8px;
    }
    .timeline-item {
      display: flex;
      gap: 14px;
      padding: 14px 0;
      border-bottom: 1px solid var(--cinza-borda);
    }
    .timeline-item:last-child { border-bottom: none; }
    .timeline-dot {
      width: 10px; height: 10px;
      border-radius: 50%;
      background: var(--azul-claro);
      flex-shrink: 0;
      margin-top: 5px;
    }
    .timeline-dot.concluido { background: var(--sucesso); }
  </style>
</head>
<body>

<div class="ns-wrapper">
  <?php include __DIR__ . '/../includes/sidebar.php'; ?>

  <main class="ns-main">

    <!-- Topbar -->
    <div class="ns-topbar">
      <span class="ns-topbar__titulo">Dashboard</span>
      <div class="ns-topbar__right">
        <span style="font-size:13px;color:var(--cinza-texto);">
          <?= date('d \d\e F \d\e Y') ?>
        </span>
        <div class="ns-avatar">
          <?= strtoupper(substr($u['nome'], 0, 1)) ?>
        </div>
      </div>
    </div>

    <div class="ns-content">

      <?php if ($msg): ?>
        <div class="ns-alerta ns-alerta--<?= $msg['tipo'] ?>">
          <?= limpar($msg['texto']) ?>
        </div>
      <?php endif; ?>

      <!-- Boas-vindas -->
      <div class="boas-vindas">
        <div>
          <h1 style="font-size:24px;font-weight:800;margin-bottom:6px;">
            Olá, <?= limpar(explode(' ', $u['nome'])[0]) ?>! 👋
          </h1>
          <p style="opacity:.75;font-size:14px;">
            <?php if ($totalProjetos === 0): ?>
              Comece criando seu primeiro projeto de intercâmbio.
            <?php else: ?>
              Você tem <strong><?= $totalProjetos ?></strong> projeto<?= $totalProjetos > 1 ? 's' : '' ?> em andamento.
            <?php endif; ?>
          </p>
        </div>
        <a href="/nextstep/usuario/projetos.php?novo=1"
           class="ns-btn ns-btn--dourado"
           style="position:relative;z-index:1;">
          + Novo Projeto
        </a>
      </div>

      <!-- Stats -->
      <div class="ns-stats">

        <div class="ns-stat ns-card--azul">
          <div class="ns-stat__label">Projetos</div>
          <div class="ns-stat__valor"><?= $totalProjetos ?></div>
          <div class="ns-stat__sub">Intercâmbios planejados</div>
        </div>

        <div class="ns-stat ns-card--dourado">
          <div class="ns-stat__label">Checklist</div>
          <div class="ns-stat__valor"><?= $pctChecklist ?>%</div>
          <div class="ns-stat__sub"><?= $totalConcluidos ?> de <?= $totalItens ?> concluídos</div>
        </div>

        <div class="ns-stat ns-card--azul-claro">
          <div class="ns-stat__label">Passaporte</div>
          <div class="ns-stat__valor"><?= $usuario['tem_passaporte'] ? '✓' : '✗' ?></div>
          <div class="ns-stat__sub">
            <?= $usuario['tem_passaporte'] ? 'Documento OK' : 'Pendente' ?>
          </div>
        </div>

        <?php if ($financeiro): ?>
        <div class="ns-stat" style="background:linear-gradient(135deg,#2ECC71,#27AE60);color:#fff;">
          <div class="ns-stat__label">Guardado</div>
          <div class="ns-stat__valor" style="font-size:22px;">
            R$ <?= number_format($financeiro['valor_guardado'], 0, ',', '.') ?>
          </div>
          <div class="ns-stat__sub">de R$ <?= number_format($financeiro['meta_total'], 0, ',', '.') ?> meta</div>
        </div>
        <?php endif; ?>

      </div>

      <!-- Grid principal -->
      <div style="display:grid;grid-template-columns:1fr 360px;gap:24px;">

        <!-- Projetos recentes -->
        <div>
          <div class="ns-secao-header">
            <h2 class="ns-secao-titulo">Meus Projetos</h2>
            <a href="/nextstep/usuario/projetos.php" class="ns-btn ns-btn--ghost ns-btn--sm">
              Ver todos
            </a>
          </div>

          <?php if (empty($projetos)): ?>
            <div class="ns-card" style="text-align:center;padding:48px 24px;">
              <div style="font-size:48px;margin-bottom:16px;">🌍</div>
              <h3 style="margin-bottom:8px;">Nenhum projeto ainda</h3>
              <p style="color:var(--cinza-texto);font-size:14px;margin-bottom:20px;">
                Crie seu primeiro projeto e comece a planejar seu intercâmbio!
              </p>
              <a href="/nextstep/usuario/projetos.php?novo=1" class="ns-btn ns-btn--primario">
                Criar meu primeiro projeto
              </a>
            </div>
          <?php else: ?>
            <div class="ns-grid ns-grid--2" style="gap:16px;">
              <?php foreach (array_slice($projetos, 0, 4) as $proj): ?>
                <div class="projeto-card">
                  <div class="projeto-card__pais"><?= limpar($proj['pais_nome']) ?></div>
                  <div class="projeto-card__nome"><?= limpar($proj['nome']) ?></div>
                  <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px;">
                    <span class="ns-badge ns-badge--azul"><?= limpar($proj['objetivo']) ?></span>
                    <span class="ns-badge ns-badge--dourado"><?= $proj['duracao_meses'] ?> meses</span>
                  </div>
                  <?php if ($proj['data_inicio']): ?>
                    <div style="font-size:12px;color:var(--cinza-texto);">
                      📅 Início: <?= date('d/m/Y', strtotime($proj['data_inicio'])) ?>
                    </div>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>

        <!-- Coluna lateral -->
        <div style="display:flex;flex-direction:column;gap:20px;">

          <!-- Próximo evento -->
          <div class="ns-card">
            <h3 style="font-size:15px;font-weight:700;margin-bottom:16px;">
              🗓️ Próximo Passo
            </h3>
            <?php if ($proximoEvento): ?>
              <div style="background:var(--bg);border-radius:var(--radius-sm);padding:14px;">
                <div style="font-size:11px;color:var(--azul-claro);font-weight:600;text-transform:uppercase;letter-spacing:.6px;margin-bottom:4px;">
                  <?= limpar($proximoEvento['pais']) ?>
                </div>
                <div style="font-weight:600;font-size:14px;margin-bottom:6px;">
                  <?= limpar($proximoEvento['titulo']) ?>
                </div>
                <div style="font-size:12px;color:var(--cinza-texto);">
                  📅 <?= date('d/m/Y', strtotime($proximoEvento['data_prevista'])) ?>
                </div>
              </div>
            <?php else: ?>
              <p style="font-size:13px;color:var(--cinza-texto);text-align:center;padding:16px 0;">
                Nenhum evento pendente.<br>Crie um projeto para gerar sua timeline!
              </p>
            <?php endif; ?>
          </div>

          <!-- Progresso checklist -->
          <?php if ($totalItens > 0): ?>
          <div class="ns-card">
            <h3 style="font-size:15px;font-weight:700;margin-bottom:16px;">
              📋 Progresso Geral
            </h3>
            <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:8px;">
              <span><?= $totalConcluidos ?> concluídos</span>
              <strong><?= $pctChecklist ?>%</strong>
            </div>
            <div class="ns-progresso">
              <div class="ns-progresso__barra" style="width:<?= $pctChecklist ?>%;"></div>
            </div>
            <div style="font-size:12px;color:var(--cinza-texto);margin-top:8px;">
              <?= $totalItens - $totalConcluidos ?> itens pendentes
            </div>
            <a href="/nextstep/usuario/checklist.php"
               class="ns-btn ns-btn--ghost ns-btn--sm ns-btn--bloco"
               style="margin-top:14px;">
              Ver checklist completo
            </a>
          </div>
          <?php endif; ?>

          <!-- Passaporte alert -->
          <?php if (!$usuario['tem_passaporte']): ?>
          <div class="ns-card" style="background:#FFFBEB;border-color:#FDE68A;">
            <h4 style="font-size:14px;font-weight:700;color:#92400E;margin-bottom:6px;">
              ⚠️ Passaporte Pendente
            </h4>
            <p style="font-size:13px;color:#78350F;line-height:1.5;">
              Você ainda não tem passaporte. Agende na Polícia Federal com antecedência!
            </p>
          </div>
          <?php endif; ?>

        </div>
      </div>

    </div><!-- /.ns-content -->
  </main>
</div>

</body>
</html>