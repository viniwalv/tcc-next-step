<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/funcoes.php';
exigirAdmin();

$u   = usuario();
$pdo = conectar();

$usuarios = $pdo->query(
    'SELECT u.*, p.nome AS perfil
       FROM usuarios u
       JOIN perfis p ON p.id_perfil = u.id_perfil
      ORDER BY u.criado_em DESC'
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Usuários — Admin NextStep</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= ROOT_URL ?>/assets/css/styles.css">
  <style>
    body { background: #FFF8E7; }
    .ns-layout { display: flex; min-height: 100vh; }
    .ns-sidebar { width: 250px; background: #1a1208; display: flex; flex-direction: column; position: fixed; top: 0; left: 0; height: 100vh; z-index: 100; }
    .ns-sidebar__logo { display: flex; align-items: center; gap: 10px; padding: 24px 20px 20px; border-bottom: 1px solid rgba(255,255,255,.08); font-family: 'Playfair Display', serif; font-size: 20px; font-weight: 700; color: #fff; }
    .ns-sidebar__logo svg { width: 24px; height: 24px; }
    .ns-sidebar__user { padding: 16px 20px; border-bottom: 1px solid rgba(255,255,255,.08); }
    .ns-sidebar__user-nome { display: block; font-size: 14px; font-weight: 600; color: #fff; }
    .ns-sidebar__user-perfil { font-size: 11px; color: #D9A404; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; }
    .ns-sidebar__nav { flex: 1; padding: 12px 0; }
    .ns-sidebar__link { display: flex; align-items: center; gap: 12px; padding: 12px 20px; color: rgba(255,255,255,.6); font-size: 14px; font-weight: 500; text-decoration: none; border-left: 3px solid transparent; transition: all .2s ease; }
    .ns-sidebar__link:hover, .ns-sidebar__link.ativo { color: #fff; background: rgba(255,255,255,.06); border-left-color: #D9A404; }
    .ns-sidebar__link svg { width: 18px; height: 18px; opacity: .65; }
    .ns-sidebar__link.ativo svg, .ns-sidebar__link:hover svg { opacity: 1; }
    .ns-sidebar__footer { padding: 16px 20px; border-top: 1px solid rgba(255,255,255,.08); }
    .ns-sidebar__logout { display: flex; align-items: center; gap: 10px; color: rgba(255,255,255,.4); font-size: 14px; font-weight: 500; text-decoration: none; transition: color .2s; }
    .ns-sidebar__logout:hover { color: #e74c3c; }
    .ns-main { margin-left: 250px; flex: 1; }
    .ns-topbar { background: #FFFBF0; border-bottom: 1px solid #e8dcc4; padding: 0 28px; height: 60px; display: flex; align-items: center; justify-content: space-between; }
    .ns-topbar__titulo { font-family: 'Playfair Display', serif; font-size: 18px; font-weight: 700; color: #2b2318; }
    .ns-content { padding: 28px; }
    .ns-card { background: #FFFBF0; border: 1px solid #e8dcc4; border-radius: 10px; padding: 22px; }
    table { width: 100%; border-collapse: collapse; font-size: 14px; }
    th { background: #FFF4D4; padding: 12px 16px; text-align: left; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #7a6a52; border-bottom: 2px solid #e8dcc4; }
    td { padding: 13px 16px; border-bottom: 1px solid #f0e8d6; color: #2b2318; }
    tbody tr:hover { background: #FFFBF2; }
    .badge-admin { background: #1a1208; color: #D9A404; padding: 3px 10px; border-radius: 99px; font-size: 11px; font-weight: 700; }
    .badge-usuario { background: #FFF4D4; color: #C98A12; padding: 3px 10px; border-radius: 99px; font-size: 11px; font-weight: 700; }
    .ns-avatar { width: 36px; height: 36px; background: #1a1208; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #D9A404; font-weight: 700; font-size: 15px; }
  </style>
</head>
<body>
<div class="ns-layout">
  <aside class="ns-sidebar">
    <div class="ns-sidebar__logo">
      <svg viewBox="0 0 24 24" fill="#D9A404"><path d="M22 16.5v-2l-8.5-5V4c0-1.1-.9-2-1.5-2s-1.5.9-1.5 2v5.5L2 14.5v2l8.5-2.6V19l-2.5 1.8V22l3.5-1 3.5 1v-1.2L12.5 19v-5.1L22 16.5z"/></svg>
      NextStep
    </div>
    <div class="ns-sidebar__user">
      <span class="ns-sidebar__user-nome"><?= limpar($u['nome']) ?></span>
      <span class="ns-sidebar__user-perfil">⚙️ Admin</span>
    </div>
    <nav class="ns-sidebar__nav">
      <a href="<?= ROOT_URL ?>/admin/dashboard.php" class="ns-sidebar__link">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
        Painel Admin
      </a>
      <a href="<?= ROOT_URL ?>/admin/usuarios.php" class="ns-sidebar__link ativo">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
        Usuários
      </a>
      <a href="<?= ROOT_URL ?>/usuario/dashboard.php" class="ns-sidebar__link">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
        Dashboard Usuário
      </a>
    </nav>
    <div class="ns-sidebar__footer">
      <a href="<?= ROOT_URL ?>/logout.php" class="ns-sidebar__logout">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
        Sair
      </a>
    </div>
  </aside>

  <main class="ns-main">
    <div class="ns-topbar">
      <span class="ns-topbar__titulo">👥 Usuários Cadastrados</span>
      <div class="ns-avatar"><?= strtoupper(substr($u['nome'], 0, 1)) ?></div>
    </div>
    <div class="ns-content">
      <div class="ns-card">
        <table>
          <thead>
            <tr>
              <th>#</th>
              <th>Nome</th>
              <th>Email</th>
              <th>Idade</th>
              <th>Cidade</th>
              <th>Perfil</th>
              <th>Passaporte</th>
              <th>Cadastro</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($usuarios as $usr): ?>
              <tr>
                <td style="color:#a3927a;"><?= $usr['id_usuario'] ?></td>
                <td><strong><?= limpar($usr['nome']) ?></strong></td>
                <td style="font-size:13px;color:#7a6a52;"><?= limpar($usr['email']) ?></td>
                <td><?= $usr['idade'] ?></td>
                <td><?= limpar($usr['cidade'] ?? '—') ?>, <?= limpar($usr['estado'] ?? '') ?></td>
                <td><span class="<?= $usr['perfil'] === 'admin' ? 'badge-admin' : 'badge-usuario' ?>"><?= limpar($usr['perfil']) ?></span></td>
                <td><?= $usr['tem_passaporte'] ? '✅' : '❌' ?></td>
                <td style="font-size:12px;color:#a3927a;"><?= date('d/m/Y', strtotime($usr['criado_em'])) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>
</body>
</html>
