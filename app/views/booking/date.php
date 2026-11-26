<?php require BASE_PATH . '/app/views/booking/_steps.php'; ?>

<h2>Wybierz datę</h2>
<p class="text-muted">
    Krok 3 z 5. Usługa: <strong><?= htmlspecialchars($service['name'], ENT_QUOTES, 'UTF-8') ?></strong>,
    pracownik: <strong><?= htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name'], ENT_QUOTES, 'UTF-8') ?></strong>.
</p>

<?php foreach ($errors as $error): ?>
    <p role="alert" class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
<?php endforeach; ?>

<?php if ($calendar !== []): ?>
    <div class="calendar-header">
        <a class="btn btn-outline-light btn-sm"
           href="/?page=booking&amp;step=date&amp;month=<?= htmlspecialchars($calendar['prev'], ENT_QUOTES, 'UTF-8') ?>"
           aria-label="Poprzedni miesiąc">&larr;</a>
        <h3 class="h4 mb-0"><?= htmlspecialchars($calendar['label'], ENT_QUOTES, 'UTF-8') ?></h3>
        <a class="btn btn-outline-light btn-sm"
           href="/?page=booking&amp;step=date&amp;month=<?= htmlspecialchars($calendar['next'], ENT_QUOTES, 'UTF-8') ?>"
           aria-label="Następny miesiąc">&rarr;</a>
    </div>

    <div class="admin-table-wrap mb-4">
        <table class="calendar">
            <thead>
                <tr>
                    <th scope="col">Pon</th>
                    <th scope="col">Wt</th>
                    <th scope="col">Śr</th>
                    <th scope="col">Czw</th>
                    <th scope="col">Pt</th>
                    <th scope="col">Sob</th>
                    <th scope="col">Ndz</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($calendar['weeks'] as $week): ?>
                    <tr>
                        <?php foreach ($week as $cell): ?>
                            <td class="calendar-cell<?= $cell !== null && $cell['today'] ? ' is-today' : '' ?>">
                                <?php if ($cell === null): ?>
                                    <span class="calendar-day is-empty" aria-hidden="true"></span>
                                <?php elseif ($cell['status'] === 'free'): ?>
                                    <a class="calendar-day is-free"
                                       href="/?page=booking&amp;step=date&amp;pick=<?= htmlspecialchars($cell['date'], ENT_QUOTES, 'UTF-8') ?>&amp;month=<?= htmlspecialchars($calendar['month'], ENT_QUOTES, 'UTF-8') ?>">
                                        <span class="calendar-day-number"><?= (int) $cell['day'] ?></span>
                                        <span class="badge text-bg-success"><?= (int) $cell['slots'] ?></span>
                                    </a>
                                <?php else: ?>
                                    <span class="calendar-day is-<?= htmlspecialchars($cell['status'], ENT_QUOTES, 'UTF-8') ?>">
                                        <span class="calendar-day-number"><?= (int) $cell['day'] ?></span>
                                        <span class="badge text-bg-secondary"><?= (int) $cell['slots'] ?></span>
                                    </span>
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <p class="empty-state">Kalendarz jest chwilowo niedostępny. Wybierz datę ręcznie w formularzu poniżej.</p>
<?php endif; ?>

<form method="post" action="/?page=booking&amp;step=date" class="booking-form">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">

    <div class="mb-3">
        <label class="form-label" for="booking-date">Data wizyty</label>
        <input class="form-control" id="booking-date" type="date" name="date" required
               min="<?= htmlspecialchars(date('Y-m-d'), ENT_QUOTES, 'UTF-8') ?>"
               value="<?= htmlspecialchars($booking['date'], ENT_QUOTES, 'UTF-8') ?>">
        <div class="form-text">Wybierz dzień z kalendarza albo wpisz datę ręcznie.</div>
    </div>

    <div class="booking-actions">
        <a class="btn btn-outline-light" href="/?page=booking&amp;step=employee&amp;back=1">Wstecz</a>
        <button type="submit" class="btn btn-primary">Pokaż godziny</button>
    </div>
</form>
