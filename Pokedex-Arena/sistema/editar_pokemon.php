<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/forms.php';
require_login();
$tipos = ['Água', 'Elétrico', 'Fogo', 'Fantasma', 'Lutador', 'Planta', 'Psíquico', 'Terra'];
$id = valid_id($_GET['id'] ?? $_POST['id'] ?? 0);
$stmt = run_stmt($conn, 'SELECT id, treinador_id, nome, especie, tipo, nivel FROM pokemons WHERE id = ?', 'i', [$id]);
$pokemon = $stmt->get_result()->fetch_assoc();
if (!$pokemon) {
    http_response_code(404);
    exit('Pokémon não encontrado.');
}
$treinadores = read_options($conn, 'SELECT id, nome FROM treinadores ORDER BY nome');
$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $treinadorId = valid_id($_POST['treinador_id'] ?? 0);
    $nome = post_string('nome');
    $especie = post_string('especie');
    $tipo = post_string('tipo');
    $nivel = filter_var($_POST['nivel'] ?? null, FILTER_VALIDATE_INT);
    if (!$treinadorId || $nome === '' || $especie === '' || !in_array($tipo, $tipos, true) || $nivel === false || $nivel < 1 || $nivel > 100) {
        $erro = 'Confira os campos e tente novamente.';
    } else {
        run_stmt($conn, 'UPDATE pokemons SET treinador_id = ?, nome = ?, especie = ?, tipo = ?, nivel = ? WHERE id = ?', 'isssii', [$treinadorId, $nome, $especie, $tipo, $nivel, $id]);
        flash('Pokémon atualizado com sucesso!');
        redirect_to('pokemons.php');
    }
}
render_header('Editar Pokémon', 'pokemons');
?>
<section class="edit-card">
    <a class="back-link" href="pokemons.php">← Voltar para Pokémons</a>
    <div class="eyebrow">AJUSTE DO TIME</div><h1>Editar Pokémon</h1>
    <?php if ($erro): ?><div class="notice notice-error"><?= e($erro) ?></div><?php endif; ?>
    <form method="post" class="stack-form">
        <?php csrf_field(); ?><input type="hidden" name="id" value="<?= $id ?>">
        <label for="treinador_id">Treinador</label><select id="treinador_id" name="treinador_id" required><?php foreach ($treinadores as $treinador): ?><option value="<?= (int)$treinador['id'] ?>" <?= (int)$treinador['id'] === (int)($pokemon['treinador_id']) ? 'selected' : '' ?>><?= e($treinador['nome']) ?></option><?php endforeach; ?></select>
        <label for="nome">Nome ou apelido</label><input id="nome" name="nome" value="<?= e($_POST['nome'] ?? $pokemon['nome']) ?>" required maxlength="80">
        <label for="especie">Espécie</label><input id="especie" name="especie" value="<?= e($_POST['especie'] ?? $pokemon['especie']) ?>" required maxlength="80">
        <div class="form-row"><div class="stack-form"><label for="tipo">Tipo</label><select id="tipo" name="tipo" required><?php foreach ($tipos as $tipo): ?><option value="<?= e($tipo) ?>" <?= $tipo === ($pokemon['tipo']) ? 'selected' : '' ?>><?= e($tipo) ?></option><?php endforeach; ?></select></div><div class="stack-form"><label for="nivel">Nível</label><input id="nivel" type="number" name="nivel" min="1" max="100" value="<?= e($_POST['nivel'] ?? $pokemon['nivel']) ?>" required></div></div>
        <button class="button button-primary" type="submit">Salvar alterações <span>→</span></button>
    </form>
</section>
<?php render_footer(); ?>
