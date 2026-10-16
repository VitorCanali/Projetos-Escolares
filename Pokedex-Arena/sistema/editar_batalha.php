<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/forms.php';
require_login();
$resultados = ['Vitória', 'Derrota', 'Empate'];
$id = valid_id($_GET['id'] ?? $_POST['id'] ?? 0);
$stmt = run_stmt($conn, 'SELECT id, pokemon_id, adversario, local, data_batalha, resultado FROM batalhas WHERE id = ?', 'i', [$id]);
$batalha = $stmt->get_result()->fetch_assoc();
if (!$batalha) {
    http_response_code(404);
    exit('Batalha não encontrada.');
}
$pokemons = read_options($conn, 'SELECT p.id, p.nome, t.nome AS treinador_nome FROM pokemons p JOIN treinadores t ON t.id = p.treinador_id ORDER BY p.nome');
$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $pokemonId = valid_id($_POST['pokemon_id'] ?? 0);
    $adversario = post_string('adversario');
    $local = post_string('local');
    $data = post_string('data_batalha');
    $resultado = post_string('resultado');
    $dataValida = DateTime::createFromFormat('Y-m-d', $data);
    if (!$pokemonId || $adversario === '' || $local === '' || !$dataValida || $dataValida->format('Y-m-d') !== $data || !in_array($resultado, $resultados, true)) {
        $erro = 'Confira os campos e tente novamente.';
    } else {
        run_stmt($conn, 'UPDATE batalhas SET pokemon_id = ?, adversario = ?, local = ?, data_batalha = ?, resultado = ? WHERE id = ?', 'issssi', [$pokemonId, $adversario, $local, $data, $resultado, $id]);
        flash('Batalha atualizada com sucesso!');
        redirect_to('batalhas.php');
    }
}
render_header('Editar batalha', 'batalhas');
?>
<section class="edit-card">
    <a class="back-link" href="batalhas.php">← Voltar para batalhas</a>
    <div class="eyebrow">DIÁRIO DE CAMPO</div><h1>Editar batalha</h1>
    <p class="edit-context">Atualize os detalhes deste encontro para manter seu histórico em dia.</p>
    <?php if ($erro): ?><div class="notice notice-error"><?= e($erro) ?></div><?php endif; ?>
    <form method="post" class="stack-form">
        <?php csrf_field(); ?><input type="hidden" name="id" value="<?= $id ?>">
        <label for="pokemon_id">Pokémon participante</label><select id="pokemon_id" name="pokemon_id" required><?php foreach ($pokemons as $pokemon): ?><option value="<?= (int)$pokemon['id'] ?>" <?= (int)$pokemon['id'] === (int)$batalha['pokemon_id'] ? 'selected' : '' ?>><?= e($pokemon['nome']) ?> · <?= e($pokemon['treinador_nome']) ?></option><?php endforeach; ?></select>
        <label for="adversario">Adversário</label><input id="adversario" name="adversario" value="<?= e($_POST['adversario'] ?? $batalha['adversario']) ?>" required maxlength="80">
        <label for="local">Local da batalha</label><input id="local" name="local" value="<?= e($_POST['local'] ?? $batalha['local']) ?>" required maxlength="100">
        <div class="form-row"><div class="stack-form"><label for="data_batalha">Data</label><input id="data_batalha" type="date" name="data_batalha" value="<?= e($_POST['data_batalha'] ?? $batalha['data_batalha']) ?>" required></div><div class="stack-form"><label for="resultado">Resultado</label><select id="resultado" name="resultado" required><?php foreach ($resultados as $resultado): ?><option value="<?= e($resultado) ?>" <?= $resultado === ($batalha['resultado']) ? 'selected' : '' ?>><?= e($resultado) ?></option><?php endforeach; ?></select></div></div>
        <button class="button button-primary" type="submit">Salvar alterações <span>→</span></button>
    </form>
</section>
<?php render_footer(); ?>
