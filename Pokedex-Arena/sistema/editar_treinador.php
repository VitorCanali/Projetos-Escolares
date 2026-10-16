<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/forms.php';
require_login();

$id = valid_id($_GET['id'] ?? $_POST['id'] ?? 0);
$stmt = run_stmt($conn, 'SELECT id, nome, regiao, insignias FROM treinadores WHERE id = ?', 'i', [$id]);
$treinador = $stmt->get_result()->fetch_assoc();
if (!$treinador) {
    http_response_code(404);
    exit('Treinador não encontrado.');
}

$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $nome = post_string('nome');
    $regiao = post_string('regiao');
    $insignias = filter_var($_POST['insignias'] ?? null, FILTER_VALIDATE_INT);
    if ($nome === '' || $regiao === '' || $insignias === false || $insignias < 0 || $insignias > 8) {
        $erro = 'Confira os campos e tente novamente.';
    } else {
        run_stmt($conn, 'UPDATE treinadores SET nome = ?, regiao = ?, insignias = ? WHERE id = ?', 'ssii', [$nome, $regiao, $insignias, $id]);
        flash('Treinador atualizado com sucesso!');
        redirect_to('treinadores.php');
    }
}

render_header('Editar treinador', 'treinadores');
?>
<section class="edit-card">
    <a class="back-link" href="treinadores.php">← Voltar para treinadores</a>
    <div class="eyebrow">ATUALIZE O PERFIL</div><h1>Editar treinador</h1>
    <?php if ($erro): ?><div class="notice notice-error"><?= e($erro) ?></div><?php endif; ?>
    <form method="post" class="stack-form">
        <?php csrf_field(); ?><input type="hidden" name="id" value="<?= $id ?>">
        <label for="nome">Nome</label><input id="nome" name="nome" value="<?= e($_POST['nome'] ?? $treinador['nome']) ?>" required maxlength="80">
        <label for="regiao">Região</label><input id="regiao" name="regiao" value="<?= e($_POST['regiao'] ?? $treinador['regiao']) ?>" required maxlength="60">
        <label for="insignias">Insígnias conquistadas</label><input id="insignias" type="number" name="insignias" min="0" max="8" value="<?= e($_POST['insignias'] ?? $treinador['insignias']) ?>" required>
        <button class="button button-primary" type="submit">Salvar alterações <span>→</span></button>
    </form>
</section>
<?php render_footer(); ?>
