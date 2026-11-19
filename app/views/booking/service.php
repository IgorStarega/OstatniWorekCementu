<?php require BASE_PATH . '/app/views/booking/_steps.php'; ?>

<h2>Wybierz usługę</h2>
<p class="text-muted">Krok 1 z 5. Wybierz usługę, którą chcesz zarezerwować.</p>

<?php if ($services === []): ?>
    <p>Na razie nie ma żadnych aktywnych usług.</p>
    <a class="btn btn-outline-light" style="min-height:44px" href="/?page=offer">Wróć do oferty</a>
<?php else: ?>
    <?php foreach ($services as $service): ?>
        <div class="card mb-3">
            <div class="card-body">
                <h3 class="h5 mb-1"><?= htmlspecialchars($service['name'], ENT_QUOTES, 'UTF-8') ?></h3>
                <p class="mb-1">
                    <span class="badge text-bg-secondary"><?= htmlspecialchars($service['category_name'], ENT_QUOTES, 'UTF-8') ?></span>
                </p>
                <p class="card-text"><?= htmlspecialchars($service['description'], ENT_QUOTES, 'UTF-8') ?></p>
                <p class="card-text mb-2">
                    <small class="text-muted">
                        Czas trwania: <?= formatDuration((int) $service['duration_minutes']) ?>
                        · Cena: <?= formatPrice($service['price']) ?>
                    </small>
                </p>

                <form method="post" action="/?page=booking&amp;step=service">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="action" value="select">
                    <input type="hidden" name="service_id" value="<?= (int) $service['id'] ?>">
                    <button type="submit" class="btn btn-primary" style="min-height:44px">Wybierz</button>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
