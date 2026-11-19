<?php
$currentDate = date('d.m.Y', strtotime((string) $reservation['reservation_date']));
$currentStart = substr((string) $reservation['start_time'], 0, 5);
$currentEnd = substr((string) $reservation['end_time'], 0, 5);
$pickedDate = date('d.m.Y', strtotime($date));
?>
<h2>Przełożenie wizyty #<?= (int) $reservation['id'] ?></h2>
<p class="text-muted">
    Usługa: <strong><?= htmlspecialchars($reservation['service_name'], ENT_QUOTES, 'UTF-8') ?></strong>,
    pracownik: <strong><?= htmlspecialchars($reservation['employee_name'], ENT_QUOTES, 'UTF-8') ?></strong>.
</p>

<div class="booking-summary mb-4">
    <dl class="row mb-0">
        <dt class="col-sm-4">Obecny termin</dt>
        <dd class="col-sm-8">
            <?= htmlspecialchars($currentDate, ENT_QUOTES, 'UTF-8') ?>,
            <?= htmlspecialchars($currentStart . '–' . $currentEnd, ENT_QUOTES, 'UTF-8') ?>
        </dd>
    </dl>
</div>

<?php foreach ($errors as $error): ?>
    <p role="alert" class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
<?php endforeach; ?>

<form method="get" action="/" class="booking-form mb-4">
    <input type="hidden" name="page" value="reschedule">
    <input type="hidden" name="id" value="<?= (int) $reservation['id'] ?>">

    <div class="mb-3">
        <label class="form-label" for="reschedule-date">Nowa data</label>
        <input class="form-control" id="reschedule-date" type="date" name="date" required
               min="<?= htmlspecialchars(date('Y-m-d'), ENT_QUOTES, 'UTF-8') ?>"
               value="<?= htmlspecialchars($date, ENT_QUOTES, 'UTF-8') ?>">
        <div class="form-text">Wybierz dzień, a poniżej pojawią się wolne godziny.</div>
    </div>

    <div class="booking-actions">
        <a class="btn btn-outline-light" href="/?page=reservation&amp;id=<?= (int) $reservation['id'] ?>">Wstecz</a>
        <button type="submit" class="btn btn-outline-light">Pokaż terminy</button>
    </div>
</form>

<h3 class="h4 mb-3">Wolne terminy — <?= htmlspecialchars($pickedDate, ENT_QUOTES, 'UTF-8') ?></h3>

<?php if ($slots === []): ?>
    <p class="alert alert-warning" role="status">
        Brak wolnych terminów w tym dniu. Wybierz inną datę.
    </p>
<?php else: ?>
    <form method="post" action="/?page=reschedule&amp;id=<?= (int) $reservation['id'] ?>">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="id" value="<?= (int) $reservation['id'] ?>">
        <input type="hidden" name="date" value="<?= htmlspecialchars($date, ENT_QUOTES, 'UTF-8') ?>">

        <div class="slot-grid mb-4">
            <?php foreach ($slots as $slot): ?>
                <label class="slot-option">
                    <input class="form-check-input" type="radio" name="start_time"
                           value="<?= htmlspecialchars($slot, ENT_QUOTES, 'UTF-8') ?>" required>
                    <span class="slot-option-time"><?= htmlspecialchars($slot, ENT_QUOTES, 'UTF-8') ?></span>
                </label>
            <?php endforeach; ?>
        </div>

        <div class="booking-actions">
            <a class="btn btn-outline-light" href="/?page=reservation&amp;id=<?= (int) $reservation['id'] ?>">Wstecz</a>
            <button type="submit" class="btn btn-primary">Zapisz nowy termin</button>
        </div>
    </form>
<?php endif; ?>
