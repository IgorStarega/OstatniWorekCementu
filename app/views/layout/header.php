<?php
$config = $config ?? ($GLOBALS['config'] ?? []);
$currentPage = $_GET['page'] ?? 'home';
if (!is_string($currentPage)) {
    $currentPage = '';
}
$isAdminPage = strpos($currentPage, 'admin-') === 0;
?>
<!DOCTYPE html>
<html lang="pl" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'System Rezerwacji') ?> — System Rezerwacji Usług Budowlanych</title>
    <link rel="stylesheet" href="/vendor/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="/css/style.css">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
</head>
<body>
    <header class="site-header">
        <nav class="navbar navbar-expand-lg" aria-label="Nawigacja główna">
            <div class="container-xxl">
                <a class="navbar-brand site-brand" href="/?page=home">
                    <span class="site-brand-mark" aria-hidden="true">OWC</span>
                    <span class="site-brand-text">
                        <strong>Ostatni Worek Cementu</strong>
                        <small>System rezerwacji usług</small>
                    </span>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
                        aria-controls="mainNav" aria-expanded="false" aria-label="Przełącz nawigację">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="mainNav">
                    <ul class="navbar-nav site-nav ms-auto align-items-lg-center gap-lg-1">
                        <li class="nav-item">
                            <a class="nav-link<?= $currentPage === 'home' ? ' is-active' : '' ?>" href="/?page=home">Strona główna</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link<?= in_array($currentPage, ['offer', 'service'], true) ? ' is-active' : '' ?>" href="/?page=offer">Oferta</a>
                        </li>
                        <?php if (currentUserRole() === 'klient'): ?>
                            <li class="nav-item">
                                <a class="nav-link<?= $currentPage === 'my-reservations' || $currentPage === 'reservation' ? ' is-active' : '' ?>" href="/?page=my-reservations">Moje rezerwacje</a>
                            </li>
                        <?php elseif (currentUserRole() === 'pracownik'): ?>
                            <li class="nav-item">
                                <a class="nav-link<?= $currentPage === 'dashboard' ? ' is-active' : '' ?>" href="/?page=dashboard">Panel pracownika</a>
                            </li>
                        <?php endif; ?>
                        <?php if (currentUserRole() === 'admin'): ?>
                            <li class="nav-item">
                                <a class="nav-link<?= $isAdminPage ? ' is-active' : '' ?>" href="/?page=admin-dashboard">Panel administratora</a>
                            </li>
                        <?php endif; ?>
                        <?php if (currentUserId() !== null): ?>
                            <li class="nav-item">
                                <span class="navbar-text">
                                    Zalogowano jako: <strong><?= htmlspecialchars(currentUserFirstName($config), ENT_QUOTES, 'UTF-8') ?></strong>
                                    <span class="badge text-bg-secondary"><?= htmlspecialchars($_SESSION['role'] ?? 'klient', ENT_QUOTES, 'UTF-8') ?></span>
                                </span>
                            </li>
                            <li class="nav-item">
                                <form method="post" action="/?page=logout" class="logout-form">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                                    <button type="submit" class="btn btn-outline-light btn-sm">Wyloguj się</button>
                                </form>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link" href="/?page=login">Logowanie</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="/?page=register">Rejestracja</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
    <main class="site-main <?= htmlspecialchars($pageClass ?? '', ENT_QUOTES, 'UTF-8') ?>">
        <?php $globalFlash = function_exists('flash_pull') ? flash_pull() : null; ?>
        <?php if ($globalFlash !== null): ?>
            <?php $flashRole = $globalFlash['type'] === 'status' ? 'status' : 'alert'; ?>
            <p role="<?= htmlspecialchars($flashRole, ENT_QUOTES, 'UTF-8') ?>"
               aria-live="<?= $flashRole === 'status' ? 'polite' : 'assertive' ?>"
               class="alert <?= $flashRole === 'status' ? 'alert-success' : 'alert-danger' ?> mb-4">
                <?= htmlspecialchars($globalFlash['message'], ENT_QUOTES, 'UTF-8') ?>
            </p>
        <?php endif; ?>
