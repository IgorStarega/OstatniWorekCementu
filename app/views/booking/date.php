<?php require BASE_PATH . '/app/views/booking/_steps.php'; ?>

<h2>Wybierz datę</h2>
<p class="text-muted">
    Krok 3 z 5. Usługa: <strong><?= htmlspecialchars($service['name'], ENT_QUOTES, 'UTF-8') ?></strong>,
    pracownik: <strong><?= htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name'], ENT_QUOTES, 'UTF-8') ?></strong>.
</p>

<form method="post" action="/?page=booking&amp;step=date" class="mb-3">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
    <input type="hidden" name="action" value="select">

    <div class="mb-3">
        <label class="form-label" for="booking-date">Data wizyty</label>
        <input class="form-control" id="booking-date" type="date" name="date" required
               min="<?= htmlspecialchars(date('Y-m-d'), ENT_QUOTES, 'UTF-8') ?>"
               max="<?= htmlspecialchars(date('Y-m-d', strtotime('+60 days')), ENT_QUOTES, 'UTF-8') ?>"
               value="<?= htmlspecialchars($booking['date'], ENT_QUOTES, 'UTF-8') ?>">
    </div>

    <button type="submit" class="btn btn-primary" style="min-height:44px">Pokaż godziny</button>
</form>

<form method="post" action="/?page=booking&amp;step=date">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
    <input type="hidden" name="action" value="back">
    <input type="hidden" name="to" value="employee">
    <button type="submit" class="btn btn-outline-light" style="min-height:44px">Wstecz</button>
</form>
