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
        // Rezerwacja wymaga konta (BookingController powstanie w etapie 4),
        // więc gość trafia na logowanie, a zalogowany na razie wraca do oferty.
        $bookingHref = currentUserId() === null ? '/?page=login' : '/?page=offer';
        ?>
        <?php if ($employeeList === []): ?>
            <button type="button" class="btn btn-primary" disabled aria-disabled="true">Zarezerwuj</button>
        <?php else: ?>
            <a class="btn btn-primary" href="<?= htmlspecialchars($bookingHref, ENT_QUOTES, 'UTF-8') ?>">Zarezerwuj</a>
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
