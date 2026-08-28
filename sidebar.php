<?php
// ============================================================
//  NextStep — Sidebar (incluída nos dashboards)
//  Uso: include __DIR__ . '/../includes/sidebar.php';
//  Requer: $paginaAtiva (string) definida antes do include
// ============================================================
$u = dadosUsuario();
$paginaAtiva = $paginaAtiva ?? '';
?>
<aside class="ns-sidebar">

  <div class="ns-sidebar__logo">
    <div class="ns-sidebar__logo-icon">✈️</div>
    <div>
      <span class="ns-sidebar__logo-text">NextStep</span>
      <span class="ns-sidebar__logo-sub">Planner de Intercâmbio</span>
    </div>
  </div>

  <div class="ns-sidebar__user">
    <span class="ns-sidebar__user-nome"><?= limpar($u['nome']) ?></span>
    <span class="ns-sidebar__user-perfil"><?= limpar($u['perfil']) ?></span>
  </div>

  <nav class="ns-sidebar__nav">

    <?php if ($u['perfil'] === 'admin'): ?>

      <span class="ns-sidebar__section-label">Administração</span>

      <a href="/nextstep/admin/dashboard.php"
         class="ns-sidebar__link <?= $paginaAtiva === 'admin-home' ? 'ativo' : '' ?>">
        <svg class="icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7m-9 5v6h4v-6"/>
        </svg>
        Painel Admin
      </a>

      <a href="/nextstep/admin/usuarios.php"
         class="ns-sidebar__link <?= $paginaAtiva === 'admin-usuarios' ? 'ativo' : '' ?>">
        <svg class="icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-5-3.87M9 20H4v-2a4 4 0 015-3.87M12 12a4 4 0 100-8 4 4 0 000 8z"/>
        </svg>
        Usuários
      </a>

      <a href="/nextstep/admin/projetos.php"
         class="ns-sidebar__link <?= $paginaAtiva === 'admin-projetos' ? 'ativo' : '' ?>">
        <svg class="icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2a4 4 0 014-4h2m4 0V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2h7"/>
        </svg>
        Projetos
      </a>

      <span class="ns-sidebar__section-label" style="margin-top:8px;">Acesso de Usuário</span>

    <?php endif; ?>

    <a href="/nextstep/usuario/dashboard.php"
       class="ns-sidebar__link <?= $paginaAtiva === 'home' ? 'ativo' : '' ?>">
      <svg class="icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
      </svg>
      Dashboard
    </a>

    <a href="/nextstep/usuario/projetos.php"
       class="ns-sidebar__link <?= $paginaAtiva === 'projetos' ? 'ativo' : '' ?>">
      <svg class="icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064"/>
      </svg>
      Meus Projetos
    </a>

    <a href="/nextstep/usuario/checklist.php"
       class="ns-sidebar__link <?= $paginaAtiva === 'checklist' ? 'ativo' : '' ?>">
      <svg class="icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
      </svg>
      Checklist
    </a>

    <a href="/nextstep/usuario/financeiro.php"
       class="ns-sidebar__link <?= $paginaAtiva === 'financeiro' ? 'ativo' : '' ?>">
      <svg class="icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      Financeiro
    </a>

    <a href="/nextstep/usuario/paises.php"
       class="ns-sidebar__link <?= $paginaAtiva === 'paises' ? 'ativo' : '' ?>">
      <svg class="icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064"/>
      </svg>
      Países &amp; Destinos
    </a>

  </nav>

  <div class="ns-sidebar__footer">
    <a href="/nextstep/logout.php" class="ns-sidebar__logout">
      <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
      </svg>
      Sair da conta
    </a>
  </div>

</aside>