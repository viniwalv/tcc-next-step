<?php
// ============================================================
//  NextStep — Dashboard do Admin
//  Arquivo: admin/dashboard.php
// ============================================================

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/funcoes.php';
exigirAdmin();

$u   = usuario();
$pdo = conectar();

// Stats gerais
$totalUsuarios = $pdo->query('SELECT COUNT(*) FROM usuarios WHERE id_perfil = 2')->fetchColumn();
$totalProjetos = $pdo->query('SELECT COUNT(*) FROM projetos')->fetchColumn();
$totalPaises   = $pdo->query('SELECT COUNT(DISTINCT id_pais) FROM projetos')->fetchColumn();

// Últimos usuários cadastrados
$ultUsu = $pdo->query(
    'SELECT u.id_usuario, u.nome, u.email, u.idade, u.criado_em, p.nome AS perfil
       FROM usuarios u
       JOIN perfis p ON p.id_perfil = u.id_perfil
      ORDER BY u.criado_em DESC LIMIT 8'
)->fetchAll();

// Projetos recentes
$ultProj = $pdo->query(
    'SELECT pr.nome, pr.duracao_meses, pr.criado_em,
            u.nome AS usuario, pa.nome AS pais, pa.bandeira
       FROM projetos pr
       JOIN usuarios u  ON u.id_usuario = pr.id_usuario
       JOIN paises   pa ON pa.id_pais   = pr.id_pais
      ORDER BY pr.criado_em DESC LIMIT 6'
)->fetchAll();

$msg = getMsg();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin — NextStep</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= ROOT_URL ?>/assets/css/styles.css">
  <style>
    body { background: #FFF8E7; }
    .ns-layout { display: flex; min-height: 100vh; }

    .ns-sidebar {
      width: 250px; background: #1a1208;
      display: flex; flex-direction: column;
      position: fixed; top: 0; left: 0; height: 100vh; z-index: 100;
    }
    .ns-sidebar__logo {
      display: flex; align-items: center; gap: 10px;
      padding: 24px 20px 20px;
      border-bottom: 1px solid rgba(255,255,255,.08);
      font-family: 'Playfair Display', serif;
      font-size: 20px; font-weight: 700; color: #fff;
    }
    .ns-sidebar__logo svg { width: 24px; height: 24px; }
    .ns-sidebar__user {
      padding: 16px 20px;
      border-bottom: 1px solid rgba(255,255,255,.08);
    }
    .ns-sidebar__user-nome { display: block; font-size: 14px; font-weight: 600; color: #fff; }
    .ns-sidebar__user-perfil { font-size: 11px; color: #D9A404; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; }
    .ns-sidebar__nav { flex: 1; padding: 12px 0; }
    .ns-sidebar__section {
      font-size: 10px; font-weight: 600; text-transform: uppercase;
      letter-spacing: 1.2px; color: rgba(255,255,255,.3);
      padding: 12px 20px 4px;
    }
    .ns-sidebar__link {
      display: flex; align-items: center; gap: 12px;
      padding: 12px 20px; color: rgba(255,255,255,.6);
      font-size: 14px; font-weight: 500; text-decoration: none;
      border-left: 3px solid transparent; transition: all .2s ease;
    }
    .ns-sidebar__link:hover, .ns-sidebar__link.ativo {
      color: #fff; background: rgba(255,255,255,.06); border-left-color: #D9A404;
    }
    .ns-sidebar__link svg { width: 18px; height: 18px; opacity: .65; flex-shrink: 0; }
    .ns-sidebar__link.ativo svg, .ns-sidebar__link:hover svg { opacity: 1; }
    .ns-sidebar__footer { padding: 16px 20px; border-top: 1px solid rgba(255,255,255,.08); }
    .ns-sidebar__logout {
      display: flex; align-items: center; gap: 10px;
      color: rgba(255,255,255,.4); font-size: 14px; font-weight: 500;
      text-decoration: none; transition: color .2s;
    }
    .ns-sidebar__logout:hover { color: #e74c3c; }

    .ns-main { margin-left: 250px; flex: 1; display: flex; flex-direction: column; }
    .ns-topbar {
      background: #FFFBF0; border-bottom: 1px solid #e8dcc4;
      padding: 0 28px; height: 60px;
      display: flex; align-items: center; justify-content: space-between;
      position: sticky; top: 0; z-index: 50;
    }
    .ns-topbar__titulo { font-family: 'Playfair Display', serif; font-size: 18px; font-weight: 700; color: #2b2318; }
    .ns-avatar {
      width: 36px; height: 36px; background: #1a1208; border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      color: #D9A404; font-weight: 700; font-size: 15px; font-family: 'Playfair Display', serif;
    }
    .ns-content { padding: 28px; }

    .ns-stats {
      display: grid; grid-template-columns: repeat(3, 1fr);
      gap: 16px; margin-bottom: 24px;
    }
    .ns-stat {
      background: #FFFBF0; border: 1px solid rgba(217,164,4,0.2);
      border-radius: 10px; padding: 20px;
      border-top: 3px solid #D9A404;
    }
    .ns-stat__label { font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: .8px; color: #a3927a; margin-bottom: 8px; }
    .ns-stat__valor { font-family: 'Playfair Display', serif; font-size: 36px; font-weight: 800; color: #2b2318; }
    .ns-stat__sub { font-size: 12px; color: #a3927a; margin-top: 4px; }

    .ns-card { background: #FFFBF0; border: 1px solid rgba(217,164,4,0.2); border-radius: 10px; padding: 22px; }
    .ns-card h3 { font-size: 16px; font-weight: 700; color: #2b2318; margin-bottom: 16px; }

    table { width: 100%; border-collapse: collapse; font-size: 14px; }
    th { background: #FFF4D4; padding: 10px 14px; text-align: left; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #7a6a52; border-bottom: 2px solid #e8dcc4; }
    td { padding: 12px 14px; border-bottom: 1px solid #f0e8d6; color: #2b2318; }
    tbody tr:hover { background: #FFFBF2; }

    .badge-admin { background: #1a1208; color: #D9A404; padding: 2px 8px; border-radius: 99px; font-size: 11px; font-weight: 700; }
    .badge-usuario { background: #FFF4D4; color: #C98A12; padding: 2px 8px; border-radius: 99px; font-size: 11px; font-weight: 700; }
  </style>
</head>
<body>

<div class="ns-layout">

  <aside class="ns-sidebar">
    <div class="ns-sidebar__logo">
      <svg viewBox="0 0 24 24" fill="#E8A020" xmlns="http://www.w3.org/2000/svg">
        <path d="M22 16.5v-2l-8.5-5V4c0-1.1-.9-2-1.5-2s-1.5.9-1.5 2v5.5L2 14.5v2l8.5-2.6V19l-2.5 1.8V22l3.5-1 3.5 1v-1.2L12.5 19v-5.1L22 16.5z"/>
      </svg>
      NextStep
    </div>

    <div class="ns-sidebar__user">
      <span class="ns-sidebar__user-nome"><?= limpar($u['nome']) ?></span>
      <span class="ns-sidebar__user-perfil">⚙️ Admin</span>
    </div>

    <nav class="ns-sidebar__nav">
      <span class="ns-sidebar__section">Administração</span>
      <a href="<?= ROOT_URL ?>/admin/dashboard.php" class="ns-sidebar__link ativo">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
        </svg>
        Painel Admin
      </a>
      <a href="<?= ROOT_URL ?>/admin/usuarios.php" class="ns-sidebar__link">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
        </svg>
        Usuários
      </a>

      <span class="ns-sidebar__section" style="margin-top:8px;">Área do Usuário</span>
      <a href="<?= ROOT_URL ?>/usuario/dashboard.php" class="ns-sidebar__link">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
        </svg>
        Dashboard Usuário
      </a>
    </nav>

    <div class="ns-sidebar__footer">
      <a href="<?= ROOT_URL ?>/logout.php" class="ns-sidebar__logout">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
        </svg>
        Sair
      </a>
    </div>
  </aside>

  <main class="ns-main">
    <div class="ns-topbar">
      <span class="ns-topbar__titulo">⚙️ Painel Administrativo</span>
      <div style="display:flex;align-items:center;gap:12px;">
        <span style="font-size:13px;color:#a3927a;"><?= date('d/m/Y') ?></span>
        <div class="ns-avatar"><?= strtoupper(substr($u['nome'], 0, 1)) ?></div>
      </div>
    </div>

    <div class="ns-content">

      <?php if ($msg): ?>
        <div style="background:#d4edda;border-left:4px solid #28a745;color:#155724;padding:12px 16px;border-radius:6px;margin-bottom:20px;font-size:14px;">
          <?= limpar($msg['texto']) ?>
        </div>
      <?php endif; ?>

      <!-- Stats -->
      <div class="ns-stats">
        <div class="ns-stat">
          <div class="ns-stat__label">Usuários</div>
          <div class="ns-stat__valor"><?= $totalUsuarios ?></div>
          <div class="ns-stat__sub">contas cadastradas</div>
        </div>
        <div class="ns-stat">
          <div class="ns-stat__label">Projetos</div>
          <div class="ns-stat__valor"><?= $totalProjetos ?></div>
          <div class="ns-stat__sub">intercâmbios criados</div>
        </div>
        <div class="ns-stat">
          <div class="ns-stat__label">Países</div>
          <div class="ns-stat__valor"><?= $totalPaises ?></div>
          <div class="ns-stat__sub">destinos escolhidos</div>
        </div>
      </div>

      <!-- Grid: usuários + projetos -->
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

        <div class="ns-card">
          <h3>👥 Últimos Usuários</h3>
          <table>
            <thead>
              <tr>
                <th>Nome</th>
                <th>Email</th>
                <th>Perfil</th>
                <th>Cadastro</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($ultUsu as $usr): ?>
                <tr>
                  <td><?= limpar($usr['nome']) ?></td>
                  <td style="font-size:13px;color:#7a6a52;"><?= limpar($usr['email']) ?></td>
                  <td>
                    <span class="<?= $usr['perfil'] === 'admin' ? 'badge-admin' : 'badge-usuario' ?>">
                      <?= limpar($usr['perfil']) ?>
                    </span>
                  </td>
                  <td style="font-size:12px;color:#a3927a;">
                    <?= date('d/m/Y', strtotime($usr['criado_em'])) ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <div class="ns-card">
          <h3>📋 Projetos Recentes</h3>
          <table>
            <thead>
              <tr>
                <th>Projeto</th>
                <th>Usuário</th>
                <th>Destino</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($ultProj as $pj): ?>
                <tr>
                  <td><?= limpar($pj['nome']) ?></td>
                  <td style="font-size:13px;color:#7a6a52;"><?= limpar($pj['usuario']) ?></td>
                  <td><?= limpar($pj['bandeira']) ?> <?= limpar($pj['pais']) ?></td>
                </tr>
              <?php endforeach; ?>
              <?php if (empty($ultProj)): ?>
                <tr><td colspan="3" style="text-align:center;color:#a3927a;padding:20px;">Nenhum projeto ainda.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

      </div>
    </div>
  </main>
</div>

</body>
</html>
