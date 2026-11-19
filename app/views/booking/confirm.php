<?php require BASE_PATH . '/app/views/booking/_steps.php'; ?>

<h2>Potwierdź rezerwację</h2>
<p class="text-muted">Krok 5 z 5. Sprawdź dane przed zapisem.</p>

<div class="card mb-3">
    <div class="card-body">
        <dl class="row mb-0">
            <dt class="col-sm-4">Usługa</dt>
            <dd class="col-sm-8"><?= htmlspecialchars($service['name'], ENT_QUOTES, 'UTF-8') ?></dd>

            <dt class="col-sm-4">Czas trwania</dt>
            <dd class="col-sm-8"><?= formatDuration((int) $service['duration_minutes']) ?></dd>

            <dt class="col-sm-4">Cena</dt>
            <dd class="col-sm-8"><?= formatPrice($service['price']) ?></dd>

            <dt class="col-sm-4">Pracownik</dt>
            <dd class="col-sm-8"><?= htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name'], ENT_QUOTES, 'UTF-8') ?></dd>

            <dt class="col-sm-4">Data</dt>
            <dd class="col-sm-8"><?= htmlspecialchars(date('d.m.Y', strtotime($booking['date'])), ENT_QUOTES, 'UTF-8') ?></dd>

            <dt class="col-sm-4">Godzina</dt>
            <dd class="col-sm-8"><?= htmlspecialchars($booking['start_time'], ENT_QUOTES, 'UTF-8') ?></dd>
        </dl>
    </div>
</div>

<form method="post" action="/?page=booking&amp;step=confirm" class="mb-3">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
    <input type="hidden" name="action" value="create">

    <div class="mb-3">
        <label class="form-label" for="booking-comment">Uwagi do zlecenia (opcjonalnie)</label>
        <textarea class="form-control" id="booking-comment" name="comment" rows="3"
                  maxlength="500"><?= htmlspecialchars($booking['comment'], ENT_QUOTES, 'UTF-8') ?></textarea>
    </div>

    <button type="submit" class="btn btn-primary" style="min-height:44px">Potwierdź rezerwację</button>
</form>

<form method="post" action="/?page=booking&amp;step=confirm">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
    <input type="hidden" name="action" value="back">
    <input type="hidden" name="to" value="time">
    <button type="submit" class="btn btn-outline-light" style="min-height:44px">Wstecz</button>
</form>
