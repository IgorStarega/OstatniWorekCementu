<?php require BASE_PATH . '/app/views/booking/_steps.php'; ?>

<h2>Wybierz godzinę</h2>
<p class="text-muted">
    Krok 4 z 5. <?= htmlspecialchars($booking['date'], ENT_QUOTES, 'UTF-8') ?>,
    usługa: <strong><?= htmlspecialchars($service['name'], ENT_QUOTES, 'UTF-8') ?></strong>,
    pracownik: <strong><?= htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name'], ENT_QUOTES, 'UTF-8') ?></strong>.
</p>

<?php foreach ($errors as $error): ?>
    <p role="alert" class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
<?php endforeach; ?>

<?php if (count($slots) === 1): ?>
    <div class="alert alert-info" role="status">
        To jedyny dostępny termin w tym dniu.
    </div>
<?php endif; ?>

<form method="post" action="/?page=booking&amp;step=time">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">

    <div class="slot-grid mb-4">
        <?php foreach ($slots as $slot): ?>
            <label class="slot-option">
                <input class="form-check-input" type="radio" name="start_time"
                       value="<?= htmlspecialchars($slot, ENT_QUOTES, 'UTF-8') ?>" required
                    <?= $booking['start_time'] === $slot ? 'checked' : '' ?>>
                <span class="slot-option-time"><?= htmlspecialchars($slot, ENT_QUOTES, 'UTF-8') ?></span>
            </label>
        <?php endforeach; ?>
    </div>

    <div class="booking-actions">
        <a class="btn btn-outline-light" href="/?page=booking&amp;step=date&amp;back=1">Wstecz</a>
        <button type="submit" class="btn btn-primary">Dalej: potwierdzenie</button>
    </div>
</form>
