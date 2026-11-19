<?php require BASE_PATH . '/app/views/booking/_steps.php'; ?>

<h2>Wybierz usługę</h2>
<p class="text-muted">Krok 1 z 5. Wybierz usługę, którą chcesz zarezerwować.</p>

<?php foreach ($errors as $error): ?>
    <p role="alert" class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
<?php endforeach; ?>

<?php if ($services === []): ?>
    <div class="empty-state">
        <p class="mb-2">Na razie nie ma żadnych aktywnych usług.</p>
        <a class="btn btn-outline-light" href="/?page=offer">Wróć do oferty</a>
    </div>
<?php else: ?>
    <form method="post" action="/?page=booking&amp;step=service">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">

        <div class="d-grid gap-2 mb-4">
            <?php foreach ($services as $service): ?>
                <label class="booking-choice">
                    <input class="form-check-input" type="radio" name="service_id"
                           value="<?= (int) $service['id'] ?>" required
                        <?= (int) $booking['service_id'] === (int) $service['id'] ? 'checked' : '' ?>>
                    <span class="booking-choice-body">
                        <strong><?= htmlspecialchars($service['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                        <span class="booking-choice-meta">
                            <?= htmlspecialchars($service['category_name'], ENT_QUOTES, 'UTF-8') ?>
                            · <?= formatDuration((int) $service['duration_minutes']) ?>
                            · <?= formatPrice($service['price']) ?>
                        </span>
                    </span>
                </label>
            <?php endforeach; ?>
        </div>

        <div class="booking-actions">
            <a class="btn btn-outline-light" href="/?page=offer">Wróć do oferty</a>
            <button type="submit" class="btn btn-primary">Dalej: pracownik</button>
        </div>
    </form>
<?php endif; ?>
