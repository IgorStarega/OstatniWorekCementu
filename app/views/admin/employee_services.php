<div class="admin-toolbar">
    <div>
        <h2>Przypisania usług</h2>
        <p class="text-muted mb-0">Wybierz pracownika i zaznacz usługi, które może wykonywać.</p>
    </div>
    <a class="btn btn-outline-light btn-sm" href="/?page=admin-employees">Wróć do pracowników</a>
</div>

<?php foreach ($errors as $error): ?>
    <p role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
<?php endforeach; ?>

<?php if ($employees === []): ?>
    <div class="admin-section">
        <p class="admin-empty mb-0">Nie ma jeszcze pracowników. Najpierw dodaj pracownika w module
            <a href="/?page=admin-employees">Pracownicy</a>.</p>
    </div>
<?php else: ?>
    <div class="admin-section">
        <h3>Wybierz pracownika</h3>
        <form method="get" action="/" class="admin-form">
            <input type="hidden" name="page" value="admin-employee-services">
            <div class="row align-items-end">
                <div class="col-md-8">
                    <label class="form-label" for="assignment-employee">Pracownik</label>
                    <select class="form-select" id="assignment-employee" name="employee_id">
                        <?php foreach ($employees as $employee): ?>
                            <option value="<?= (int) $employee['id'] ?>"
                                <?= $selectedEmployeeId === (int) $employee['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars((string) $employee['first_name'] . ' ' . (string) $employee['last_name'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-outline-light mt-md-0 mt-2">Pokaż przypisania</button>
                </div>
            </div>
        </form>
    </div>

    <?php if ($selectedEmployee !== null): ?>
        <div class="admin-section">
            <h3>
                Usługi pracownika:
                <?= htmlspecialchars((string) $selectedEmployee['first_name'] . ' ' . (string) $selectedEmployee['last_name'], ENT_QUOTES, 'UTF-8') ?>
            </h3>
            <p class="text-muted">
                Zaznaczonych usług: <strong><?= count($assignedServiceIds) ?></strong> z <?= count($services) ?>.
            </p>

            <?php if ($services === []): ?>
                <p class="admin-empty mb-0">Brak usług w bazie. Dodaj je w module
                    <a href="/?page=admin-services">Usługi</a>.</p>
            <?php else: ?>
                <form method="post" action="/?page=admin-employee-services" class="admin-form">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="action" value="sync">
                    <input type="hidden" name="employee_id" value="<?= (int) $selectedEmployeeId ?>">

                    <div class="row g-2">
                        <?php foreach ($services as $service): ?>
                            <?php $isAssigned = in_array((int) $service['id'], $assignedServiceIds, true); ?>
                            <div class="col-md-6">
                                <div class="form-check admin-check-card">
                                    <input class="form-check-input" type="checkbox"
                                           id="service-<?= (int) $service['id'] ?>"
                                           name="service_ids[]"
                                           value="<?= (int) $service['id'] ?>"
                                        <?= $isAssigned ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="service-<?= (int) $service['id'] ?>">
                                        <strong><?= htmlspecialchars((string) $service['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                                        <br>
                                        <small class="text-muted">
                                            <?= htmlspecialchars((string) $service['category_name'], ENT_QUOTES, 'UTF-8') ?>
                                            · <?= htmlspecialchars(number_format((float) $service['price'], 2, '.', ''), ENT_QUOTES, 'UTF-8') ?> zł
                                            · <?= (int) $service['duration_minutes'] ?> min
                                        </small>
                                    </label>
                                    <?php if ((int) $service['is_active'] !== 1): ?>
                                        <span class="badge-status is-inactive ms-2">nieaktywna</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <button type="submit" class="btn btn-primary mt-3">Zapisz przypisania</button>
                </form>
            <?php endif; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>
