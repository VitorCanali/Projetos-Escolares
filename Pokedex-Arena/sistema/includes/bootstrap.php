<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../config.php';

function e($value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function require_login(): void
{
    if (empty($_SESSION['usuario'])) {
        header('Location: login.php');
        exit;
    }

    $lastAccess = (int)($_SESSION['ultimo_acesso'] ?? time());
    if (time() - $lastAccess > 1800) {
        $_SESSION = [];
        session_destroy();
        header('Location: login.php?expirou=1');
        exit;
    }

    $_SESSION['ultimo_acesso'] = time();
}

function render_header(string $title, string $active = ''): void
{
    $loggedIn = !empty($_SESSION['usuario']);
    $userName = $loggedIn ? e($_SESSION['usuario']['nome']) : '';
    ?>
    <!doctype html>
    <html lang="pt-BR">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= e($title) ?> · Pokédex Arena</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="css/style.css">
    </head>
    <body>
    <?php if ($loggedIn): ?>
        <header class="topbar">
            <a class="brand" href="principal.php" aria-label="Pokédex Arena, início">
                <span class="brand-ball" aria-hidden="true"><span></span></span>
                <span>POKÉDEX <b>ARENA</b></span>
            </a>
            <nav class="main-nav" aria-label="Navegação principal">
                <a class="<?= $active === 'home' ? 'active' : '' ?>" href="principal.php">Visão geral</a>
                <a class="<?= $active === 'treinadores' ? 'active' : '' ?>" href="treinadores.php">Treinadores</a>
                <a class="<?= $active === 'pokemons' ? 'active' : '' ?>" href="pokemons.php">Pokémons</a>
                <a class="<?= $active === 'batalhas' ? 'active' : '' ?>" href="batalhas.php">Batalhas</a>
            </nav>
            <div class="account">
                <span class="avatar"><?= e(mb_strtoupper(mb_substr($userName, 0, 1))) ?></span>
                <span class="account-name"><?= $userName ?></span>
                <a class="logout-link" href="logout.php">Sair <span aria-hidden="true">↗</span></a>
            </div>
        </header>
    <?php endif; ?>
    <main class="<?= $loggedIn ? 'page' : 'auth-page' ?>">
    <?php
}

function render_footer(): void
{
    ?>
    </main>
    <footer class="footer">POKÉDEX ARENA <span>·</span> Sua próxima jornada começa aqui.</footer>
    </body>
    </html>
    <?php
}

function redirect_to(string $page): void
{
    header('Location: ' . $page);
    exit;
}

function post_string(string $key): string
{
    return trim((string)($_POST[$key] ?? ''));
}

function flash(string $message, string $type = 'success'): void
{
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

function render_flash(): void
{
    if (empty($_SESSION['flash'])) {
        return;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    ?>
    <div class="notice notice-<?= e($flash['type']) ?>" role="status"><?= e($flash['message']) ?></div>
    <?php
}

function valid_id($value): int
{
    return filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;
}
