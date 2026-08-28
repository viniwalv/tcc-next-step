<?php
// ============================================================
//  NextStep — Página Inicial (Landing Page)
//  Arquivo: index.php
// ============================================================

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/funcoes.php';

// Se já está logado, vai direto pro dashboard
if (usuarioLogado()) {
    redirecionarPorPerfil();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>NextStep - Planeje seu intercâmbio</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= ROOT_URL ?>/assets/css/styles.css">
</head>
<body>

  <!-- ===== NAVBAR ===== -->
  <header class="navbar">
    <div class="logo">
      <svg viewBox="0 0 24 24" fill="#D9A404" xmlns="http://www.w3.org/2000/svg">
        <path d="M22 16.5v-2l-8.5-5V4c0-1.1-.9-2-1.5-2s-1.5.9-1.5 2v5.5L2 14.5v2l8.5-2.6V19l-2.5 1.8V22l3.5-1 3.5 1v-1.2L12.5 19v-5.1L22 16.5z"/>
      </svg>
      NextStep
    </div>

    <nav class="nav-links">
      <a href="#como-funciona">Como Funciona</a>
      <a href="#recursos">Recursos</a>
      <a href="#precos">Preços</a>
    </nav>

    <div class="nav-actions">
      <a href="<?= ROOT_URL ?>/login.php" class="login-link">Login</a>
      <a href="<?= ROOT_URL ?>/cadastro.php" class="btn-primary">Começar Grátis</a>
    </div>
  </header>

  <!-- ===== HERO ===== -->
  <section class="hero">
    <div class="hero-content">
      <h1>Planeje seu intercâmbio do zero ao embarque</h1>
      <p>Organize documentos, planeje financeiramente e conquiste seu sonho de morar fora. Tudo em um só lugar, de forma simples e prática.</p>

      <div class="hero-buttons">
        <a href="<?= ROOT_URL ?>/cadastro.php" class="btn-white">Criar Conta Grátis &rarr;</a>
        <a href="#como-funciona" class="btn-outline">Saiba Mais</a>
      </div>

      <div class="hero-checks">
        <span>&#10003; Sem cartão de crédito</span>
        <span>&#10003; Comece agora mesmo</span>
      </div>
    </div>

    <div class="hero-image tilt">
      <img src="https://images.pexels.com/photos/1098365/pexels-photo-1098365.jpeg" alt="Vista da asa do avião no céu">
    </div>
  </section>

  <!-- ===== COMO FUNCIONA ===== -->
  <section class="how-it-works" id="como-funciona">
    <div class="section-header">
      <h2>Como Funciona</h2>
      <p>"Quero morar fora, mas não sei por onde começar."<br>Deixa com a gente! 🚀</p>
    </div>

    <div class="steps-wrap">
      <svg class="flight-path" viewBox="0 0 1200 120" preserveAspectRatio="none" aria-hidden="true">
        <path id="flightPathLine" d="M 60 100 C 300 -20, 450 220, 600 60 S 900 -20, 1140 60" />
        <g class="flight-plane">
          <path d="M2 12l19-9-7 9 7 9-19-9z" transform="scale(1.4)"/>
        </g>
      </svg>

      <div class="steps-grid">
        <div class="step-card reveal">
          <div class="step-icon">◎</div>
          <h3>1. Crie seu Projeto</h3>
          <p>Escolha país, cidade e objetivo do intercâmbio</p>
        </div>
        <div class="step-card reveal">
          <div class="step-icon">✓</div>
          <h3>2. Checklist Inteligente</h3>
          <p>Receba lista personalizada de documentos e tarefas</p>
        </div>
        <div class="step-card reveal">
          <div class="step-icon">$</div>
          <h3>3. Planeje Financeiro</h3>
          <p>Calcule custos e acompanhe suas economias</p>
        </div>
        <div class="step-card reveal">
          <div class="step-icon">📅</div>
          <h3>4. Linha do Tempo</h3>
          <p>Organize tudo por datas e não perca prazos</p>
        </div>
      </div>
    </div>
  </section>

  <!-- ===== RECURSOS GRATUITOS ===== -->
  <section class="feature-split alt" id="recursos">
    <div class="section-header" style="text-align:center; max-width:700px; margin:0 auto 60px;">
      <h2>Tudo que você precisa em um só lugar</h2>
      <p>Pare de usar Notion + Excel + YouTube + Google Docs</p>
    </div>

    <div class="feature-row">
      <div class="feature-media tilt">
        <img src="https://images.pexels.com/photos/207691/pexels-photo-207691.jpeg" alt="Planejamento de intercâmbio">
      </div>

      <div class="feature-list">
        <h2 style="color:#BF9004;">Recursos Gratuitos</h2>

        <div class="feature-item reveal">
          <div class="icon">✓</div>
          <div>
            <h4>1 Projeto de Intercâmbio</h4>
            <p>Planeje sua viagem completa</p>
          </div>
        </div>

        <div class="feature-item reveal">
          <div class="icon">✓</div>
          <div>
            <h4>Checklist Básico</h4>
            <p>Documentos essenciais por país</p>
          </div>
        </div>

        <div class="feature-item reveal">
          <div class="icon">✓</div>
          <div>
            <h4>Planejamento Financeiro</h4>
            <p>Controle de gastos e economias</p>
          </div>
        </div>

        <div class="feature-item reveal">
          <div class="icon">✓</div>
          <div>
            <h4>Linha do Tempo</h4>
            <p>Organize por datas importantes</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ===== RECURSOS PREMIUM ===== -->
  <section class="feature-split">
    <div class="feature-row reverse">
      <div class="feature-media tilt">
        <img src="https://images.pexels.com/photos/2325446/pexels-photo-2325446.jpeg" alt="Mapa com destinos">
      </div>

      <div class="feature-list">
        <span class="badge">PREMIUM</span>
        <h2>Recursos Premium</h2>

        <div class="feature-item reveal">
          <div class="icon">🛡</div>
          <div>
            <h4>Projetos Ilimitados</h4>
            <p>Compare vários destinos</p>
          </div>
        </div>

        <div class="feature-item reveal">
          <div class="icon">$</div>
          <div>
            <h4>Simulador de Custo Real</h4>
            <p>Custos detalhados por cidade</p>
          </div>
        </div>

        <div class="feature-item reveal">
          <div class="icon">🌐</div>
          <div>
            <h4>Comparador de Países</h4>
            <p>Visto, custos e oportunidades</p>
          </div>
        </div>

        <div class="feature-item reveal">
          <div class="icon">✓</div>
          <div>
            <h4>Modelos de Documentos</h4>
            <p>Carta de intenção, CV e mais</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ===== PREÇOS ===== -->
  <section class="pricing" id="precos">
    <div class="section-header">
      <h2>Planos Simples e Transparentes</h2>
      <p>Comece grátis, faça upgrade quando precisar</p>
    </div>

    <div class="pricing-grid">

      <!-- Plano Grátis -->
      <div class="ticket" tabindex="0" role="button" aria-pressed="false" aria-label="Plano Grátis">
        <div class="ticket-inner">
          <div class="ticket-face ticket-front">
            <div class="ticket-top-row">
              <h3>Grátis</h3>
              <span class="ticket-plane-icon">✈</span>
            </div>
            <div class="price">R$ 0<span>/sempre</span></div>
            <div class="ticket-divider"></div>
            <ul class="price-features">
              <li>✓ 1 projeto de intercâmbio</li>
              <li>✓ Checklist básico</li>
              <li>✓ Planejamento financeiro</li>
              <li>✓ Linha do tempo</li>
            </ul>
            <a href="<?= ROOT_URL ?>/cadastro.php" class="price-cta free">Começar Grátis</a>
            <span class="ticket-hint">toque no bilhete ✦</span>
          </div>
          <div class="ticket-face ticket-back">
            <p class="ticket-back-title">Cartão de Embarque</p>
            <div class="ticket-back-row"><span>Passageiro</span><strong>Você</strong></div>
            <div class="ticket-back-row"><span>Destino</span><strong>Onde quiser</strong></div>
            <div class="ticket-back-row"><span>Classe</span><strong>Grátis</strong></div>
            <div class="ticket-barcode" aria-hidden="true"></div>
            <span class="ticket-hint">toque para voltar</span>
          </div>
        </div>
      </div>

      <!-- Plano Premium -->
      <div class="ticket popular" tabindex="0" role="button" aria-pressed="false" aria-label="Plano Premium">
        <span class="popular-tag">Mais Popular</span>
        <div class="ticket-inner">
          <div class="ticket-face ticket-front">
            <div class="ticket-top-row">
              <h3>Premium</h3>
              <span class="ticket-plane-icon">✈</span>
            </div>
            <div class="price">R$ 29<span>/mês</span></div>
            <div class="ticket-divider"></div>
            <ul class="price-features">
              <li>✓ Projetos ilimitados</li>
              <li>✓ Simulador de custos real</li>
              <li>✓ Comparador de países</li>
              <li>✓ Modelos de documentos</li>
              <li>✓ Alertas inteligentes</li>
              <li>✓ Histórico de versões</li>
            </ul>
            <a href="<?= ROOT_URL ?>/cadastro.php" class="price-cta premium">Começar Premium</a>
            <span class="ticket-hint">toque no bilhete ✦</span>
          </div>
          <div class="ticket-face ticket-back">
            <p class="ticket-back-title">Cartão de Embarque</p>
            <div class="ticket-back-row"><span>Passageiro</span><strong>Você</strong></div>
            <div class="ticket-back-row"><span>Destino</span><strong>Qualquer lugar</strong></div>
            <div class="ticket-back-row"><span>Classe</span><strong>Premium</strong></div>
            <div class="ticket-barcode" aria-hidden="true"></div>
            <span class="ticket-hint">toque para voltar</span>
          </div>
        </div>
      </div>

    </div>
  </section>

  <!-- ===== CTA FINAL ===== -->
  <section class="cta-final">
    <h2>Pronto para realizar seu sonho?</h2>
    <p>Junte-se a centenas de estudantes que já estão planejando seu intercâmbio conosco</p>
    <a href="<?= ROOT_URL ?>/cadastro.php" class="btn-white js-confetti-btn">Criar Conta Grátis &rarr;</a>
  </section>

  <!-- ===== FOOTER ===== -->
  <footer class="footer">
    <div class="logo">
      <svg viewBox="0 0 24 24" fill="#D9A404" xmlns="http://www.w3.org/2000/svg" width="24" height="24">
        <path d="M22 16.5v-2l-8.5-5V4c0-1.1-.9-2-1.5-2s-1.5.9-1.5 2v5.5L2 14.5v2l8.5-2.6V19l-2.5 1.8V22l3.5-1 3.5 1v-1.2L12.5 19v-5.1L22 16.5z"/>
      </svg>
      NextStep
    </div>
    <p>Planeje seu intercâmbio do zero ao embarque</p>
    <p class="copyright">© 2026 NextStep · TCC Ensino Médio Técnico em Informática — UNASP Campinas</p>
  </footer>

  <script>
    // ===== Bilhetes interativos (flip) =====
    document.querySelectorAll('.ticket').forEach(ticket => {
      ticket.addEventListener('click', () => {
        ticket.classList.toggle('is-flipped');
        ticket.setAttribute('aria-pressed', ticket.classList.contains('is-flipped'));
      });
    });

    // ===== Confetti de aviõezinhos =====
    document.querySelectorAll('.js-confetti-btn').forEach(btn => {
      btn.addEventListener('click', (e) => {
        for (let i = 0; i < 12; i++) {
          const plane = document.createElement('span');
          plane.textContent = '✈';
          plane.className = 'confetti-plane';
          plane.style.left = e.clientX + 'px';
          plane.style.top  = e.clientY + 'px';
          const angle = Math.random() * 360;
          const dist  = 80 + Math.random() * 120;
          plane.style.setProperty('--dx', Math.cos(angle * Math.PI / 180) * dist + 'px');
          plane.style.setProperty('--dy', Math.sin(angle * Math.PI / 180) * dist + 'px');
          plane.style.setProperty('--rot', (Math.random() * 360) + 'deg');
          document.body.appendChild(plane);
          setTimeout(() => plane.remove(), 900);
        }
      });
    });

    // ===== Reveal on scroll =====
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(e => {
        if (e.isIntersecting) {
          e.target.classList.add('is-visible');
          observer.unobserve(e.target);
        }
      });
    }, { threshold: 0.15 });

    document.querySelectorAll('.reveal').forEach(el => observer.observe(el));

    // ===== Animação avião na trilha =====
    const stepsWrap = document.querySelector('.steps-wrap');
    if (stepsWrap) {
      const wrapObserver = new IntersectionObserver((entries) => {
        entries.forEach(e => {
          if (e.isIntersecting) {
            stepsWrap.classList.add('in-view');
            wrapObserver.unobserve(stepsWrap);
          }
        });
      }, { threshold: 0.3 });
      wrapObserver.observe(stepsWrap);
    }

    // ===== Tilt 3D nas imagens =====
    document.querySelectorAll('.tilt').forEach(el => {
      el.addEventListener('mousemove', (e) => {
        const rect = el.getBoundingClientRect();
        const x = (e.clientX - rect.left) / rect.width  - 0.5;
        const y = (e.clientY - rect.top)  / rect.height - 0.5;
        el.style.transform = `perspective(800px) rotateY(${x * 10}deg) rotateX(${-y * 10}deg)`;
      });
      el.addEventListener('mouseleave', () => {
        el.style.transform = '';
      });
    });
  </script>

</body>
</html>
