<?php
// Wspólna rama panelu administratora: sidebar + treść modułu.
$adminModules = [
    [
        'page'  => 'admin-dashboard',
        'label' => 'Dashboard',
        'icon'  => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>',
    ],
    [
        'page'  => 'admin-categories',
        'label' => 'Kategorie',
        'icon'  => '<path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>',
    ],
    [
        'page'  => 'admin-services',
        'label' => 'Usługi',
        'icon'  => '<path d="M14 6l4 4"/><path d="M3 21l6-6"/><path d="M13 5l6 6-3 3-6-6z"/><path d="M5 13l6 6"/>',
    ],
    [
        'page'  => 'admin-employees',
        'label' => 'Pracownicy',
        'icon'  => '<circle cx="9" cy="8" r="3"/><path d="M3 20a6 6 0 0 1 12 0"/><path d="M16 5a3 3 0 0 1 0 6"/><path d="M17 14a6 6 0 0 1 4 6"/>',
    ],
    [
        'page'  => 'admin-availability',
        'label' => 'Dostępność',
        'icon'  => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
    ],
    [
        'page'  => 'admin-users',
        'label' => 'Użytkownicy',
        'icon'  => '<circle cx="12" cy="8" r="3.5"/><path d="M5 20a7 7 0 0 1 14 0"/>',
    ],
    [
        'page'  => 'admin-reservations',
        'label' => 'Rezerwacje',
        'icon'  => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 3v4M16 3v4M3 10h18"/><path d="M8 14h.01M12 14h.01M16 14h.01M8 17h.01M12 17h.01"/>',
    ],
];
?>
<div class="admin-layout">
    <aside class="admin-sidebar" aria-label="Nawigacja panelu administratora">
        <p class="admin-sidebar-title mb-0">Panel administratora</p>
        <nav class="admin-nav">
            <?php foreach ($adminModules as $module): ?>
                <a href="/?page=<?= htmlspecialchars($module['page'], ENT_QUOTES, 'UTF-8') ?>"
                   class="<?= ($navKey ?? '') === $module['page'] ? 'is-active' : '' ?>"
                   <?= ($navKey ?? '') === $module['page'] ? 'aria-current="page"' : '' ?>>
                    <svg class="admin-nav-icon" viewBox="0 0 24 24" width="18" height="18" fill="none"
                         stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                         stroke-linejoin="round" aria-hidden="true"><?= $module['icon'] ?></svg>
                    <span><?= htmlspecialchars($module['label'], ENT_QUOTES, 'UTF-8') ?></span>
                </a>
            <?php endforeach; ?>
        </nav>
    </aside>

    <section class="admin-content">
        <?php if (!empty($flash['message'])): ?>
            <p role="<?= htmlspecialchars($flash['type'], ENT_QUOTES, 'UTF-8') ?>" class="mb-3">
                <?= htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8') ?>
            </p>
        <?php endif; ?>

        <?php require $adminView; ?>
    </section>
</div>
