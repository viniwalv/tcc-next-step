<?php
require_once __DIR__.'/config/config.php';
require_once __DIR__.'/includes/funcoes.php';
if(usuarioLogado()) redirecionarPorPerfil();
?><!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>NextStep - Planeje seu intercâmbio</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700;800&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Poppins',sans-serif;background-color:#FDF8EE;color:#4a3f2e;cursor:url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='26' height='26' viewBox='0 0 24 24'><path d='M2 12l19-9-7 9 7 9-19-9z' fill='%23C98A12' stroke='%234a3a12' stroke-width='0.6'/></svg>") 2 12,auto;}
h1,h2,h3,.logo{font-family:'Playfair Display','Poppins',serif;}
a,button,.ticket,.price-cta,.btn-white,.btn-outline,.btn-primary{cursor:url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='26' height='26' viewBox='0 0 24 24'><path d='M2 12l19-9-7 9 7 9-19-9z' fill='%23E8A020' stroke='%234a3a12' stroke-width='0.6'/></svg>") 2 12,pointer;}

/* NAVBAR */
.navbar{display:flex;align-items:center;justify-content:space-between;padding:16px 60px;background-color:#FDF8EE;border-bottom:1px solid #e8dcc4;}
.logo{display:flex;align-items:center;gap:10px;font-size:23px;font-weight:700;color:#2b2318;letter-spacing:0.3px;text-decoration:none;}
.logo svg{width:26px;height:26px;}
.nav-links{display:flex;align-items:center;gap:34px;}
.nav-links a{text-decoration:none;color:#5a4d38;font-size:15px;font-weight:500;transition:color 0.2s;}
.nav-links a:hover{color:#E8A020;}
.nav-actions{display:flex;align-items:center;gap:24px;}
.login-link{text-decoration:none;color:#2b2318;font-weight:600;font-size:15px;}
.btn-primary{background-color:#E8A020;color:#fff;border:none;padding:12px 24px;border-radius:6px;font-weight:600;font-size:15px;cursor:pointer;text-decoration:none;display:inline-block;transition:transform .2s,box-shadow .2s,filter .2s;}
.btn-primary:hover{transform:translateY(-3px);filter:brightness(1.05);box-shadow:0 10px 18px rgba(232,160,32,.35);}

/* HERO */
.hero{background:linear-gradient(135deg,#D9A017 0%,#E8B923 45%,#FBDB6E 100%);padding:70px 60px;display:flex;align-items:center;justify-content:space-between;gap:40px;flex-wrap:wrap;position:relative;overflow:hidden;}
.hero-content{max-width:550px;}
.hero-content h1{font-size:48px;font-weight:800;color:#fff;line-height:1.2;margin-bottom:24px;animation:heroRise .8s ease .05s both;}
.hero-content p{font-size:17px;color:#fff8ec;line-height:1.6;margin-bottom:20px;max-width:480px;animation:heroRise .8s ease .15s both;}
.hero-buttons{display:flex;gap:16px;margin-bottom:18px;animation:heroRise .8s ease .25s both;}
.hero-checks{display:flex;gap:18px;flex-wrap:wrap;animation:heroRise .8s ease .35s both;}
.hero-checks span{display:flex;align-items:center;gap:8px;color:#fff;font-size:14px;}
.hero-image{flex:1;max-width:550px;min-width:320px;animation:heroFadeScale .9s ease .2s both;}
.hero-image img{width:100%;height:auto;display:block;border-radius:10px;box-shadow:0 20px 40px rgba(0,0,0,.2);}
.btn-white{background-color:#FFFDF7;color:#C98A12;border:none;padding:14px 28px;border-radius:6px;font-weight:700;font-size:15px;display:inline-flex;align-items:center;gap:8px;text-decoration:none;transition:transform .2s,box-shadow .2s;}
.btn-white:hover{transform:translateY(-3px);box-shadow:0 10px 20px rgba(0,0,0,.15);}
.btn-outline{background-color:transparent;color:#fff;border:2px solid rgba(255,255,255,.7);padding:12px 28px;border-radius:6px;font-weight:600;font-size:15px;text-decoration:none;display:inline-block;transition:transform .2s,background .2s;}
.btn-outline:hover{transform:translateY(-3px);background-color:rgba(255,255,255,.12);}

@keyframes heroRise{from{opacity:0;transform:translateY(18px);}to{opacity:1;transform:translateY(0);}}
@keyframes heroFadeScale{from{opacity:0;transform:scale(.94);}to{opacity:1;transform:scale(1);}}

/* SECTIONS */
section{padding:64px 60px;}
.section-header{text-align:center;max-width:700px;margin:0 auto 40px;}
.section-header h2{font-size:34px;font-weight:800;color:#2b2318;margin-bottom:16px;}
.section-header p{font-size:17px;color:#7a6a52;line-height:1.6;}

/* COMO FUNCIONA */
.how-it-works{background-color:#FFFDF7;}
.steps-wrap{position:relative;}
.steps-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:24px;max-width:1200px;margin:0 auto;}
.step-card{border:1px solid #e8dcc4;border-radius:10px;padding:26px 22px;text-align:center;transition:transform .25s,box-shadow .25s,border-color .25s;}
.step-card:hover{transform:translateY(-8px);box-shadow:0 16px 30px rgba(232,160,32,.18);border-color:#F2C338;}
.step-icon{width:56px;height:56px;border-radius:50%;background-color:#FDF3DA;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:22px;color:#E8A020;transition:transform .3s;}
.step-card:hover .step-icon{transform:scale(1.15) rotate(-8deg);}
.step-card h3{font-size:18px;font-weight:700;color:#C98A12;margin-bottom:12px;}
.step-card p{font-size:14.5px;color:#7a6a52;line-height:1.6;}

/* Trilha de voo */
.flight-path{position:absolute;top:-46px;left:0;width:100%;height:90px;overflow:visible;pointer-events:none;}
.flight-path path#flightPathLine{fill:none;stroke:#F2C338;stroke-width:2;stroke-dasharray:8 10;stroke-linecap:round;opacity:.55;}
.flight-path .flight-plane{fill:#E8A020;offset-path:path("M 60 100 C 300 -20, 450 220, 600 60 S 900 -20, 1140 60");offset-distance:0%;offset-rotate:auto;opacity:0;}
.steps-wrap.in-view .flight-plane{animation:flyAlongPath 2.6s ease-in-out .2s forwards;}
@keyframes flyAlongPath{0%{opacity:0;offset-distance:0%;}8%{opacity:1;}92%{opacity:1;}100%{opacity:0;offset-distance:100%;}}

/* FEATURE SPLIT */
.feature-split{background-color:#FFFDF7;}
.feature-split.alt{background-color:#FBF4E4;}
.feature-row{display:flex;align-items:center;gap:40px;max-width:1200px;margin:0 auto;flex-wrap:wrap;}
.feature-row.reverse{flex-direction:row-reverse;}
.feature-media{flex:1;min-width:320px;}
.feature-media img{width:100%;height:auto;border-radius:10px;display:block;}
.feature-list{flex:1;min-width:300px;}
.badge-tag{display:inline-block;border:1.5px solid #E8A020;color:#C98A12;font-size:12px;font-weight:700;letter-spacing:.5px;padding:6px 14px;border-radius:4px;margin-bottom:14px;transform:rotate(-2deg);}
.feature-list h2{font-size:30px;font-weight:800;color:#2b2318;margin-bottom:20px;}
.feature-item{display:flex;align-items:flex-start;gap:16px;margin-bottom:16px;}
.feature-item .icon{width:40px;height:40px;flex-shrink:0;border-radius:10px;display:flex;align-items:center;justify-content:center;color:#E8A020;font-size:18px;}
.feature-item h4{font-size:16px;font-weight:700;color:#2b2318;margin-bottom:4px;}
.feature-item p{font-size:14.5px;color:#7a6a52;}

/* TILT */
.tilt{transform-style:preserve-3d;perspective:1000px;transition:transform .15s ease-out;}

/* REVEAL */
.reveal{opacity:0;transform:translateY(24px);transition:opacity .7s ease,transform .7s ease;}
.reveal.is-visible{opacity:1;transform:translateY(0);}

/* PRICING — FLIP CARDS */
.pricing{background-color:#FFFDF7;}
.pricing-grid{display:flex;gap:22px;max-width:900px;margin:0 auto;justify-content:center;flex-wrap:wrap;perspective:1600px;}
.ticket{flex:1;min-width:300px;max-width:380px;position:relative;}
.ticket-inner{position:relative;width:100%;min-height:440px;transition:transform .7s cubic-bezier(.4,.2,.2,1);transform-style:preserve-3d;}
.ticket.is-flipped .ticket-inner{transform:rotateY(180deg);}
.ticket:not(.is-flipped):hover .ticket-inner{transform:translateY(-6px) rotateX(2deg);}
.ticket-face{position:absolute;inset:0;backface-visibility:hidden;border-radius:10px;padding:30px 26px;background-color:#FFFBF2;border:1px solid #e8dcc4;box-shadow:0 10px 25px rgba(0,0,0,.06);}
.ticket.popular .ticket-face{border:2px solid #E8A020;}
.ticket-face::before,.ticket-face::after{content:"";position:absolute;top:50%;width:24px;height:24px;background:#FDF8EE;border-radius:50%;transform:translateY(-50%);}
.ticket-face::before{left:-12px;}
.ticket-face::after{right:-12px;}
.ticket-top-row{display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;}
.ticket-top-row h3{font-size:22px;font-weight:700;color:#C98A12;}
.ticket-plane-icon{color:#E8A020;font-size:20px;transition:transform .3s;}
.ticket:hover .ticket-plane-icon{transform:translateX(4px) rotate(8deg);}
.price{font-size:40px;font-weight:800;color:#2b2318;margin-bottom:20px;}
.price span{font-size:16px;font-weight:500;color:#a3927a;}
.ticket-divider{border-top:2px dashed #e8d7a8;margin:20px 0 28px;}
.price-features{list-style:none;margin-bottom:24px;}
.price-features li{display:flex;align-items:center;gap:10px;font-size:15px;color:#C98A12;font-weight:500;margin-bottom:18px;}
.price-cta{display:block;text-align:center;padding:14px;border-radius:6px;font-weight:700;font-size:15px;text-decoration:none;transition:transform .2s,box-shadow .2s;}
.price-cta:hover{transform:translateY(-3px);box-shadow:0 10px 20px rgba(0,0,0,.15);}
.price-cta.free{background-color:#FDF3DA;color:#C98A12;}
.price-cta.premium{background-color:#E8A020;color:#fff;}
.ticket-hint{display:block;text-align:center;font-size:12px;color:#b8934a;margin-top:16px;letter-spacing:.3px;}
.ticket-back{transform:rotateY(180deg);display:flex;flex-direction:column;}
.ticket-back-title{font-size:18px;font-weight:700;color:#2b2318;margin-bottom:24px;text-align:center;}
.ticket-back-row{display:flex;justify-content:space-between;padding:12px 0;border-bottom:1px dashed #e8d7a8;font-size:14.5px;color:#7a6a52;}
.ticket-back-row strong{color:#2b2318;}
.ticket-barcode{margin-top:auto;height:46px;background:repeating-linear-gradient(90deg,#2b2318 0px,#2b2318 2px,transparent 2px,transparent 5px,#2b2318 5px,#2b2318 6px,transparent 6px,transparent 10px);border-radius:4px;opacity:.85;}
.popular-tag{position:absolute;top:-14px;left:50%;transform:translateX(-50%) rotate(-3deg);background-color:#E8A020;color:#fff;font-size:12px;font-weight:700;padding:6px 16px;border-radius:4px;z-index:2;}

/* CTA FINAL */
.cta-final{background:linear-gradient(135deg,#D9A017 0%,#E8B923 50%,#F2C338 100%);text-align:center;padding:70px 60px;}
.cta-final h2{font-size:36px;font-weight:800;color:#fff;margin-bottom:16px;}
.cta-final p{font-size:17px;color:#fffaf0;margin-bottom:24px;}

/* FOOTER */
.footer{background-color:#211a12;text-align:center;padding:44px 24px;}
.footer .logo{color:#fff;justify-content:center;margin-bottom:16px;text-decoration:none;}
.footer p{color:#c4b79c;font-size:15px;margin-bottom:10px;}
.footer .copyright{color:#8a7c63;font-size:13px;margin-top:24px;}

/* CONFETTI */
.confetti-plane{position:fixed;font-size:16px;pointer-events:none;z-index:9997;color:#E8A020;animation:confettiFly .9s ease-out forwards;}
@keyframes confettiFly{0%{opacity:1;transform:translate(0,0) rotate(0deg) scale(1);}100%{opacity:0;transform:translate(var(--dx),var(--dy)) rotate(var(--rot)) scale(.4);}}

/* RESPONSIVE */
@media(max-width:900px){
  .navbar{flex-wrap:wrap;gap:16px;padding:16px 24px;}
  .nav-links{order:3;width:100%;justify-content:center;}
  .hero{padding:60px 24px;text-align:center;justify-content:center;}
  .hero-content{max-width:100%;}
  .hero-buttons,.hero-checks{justify-content:center;}
  .hero-content h1{font-size:34px;}
  .steps-grid{grid-template-columns:1fr 1fr;}
  .feature-row,.feature-row.reverse{flex-direction:column;}
  section{padding:60px 24px;}
  .flight-path{display:none;}
}
@media(max-width:600px){.steps-grid{grid-template-columns:1fr;}}
@media(prefers-reduced-motion:reduce){
  .flight-path .flight-plane,.confetti-plane{display:none!important;}
  a,button,.ticket{cursor:pointer;}
  .hero-content h1,.hero-content p,.hero-buttons,.hero-checks,.hero-image{animation:none!important;opacity:1!important;transform:none!important;}
  .reveal{opacity:1;transform:none;transition:none;}
  .ticket-inner{transition:none;}
}
</style>
</head>
<body>

<!-- NAVBAR -->
<header class="navbar">
  <a href="<?= ROOT_URL ?>/index.php" class="logo">
    <svg viewBox="0 0 24 24" fill="#E8A020"><path d="M22 16.5v-2l-8.5-5V4c0-1.1-.9-2-1.5-2s-1.5.9-1.5 2v5.5L2 14.5v2l8.5-2.6V19l-2.5 1.8V22l3.5-1 3.5 1v-1.2L12.5 19v-5.1L22 16.5z"/></svg>
    NextStep
  </a>
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

<!-- HERO -->
<section class="hero">
  <div class="hero-content">
    <h1>Planeje seu intercâmbio do zero ao embarque</h1>
    <p>Organize documentos, planeje financeiramente e conquiste seu sonho de morar fora. Tudo em um só lugar, de forma simples e prática.</p>
    <div class="hero-buttons">
      <a href="<?= ROOT_URL ?>/cadastro.php" class="btn-white js-confetti-btn">Criar Conta Grátis &rarr;</a>
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

<!-- COMO FUNCIONA -->
<section class="how-it-works" id="como-funciona">
  <div class="section-header">
    <h2>Como Funciona</h2>
    <p>"Quero morar fora, mas não sei por onde começar."<br>Deixa com a gente! 🚀</p>
  </div>
  <div class="steps-wrap">
    <svg class="flight-path" viewBox="0 0 1200 120" preserveAspectRatio="none" aria-hidden="true">
      <path id="flightPathLine" d="M 60 100 C 300 -20, 450 220, 600 60 S 900 -20, 1140 60"/>
      <g class="flight-plane"><path d="M2 12l19-9-7 9 7 9-19-9z" transform="scale(1.4)"/></g>
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

<!-- RECURSOS GRATUITOS -->
<section class="feature-split alt" id="recursos">
  <div class="section-header" style="text-align:center;max-width:700px;margin:0 auto 60px;">
    <h2>Tudo que você precisa em um só lugar</h2>
    <p>Pare de usar Notion + Excel + YouTube + Google Docs</p>
  </div>
  <div class="feature-row">
    <div class="feature-media tilt">
      <img src="https://images.pexels.com/photos/207691/pexels-photo-207691.jpeg" alt="Planejamento de intercâmbio">
    </div>
    <div class="feature-list">
      <h2 style="color:#C98A12;">Recursos Gratuitos</h2>
      <div class="feature-item reveal">
        <div class="icon">✓</div>
        <div><h4>1 Projeto de Intercâmbio</h4><p>Planeje sua viagem completa</p></div>
      </div>
      <div class="feature-item reveal">
        <div class="icon">✓</div>
        <div><h4>Checklist Básico</h4><p>Documentos essenciais por país</p></div>
      </div>
      <div class="feature-item reveal">
        <div class="icon">✓</div>
        <div><h4>Planejamento Financeiro</h4><p>Controle de gastos e economias</p></div>
      </div>
      <div class="feature-item reveal">
        <div class="icon">✓</div>
        <div><h4>Linha do Tempo</h4><p>Organize por datas importantes</p></div>
      </div>
    </div>
  </div>
</section>

<!-- RECURSOS PREMIUM -->
<section class="feature-split">
  <div class="feature-row reverse">
    <div class="feature-media tilt">
      <img src="https://images.pexels.com/photos/2325446/pexels-photo-2325446.jpeg" alt="Mapa com destinos">
    </div>
    <div class="feature-list">
      <span class="badge-tag">PREMIUM</span>
      <h2>Recursos Premium</h2>
      <div class="feature-item reveal">
        <div class="icon">🛡</div>
        <div><h4>Projetos Ilimitados</h4><p>Compare vários destinos</p></div>
      </div>
      <div class="feature-item reveal">
        <div class="icon">$</div>
        <div><h4>Simulador de Custo Real</h4><p>Custos detalhados por cidade</p></div>
      </div>
      <div class="feature-item reveal">
        <div class="icon">🌐</div>
        <div><h4>Comparador de Países</h4><p>Visto, custos e oportunidades</p></div>
      </div>
      <div class="feature-item reveal">
        <div class="icon">✓</div>
        <div><h4>Modelos de Documentos</h4><p>Carta de intenção, CV e mais</p></div>
      </div>
    </div>
  </div>
</section>

<!-- PREÇOS — FLIP CARDS -->
<section class="pricing" id="precos">
  <div class="section-header">
    <h2>Planos Simples e Transparentes</h2>
    <p>Comece grátis, faça upgrade quando precisar</p>
  </div>
  <div class="pricing-grid">

    <!-- GRÁTIS -->
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

    <!-- PREMIUM -->
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
          <a href="<?= ROOT_URL ?>/cadastro.php" class="price-cta premium js-confetti-btn">Começar Premium</a>
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

<!-- CTA FINAL -->
<section class="cta-final">
  <h2>Pronto para realizar seu sonho?</h2>
  <p>Junte-se a centenas de estudantes que já estão planejando seu intercâmbio conosco</p>
  <a href="<?= ROOT_URL ?>/cadastro.php" class="btn-white js-confetti-btn">Criar Conta Grátis &rarr;</a>
</section>

<!-- FOOTER -->
<footer class="footer">
  <a href="<?= ROOT_URL ?>/index.php" class="logo" style="justify-content:center;color:#fff;">
    <svg viewBox="0 0 24 24" fill="#E8A020" width="24" height="24"><path d="M22 16.5v-2l-8.5-5V4c0-1.1-.9-2-1.5-2s-1.5.9-1.5 2v5.5L2 14.5v2l8.5-2.6V19l-2.5 1.8V22l3.5-1 3.5 1v-1.2L12.5 19v-5.1L22 16.5z"/></svg>
    NextStep
  </a>
  <p>Planeje seu intercâmbio do zero ao embarque</p>
  <p class="copyright">© 2026 NextStep · TCC — Ensino Médio Técnico em Informática · UNASP Campinas</p>
</footer>

<script>
// Flip cards
document.querySelectorAll('.ticket').forEach(t=>{
  t.addEventListener('click',function(){
    this.classList.toggle('is-flipped');
    this.setAttribute('aria-pressed',this.classList.contains('is-flipped'));
  });
  t.addEventListener('keydown',function(e){if(e.key==='Enter'||e.key===' '){e.preventDefault();this.click();}});
});

// Confetti aviõezinhos
document.querySelectorAll('.js-confetti-btn').forEach(btn=>{
  btn.addEventListener('click',function(e){
    for(let i=0;i<14;i++){
      const p=document.createElement('span');
      p.textContent='✈';p.className='confetti-plane';
      p.style.left=e.clientX+'px';p.style.top=e.clientY+'px';
      const ang=Math.random()*360,dist=80+Math.random()*130;
      p.style.setProperty('--dx',Math.cos(ang*Math.PI/180)*dist+'px');
      p.style.setProperty('--dy',Math.sin(ang*Math.PI/180)*dist+'px');
      p.style.setProperty('--rot',(Math.random()*360)+'deg');
      document.body.appendChild(p);
      setTimeout(()=>p.remove(),950);
    }
  });
});

// Reveal on scroll
const obs=new IntersectionObserver(entries=>{
  entries.forEach(e=>{if(e.isIntersecting){e.target.classList.add('is-visible');obs.unobserve(e.target);}});
},{threshold:.15});
document.querySelectorAll('.reveal').forEach(el=>obs.observe(el));

// Avião na trilha
const sw=document.querySelector('.steps-wrap');
if(sw){
  const swo=new IntersectionObserver(entries=>{
    entries.forEach(e=>{if(e.isIntersecting){sw.classList.add('in-view');swo.unobserve(sw);}});
  },{threshold:.3});
  swo.observe(sw);
}

// Tilt 3D nas imagens
document.querySelectorAll('.tilt').forEach(el=>{
  el.addEventListener('mousemove',e=>{
    const r=el.getBoundingClientRect();
    const x=(e.clientX-r.left)/r.width-.5;
    const y=(e.clientY-r.top)/r.height-.5;
    el.style.transform=`perspective(800px) rotateY(${x*10}deg) rotateX(${-y*10}deg)`;
  });
  el.addEventListener('mouseleave',()=>el.style.transform='');
});
</script>
<script src="<?= ROOT_URL ?>/assets/js/acessibilidade.js" defer></script>
</body>
</html>
