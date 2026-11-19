<?php require BASE_PATH . '/app/views/booking/_steps.php'; ?>

<h2>Wybierz pracownika</h2>
<p class="text-muted">
    Krok 2 z 5. Usługa: <strong><?= htmlspecialchars($service['name'], ENT_QUOTES, 'UTF-8') ?></strong>
</p>

<?php foreach ($errors as $error): ?>
    <p role="alert" class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
<?php endforeach; ?>

<?php if ($employees === []): ?>
    <div class="empty-state">
        <p class="mb-2">Ta usługa nie ma obecnie dostępnego pracownika.</p>
        <a class="btn btn-outline-light" href="/?page=booking&amp;step=service&amp;back=1">Wybierz inną usługę</a>
    </div>
<?php else: ?>
    <form method="post" action="/?page=booking&amp;step=employee">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">

        <div class="d-grid gap-2 mb-4">
            <?php foreach ($employees as $employee): ?>
                <label class="booking-choice">
                    <input class="form-check-input" type="radio" name="employee_id"
                           value="<?= (int) $employee['id'] ?>" required
                        <?= (int) $booking['employee_id'] === (int) $employee['id'] ? 'checked' : '' ?>>
                    <span class="booking-choice-body">
                        <strong>
                            <?= htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name'], ENT_QUOTES, 'UTF-8') ?>
                        </strong>
                        <?php if ((string) ($employee['description'] ?? '') !== ''): ?>
                            <span class="booking-choice-meta">
                                <?= htmlspecialchars($employee['description'], ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        <?php endif; ?>
                    </span>
                </label>
            <?php endforeach; ?>
        </div>

        <div class="booking-actions">
            <a class="btn btn-outline-light" href="/?page=booking&amp;step=service&amp;back=1">Wstecz</a>
            <button type="submit" class="btn btn-primary">Dalej: data</button>
        </div>
    </form>
<?php endif; ?>
