<?php require BASE_PATH . '/app/views/booking/_steps.php'; ?>

<h2>Wybierz datę</h2>
<p class="text-muted">
    Krok 3 z 5. Usługa: <strong><?= htmlspecialchars($service['name'], ENT_QUOTES, 'UTF-8') ?></strong>,
    pracownik: <strong><?= htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name'], ENT_QUOTES, 'UTF-8') ?></strong>.
</p>

<?php foreach ($errors as $error): ?>
    <p role="alert" class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
<?php endforeach; ?>

<form method="post" action="/?page=booking&amp;step=date" class="booking-form">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">

    <div class="mb-3">
        <label class="form-label" for="booking-date">Data wizyty</label>
        <input class="form-control" id="booking-date" type="date" name="date" required
               min="<?= htmlspecialchars(date('Y-m-d'), ENT_QUOTES, 'UTF-8') ?>"
               value="<?= htmlspecialchars($booking['date'], ENT_QUOTES, 'UTF-8') ?>">
        <div class="form-text">Wybierz dzień, w którym pracownik ma wolny termin.</div>
    </div>

    <div class="booking-actions">
        <a class="btn btn-outline-light" href="/?page=booking&amp;step=employee&amp;back=1">Wstecz</a>
        <button type="submit" class="btn btn-primary">Pokaż godziny</button>
    </div>
</form>
