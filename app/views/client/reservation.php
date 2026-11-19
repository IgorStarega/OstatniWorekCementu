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
<p>
    <span class="badge <?= htmlspecialchars($badgeClass, ENT_QUOTES, 'UTF-8') ?>">
        <?= htmlspecialchars($reservation['status'], ENT_QUOTES, 'UTF-8') ?>
    </span>
</p>

<div class="card mb-4">
    <div class="card-body">
        <dl class="row mb-0">
            <dt class="col-sm-4">Usługa</dt>
            <dd class="col-sm-8"><?= htmlspecialchars($reservation['service_name'], ENT_QUOTES, 'UTF-8') ?></dd>

            <dt class="col-sm-4">Pracownik</dt>
            <dd class="col-sm-8"><?= htmlspecialchars($reservation['employee_name'], ENT_QUOTES, 'UTF-8') ?></dd>

            <dt class="col-sm-4">Data</dt>
            <dd class="col-sm-8"><?= htmlspecialchars(date('d.m.Y', strtotime((string) $reservation['reservation_date'])), ENT_QUOTES, 'UTF-8') ?></dd>

            <dt class="col-sm-4">Godzina</dt>
            <dd class="col-sm-8">
                <?= htmlspecialchars(substr((string) $reservation['start_time'], 0, 5), ENT_QUOTES, 'UTF-8') ?>–<?= htmlspecialchars(substr((string) $reservation['end_time'], 0, 5), ENT_QUOTES, 'UTF-8') ?>
            </dd>

            <dt class="col-sm-4">Cena</dt>
            <dd class="col-sm-8"><?= formatPrice($reservation['final_price']) ?></dd>

            <?php if ((string) ($reservation['comment'] ?? '') !== ''): ?>
                <dt class="col-sm-4">Komentarz</dt>
                <dd class="col-sm-8"><?= nl2br(htmlspecialchars($reservation['comment'], ENT_QUOTES, 'UTF-8')) ?></dd>
            <?php endif; ?>
        </dl>
    </div>
</div>

<h3>Historia statusów</h3>
<?php if ($history === []): ?>
    <p class="text-muted">Brak wpisów historii.</p>
<?php else: ?>
    <div class="table-responsive mb-4">
        <table class="table table-sm align-middle">
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
                        <td><?= htmlspecialchars((string) $entry['changed_at'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <?= htmlspecialchars((string) ($entry['old_status'] ?? '—'), ENT_QUOTES, 'UTF-8') ?>
                            &rarr;
                            <?= htmlspecialchars((string) $entry['new_status'], ENT_QUOTES, 'UTF-8') ?>
                        </td>
                        <td><?= htmlspecialchars((string) ($entry['changed_by_name'] ?? 'System'), ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<div class="d-flex flex-wrap gap-2">
    <a class="btn btn-outline-light" style="min-height:44px" href="/?page=my-reservations">Wróć do rezerwacji</a>

    <?php if ($canCancel): ?>
        <form method="post" action="/?page=reservation">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="action" value="cancel">
            <input type="hidden" name="id" value="<?= (int) $reservation['id'] ?>">
            <button type="submit" class="btn btn-outline-danger" style="min-height:44px">Anuluj rezerwację</button>
        </form>
    <?php endif; ?>
</div>
