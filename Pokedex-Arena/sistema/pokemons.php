<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/forms.php';
require_login();
$tipos = ['Água', 'Elétrico', 'Fogo', 'Fantasma', 'Lutador', 'Planta', 'Psíquico', 'Terra'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = (string)($_POST['action'] ?? '');
    if ($action === 'delete') {
        $id = valid_id($_POST['id'] ?? 0);
        if ($id) {
            run_stmt($conn, 'DELETE FROM pokemons WHERE id = ?', 'i', [$id]);
            flash('Pokémon removido. As batalhas vinculadas também foram excluídas.');
        }
        redirect_to('pokemons.php');
    }
    $treinadorId = valid_id($_POST['treinador_id'] ?? 0);
    $nome = post_string('nome');
    $especie = post_string('especie');
    $tipo = post_string('tipo');
    $nivel = filter_var($_POST['nivel'] ?? null, FILTER_VALIDATE_INT);
    if (!$treinadorId || $nome === '' || $especie === '' || !in_array($tipo, $tipos, true) || $nivel === false || $nivel < 1 || $nivel > 100) {
        flash('Preencha os dados do Pokémon corretamente.', 'error');
    } else {
        run_stmt($conn, 'INSERT INTO pokemons (treinador_id, nome, especie, tipo, nivel) VALUES (?, ?, ?, ?, ?)', 'isssi', [$treinadorId, $nome, $especie, $tipo, $nivel]);
        flash('Pokémon adicionado ao time!');
    }
    redirect_to('pokemons.php');
}

$busca = trim((string)($_GET['busca'] ?? ''));
$like = '%' . $busca . '%';
$stmt = run_stmt(
    $conn,
    'SELECT p.id, p.nome, p.especie, p.tipo, p.nivel, t.nome AS treinador_nome
     FROM pokemons p JOIN treinadores t ON t.id = p.treinador_id
     WHERE (? = "" OR p.nome LIKE ? OR p.especie LIKE ? OR t.nome LIKE ?)
     ORDER BY p.nome',
    'ssss',
    [$busca, $like, $like, $like]
);
$pokemons = $stmt->get_result();
$treinadores = read_options($conn, 'SELECT id, nome FROM treinadores ORDER BY nome');
$total = (int)$conn->query('SELECT COUNT(*) AS total FROM pokemons')->fetch_assoc()['total'];
render_header('Pokémons', 'pokemons');
render_flash();
?>
<section class="page-heading">
    <div><div class="eyebrow"><span class="status-dot"></span> COMPANHEIROS DE JORNADA</div><h1>Pokémons</h1><p>Monte equipes equilibradas e acompanhe a evolução do seu time.</p></div>
    <span class="heading-count"><strong><?= $total ?></strong> no time</span>
</section>
<section class="crud-grid">
    <div class="table-panel">
        <div class="table-tools"><form class="search-form" method="get"><input name="busca" placeholder="Buscar por nome, espécie ou treinador..." value="<?= e($busca) ?>"><button class="button button-primary" type="submit">Buscar</button></form></div>
        <div class="table-wrap">
            <?php if ($pokemons->num_rows): ?>
            <table>
                <thead><tr><th>POKÉMON</th><th>TIPO</th><th>NÍVEL</th><th>TREINADOR</th><th>AÇÕES</th></tr></thead>
                <tbody>
                <?php while ($row = $pokemons->fetch_assoc()): ?>
                    <tr>
                        <td><div class="table-name"><span class="table-avatar">✦</span><span><strong><?= e($row['nome']) ?></strong><br><small><?= e($row['especie']) ?></small></span></div></td>
                        <td><?= render_type($row['tipo']) ?></td>
                        <td><span class="level-tag">Nv. <?= (int)$row['nivel'] ?></span></td>
                        <td><?= e($row['treinador_nome']) ?></td>
                        <td><div class="actions"><a class="action-link" href="editar_pokemon.php?id=<?= (int)$row['id'] ?>">Editar</a><form class="delete-form" method="post" onsubmit="return confirm('Excluir este Pokémon também removerá suas batalhas. Continuar?')"><?php csrf_field(); ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$row['id'] ?>"><button class="button button-danger" type="submit">Excluir</button></form></div></td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?><div class="empty-table">Nenhum Pokémon encontrado. <a href="pokemons.php">Limpar busca</a></div><?php endif; ?>
        </div>
        <div class="table-foot">Cada Pokémon está vinculado a um treinador.</div>
    </div>
    <aside class="form-panel">
        <div class="eyebrow">NOVO COMPANHEIRO</div><h2>Cadastrar Pokémon</h2><p>Adicione um novo integrante ao time.</p>
        <?php if (!$treinadores): ?><div class="notice notice-warning">Cadastre um treinador antes de adicionar Pokémons.</div><a class="button button-primary" href="treinadores.php">Cadastrar treinador →</a>
        <?php else: ?>
        <form method="post" class="stack-form">
            <?php csrf_field(); ?>
            <label for="treinador_id">Treinador</label><select id="treinador_id" name="treinador_id" required><option value="">Selecione</option><?php foreach ($treinadores as $treinador): ?><option value="<?= (int)$treinador['id'] ?>"><?= e($treinador['nome']) ?></option><?php endforeach; ?></select>
            <label for="nome">Nome ou apelido</label><input id="nome" name="nome" placeholder="Ex.: Pikachu" required maxlength="80">
            <label for="especie">Espécie</label><input id="especie" name="especie" placeholder="Ex.: Pokémon Rato" required maxlength="80">
            <div class="form-row"><div class="stack-form"><label for="tipo">Tipo</label><select id="tipo" name="tipo" required><option value="">Selecione</option><?php foreach ($tipos as $tipo): ?><option value="<?= e($tipo) ?>"><?= e($tipo) ?></option><?php endforeach; ?></select></div><div class="stack-form"><label for="nivel">Nível</label><input id="nivel" type="number" name="nivel" min="1" max="100" value="5" required></div></div>
            <button class="button button-primary" type="submit">Adicionar ao time <span>→</span></button>
        </form>
        <?php endif; ?>
    </aside>
</section>
<?php render_footer(); ?>
