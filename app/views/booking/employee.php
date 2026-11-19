<?php require BASE_PATH . '/app/views/booking/_steps.php'; ?>

<h2>Wybierz pracownika</h2>
<p class="text-muted">
    Krok 2 z 5. Usługa: <strong><?= htmlspecialchars($service['name'], ENT_QUOTES, 'UTF-8') ?></strong>
</p>

<?php if ($employees === []): ?>
    <div class="alert alert-warning" role="alert">
        Ta usługa nie ma obecnie dostępnego pracownika.
    </div>
    <a class="btn btn-outline-light" style="min-height:44px" href="/?page=booking&amp;step=service">Wybierz inną usługę</a>
<?php else: ?>
    <form method="post" action="/?page=booking&amp;step=employee">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="action" value="select">

        <?php foreach ($employees as $employee): ?>
            <div class="form-check border rounded p-3 mb-2">
                <input class="form-check-input" type="radio" name="employee_id"
                       id="employee-<?= (int) $employee['id'] ?>" value="<?= (int) $employee['id'] ?>" required>
                <label class="form-check-label" for="employee-<?= (int) $employee['id'] ?>">
                    <strong><?= htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name'], ENT_QUOTES, 'UTF-8') ?></strong>
                    <?php if ((string) ($employee['description'] ?? '') !== ''): ?>
                        <br><span class="text-muted"><?= htmlspecialchars($employee['description'], ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endif; ?>
                </label>
            </div>
        <?php endforeach; ?>

        <button type="submit" class="btn btn-primary" style="min-height:44px">Dalej: data</button>
    </form>
<?php endif; ?>

<form method="post" action="/?page=booking&amp;step=employee" class="mt-3">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
    <input type="hidden" name="action" value="back">
    <input type="hidden" name="to" value="service">
    <button type="submit" class="btn btn-outline-light" style="min-height:44px">Wstecz</button>
</form>
