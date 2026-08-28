<?php
// ============================================================
//  NextStep — Dashboard do Usuário
//  Visual baseado no protótipo Figma do projeto
// ============================================================

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/funcoes.php';
exigirUsuario();

$u   = usuario();
$pdo = conectar();

$stmtU = $pdo->prepare('SELECT * FROM usuarios WHERE id_usuario = ?');
$stmtU->execute([$u['id']]);
$dados = $stmtU->fetch();

$stmtP = $pdo->prepare(
    'SELECT p.*, pa.nome AS pais_nome, pa.bandeira, o.descricao AS objetivo
       FROM projetos p
       JOIN paises   pa ON pa.id_pais     = p.id_pais
       JOIN objetivos o  ON o.id_objetivo = p.id_objetivo
      WHERE p.id_usuario = ?
      ORDER BY p.criado_em DESC'
);
$stmtP->execute([$u['id']]);
$projetos  = $stmtP->fetchAll();
$totalProj = count($projetos);

$stmtC = $pdo->prepare(
    'SELECT COUNT(*) AS total, COALESCE(SUM(c.concluido),0) AS feitos
       FROM checklist c
       JOIN projetos p ON p.id_projeto = c.id_projeto
      WHERE p.id_usuario = ?'
);
$stmtC->execute([$u['id']]);
$ck  = $stmtC->fetch();
$pct = $ck['total'] > 0 ? round($ck['feitos'] / $ck['total'] * 100) : 0;

$stmtT = $pdo->prepare(
    'SELECT t.titulo, t.data_prevista, pa.nome AS pais, pa.bandeira
       FROM timeline t
       JOIN projetos p  ON p.id_projeto = t.id_projeto
       JOIN paises   pa ON pa.id_pais   = p.id_pais
      WHERE p.id_usuario = ? AND t.concluido = 0 AND t.data_prevista >= CURDATE()
      ORDER BY t.data_prevista ASC LIMIT 1'
);
$stmtT->execute([$u['id']]);
$prox = $stmtT->fetch();

$stmtF = $pdo->prepare(
    'SELECT pf.*, pr.nome AS proj_nome
       FROM plano_financeiro pf
       JOIN projetos pr ON pr.id_projeto = pf.id_projeto
      WHERE pr.id_usuario = ?
      ORDER BY pr.criado_em DESC LIMIT 1'
);
$stmtF->execute([$u['id']]);
$fin = $stmtF->fetch();

$msg          = getMsg();
$primeiroNome = explode(' ', $u['nome'])[0];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard — NextStep</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    /* === TEMA NEXTSTEP (baseado no protótipo Figma) === */
    :root {
      --primary:    #D9A404;
      --primary-dk: #BF9004;
      --secondary:  #D9C27E;
      --accent:     #F5C542;
      --bg:         #FFF8E7;
      --card:       #ffffff;
      --muted:      #FFF4D4;
      --border:     rgba(217,164,4,0.2);
      --foreground: #8B6803;
      --text-dark:  #2b2318;
      --text-mid:   #7a6a52;
      --text-light: #a3927a;
      --sidebar-bg: #FFFBF0;
      --radius:     10px;
      --shadow:     0 4px 20px rgba(217,164,4,0.10);
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'Inter', sans-serif;
      background: var(--bg);
      color: var(--text-dark);
      min-height: 100vh;
    }

    a { text-decoration: none; color: inherit; }

    /* === LAYOUT === */
    .layout { display: flex; min-height: 100vh; }

    /* === SIDEBAR === */
    .sidebar {
      width: 256px;
      background: var(--sidebar-bg);
      border-right: 1px solid var(--border);
      display: flex;
      flex-direction: column;
      position: fixed;
      top: 0; left: 0;
      height: 100vh;
      z-index: 100;
    }

    .sidebar-logo {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 24px 20px;
      border-bottom: 1px solid var(--border);
    }

    .sidebar-logo-icon {
      width: 36px; height: 36px;
      background: var(--primary);
      border-radius: 8px;
      display: flex; align-items: center; justify-content: center;
      font-size: 18px;
    }

    .sidebar-logo-text {
      font-size: 20px;
      font-weight: 800;
      color: var(--text-dark);
      letter-spacing: -.3px;
    }

    .sidebar-user {
      padding: 16px 20px;
      border-bottom: 1px solid var(--border);
    }

    .sidebar-user-nome {
      display: block;
      font-size: 14px;
      font-weight: 600;
      color: var(--text-dark);
    }

    .sidebar-user-perfil {
      font-size: 12px;
      color: var(--primary);
      font-weight: 600;
    }

    .sidebar-nav { flex: 1; padding: 12px 8px; overflow-y: auto; }

    .sidebar-label {
      font-size: 10px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 1px;
      color: var(--text-light);
      padding: 12px 12px 4px;
    }

    .sidebar-link {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 10px 12px;
      border-radius: 8px;
      font-size: 14px;
      font-weight: 500;
      color: var(--text-mid);
      transition: all .18s ease;
      margin-bottom: 2px;
    }

    .sidebar-link:hover,
    .sidebar-link.active {
      background: var(--muted);
      color: var(--primary-dk);
    }

    .sidebar-link svg { width: 18px; height: 18px; opacity: .7; flex-shrink: 0; }
    .sidebar-link.active svg, .sidebar-link:hover svg { opacity: 1; }

    .sidebar-footer {
      padding: 16px 20px;
      border-top: 1px solid var(--border);
    }

    .sidebar-logout {
      display: flex;
      align-items: center;
      gap: 10px;
      color: var(--text-light);
      font-size: 14px;
      font-weight: 500;
      cursor: pointer;
      transition: color .18s;
    }

    .sidebar-logout:hover { color: #dc2626; }

    /* === MAIN === */
    .main { margin-left: 256px; flex: 1; display: flex; flex-direction: column; }

    .topbar {
      background: var(--card);
      border-bottom: 1px solid var(--border);
      padding: 0 28px;
      height: 60px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      position: sticky;
      top: 0;
      z-index: 50;
      box-shadow: var(--shadow);
    }

    .topbar-titulo {
      font-size: 17px;
      font-weight: 700;
      color: var(--text-dark);
    }

    .avatar {
      width: 36px; height: 36px;
      background: var(--primary);
      border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      color: #fff;
      font-weight: 700;
      font-size: 14px;
    }

    .content { padding: 28px; }

    /* === BOAS-VINDAS === */
    .boas-vindas {
      background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
      border-radius: var(--radius);
      padding: 28px 32px;
      color: #fff;
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 24px;
      box-shadow: 0 8px 24px rgba(217,164,4,0.25);
    }

    .boas-vindas h2 { font-size: 22px; font-weight: 800; margin-bottom: 6px; }
    .boas-vindas p  { opacity: .88; font-size: 14px; margin: 0; }

    /* === STATS === */
    .stats {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 16px;
      margin-bottom: 24px;
    }

    .stat {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 20px;
      box-shadow: var(--shadow);
    }

    .stat-label {
      font-size: 12px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: .7px;
      color: var(--text-light);
      margin-bottom: 8px;
    }

    .stat-valor {
      font-size: 32px;
      font-weight: 800;
      color: var(--text-dark);
      line-height: 1;
    }

    .stat-sub { font-size: 12px; color: var(--text-light); margin-top: 4px; }
    .stat-primary { border-top: 3px solid var(--primary); }
    .stat-accent  { border-top: 3px solid var(--accent); }

    /* === CARDS === */
    .card {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 22px;
      box-shadow: var(--shadow);
    }

    .card-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 18px;
    }

    .card-titulo { font-size: 16px; font-weight: 700; color: var(--text-dark); }

    /* === PROJETOS === */
    .proj-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 14px;
    }

    .proj-item {
      border: 1.5px solid var(--border);
      border-radius: 8px;
      padding: 16px;
      background: var(--muted);
      transition: border-color .18s, box-shadow .18s;
      cursor: pointer;
    }

    .proj-item:hover {
      border-color: var(--primary);
      box-shadow: 0 4px 16px rgba(217,164,4,.15);
    }

    .proj-pais {
      font-size: 11px;
      font-weight: 700;
      color: var(--primary-dk);
      text-transform: uppercase;
      letter-spacing: .6px;
      margin-bottom: 4px;
    }

    .proj-nome {
      font-size: 15px;
      font-weight: 700;
      color: var(--text-dark);
      margin-bottom: 8px;
    }

    /* === BADGES === */
    .badge {
      display: inline-block;
      padding: 3px 10px;
      border-radius: 99px;
      font-size: 11px;
      font-weight: 600;
    }

    .badge-primary { background: var(--muted); color: var(--primary-dk); border: 1px solid var(--border); }
    .badge-muted   { background: #f0e8d6; color: var(--text-mid); }

    /* === PROGRESS === */
    .progress-bg {
      background: var(--muted);
      border-radius: 99px;
      height: 8px;
      overflow: hidden;
      margin: 8px 0;
    }

    .progress-bar {
      height: 100%;
      background: linear-gradient(90deg, var(--primary), var(--accent));
      border-radius: 99px;
      transition: width .5s ease;
    }

    /* === BOTÕES === */
    .btn {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 10px 20px;
      border-radius: 8px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      transition: all .18s;
      border: none;
      text-decoration: none;
    }

    .btn-primary { background: var(--primary); color: #fff; }
    .btn-primary:hover { background: var(--primary-dk); color: #fff; }

    .btn-outline {
      background: transparent;
      color: var(--text-mid);
      border: 1.5px solid var(--border);
    }
    .btn-outline:hover { border-color: var(--primary); color: var(--primary-dk); }

    .btn-white { background: #fff; color: var(--primary-dk); font-weight: 700; }
    .btn-white:hover { background: #fffbf0; color: var(--primary-dk); }

    /* === FLASH === */
    .flash-ok {
      background: #d4edda;
      border-left: 4px solid #28a745;
      color: #155724;
      padding: 12px 16px;
      border-radius: 8px;
      margin-bottom: 20px;
      font-size: 14px;
    }

    /* === EVENTO === */
    .evento-box {
      background: var(--muted);
      border-radius: 8px;
      padding: 14px;
    }

    .evento-pais { font-size: 11px; font-weight: 700; color: var(--primary-dk); text-transform: uppercase; letter-spacing: .6px; margin-bottom: 4px; }
    .evento-nome { font-weight: 600; font-size: 14px; color: var(--text-dark); margin-bottom: 6px; }
    .evento-data { font-size: 12px; color: var(--text-mid); }

    /* === ALERTA === */
    .alerta-passaporte {
      background: #FFFBEB;
      border: 1px solid #FDE68A;
      border-radius: 8px;
      padding: 16px;
    }

    /* === VAZIO === */
    .empty-state { text-align: center; padding: 48px 20px; }
    .empty-icon  { font-size: 52px; margin-bottom: 14px; }

    @media (max-width: 1024px) {
      .stats { grid-template-columns: repeat(2, 1fr); }
    }

    @media (max-width: 768px) {
      .sidebar { width: 0; overflow: hidden; }
      .main { margin-left: 0; }
    }
  </style>
</head>
<body>
<div class="layout">

  <!-- ===== SIDEBAR ===== -->
  <aside class="sidebar">
    <div class="sidebar-logo">
      <div class="sidebar-logo-icon">✈️</div>
      <span class="sidebar-logo-text">NextStep</span>
    </div>

    <div class="sidebar-user">
      <span class="sidebar-user-nome"><?= limpar($u['nome']) ?></span>
      <span class="sidebar-user-perfil"><?= limpar($u['perfil']) ?></span>
    </div>

    <nav class="sidebar-nav">
      <a href="<?= ROOT_URL ?>/usuario/dashboard.php" class="sidebar-link active">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
        Dashboard
      </a>
      <a href="<?= ROOT_URL ?>/usuario/projetos.php" class="sidebar-link">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064"/></svg>
        Meus Projetos
      </a>
      <a href="<?= ROOT_URL ?>/usuario/checklist.php" class="sidebar-link">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
        Checklist
      </a>
      <a href="<?= ROOT_URL ?>/usuario/financeiro.php" class="sidebar-link">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/></svg>
        Financeiro
      </a>
      <a href="<?= ROOT_URL ?>/usuario/paises.php" class="sidebar-link">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z"/></svg>
        Países
      </a>
    </nav>

    <div class="sidebar-footer">
      <a href="<?= ROOT_URL ?>/logout.php" class="sidebar-logout">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
        Sair da conta
      </a>
    </div>
  </aside>

  <!-- ===== MAIN ===== -->
  <main class="main">
    <div class="topbar">
      <span class="topbar-titulo">Dashboard</span>
      <div style="display:flex;align-items:center;gap:12px;">
        <span style="font-size:13px;color:var(--text-light);"><?= date('d/m/Y') ?></span>
        <div class="avatar"><?= strtoupper(substr($u['nome'], 0, 1)) ?></div>
      </div>
    </div>

    <div class="content">

      <?php if ($msg): ?>
        <div class="flash-ok"><?= limpar($msg['texto']) ?></div>
      <?php endif; ?>

      <!-- Boas-vindas -->
      <div class="boas-vindas">
        <div>
          <h2>Olá, <?= limpar($primeiroNome) ?>! ✈️</h2>
          <p>
            <?= $totalProj === 0
              ? 'Crie seu primeiro projeto e comece a planejar seu intercâmbio!'
              : "Você tem <strong>$totalProj</strong> projeto" . ($totalProj > 1 ? 's' : '') . " em andamento." ?>
          </p>
        </div>
        <a href="<?= ROOT_URL ?>/usuario/projetos.php?novo=1" class="btn btn-white">
          + Novo Projeto
        </a>
      </div>

      <!-- Stats -->
      <div class="stats">
        <div class="stat stat-primary">
          <div class="stat-label">Projetos</div>
          <div class="stat-valor"><?= $totalProj ?></div>
          <div class="stat-sub">intercâmbios planejados</div>
        </div>
        <div class="stat stat-accent">
          <div class="stat-label">Checklist</div>
          <div class="stat-valor"><?= $pct ?>%</div>
          <div class="stat-sub"><?= (int)$ck['feitos'] ?>/<?= (int)$ck['total'] ?> itens</div>
        </div>
        <div class="stat">
          <div class="stat-label">Passaporte</div>
          <div class="stat-valor" style="font-size:24px;"><?= $dados['tem_passaporte'] ? '✅' : '❌' ?></div>
          <div class="stat-sub"><?= $dados['tem_passaporte'] ? 'Documento OK' : 'Pendente' ?></div>
        </div>
        <?php if ($fin): ?>
        <div class="stat stat-primary">
          <div class="stat-label">Guardado</div>
          <div class="stat-valor" style="font-size:20px;">R$ <?= number_format($fin['valor_guardado'], 0, ',', '.') ?></div>
          <div class="stat-sub">meta: R$ <?= number_format($fin['meta_total'], 0, ',', '.') ?></div>
        </div>
        <?php else: ?>
        <div class="stat">
          <div class="stat-label">Financeiro</div>
          <div class="stat-valor" style="font-size:24px;">—</div>
          <div class="stat-sub">sem projetos ainda</div>
        </div>
        <?php endif; ?>
      </div>

      <!-- Grid principal -->
      <div style="display:grid;grid-template-columns:1fr 300px;gap:20px;">

        <!-- Projetos -->
        <div class="card">
          <div class="card-header">
            <span class="card-titulo">Meus Projetos</span>
            <a href="<?= ROOT_URL ?>/usuario/projetos.php" class="btn btn-outline" style="padding:7px 14px;font-size:13px;">Ver todos</a>
          </div>

          <?php if (empty($projetos)): ?>
            <div class="empty-state">
              <div class="empty-icon">🌍</div>
              <p style="color:var(--text-mid);font-size:14px;margin-bottom:16px;">Nenhum projeto ainda.</p>
              <a href="<?= ROOT_URL ?>/usuario/projetos.php?novo=1" class="btn btn-primary">Criar projeto</a>
            </div>
          <?php else: ?>
            <div class="proj-grid">
              <?php foreach (array_slice($projetos, 0, 4) as $p): ?>
                <div class="proj-item">
                  <div class="proj-pais"><?= limpar($p['bandeira']) ?> <?= limpar($p['pais_nome']) ?></div>
                  <div class="proj-nome"><?= limpar($p['nome']) ?></div>
                  <div style="display:flex;gap:6px;flex-wrap:wrap;">
                    <span class="badge badge-primary"><?= limpar($p['objetivo']) ?></span>
                    <span class="badge badge-muted"><?= $p['duracao_meses'] ?> meses</span>
                  </div>
                  <?php if ($p['data_inicio']): ?>
                    <div style="font-size:12px;color:var(--text-light);margin-top:8px;">📅 <?= date('d/m/Y', strtotime($p['data_inicio'])) ?></div>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>

        <!-- Lateral -->
        <div style="display:flex;flex-direction:column;gap:16px;">

          <div class="card">
            <div class="card-titulo" style="margin-bottom:14px;">🗓️ Próximo Passo</div>
            <?php if ($prox): ?>
              <div class="evento-box">
                <div class="evento-pais"><?= limpar($prox['bandeira']) ?> <?= limpar($prox['pais']) ?></div>
                <div class="evento-nome"><?= limpar($prox['titulo']) ?></div>
                <div class="evento-data">📅 <?= date('d/m/Y', strtotime($prox['data_prevista'])) ?></div>
              </div>
            <?php else: ?>
              <p style="font-size:13px;color:var(--text-light);text-align:center;padding:12px 0;">Nenhum evento pendente.</p>
            <?php endif; ?>
          </div>

          <?php if ($ck['total'] > 0): ?>
          <div class="card">
            <div class="card-titulo" style="margin-bottom:12px;">📋 Progresso</div>
            <div style="display:flex;justify-content:space-between;font-size:13px;">
              <span style="color:var(--text-mid);"><?= (int)$ck['feitos'] ?> concluídos</span>
              <strong><?= $pct ?>%</strong>
            </div>
            <div class="progress-bg">
              <div class="progress-bar" style="width:<?= $pct ?>%;"></div>
            </div>
            <div style="font-size:12px;color:var(--text-light);margin-top:4px;"><?= (int)$ck['total'] - (int)$ck['feitos'] ?> itens pendentes</div>
            <a href="<?= ROOT_URL ?>/usuario/checklist.php" class="btn btn-outline" style="display:block;text-align:center;margin-top:12px;padding:8px;">Ver checklist</a>
          </div>
          <?php endif; ?>

          <?php if (!$dados['tem_passaporte']): ?>
          <div class="alerta-passaporte">
            <div style="font-size:14px;font-weight:700;color:#92400E;margin-bottom:6px;">⚠️ Passaporte Pendente</div>
            <p style="font-size:13px;color:#78350F;line-height:1.5;">Agende na Polícia Federal com antecedência!</p>
          </div>
          <?php endif; ?>

        </div>
      </div>

    </div>
  </main>
</div>
</body>
</html>
