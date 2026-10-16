<?php
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): void
{
    echo '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $sent = (string)($_POST['csrf_token'] ?? '');
    if ($sent === '' || !hash_equals(csrf_token(), $sent)) {
        http_response_code(419);
        exit('Formulário expirado. Volte à página e tente novamente.');
    }
}

function run_stmt(mysqli $conn, string $sql, string $types = '', array $values = []): mysqli_stmt
{
    $stmt = $conn->prepare($sql);
    if ($types !== '') {
        $references = [];
        foreach ($values as $key => &$value) {
            $references[$key] = &$value;
        }
        array_unshift($references, $types);
        call_user_func_array([$stmt, 'bind_param'], $references);
    }
    $stmt->execute();
    return $stmt;
}

function read_options(mysqli $conn, string $sql): array
{
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

function render_type(string $type): string
{
    return '<span class="type-tag type-' . e(mb_strtolower($type)) . '">' . e($type) . '</span>';
}

function render_result(string $result): string
{
    $class = $result === 'Vitória' ? 'pill-win' : ($result === 'Derrota' ? 'pill-loss' : 'pill-draw');
    return '<span class="result-pill ' . $class . '">' . e($result) . '</span>';
}
