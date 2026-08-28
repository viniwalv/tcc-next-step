<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/funcoes.php';
$_SESSION = [];
session_destroy();
header('Location: ' . ROOT_URL . '/login.php');
exit;
