<?php require BASE_PATH . '/app/views/booking/_steps.php'; ?>

<h2>Potwierdź rezerwację</h2>
<p class="text-muted">Krok 5 z 5. Sprawdź dane przed zapisem.</p>

<?php foreach ($errors as $error): ?>
    <p role="alert" class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
<?php endforeach; ?>

<div class="booking-summary mb-4">
    <dl class="row mb-0">
        <dt class="col-sm-4">Usługa</dt>
        <dd class="col-sm-8"><?= htmlspecialchars($service['name'], ENT_QUOTES, 'UTF-8') ?></dd>

        <dt class="col-sm-4">Cena</dt>
        <dd class="col-sm-8"><?= formatPrice($service['price']) ?></dd>

        <dt class="col-sm-4">Czas trwania</dt>
        <dd class="col-sm-8"><?= formatDuration((int) $service['duration_minutes']) ?></dd>

        <dt class="col-sm-4">Pracownik</dt>
        <dd class="col-sm-8"><?= htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name'], ENT_QUOTES, 'UTF-8') ?></dd>

        <dt class="col-sm-4">Data</dt>
        <dd class="col-sm-8"><?= htmlspecialchars($booking['date'], ENT_QUOTES, 'UTF-8') ?></dd>

        <dt class="col-sm-4">Godzina</dt>
        <dd class="col-sm-8"><?= htmlspecialchars($booking['start_time'], ENT_QUOTES, 'UTF-8') ?></dd>
    </dl>
</div>

<form method="post" action="/?page=booking&amp;step=confirm">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">

    <div class="mb-3">
        <label class="form-label" for="booking-comment">Uwagi do zlecenia (opcjonalnie)</label>
        <textarea class="form-control" id="booking-comment" name="comment" rows="4" maxlength="500"
                  placeholder="Krótki opis prac lub dodatkowe informacje"><?= htmlspecialchars($booking['comment'], ENT_QUOTES, 'UTF-8') ?></textarea>
        <div class="form-text">Maksymalnie 500 znaków.</div>
    </div>

    <div class="booking-actions">
        <a class="btn btn-outline-light" href="/?page=booking&amp;step=time&amp;back=1">Wstecz</a>
        <button type="submit" class="btn btn-primary">Zarezerwuj</button>
    </div>
</form>
