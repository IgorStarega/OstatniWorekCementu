<h2><?= htmlspecialchars($service['name'], ENT_QUOTES, 'UTF-8') ?></h2>

<div class="card mb-4">
    <div class="card-body">
        <p class="mb-1">
            <span class="badge text-bg-secondary">
                <?= htmlspecialchars($service['category_name'], ENT_QUOTES, 'UTF-8') ?>
            </span>
        </p>
        <p class="card-text">
            <?= htmlspecialchars($service['description'], ENT_QUOTES, 'UTF-8') ?>
        </p>
        <p class="card-text mb-1">
            <small class="text-muted">
                Czas trwania: <?= formatDuration((int) $service['duration_minutes']) ?>
            </small>
        </p>
        <p class="card-text fw-bold fs-4 mb-3"><?= formatPrice($service['price']) ?></p>

        <?php
        // Rezerwację składa wyłącznie klient. Gość trafia na logowanie,
        // a pracownik i administrator widzą nieaktywny przycisk.
        $role = currentUserRole();
        ?>
        <?php if ($role === null): ?>
            <a class="btn btn-primary" href="/?page=login">Zarezerwuj</a>
        <?php elseif ($role === 'klient'): ?>
            <a class="btn btn-primary" href="/?page=booking&amp;service_id=<?= (int) $service['id'] ?>">Zarezerwuj</a>
        <?php else: ?>
            <button type="button" class="btn btn-primary" disabled aria-disabled="true">Rezerwacje składa klient</button>
        <?php endif; ?>
    </div>
</div>

<h3 class="h4 mt-4 mb-3">Wykonawcy usługi</h3>
<?php if ($employeeList === []): ?>
    <p class="text-muted">Ta usługa nie ma obecnie dostępnego pracownika.</p>
<?php else: ?>
    <ul class="list-group mb-4">
        <?php foreach ($employeeList as $employee): ?>
            <li class="list-group-item">
                <strong>
                    <?= htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name'], ENT_QUOTES, 'UTF-8') ?>
                </strong>
                <?php if ((string) $employee['description'] !== ''): ?>
                    — <?= htmlspecialchars($employee['description'], ENT_QUOTES, 'UTF-8') ?>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<p>
    <a href="/?page=offer">&larr; Wróć do oferty</a>
</p>
