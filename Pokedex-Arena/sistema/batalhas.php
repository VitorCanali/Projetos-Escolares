<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/forms.php';
require_login();
$resultados = ['Vitória', 'Derrota', 'Empate'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = (string)($_POST['action'] ?? '');
    if ($action === 'delete') {
        $id = valid_id($_POST['id'] ?? 0);
        if ($id) {
            run_stmt($conn, 'DELETE FROM batalhas WHERE id = ?', 'i', [$id]);
            flash('Batalha removida do diário.');
        }
        redirect_to('batalhas.php');
    }
    $pokemonId = valid_id($_POST['pokemon_id'] ?? 0);
    $adversario = post_string('adversario');
    $local = post_string('local');
    $data = post_string('data_batalha');
    $resultado = post_string('resultado');
    $dataValida = DateTime::createFromFormat('Y-m-d', $data);
    if (!$pokemonId || $adversario === '' || $local === '' || !$dataValida || $dataValida->format('Y-m-d') !== $data || !in_array($resultado, $resultados, true)) {
        flash('Preencha todos os dados da batalha corretamente.', 'error');
    } else {
        run_stmt($conn, 'INSERT INTO batalhas (pokemon_id, adversario, local, data_batalha, resultado) VALUES (?, ?, ?, ?, ?)', 'issss', [$pokemonId, $adversario, $local, $data, $resultado]);
        flash('Batalha registrada no diário!');
    }
    redirect_to('batalhas.php');
}

$busca = trim((string)($_GET['busca'] ?? ''));
$like = '%' . $busca . '%';
$stmt = run_stmt(
    $conn,
    'SELECT b.id, b.adversario, b.local, b.data_batalha, b.resultado, p.nome AS pokemon_nome, t.nome AS treinador_nome
     FROM batalhas b JOIN pokemons p ON p.id = b.pokemon_id JOIN treinadores t ON t.id = p.treinador_id
     WHERE (? = "" OR b.adversario LIKE ? OR b.local LIKE ? OR p.nome LIKE ? OR t.nome LIKE ?)
     ORDER BY b.data_batalha DESC, b.id DESC',
    'sssss',
    [$busca, $like, $like, $like, $like]
);
$batalhas = $stmt->get_result();
$pokemons = read_options($conn, 'SELECT p.id, p.nome, t.nome AS treinador_nome FROM pokemons p JOIN treinadores t ON t.id = p.treinador_id ORDER BY p.nome');
$total = (int)$conn->query('SELECT COUNT(*) AS total FROM batalhas')->fetch_assoc()['total'];
render_header('Batalhas', 'batalhas');
render_flash();
?>
<section class="page-heading">
    <div><div class="eyebrow"><span class="status-dot"></span> DIÁRIO DE CAMPO</div><h1>Batalhas</h1><p>Reviva cada encontro. A lista está organizada pelas datas mais recentes.</p></div>
    <span class="heading-count"><strong><?= $total ?></strong> registros</span>
</section>
<section class="crud-grid">
    <div class="table-panel">
        <div class="table-tools"><form class="search-form" method="get"><input name="busca" placeholder="Buscar por adversário, local ou Pokémon..." value="<?= e($busca) ?>"><button class="button button-primary" type="submit">Buscar</button></form></div>
        <div class="table-wrap">
            <?php if ($batalhas->num_rows): ?>
            <table>
                <thead><tr><th>DATA</th><th>POKÉMON</th><th>ADVERSÁRIO</th><th>LOCAL</th><th>RESULTADO</th><th>AÇÕES</th></tr></thead>
                <tbody>
                <?php while ($row = $batalhas->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?= date('d/m/Y', strtotime($row['data_batalha'])) ?></strong></td>
                        <td><strong><?= e($row['pokemon_nome']) ?></strong><br><small><?= e($row['treinador_nome']) ?></small></td>
                        <td><?= e($row['adversario']) ?></td>
                        <td><?= e($row['local']) ?></td>
                        <td><?= render_result($row['resultado']) ?></td>
                        <td><div class="actions"><a class="action-link" href="editar_batalha.php?id=<?= (int)$row['id'] ?>">Editar</a><form class="delete-form" method="post" onsubmit="return confirm('Excluir este registro de batalha?')"><?php csrf_field(); ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$row['id'] ?>"><button class="button button-danger" type="submit">Excluir</button></form></div></td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?><div class="empty-table">Nenhuma batalha encontrada. <a href="batalhas.php">Limpar busca</a></div><?php endif; ?>
        </div>
        <div class="table-foot">Ordenação: data mais recente primeiro.</div>
    </div>
    <aside class="form-panel">
        <div class="eyebrow">REGISTRE UMA HISTÓRIA</div><h2>Nova batalha</h2><p>Adicione o resultado ao diário de campo.</p>
        <?php if (!$pokemons): ?><div class="notice notice-warning">Cadastre um Pokémon antes de registrar batalhas.</div><a class="button button-primary" href="pokemons.php">Cadastrar Pokémon →</a>
        <?php else: ?>
        <form method="post" class="stack-form">
            <?php csrf_field(); ?>
            <label for="pokemon_id">Pokémon participante</label><select id="pokemon_id" name="pokemon_id" required><option value="">Selecione</option><?php foreach ($pokemons as $pokemon): ?><option value="<?= (int)$pokemon['id'] ?>"><?= e($pokemon['nome']) ?> · <?= e($pokemon['treinador_nome']) ?></option><?php endforeach; ?></select>
            <label for="adversario">Adversário</label><input id="adversario" name="adversario" placeholder="Ex.: Gary" required maxlength="80">
            <label for="local">Local da batalha</label><input id="local" name="local" placeholder="Ex.: Ginásio de Pewter" required maxlength="100">
            <div class="form-row"><div class="stack-form"><label for="data_batalha">Data</label><input id="data_batalha" type="date" name="data_batalha" value="<?= date('Y-m-d') ?>" required></div><div class="stack-form"><label for="resultado">Resultado</label><select id="resultado" name="resultado" required><?php foreach ($resultados as $resultado): ?><option value="<?= e($resultado) ?>"><?= e($resultado) ?></option><?php endforeach; ?></select></div></div>
            <button class="button button-primary" type="submit">Salvar batalha <span>→</span></button>
        </form>
        <?php endif; ?>
    </aside>
</section>
<?php render_footer(); ?>
