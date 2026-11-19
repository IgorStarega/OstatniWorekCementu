<?php require BASE_PATH . '/app/views/booking/_steps.php'; ?>

<h2>Wybierz godzinę</h2>
<p class="text-muted">
    Krok 4 z 5. <?= htmlspecialchars(date('d.m.Y', strtotime($booking['date'])), ENT_QUOTES, 'UTF-8') ?>,
    usługa: <strong><?= htmlspecialchars($service['name'], ENT_QUOTES, 'UTF-8') ?></strong>,
    pracownik: <strong><?= htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name'], ENT_QUOTES, 'UTF-8') ?></strong>.
</p>

<?php if ($slots === []): ?>
    <div class="alert alert-warning" role="alert">
        Brak wolnych terminów w tym dniu, wybierz inny.
    </div>
    <a class="btn btn-outline-light mb-3" href="/?page=booking&amp;step=date">Wybierz inną datę</a>
<?php else: ?>
    <?php if (count($slots) === 1): ?>
        <div class="alert alert-info" role="status">
            To jedyny dostępny termin tego dnia dla tej usługi.
        </div>
    <?php endif; ?>

    <form method="post" action="/?page=booking&amp;step=time" class="mb-3">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="action" value="select">

        <div class="row g-2 mb-3">
            <?php foreach ($slots as $slot): ?>
                <?php $slotId = 'slot-' . str_replace(':', '', $slot); ?>
                <div class="col-6 col-sm-4 col-md-3">
                    <input class="btn-check" type="radio" name="start_time"
                           id="<?= htmlspecialchars($slotId, ENT_QUOTES, 'UTF-8') ?>"
                           value="<?= htmlspecialchars($slot, ENT_QUOTES, 'UTF-8') ?>" required>
                    <label class="btn btn-outline-light w-100 d-flex align-items-center justify-content-center"
                           for="<?= htmlspecialchars($slotId, ENT_QUOTES, 'UTF-8') ?>" style="min-height:44px">
                        <?= htmlspecialchars($slot, ENT_QUOTES, 'UTF-8') ?>
                    </label>
                </div>
            <?php endforeach; ?>
        </div>

        <button type="submit" class="btn btn-primary" style="min-height:44px">Dalej: potwierdzenie</button>
    </form>
<?php endif; ?>

<form method="post" action="/?page=booking&amp;step=time">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
    <input type="hidden" name="action" value="back">
    <input type="hidden" name="to" value="date">
    <button type="submit" class="btn btn-outline-light" style="min-height:44px">Wstecz</button>
</form>
