<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/forms.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = (string)($_POST['action'] ?? '');
    if ($action === 'delete') {
        $id = valid_id($_POST['id'] ?? 0);
        if ($id) {
            run_stmt($conn, 'DELETE FROM treinadores WHERE id = ?', 'i', [$id]);
            flash('Treinador removido. Pokémons e batalhas vinculados também foram excluídos.');
        }
        redirect_to('treinadores.php');
    }

    $nome = post_string('nome');
    $regiao = post_string('regiao');
    $insignias = filter_var($_POST['insignias'] ?? 0, FILTER_VALIDATE_INT);
    if ($nome === '' || $regiao === '' || $insignias === false || $insignias < 0 || $insignias > 8) {
        flash('Preencha os campos corretamente. Insígnias deve ser um número de 0 a 8.', 'error');
    } else {
        run_stmt($conn, 'INSERT INTO treinadores (nome, regiao, insignias) VALUES (?, ?, ?)', 'ssi', [$nome, $regiao, $insignias]);
        flash('Treinador cadastrado com sucesso!');
    }
    redirect_to('treinadores.php');
}

$busca = trim((string)($_GET['busca'] ?? ''));
$like = '%' . $busca . '%';
$stmt = run_stmt(
    $conn,
    'SELECT t.id, t.nome, t.regiao, t.insignias, COUNT(DISTINCT p.id) AS total_pokemons
     FROM treinadores t LEFT JOIN pokemons p ON p.treinador_id = t.id
     WHERE (? = "" OR t.nome LIKE ? OR t.regiao LIKE ?)
     GROUP BY t.id ORDER BY t.nome',
    'sss',
    [$busca, $like, $like]
);
$treinadores = $stmt->get_result();
$total = (int)$conn->query('SELECT COUNT(*) AS total FROM treinadores')->fetch_assoc()['total'];
render_header('Treinadores', 'treinadores');
render_flash();
?>
<section class="page-heading">
    <div><div class="eyebrow"><span class="status-dot"></span> SEU PONTO DE PARTIDA</div><h1>Treinadores</h1><p>Conheça quem está por trás de cada jornada Pokémon.</p></div>
    <span class="heading-count"><strong><?= $total ?></strong> perfis</span>
</section>
<section class="crud-grid">
    <div class="table-panel">
        <div class="table-tools">
            <form class="search-form" method="get">
                <input name="busca" placeholder="Buscar por nome ou região..." value="<?= e($busca) ?>">
                <button class="button button-primary" type="submit">Buscar</button>
            </form>
        </div>
        <div class="table-wrap">
            <?php if ($treinadores->num_rows): ?>
            <table>
                <thead><tr><th>TREINADOR</th><th>REGIÃO</th><th>INSÍGNIAS</th><th>TIME</th><th>AÇÕES</th></tr></thead>
                <tbody>
                <?php while ($row = $treinadores->fetch_assoc()): ?>
                    <tr>
                        <td><div class="table-name"><span class="table-avatar">♙</span><strong><?= e($row['nome']) ?></strong></div></td>
                        <td><?= e($row['regiao']) ?></td>
                        <td><span class="level-tag"><?= (int)$row['insignias'] ?> / 8</span></td>
                        <td><?= (int)$row['total_pokemons'] ?> Pokémon(s)</td>
                        <td><div class="actions"><a class="action-link" href="editar_treinador.php?id=<?= (int)$row['id'] ?>">Editar</a><form class="delete-form" method="post" onsubmit="return confirm('Excluir este treinador também removerá seus Pokémons e batalhas. Continuar?')"><?php csrf_field(); ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$row['id'] ?>"><button class="button button-danger" type="submit">Excluir</button></form></div></td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?><div class="empty-table">Nenhum treinador encontrado. <a href="treinadores.php">Limpar busca</a></div><?php endif; ?>
        </div>
        <div class="table-foot">A exclusão de um treinador também remove os registros vinculados a ele.</div>
    </div>
    <aside class="form-panel">
        <div class="eyebrow">NOVO PERFIL</div><h2>Cadastrar treinador</h2><p>Comece uma nova trajetória na arena.</p>
        <form method="post" class="stack-form">
            <?php csrf_field(); ?>
            <label for="nome">Nome do treinador</label><input id="nome" name="nome" placeholder="Ex.: Luma" required maxlength="80">
            <label for="regiao">Região favorita</label><input id="regiao" name="regiao" placeholder="Ex.: Kanto" required maxlength="60">
            <label for="insignias">Insígnias conquistadas</label><input id="insignias" type="number" name="insignias" min="0" max="8" value="0" required>
            <button class="button button-primary" type="submit">Adicionar treinador <span>→</span></button>
        </form>
        <span class="form-hint">Cada treinador pode ter vários Pokémons e cada Pokémon pode participar de várias batalhas.</span>
    </aside>
</section>
<?php render_footer(); ?>
