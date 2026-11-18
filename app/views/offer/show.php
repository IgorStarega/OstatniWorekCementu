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
        <a class="btn btn-primary" href="<?= htmlspecialchars($bookingHref, ENT_QUOTES, 'UTF-8') ?>">Zarezerwuj</a>
    </div>
</div>

<p>
    <a href="/?page=offer">&larr; Wróć do oferty</a>
</p>
