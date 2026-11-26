<?php
$statusClasses = [
    'oczekująca'   => 'text-bg-warning',
    'potwierdzona' => 'text-bg-info',
    'zrealizowana' => 'text-bg-success',
    'anulowana'    => 'text-bg-secondary',
];
$badgeClass = $statusClasses[$reservation['status']] ?? 'text-bg-secondary';
?>
<h2>Rezerwacja #<?= (int) $reservation['id'] ?></h2>
<p class="text-muted">
    <span class="badge <?= htmlspecialchars($badgeClass, ENT_QUOTES, 'UTF-8') ?>">
        <?= htmlspecialchars($reservation['status'], ENT_QUOTES, 'UTF-8') ?>
    </span>
</p>

<div class="booking-summary mb-4">
    <dl class="row mb-0">
        <dt class="col-sm-4">Usługa</dt>
        <dd class="col-sm-8"><?= htmlspecialchars($reservation['service_name'], ENT_QUOTES, 'UTF-8') ?></dd>

        <dt class="col-sm-4">Pracownik</dt>
        <dd class="col-sm-8"><?= htmlspecialchars($reservation['employee_name'], ENT_QUOTES, 'UTF-8') ?></dd>

        <dt class="col-sm-4">Termin</dt>
        <dd class="col-sm-8">
            <?= htmlspecialchars(date('d.m.Y', strtotime((string) $reservation['reservation_date'])), ENT_QUOTES, 'UTF-8') ?>,
            <?= htmlspecialchars(substr((string) $reservation['start_time'], 0, 5), ENT_QUOTES, 'UTF-8') ?>–<?= htmlspecialchars(substr((string) $reservation['end_time'], 0, 5), ENT_QUOTES, 'UTF-8') ?>
        </dd>

        <dt class="col-sm-4">Czas trwania</dt>
        <dd class="col-sm-8"><?= formatDuration((int) $reservation['duration_minutes']) ?></dd>

        <dt class="col-sm-4">Cena</dt>
        <dd class="col-sm-8"><?= formatPrice($reservation['final_price']) ?></dd>

        <?php if ((string) ($reservation['comment'] ?? '') !== ''): ?>
            <dt class="col-sm-4">Uwagi</dt>
            <dd class="col-sm-8"><?= nl2br(htmlspecialchars($reservation['comment'], ENT_QUOTES, 'UTF-8')) ?></dd>
        <?php endif; ?>
    </dl>
</div>

<h3 class="h4 mb-3">Historia statusów</h3>
<?php if ($history === []): ?>
    <p class="text-muted">Brak wpisów historii.</p>
<?php else: ?>
    <div class="table-wrap admin-table-wrap mb-4">
        <table class="table table-sm align-middle mb-0">
            <thead>
                <tr>
                    <th scope="col">Data zmiany</th>
                    <th scope="col">Status</th>
                    <th scope="col">Autor</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($history as $entry): ?>
                    <tr>
                        <td data-label="Data zmiany">
                            <?= htmlspecialchars((string) $entry['changed_at'], ENT_QUOTES, 'UTF-8') ?>
                        </td>
                        <td data-label="Status">
                            <?= htmlspecialchars((string) ($entry['old_status'] ?? '—'), ENT_QUOTES, 'UTF-8') ?>
                            &rarr;
                            <?= htmlspecialchars((string) $entry['new_status'], ENT_QUOTES, 'UTF-8') ?>
                        </td>
                        <td data-label="Autor">
                            <?= htmlspecialchars((string) ($entry['changed_by_name'] ?? 'System'), ENT_QUOTES, 'UTF-8') ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<div class="booking-actions">
    <a class="btn btn-outline-light" href="/?page=my-reservations">Wróć do rezerwacji</a>

    <?php if ($canCancel): ?>
        <a class="btn btn-outline-light" href="/?page=reschedule&amp;id=<?= (int) $reservation['id'] ?>">
            Przełóż wizytę
        </a>
    <?php endif; ?>

    <?php if ($canCancel): ?>
        <form method="post" action="/?page=reservation">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="id" value="<?= (int) $reservation['id'] ?>">
            <input type="hidden" name="action" value="cancel">
            <button type="submit" class="btn btn-outline-danger">Anuluj rezerwację</button>
        </form>
    <?php endif; ?>
</div>
