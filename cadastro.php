<?php
// ============================================================
//  NextStep — Cadastro
//  Arquivo: cadastro.php
// ============================================================

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/funcoes.php';

if (usuarioLogado()) redirecionarPorPerfil();

$erro        = '';
$idadeRecusada = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!validarCSRF($_POST['csrf_token'] ?? '')) {
        $erro = 'Requisição inválida. Tente novamente.';
    } else {
        $nome  = trim($_POST['nome']  ?? '');
        $email = trim($_POST['email'] ?? '');
        $senha = $_POST['senha']      ?? '';
        $idade = (int)($_POST['idade'] ?? 0);

        // Validações
        if (empty($nome) || empty($email) || empty($senha) || $idade <= 0) {
            $erro = 'Preencha todos os campos.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erro = 'E-mail inválido.';
        } elseif ($idade < 14 || $idade > 60) {
            $idadeRecusada = true;  // mostra tela de recusa
        } elseif (strlen($senha) < 6) {
            $erro = 'A senha deve ter pelo menos 6 caracteres.';
        } else {
            $pdo = conectar();

            // Verifica se e-mail já existe
            $chk = $pdo->prepare('SELECT id_usuario FROM usuarios WHERE email = ? LIMIT 1');
            $chk->execute([$email]);
            if ($chk->fetch()) {
                $erro = 'Este e-mail já está cadastrado.';
            } else {
                $hash = password_hash($senha, PASSWORD_BCRYPT, ['cost' => 12]);
                $ins  = $pdo->prepare(
                    'INSERT INTO usuarios (nome, email, senha_hash, idade, id_perfil)
                     VALUES (?, ?, ?, ?, 2)'
                );
                $ins->execute([$nome, $email, $hash, $idade]);

                // Loga automaticamente
                $id = $pdo->lastInsertId();
                session_regenerate_id(true);
                $_SESSION['id_usuario'] = $id;
                $_SESSION['nome']       = $nome;
                $_SESSION['email']      = $email;
                $_SESSION['perfil']     = 'usuario';

                setMsg('sucesso', 'Conta criada com sucesso! Bem-vindo ao NextStep 🎉');
                header('Location: ' . ROOT_URL . '/usuario/dashboard.php');
                exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Criar Conta - NextStep</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= ROOT_URL ?>/assets/css/styles.css">
  <style>
    .auth-erro {
      background: #FFF0F0;
      border: 1px solid #f5c6cb;
      border-left: 4px solid #dc3545;
      color: #721c24;
      padding: 12px 16px;
      border-radius: 6px;
      font-size: 14px;
      margin-bottom: 18px;
    }
    /* Tela de recusa por idade */
    .recusa-box {
      text-align: center;
      padding: 12px 0;
    }
    .recusa-icon { font-size: 48px; margin-bottom: 16px; }
    .recusa-box h3 { color: #2b2318; margin-bottom: 12px; }
    .recusa-box p  { font-size: 14px; color: #7a6a52; line-height: 1.6; margin-bottom: 16px; }
    .recusa-info {
      background: #FCEFC7;
      border-radius: 6px;
      padding: 14px;
      font-size: 13px;
      color: #5a4d38;
      margin-bottom: 20px;
    }
    .btn-voltar {
      display: inline-block;
      padding: 12px 24px;
      background: #D9A404;
      color: #fff;
      border-radius: 6px;
      font-weight: 700;
      text-decoration: none;
    }
  </style>
</head>
<body>

<div class="auth-page">
  <a href="<?= ROOT_URL ?>/index.php" class="auth-back-link">&larr; Voltar</a>

  <div class="auth-card">

    <div class="auth-logo">
      <svg viewBox="0 0 24 24" fill="#D9A404" xmlns="http://www.w3.org/2000/svg">
        <path d="M22 16.5v-2l-8.5-5V4c0-1.1-.9-2-1.5-2s-1.5.9-1.5 2v5.5L2 14.5v2l8.5-2.6V19l-2.5 1.8V22l3.5-1 3.5 1v-1.2L12.5 19v-5.1L22 16.5z"/>
      </svg>
      NextStep
    </div>

    <?php if ($idadeRecusada): ?>

      <!-- Tela de recusa por idade -->
      <div class="recusa-box">
        <div class="recusa-icon">🚫</div>
        <h3>Acesso Restrito</h3>
        <p>
          Desculpe, o NextStep é destinado a usuários entre
          <strong>14 e 60 anos</strong>. Nossos recursos são
          pensados para esse público.
        </p>
        <div class="recusa-info">
          <strong>Por que essa restrição?</strong><br>
          Nossos programas foram desenvolvidos considerando as
          necessidades específicas de estudantes e profissionais
          nessa faixa etária.
        </div>
        <p style="font-size:13px;color:#7a6a52;">
          Se acredita que houve um erro, entre em contato conosco.
        </p>
        <a href="<?= ROOT_URL ?>/cadastro.php" class="btn-voltar">Tentar novamente</a>
      </div>

    <?php else: ?>

      <h2>Criar sua conta</h2>
      <p class="auth-subtitle">Comece a planejar seu intercâmbio gratuitamente</p>

      <?php if ($erro): ?>
        <div class="auth-erro">⚠️ <?= limpar($erro) ?></div>
      <?php endif; ?>

      <form class="auth-form" method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?= tokenCSRF() ?>">

        <div class="form-group">
          <label for="nome">Nome completo</label>
          <input type="text" id="nome" name="nome"
                 placeholder="João Silva"
                 value="<?= limpar($_POST['nome'] ?? '') ?>"
                 required>
        </div>

        <div class="form-group">
          <label for="email">Email</label>
          <input type="email" id="email" name="email"
                 placeholder="seu@email.com"
                 value="<?= limpar($_POST['email'] ?? '') ?>"
                 required autocomplete="email">
        </div>

        <div class="form-group">
          <label for="senha">Senha</label>
          <input type="password" id="senha" name="senha"
                 placeholder="Mínimo 6 caracteres"
                 required autocomplete="new-password">
        </div>

        <div class="form-group">
          <label for="idade">Idade</label>
          <input type="number" id="idade" name="idade"
                 placeholder="18" min="1" max="120"
                 value="<?= limpar($_POST['idade'] ?? '') ?>"
                 required>
        </div>

        <div class="policy-box">
          <span class="policy-icon">🛡</span>
          <span>
            <strong>Política de Idade:</strong>
            Aceitamos usuários entre 14 e 60 anos.
          </span>
        </div>

        <button type="submit" class="auth-submit-btn">Criar conta</button>
      </form>

      <p class="auth-footer-text">
        Já tem uma conta?
        <a href="<?= ROOT_URL ?>/login.php">Fazer login</a>
      </p>

    <?php endif; ?>

  </div>
</div>

</body>
</html>
