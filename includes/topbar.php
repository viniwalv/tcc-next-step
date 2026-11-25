<?php
// Uso: $tabAtiva = 'dashboard'; include ROOT.'/includes/topbar.php';
$__u = usuario();
?>
<header class="topbar">
  <div class="logo">
    <svg viewBox="0 0 24 24" fill="#D9A404"><path d="M22 16.5v-2l-8.5-5V4c0-1.1-.9-2-1.5-2s-1.5.9-1.5 2v5.5L2 14.5v2l8.5-2.6V19l-2.5 1.8V22l3.5-1 3.5 1v-1.2L12.5 19v-5.1L22 16.5z"/></svg>
    NextStep
  </div>
  <div class="topbar-right">
    <div>
      <span class="user-info-nome"><?= limpar($__u['nome']) ?></span>
      <span class="user-info-email"><?= limpar($__u['email']) ?></span>
    </div>
    <a href="<?= ROOT_URL ?>/logout.php" class="btn-sair">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
      Sair
    </a>
  </div>
</header>
<div class="tabs">
  <?php
  $tabs = isPerfil('admin')
    ? [
        ['dashboard','Dashboard','M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6', ROOT_URL.'/admin/dashboard.php'],
        ['usuarios','Usuários','M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z', ROOT_URL.'/admin/usuarios.php'],
        ['projetos_admin','Projetos','M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', ROOT_URL.'/admin/projetos.php'],
      ]
    : [
        ['dashboard','Dashboard','M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6', ROOT_URL.'/usuario/dashboard.php'],
        ['projetos','Projetos','M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', ROOT_URL.'/usuario/projetos.php'],
        ['checklist','Checklist','M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4', ROOT_URL.'/usuario/checklist.php'],
        ['timeline','Timeline','M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', ROOT_URL.'/usuario/timeline.php'],
        ['financeiro','Financeiro','M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', ROOT_URL.'/usuario/financeiro.php'],
        ['paises','Países','M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064', ROOT_URL.'/usuario/paises.php'],
        ['perfil','Perfil','M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z', ROOT_URL.'/usuario/perfil.php'],
      ];
  foreach ($tabs as [$id,$label,$path,$href]):
  ?>
    <a href="<?= $href ?>" class="tab <?= ($tabAtiva??'')===$id?'active':'' ?>">
      <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="<?= $path ?>"/></svg>
      <?= $label ?>
    </a>
  <?php endforeach; ?>
</div>

<script src="<?= ROOT_URL ?>/assets/js/acessibilidade.js" defer></script>
