<?php
// ============================================================
//  NextStep — Login
//  Arquivo: login.php
// ============================================================

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/funcoes.php';

// Se já está logado, redireciona direto
if (usuarioLogado()) redirecionarPorPerfil();

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!validarCSRF($_POST['csrf_token'] ?? '')) {
        $erro = 'Requisição inválida. Tente novamente.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';

        if (empty($email) || empty($senha)) {
            $erro = 'Preencha o e-mail e a senha.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erro = 'E-mail inválido.';
        } else {
            // Prepared statement — protegido contra SQL Injection
            $pdo  = conectar();
            $stmt = $pdo->prepare(
                'SELECT u.id_usuario, u.nome, u.email, u.senha_hash, u.ativo,
                        p.nome AS perfil
                   FROM usuarios u
                   JOIN perfis p ON p.id_perfil = u.id_perfil
                  WHERE u.email = ?
                  LIMIT 1'
            );
            $stmt->execute([$email]);
            $usu = $stmt->fetch();

            if (!$usu || !password_verify($senha, $usu['senha_hash'])) {
                $erro = 'E-mail ou senha incorretos.';
            } elseif (!$usu['ativo']) {
                $erro = 'Conta desativada. Entre em contato com o suporte.';
            } else {
                session_regenerate_id(true);
                $_SESSION['id_usuario'] = $usu['id_usuario'];
                $_SESSION['nome']       = $usu['nome'];
                $_SESSION['email']      = $usu['email'];
                $_SESSION['perfil']     = $usu['perfil'];
                redirecionarPorPerfil();
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
  <title>Login - NextStep</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= ROOT_URL ?>/assets/css/styles.css">
  <style>
    /* Alerta de erro no estilo do projeto */
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
    /* Caixa de credenciais de teste */
    .teste-box {
      background: #FCEFC7;
      border-radius: 6px;
      padding: 12px 16px;
      font-size: 13px;
      color: #5a4d38;
      margin-bottom: 20px;
      line-height: 1.7;
    }
    .teste-box strong { color: #2b2318; }
  </style>
</head>
<body>

<div class="auth-page">
  <a href="<?= ROOT_URL ?>/index.php" class="auth-back-link">&larr; Voltar</a>

  <div class="auth-card auth-card-small">

    <div class="auth-logo">
      <svg viewBox="0 0 24 24" fill="#D9A404" xmlns="http://www.w3.org/2000/svg">
        <path d="M22 16.5v-2l-8.5-5V4c0-1.1-.9-2-1.5-2s-1.5.9-1.5 2v5.5L2 14.5v2l8.5-2.6V19l-2.5 1.8V22l3.5-1 3.5 1v-1.2L12.5 19v-5.1L22 16.5z"/>
      </svg>
      NextStep
    </div>

    <h2>Bem-vindo de volta</h2>
    <p class="auth-subtitle">Entre na sua conta para continuar</p>

    <!-- Credenciais de teste (remover em produção) -->
    <div class="teste-box">
      🔑 <strong>Usuário:</strong> ingridy@nextstep.com | <strong>Senha:</strong> teste123<br>
      🔑 <strong>Admin:</strong> admin@nextstep.com | <strong>Senha:</strong> admin123
    </div>

    <?php if ($erro): ?>
      <div class="auth-erro">⚠️ <?= limpar($erro) ?></div>
    <?php endif; ?>

    <form class="auth-form" method="POST" action="">
      <input type="hidden" name="csrf_token" value="<?= tokenCSRF() ?>">

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
               placeholder="••••••••"
               required autocomplete="current-password">
      </div>

      <div class="auth-row-between">
        <label class="auth-checkbox">
          <input type="checkbox" name="lembrar">
          <span>Lembrar de mim</span>
        </label>
        <a href="#" class="auth-forgot-link">Esqueceu a senha?</a>
      </div>

      <button type="submit" class="auth-submit-btn">Entrar</button>
    </form>

    <p class="auth-footer-text">
      Ainda não tem uma conta?
      <a href="<?= ROOT_URL ?>/cadastro.php">Criar conta</a>
    </p>

  </div>
</div>

</body>
</html>
