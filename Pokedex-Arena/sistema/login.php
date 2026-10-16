<?php
require_once __DIR__ . '/includes/bootstrap.php';

if (!empty($_SESSION['usuario'])) {
    redirect_to('principal.php');
}

$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim((string)($_POST['nome'] ?? ''));
    $senha = (string)($_POST['senha'] ?? '');

    $stmt = $conn->prepare('SELECT id, nome, senha FROM usuarios WHERE nome = ? LIMIT 1');
    $stmt->bind_param('s', $nome);
    $stmt->execute();
    $usuario = $stmt->get_result()->fetch_assoc();

    if ($usuario && password_verify($senha, $usuario['senha'])) {
        session_regenerate_id(true);
        $_SESSION['usuario'] = ['id' => (int)$usuario['id'], 'nome' => $usuario['nome']];
        $_SESSION['ultimo_acesso'] = time();
        redirect_to('principal.php');
    }

    $erro = 'Usuário ou senha inválidos. Confira seus dados e tente novamente.';
}

render_header('Entrar');
?>
<section class="login-layout">
    <div class="login-copy">
        <div class="eyebrow"><span class="status-dot"></span> CENTRAL DE TREINADORES</div>
        <h1>Uma nova<br><em>jornada</em><br>começa agora.</h1>
        <p>Organize seu time, acompanhe cada Pokémon e registre batalhas que merecem entrar para a história.</p>
        <div class="login-stats">
            <div><strong>01</strong><span>seu time</span></div>
            <div><strong>02</strong><span>novas rotas</span></div>
            <div><strong>∞</strong><span>possibilidades</span></div>
        </div>
    </div>
    <div class="login-card">
        <div class="card-kicker">BEM-VINDO DE VOLTA</div>
        <div class="login-ball" aria-hidden="true"><span></span></div>
        <h2>Acesse sua conta</h2>
        <p class="muted">Entre para continuar sua aventura.</p>
        <?php if (isset($_GET['expirou'])): ?>
            <div class="notice notice-warning">Sua sessão expirou. Entre novamente para continuar.</div>
        <?php endif; ?>
        <?php if ($erro): ?>
            <div class="notice notice-error"><?= e($erro) ?></div>
        <?php endif; ?>
        <form method="post" class="stack-form">
            <label for="nome">Usuário</label>
            <input id="nome" name="nome" autocomplete="username" placeholder="Ex.: ash" required value="<?= e($_POST['nome'] ?? '') ?>">
            <label for="senha">Senha</label>
            <input id="senha" type="password" name="senha" autocomplete="current-password" placeholder="Sua senha secreta" required>
            <button class="button button-primary button-wide" type="submit">Entrar na arena <span aria-hidden="true">→</span></button>
        </form>
        <div class="login-hint"><span>✦</span> Seu próximo capítulo espera por você.</div>
    </div>
</section>
<?php render_footer(); ?>
