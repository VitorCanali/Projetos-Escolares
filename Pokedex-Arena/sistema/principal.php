<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();

$totais = [];
foreach (['treinadores' => 'treinadores', 'pokemons' => 'pokemons', 'batalhas' => 'batalhas'] as $key => $table) {
    $result = $conn->query("SELECT COUNT(*) AS total FROM {$table}");
    $totais[$key] = (int)$result->fetch_assoc()['total'];
}
$recentes = $conn->query(
    'SELECT b.id, b.data_batalha, b.adversario, b.resultado, p.nome AS pokemon_nome
     FROM batalhas b JOIN pokemons p ON p.id = b.pokemon_id
     ORDER BY b.data_batalha DESC, b.id DESC LIMIT 4'
);

render_header('Visão geral', 'home');
render_flash();
?>
<section class="welcome-row">
    <div>
        <div class="eyebrow"><span class="status-dot"></span> PAINEL DO TREINADOR</div>
        <h1>Olá, <?= e($_SESSION['usuario']['nome']) ?> <span class="wave">✦</span></h1>
        <p class="muted">Sua jornada Pokémon em um só lugar. Pronto para a próxima batalha?</p>
    </div>
    <div class="date-chip"><span>◷</span> <?= date('d/m/Y') ?></div>
</section>

<section class="hero-card">
    <div class="hero-text">
        <span class="hero-tag">SUA AVENTURA, SEU LEGADO</span>
        <h2>Treine com propósito.<br><em>Vença com estratégia.</em></h2>
        <p>Cuide do seu time, explore novas batalhas e acompanhe cada conquista.</p>
        <a href="batalhas.php" class="button button-light">Registrar batalha <span aria-hidden="true">→</span></a>
    </div>
    <div class="hero-emblem" aria-hidden="true"><div class="orbit orbit-one"></div><div class="orbit orbit-two"></div><div class="hero-pokeball"><span></span></div><b>✦</b></div>
</section>

<section class="stat-grid">
    <a class="stat-card stat-red" href="treinadores.php"><span class="stat-icon">♙</span><span class="stat-label">TREINADORES</span><strong><?= $totais['treinadores'] ?></strong><span class="stat-note">perfis cadastrados ↗</span></a>
    <a class="stat-card stat-yellow" href="pokemons.php"><span class="stat-icon">✦</span><span class="stat-label">POKÉMONS</span><strong><?= $totais['pokemons'] ?></strong><span class="stat-note">companheiros no time ↗</span></a>
    <a class="stat-card stat-blue" href="batalhas.php"><span class="stat-icon">⚔</span><span class="stat-label">BATALHAS</span><strong><?= $totais['batalhas'] ?></strong><span class="stat-note">encontros registrados ↗</span></a>
</section>

<section class="content-grid">
    <div class="panel">
        <div class="panel-heading"><div><div class="eyebrow">DIÁRIO DE CAMPO</div><h2>Batalhas recentes</h2></div><a class="text-link" href="batalhas.php">Ver todas <span>→</span></a></div>
        <?php if ($recentes->num_rows): ?>
            <div class="recent-list">
            <?php while ($row = $recentes->fetch_assoc()): ?>
                <div class="recent-item">
                    <span class="recent-mark <?= $row['resultado'] === 'Vitória' ? 'mark-win' : ($row['resultado'] === 'Derrota' ? 'mark-loss' : 'mark-draw') ?>"><?= $row['resultado'] === 'Vitória' ? '✦' : ($row['resultado'] === 'Derrota' ? '×' : '↔') ?></span>
                    <div class="recent-main"><strong><?= e($row['pokemon_nome']) ?> <span>vs</span> <?= e($row['adversario']) ?></strong><small><?= date('d/m/Y', strtotime($row['data_batalha'])) ?></small></div>
                    <span class="result-pill <?= $row['resultado'] === 'Vitória' ? 'pill-win' : ($row['resultado'] === 'Derrota' ? 'pill-loss' : 'pill-draw') ?>"><?= e($row['resultado']) ?></span>
                </div>
            <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-state"><span>⚔</span><p>Nenhuma batalha por aqui ainda.</p><a href="batalhas.php">Registre a primeira →</a></div>
        <?php endif; ?>
    </div>
    <aside class="quick-panel">
        <div class="eyebrow">ATALHOS DA JORNADA</div>
        <h2>O que vamos fazer?</h2>
        <a href="treinadores.php?novo=1"><span class="quick-icon quick-red">♙</span><span><strong>Novo treinador</strong><small>Adicionar perfil à arena</small></span><b>→</b></a>
        <a href="pokemons.php?novo=1"><span class="quick-icon quick-yellow">✦</span><span><strong>Cadastrar Pokémon</strong><small>Montar seu time ideal</small></span><b>→</b></a>
        <a href="batalhas.php?novo=1"><span class="quick-icon quick-blue">⚔</span><span><strong>Registrar batalha</strong><small>Guardar mais uma história</small></span><b>→</b></a>
    </aside>
</section>
<?php render_footer(); ?>
