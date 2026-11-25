<?php
define('ROOT', dirname(__DIR__));
require_once ROOT.'/config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly'=>true,'samesite'=>'Strict']);
    session_start();
}

function usuarioLogado(): bool { return isset($_SESSION['id_usuario']); }
function isPerfil(string $p): bool { return ($_SESSION['perfil'] ?? '') === $p; }

function exigirLogin(): void {
    if (!usuarioLogado()) { header('Location:'.ROOT_URL.'/login.php'); exit; }
}
function exigirAdmin(): void {
    exigirLogin();
    if (!isPerfil('admin')) { header('Location:'.ROOT_URL.'/usuario/dashboard.php'); exit; }
}
function exigirUsuario(): void { exigirLogin(); }

function redirecionarPorPerfil(): void {
    header('Location:'.ROOT_URL.(isPerfil('admin') ? '/admin/dashboard.php' : '/usuario/dashboard.php'));
    exit;
}

function limpar(string $d): string { return htmlspecialchars(trim($d), ENT_QUOTES, 'UTF-8'); }

function tokenCSRF(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function validarCSRF(string $t): bool {
    return isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $t);
}

function setMsg(string $tipo, string $texto): void { $_SESSION['flash'] = compact('tipo','texto'); }
function getMsg(): ?array {
    if (!isset($_SESSION['flash'])) return null;
    $m = $_SESSION['flash']; unset($_SESSION['flash']); return $m;
}

function usuario(): array {
    return ['id'=>$_SESSION['id_usuario']??0,'nome'=>$_SESSION['nome']??'','email'=>$_SESSION['email']??'','perfil'=>$_SESSION['perfil']??''];
}

// Gera checklist automático
function gerarChecklist(PDO $pdo, int $id_proj, int $id_objetivo): void {
    $obj_map = [1=>'study',2=>'work',3=>'study-work',4=>'volunteer'];
    $ok = $obj_map[$id_objetivo] ?? 'study';
    $itens = [
        [1,'Passaporte válido',         'Verifique se está válido por 6+ meses após o retorno'],
        [1,'Solicitar visto',           'Verifique o tipo de visto necessário para o país'],
        [1,'Seguro saúde internacional','Contrate cobertura para todo o período'],
        [2,'Comprovação financeira',    'Extrato bancário dos últimos 3 meses'],
        [5,'Passagem aérea',            'Compre ida e volta com bagagem incluída'],
        [4,'Moradia temporária',        'Reserve acomodação para as primeiras semanas'],
    ];
    if (in_array($ok,['study','study-work'])) {
        $itens[]=[1,'Carta de aceitação','Documento oficial da instituição de ensino'];
        $itens[]=[1,'Histórico escolar traduzido','Traduza e autentique seu histórico'];
    }
    if (in_array($ok,['work','study-work'])) {
        $itens[]=[6,'Currículo internacional','CV no formato do país de destino'];
        $itens[]=[6,'Carta de apresentação','Cover letter em inglês'];
    }
    if ($ok==='volunteer') {
        $itens[]=[1,'Carta da organização','Documento oficial da ONG'];
        $itens[]=[3,'Certificado de vacinas','Vacinas necessárias para o voluntariado'];
    }
    $s = $pdo->prepare('INSERT INTO checklist(id_projeto,id_categoria,titulo,descricao)VALUES(?,?,?,?)');
    foreach ($itens as $i) $s->execute([$id_proj,$i[0],$i[1],$i[2]]);
}

// Gera timeline automática
function gerarTimeline(PDO $pdo, int $id_proj, string $data_inicio): void {
    $start = new DateTime($data_inicio);
    $eventos = [
        [-6,1,'Início do planejamento','Pesquise sobre o país e requisitos'],
        [-5,1,'Documentação inicial','Passaporte e documentos básicos'],
        [-4,1,'Solicitação de visto','Inicie o processo no consulado'],
        [-3,3,'Seguro e vacinas','Contrate seguro e atualize vacinas'],
        [-2,5,'Comprar passagem aérea','Compre com antecedência para economizar'],
        [-1,4,'Reservar moradia','Pelo menos as 2 primeiras semanas'],
        [0, 5,'Embarque! ✈️','Dia da viagem!'],
    ];
    $s = $pdo->prepare('INSERT INTO timeline(id_projeto,id_categoria,titulo,descricao,data_prevista)VALUES(?,?,?,?,?)');
    foreach ($eventos as $e) {
        $d = clone $start; $d->modify("{$e[0]} months");
        $s->execute([$id_proj,$e[1],$e[2],$e[3],$d->format('Y-m-d')]);
    }
}
