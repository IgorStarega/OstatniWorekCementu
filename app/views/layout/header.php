<?php
$config = $config ?? ($GLOBALS['config'] ?? []);
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'System Rezerwacji') ?> — System Rezerwacji Usług Budowlanych</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <header>
        <h1>System Rezerwacji Usług Budowlanych</h1>
        <nav>
            <a href="/?page=home">Strona główna</a>
            <?php if (currentUserId() !== null): ?>
                <span>Zalogowano jako: <strong><?= htmlspecialchars(currentUserFirstName($config), ENT_QUOTES, 'UTF-8') ?></strong> (<?= htmlspecialchars($_SESSION['role'] ?? 'klient', ENT_QUOTES, 'UTF-8') ?>)</span>
                <form method="post" action="/?page=logout" class="logout-form">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit">Wyloguj się</button>
                </form>
            <?php else: ?>
                <a href="/?page=login">Logowanie</a>
                <a href="/?page=register">Rejestracja</a>
            <?php endif; ?>
        </nav>
    </header>
    <main>
