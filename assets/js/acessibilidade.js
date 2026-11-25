/* NextStep — recursos de acessibilidade (painel + correções automáticas) */
(function () {
  'use strict';
  var KEY = 'ns_acess', S = { zoom: 0, hc: 0, dys: 0, calm: 0 };
  try { var sv = JSON.parse(localStorage.getItem(KEY) || '{}'); for (var k in sv) if (k in S) S[k] = sv[k]; } catch (e) {}
  function save() { try { localStorage.setItem(KEY, JSON.stringify(S)); } catch (e) {} }

  var CSS = [
    ':focus-visible{outline:3px solid #1a1208!important;outline-offset:2px!important;box-shadow:0 0 0 5px #F5C542!important}',
    '[style*="9E9E9E"],.form-hint,.stat-sub{color:#6B6B6B!important}',
    '[style*="color:#BF9004"],.form-label,.label,.tab.active{color:#7a5a02!important}',
    '.ns-skip{position:absolute;left:-9999px;top:8px;background:#fff;color:#1a1208;padding:10px 16px;border-radius:8px;font:700 14px sans-serif;z-index:100002;text-decoration:none}.ns-skip:focus{left:8px}',
    '#ns-ac-btn{position:fixed;left:16px;bottom:16px;z-index:100000;background:#1a1208;color:#fff;border:2px solid #F5C542;border-radius:99px;padding:10px 16px;font:700 14px sans-serif;cursor:pointer}',
    '#ns-ac-panel{position:fixed;left:16px;bottom:68px;z-index:100000;background:#fff;color:#1a1208;border:2px solid #1a1208;border-radius:12px;padding:12px;width:250px;font:14px sans-serif;box-shadow:0 8px 30px rgba(0,0,0,.25)}',
    '#ns-ac-panel[hidden]{display:none}',
    '#ns-ac-panel button{display:block;width:100%;margin:6px 0;padding:10px;border:2px solid #1a1208;border-radius:8px;background:#fff;color:#1a1208;font:600 14px sans-serif;cursor:pointer;text-align:left}',
    '#ns-ac-panel button[aria-pressed="true"]{background:#1a1208;color:#fff}',
    '#ns-ac-panel .ns-row{display:flex;gap:8px}',
    '@media (prefers-reduced-motion:reduce){*,*::before,*::after{animation:none!important;transition:none!important}}',
    'html.ns-calm *,html.ns-calm *::before,html.ns-calm *::after{animation:none!important;transition:none!important;cursor:auto!important}',
    'html.ns-calm .flight-plane,html.ns-calm .confetti-plane{display:none!important}',
    'html.ns-dys,html.ns-dys *:not(svg):not(path){font-family:"Lexend","Verdana",sans-serif!important;letter-spacing:.04em!important;word-spacing:.12em!important;line-height:1.7!important}',
    'html.ns-hc body,html.ns-hc body *{background-color:#000!important;background-image:none!important;color:#fff!important;border-color:#fff!important;box-shadow:none!important;text-shadow:none!important}',
    'html.ns-hc a,html.ns-hc a *{color:#FFEB3B!important}',
    'html.ns-hc button,html.ns-hc .btn{border:2px solid #FFEB3B!important}',
    'html.ns-hc .prog-bar{background-color:#FFEB3B!important}',
    'html.ns-hc .prog-bg{background-color:#555!important}',
    'html.ns-hc img{filter:grayscale(1) contrast(1.2)}'
  ].join('\n');
  var st = document.createElement('style'); st.id = 'ns-ac-css'; st.textContent = CSS; document.head.appendChild(st);

  // 1) labels ligados aos campos (for/id)
  function fixLabels() {
    var n = 0, claimed = [];
    document.querySelectorAll('label[for]').forEach(function (l) { var c = document.getElementById(l.getAttribute('for')); if (c) claimed.push(c); });
    document.querySelectorAll('label:not([for])').forEach(function (l) {
      if (l.querySelector('input,select,textarea') || !l.parentElement) return;
      var cs = l.parentElement.querySelectorAll('input:not([type=hidden]),select,textarea');
      for (var i = 0; i < cs.length; i++) {
        var c = cs[i];
        if (claimed.indexOf(c) > -1 || !(l.compareDocumentPosition(c) & Node.DOCUMENT_POSITION_FOLLOWING)) continue;
        if (!c.id) c.id = 'ns-f-' + (++n);
        l.setAttribute('for', c.id); claimed.push(c); break;
      }
    });
    document.querySelectorAll('input:not([type=hidden]),select,textarea').forEach(function (c) {
      var has = c.getAttribute('aria-label') || (c.id && document.querySelector('label[for="' + c.id + '"]')) || c.closest('label');
      if (!has) c.setAttribute('aria-label', c.placeholder || c.name || 'campo');
    });
  }

  // 2) leitores de tela: nomes, ícones, avisos
  function fixAria() {
    document.querySelectorAll('svg:not([aria-hidden])').forEach(function (s) { s.setAttribute('aria-hidden', 'true'); s.setAttribute('focusable', 'false'); });
    document.querySelectorAll('.toast').forEach(function (t) { t.setAttribute('role', 'status'); t.setAttribute('aria-live', 'polite'); });
    document.querySelectorAll('.alerta,.erro,.auth-erro').forEach(function (a) { a.setAttribute('role', 'alert'); });
    document.querySelectorAll('.tab.active').forEach(function (t) { t.setAttribute('aria-current', 'page'); });
    document.querySelectorAll('.chk-card').forEach(function (card) {
      var t = card.querySelector('.chk-titulo'), name = t ? t.textContent.trim() : 'tarefa';
      var c = card.querySelector('.chk-box'); if (c) c.setAttribute('aria-label', 'Concluir: ' + name);
      var n = card.querySelector('.nota-field'); if (n) n.setAttribute('aria-label', 'Notas sobre: ' + name);
    });
    var main = document.querySelector('.content,main,form,h1');
    if (main) { if (!main.id) main.id = 'ns-main'; main.setAttribute('tabindex', '-1');
      var a = document.createElement('a'); a.className = 'ns-skip'; a.href = '#' + main.id; a.textContent = 'Ir para o conteúdo';
      document.body.insertBefore(a, document.body.firstChild); }
  }

  // 3) botões dourados: texto escuro (contraste)
  function fixButtons() {
    document.querySelectorAll('a,button').forEach(function (b) {
      var bg = getComputedStyle(b).backgroundColor;
      if (bg === 'rgb(217, 164, 4)' || bg === 'rgb(232, 160, 32)') b.style.setProperty('color', '#1a1208', 'important');
    });
  }

  // 4) painel
  var btn, panel, pressed = {};
  function apply() {
    var h = document.documentElement;
    h.classList.toggle('ns-hc', !!S.hc); h.classList.toggle('ns-dys', !!S.dys); h.classList.toggle('ns-calm', !!S.calm);
    document.body.style.zoom = String(1 + S.zoom * 0.1);
    if (S.dys && !document.getElementById('ns-lexend')) {
      var l = document.createElement('link'); l.id = 'ns-lexend'; l.rel = 'stylesheet';
      l.href = 'https://fonts.googleapis.com/css2?family=Lexend:wght@400;600&display=swap'; document.head.appendChild(l);
    }
    for (var k in pressed) pressed[k].setAttribute('aria-pressed', S[k] ? 'true' : 'false');
  }
  function mk(text, fn, key) {
    var b = document.createElement('button'); b.type = 'button'; b.textContent = text;
    if (key) { b.setAttribute('aria-pressed', 'false'); pressed[key] = b; }
    b.addEventListener('click', function () { fn(); save(); apply(); }); return b;
  }
  function build() {
    btn = document.createElement('button'); btn.id = 'ns-ac-btn'; btn.type = 'button'; btn.textContent = '♿ Acessibilidade';
    btn.setAttribute('aria-expanded', 'false'); btn.setAttribute('aria-controls', 'ns-ac-panel');
    panel = document.createElement('div'); panel.id = 'ns-ac-panel'; panel.hidden = true;
    panel.setAttribute('role', 'group'); panel.setAttribute('aria-label', 'Opções de acessibilidade');
    var row = document.createElement('div'); row.className = 'ns-row';
    row.appendChild(mk('A+ Maior', function () { S.zoom = Math.min(4, S.zoom + 1); }));
    row.appendChild(mk('A− Menor', function () { S.zoom = Math.max(-2, S.zoom - 1); }));
    panel.appendChild(row);
    panel.appendChild(mk('Alto contraste', function () { S.hc = S.hc ? 0 : 1; }, 'hc'));
    panel.appendChild(mk('Fonte para dislexia', function () { S.dys = S.dys ? 0 : 1; }, 'dys'));
    panel.appendChild(mk('Menos movimento', function () { S.calm = S.calm ? 0 : 1; }, 'calm'));
    panel.appendChild(mk('Restaurar padrão', function () { S.zoom = S.hc = S.dys = S.calm = 0; }));
    btn.addEventListener('click', function () { panel.hidden = !panel.hidden; btn.setAttribute('aria-expanded', String(!panel.hidden)); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !panel.hidden) { panel.hidden = true; btn.setAttribute('aria-expanded', 'false'); btn.focus(); } });
    document.body.appendChild(panel); document.body.appendChild(btn);
  }

  function init() { fixLabels(); fixAria(); fixButtons(); build(); apply(); }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
})();
